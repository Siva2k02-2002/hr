<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceLocationModel extends Model
{
    protected $table          = 'attendance_locations';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = ['name', 'branch_id', 'latitude', 'longitude', 'radius_meters', 'address', 'status'];

    protected $validationRules = [
        'name'      => 'required|max_length[150]',
        'branch_id' => 'required|integer',
        'latitude'  => 'required|decimal',
        'longitude' => 'required|decimal',
    ];

    public function forBranch(int $branchId): array
    {
        return $this->where('branch_id', $branchId)->where('status', 'active')->findAll();
    }

    public function withBranch()
    {
        return $this->select('attendance_locations.*, b.name as branch_name')
            ->join('branches b', 'b.id = attendance_locations.branch_id');
    }
}
