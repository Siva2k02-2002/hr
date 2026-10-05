<?php

namespace App\Models;

use CodeIgniter\Model;

/** Staging table for the biometric-ready architecture — see AttendanceBiometricImportService. */
class AttendanceBiometricLogModel extends Model
{
    protected $table         = 'attendance_biometric_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'device_serial', 'employee_code', 'punch_time', 'punch_type', 'raw_payload', 'sync_status', 'synced_employee_id', 'created_at',
    ];

    public function pending(): array
    {
        return $this->where('sync_status', 'pending')->orderBy('punch_time')->findAll();
    }
}
