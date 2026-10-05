<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeBankAccountModel extends Model
{
    protected $table         = 'employee_bank_accounts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';
    protected $allowedFields = [
        'employee_id', 'account_holder_name', 'bank_name', 'branch_name', 'account_number', 'ifsc_code', 'upi_id', 'is_primary',
    ];

    protected $validationRules = [
        'employee_id'         => 'required|integer',
        'account_holder_name' => 'required|max_length[150]',
        'bank_name'           => 'required|max_length[150]',
        'account_number'      => 'required|alpha_numeric|max_length[30]',
        'ifsc_code'           => 'required|regex_match[/^[A-Z]{4}0[A-Z0-9]{6}$/]',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('is_primary', 'DESC')->findAll();
    }

    public function primaryFor(int $employeeId): ?array
    {
        return $this->where('employee_id', $employeeId)->where('is_primary', 1)->first();
    }
}
