<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceDeviceModel extends Model
{
    protected $table         = 'attendance_devices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'employee_id', 'device_uid', 'device_name', 'browser', 'os', 'user_agent', 'ip_address',
        'first_login_at', 'last_login_at', 'status', 'approved_by', 'approved_at', 'remarks',
    ];

    protected $validationRules = [
        'employee_id' => 'required|integer',
        'device_uid'  => 'required|max_length[100]',
    ];

    public function findByUid(int $employeeId, string $deviceUid): ?array
    {
        return $this->where('employee_id', $employeeId)->where('device_uid', $deviceUid)->first();
    }

    public function withEmployee()
    {
        return $this->select("attendance_devices.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.employee_code")
            ->join('employees e', 'e.id = attendance_devices.employee_id');
    }
}
