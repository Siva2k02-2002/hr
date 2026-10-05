<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollSalaryStructureModel extends Model
{
    protected $table          = 'payroll_salary_structures';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = ['name', 'description', 'effective_from', 'status', 'created_by', 'updated_by'];

    protected $validationRules = [
        'name'           => 'required|max_length[150]',
        'effective_from' => 'required|valid_date',
    ];

    public function applyFilters(array $f): static
    {
        if (! empty($f['q'])) {
            $this->like('name', $f['q']);
        }
        if (! empty($f['status'])) {
            $this->where('status', $f['status']);
        }

        return $this;
    }
}
