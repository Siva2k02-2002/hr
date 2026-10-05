<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeAddressModel extends Model
{
    protected $table         = 'employee_addresses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $allowedFields = [
        'employee_id', 'address_type', 'country', 'state', 'city', 'district', 'pincode', 'address_line1', 'address_line2',
    ];

    protected $validationRules = [
        'employee_id'   => 'required|integer',
        'address_type'  => 'required|in_list[permanent,current]',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('address_type')->findAll();
    }
}
