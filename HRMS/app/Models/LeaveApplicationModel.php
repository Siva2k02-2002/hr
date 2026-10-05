<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveApplicationModel extends Model
{
    protected $table          = 'leave_applications';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'leave_type_id', 'leave_policy_id', 'from_date', 'to_date', 'is_half_day', 'half_day_session',
        'total_days', 'reason', 'emergency_contact_name', 'emergency_contact_phone', 'attachment_path', 'is_emergency',
        'status', 'current_level', 'level1_approver_id', 'level1_status', 'level1_acted_by', 'level1_acted_at', 'level1_remarks',
        'level2_status', 'level2_acted_by', 'level2_acted_at', 'level2_remarks', 'balance_deducted', 'attendance_applied',
        'cancelled_by', 'cancelled_at', 'cancellation_reason', 'submitted_at', 'created_by', 'updated_by',
    ];

    protected $validationRules = [
        'employee_id'   => 'required|integer',
        'leave_type_id' => 'required|integer',
        'from_date'     => 'required|valid_date',
        'to_date'       => 'required|valid_date',
        'reason'        => 'required|max_length[500]',
        'status'        => 'required|in_list[draft,pending,approved,rejected,cancelled]',
    ];

    public function withEmployee()
    {
        return $this->select("
                leave_applications.*,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code, e.branch_id, e.department_id,
                lt.name as leave_type_name, lt.code as leave_type_code, lt.color as leave_type_color
            ")
            ->join('employees e', 'e.id = leave_applications.employee_id')
            ->join('leave_types lt', 'lt.id = leave_applications.leave_type_id');
    }

    public function applyFilters(array $f): static
    {
        if (! empty($f['employee_id'])) {
            $this->where('leave_applications.employee_id', $f['employee_id']);
        }
        if (! empty($f['leave_type_id'])) {
            $this->where('leave_applications.leave_type_id', $f['leave_type_id']);
        }
        if (! empty($f['status'])) {
            $this->where('leave_applications.status', $f['status']);
        }
        if (! empty($f['branch_id'])) {
            $this->where('e.branch_id', $f['branch_id']);
        }
        if (! empty($f['department_id'])) {
            $this->where('e.department_id', $f['department_id']);
        }
        if (! empty($f['manager_id'])) {
            $this->where('e.reporting_manager_id', $f['manager_id']);
        }
        if (! empty($f['date_from'])) {
            $this->where('leave_applications.from_date >=', $f['date_from']);
        }
        if (! empty($f['date_to'])) {
            $this->where('leave_applications.to_date <=', $f['date_to']);
        }
        if (! empty($f['level'])) {
            $this->where('leave_applications.current_level', $f['level']);
        }

        return $this;
    }

    /** Blocks a new/edited application from overlapping an existing pending/approved one for the same employee. */
    public function hasOverlap(int $employeeId, string $fromDate, string $toDate, ?int $excludeId = null): bool
    {
        $query = $this->where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('from_date <=', $toDate)
            ->where('to_date >=', $fromDate);

        if ($excludeId !== null) {
            $query->where('id !=', $excludeId);
        }

        return $query->countAllResults() > 0;
    }

    /** Row-locked read for LeaveBalanceService/LeaveApprovalService's mutation methods — only takes an actual lock inside a transaction. */
    public function lockForUpdate(int $id): ?array
    {
        return $this->db->query('SELECT * FROM leave_applications WHERE id = ? FOR UPDATE', [$id])->getRowArray();
    }

    /** $fyStart/$fyEnd are the financial year's actual date bounds (see leave_financial_year_bounds() in leave_helper.php) — never a bare calendar-year YEAR() match, since financial_year_start_month may not be January. */
    public function countForYear(int $employeeId, int $leaveTypeId, string $fyStart, string $fyEnd): int
    {
        return $this->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('from_date >=', $fyStart)
            ->where('from_date <=', $fyEnd)
            ->countAllResults();
    }
}
