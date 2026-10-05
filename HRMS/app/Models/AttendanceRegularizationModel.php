<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceRegularizationModel extends Model
{
    protected $table         = 'attendance_regularizations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'employee_id', 'attendance_date', 'reason', 'requested_punch_in', 'requested_punch_out',
        'attachment_path', 'status', 'reviewed_by', 'reviewed_at', 'review_remarks', 'created_by',
    ];

    protected $validationRules = [
        'employee_id'     => 'required|integer',
        'attendance_date' => 'required|valid_date',
        'reason'          => 'required|max_length[255]',
    ];

    public function withEmployee()
    {
        return $this->select("attendance_regularizations.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code")
            ->join('employees e', 'e.id = attendance_regularizations.employee_id');
    }
}
