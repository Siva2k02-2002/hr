<?php

namespace App\Models;

use CodeIgniter\Model;

/** The rows of the Salary Structure Builder — always fully replaced (delete + reinsert) by SalaryStructureService::saveItems(), never patched field-by-field. */
class PayrollSalaryStructureItemModel extends Model
{
    protected $table         = 'payroll_salary_structure_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'salary_structure_id', 'salary_component_id', 'calculation_type', 'value', 'formula', 'is_editable', 'display_order',
    ];

    public function forStructure(int $structureId): array
    {
        return $this->select('payroll_salary_structure_items.*, c.name as component_name, c.code as component_code, c.type as component_type, c.status as component_status, c.is_taxable, c.pf_applicable, c.esi_applicable, c.percentage_of as component_percentage_of')
            ->join('payroll_salary_components c', 'c.id = payroll_salary_structure_items.salary_component_id')
            ->where('salary_structure_id', $structureId)
            ->orderBy('display_order')
            ->findAll();
    }

    public function deleteForStructure(int $structureId): void
    {
        $this->where('salary_structure_id', $structureId)->delete();
    }
}
