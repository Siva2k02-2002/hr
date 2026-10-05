<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveTypeModel extends Model
{
    protected $table          = 'leave_types';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'name', 'code', 'description', 'color', 'is_paid', 'annual_allocation', 'half_day_allowed',
        'attachment_required', 'medical_certificate_required', 'carry_forward_allowed', 'encashment_allowed',
        'attendance_status_map', 'sort_order', 'status', 'created_by', 'updated_by',
    ];

    /** No is_unique — this model is always bound to the dynamic tenant connection; see EmployeeModel's comment. */
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'code' => 'required|max_length[20]',
        'attendance_status_map' => 'required|in_list[leave,lop,work_from_home,on_duty]',
    ];

    public function findByCode(string $code): ?array
    {
        return $this->where('code', $code)->first();
    }

    public function applyFilters(array $f): static
    {
        if (! empty($f['q'])) {
            $this->groupStart()->like('name', $f['q'])->orLike('code', $f['q'])->groupEnd();
        }
        if (! empty($f['status'])) {
            $this->where('status', $f['status']);
        }
        if (isset($f['is_paid']) && $f['is_paid'] !== '') {
            $this->where('is_paid', $f['is_paid']);
        }

        return $this;
    }
}
