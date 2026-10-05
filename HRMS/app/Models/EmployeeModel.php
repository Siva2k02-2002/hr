<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table          = 'employees';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_code', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'blood_group',
        'marital_status', 'nationality', 'aadhaar_number', 'pan_number', 'passport_number', 'driving_license_number',
        'mobile', 'alternate_mobile', 'personal_email', 'company_email',
        'branch_id', 'department_id', 'designation_id', 'reporting_manager_id',
        'employment_type', 'employment_category', 'shift', 'work_location',
        'date_of_joining', 'date_of_confirmation', 'probation_period_months', 'status',
        'photo_path', 'user_id', 'created_by', 'updated_by',
    ];

    /**
     * No is_unique here — same reason as DepartmentModel/UserModel: this model is
     * always constructed against the dynamic tenant connection, and is_unique
     * always checks the default DB group instead. Uniqueness (employee_code,
     * company_email, personal_email) is checked by hand in EmployeeService.
     */
    protected $validationRules = [
        'first_name'      => 'required|min_length[1]|max_length[100]',
        'last_name'       => 'required|min_length[1]|max_length[100]',
        'mobile'          => 'required|regex_match[/^[0-9]{10}$/]',
        'personal_email'  => 'permit_empty|valid_email|max_length[150]',
        'company_email'   => 'permit_empty|valid_email|max_length[150]',
        'aadhaar_number'  => 'permit_empty|regex_match[/^[0-9]{12}$/]',
        'pan_number'      => 'permit_empty|regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]$/]',
        'branch_id'       => 'required|integer',
        'department_id'   => 'required|integer',
        'designation_id'  => 'required|integer',
        'date_of_joining' => 'required|valid_date',
        'status'          => 'required|in_list[active,probation,notice_period,suspended,resigned,terminated,retired,absconded,relieved]',
    ];

    public function withRelations()
    {
        return $this->select("
                employees.*,
                b.name as branch_name,
                d.name as department_name,
                dg.name as designation_name,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name
            ")
            ->join('branches b', 'b.id = employees.branch_id')
            ->join('departments d', 'd.id = employees.department_id')
            ->join('designations dg', 'dg.id = employees.designation_id')
            ->join('employees m', 'm.id = employees.reporting_manager_id', 'left');
    }

    /**
     * Shared between EmployeesController::index() (paginated list) and
     * EmployeeExportController (unpaginated) so export always matches
     * whatever the list page's filter bar currently shows.
     */
    public function applyFilters(array $f): static
    {
        if (! empty($f['q'])) {
            $this->groupStart()
                ->like('employees.employee_code', $f['q'])
                ->orLike('employees.first_name', $f['q'])
                ->orLike('employees.last_name', $f['q'])
                ->orLike('employees.company_email', $f['q'])
                ->orLike('employees.personal_email', $f['q'])
                ->orLike('employees.mobile', $f['q'])
                ->orLike('employees.pan_number', $f['q'])
                ->orLike('employees.aadhaar_number', $f['q'])
                ->groupEnd();
        }
        foreach (['branch_id', 'department_id', 'designation_id', 'status', 'employment_type', 'gender', 'blood_group'] as $field) {
            if (! empty($f[$field])) {
                $this->where('employees.' . $field, $f[$field]);
            }
        }
        if (! empty($f['manager_id'])) {
            $this->where('employees.reporting_manager_id', $f['manager_id']);
        }
        if (! empty($f['joined_from'])) {
            $this->where('employees.date_of_joining >=', $f['joined_from']);
        }
        if (! empty($f['joined_to'])) {
            $this->where('employees.date_of_joining <=', $f['joined_to']);
        }

        return $this;
    }

    public function findByCode(string $code): ?array
    {
        return $this->where('employee_code', $code)->first();
    }

    public function fullName(array $employee): string
    {
        return implode(' ', array_filter([$employee['first_name'], $employee['middle_name'] ?? null, $employee['last_name']]));
    }

    /**
     * Walks the reporting chain upward from $managerId. True if $employeeId is
     * found in it — i.e. making $managerId report to $employeeId would create a
     * cycle. Called before every reporting_manager_id write (see EmployeeService).
     */
    public function wouldCreateCycle(int $employeeId, int $managerId): bool
    {
        $currentId = $managerId;
        $seen      = [];

        while ($currentId !== null) {
            if ($currentId === $employeeId) {
                return true;
            }
            if (isset($seen[$currentId])) {
                return true; // pre-existing cycle in the data — treat as blocked, not infinite loop
            }
            $seen[$currentId] = true;

            $row       = $this->select('reporting_manager_id')->find($currentId);
            $currentId = $row['reporting_manager_id'] ?? null;
        }

        return false;
    }
}
