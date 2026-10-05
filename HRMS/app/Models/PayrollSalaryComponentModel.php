<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollSalaryComponentModel extends Model
{
    protected $table          = 'payroll_salary_components';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'name', 'code', 'type', 'calculation_type', 'percentage_of', 'formula', 'is_taxable',
        'pf_applicable', 'esi_applicable', 'display_order', 'status', 'created_by', 'updated_by',
    ];

    /** No is_unique — always bound to the dynamic tenant connection; code uniqueness checked by hand in SalaryComponentService. */
    protected $validationRules = [
        'name'             => 'required|max_length[100]',
        'code'             => 'required|max_length[30]',
        'type'             => 'required|in_list[earning,deduction]',
        'calculation_type' => 'required|in_list[fixed,percentage,formula]',
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
        if (! empty($f['type'])) {
            $this->where('type', $f['type']);
        }
        if (! empty($f['status'])) {
            $this->where('status', $f['status']);
        }

        return $this;
    }
}
