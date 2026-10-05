<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollProfessionalTaxSettingModel extends Model
{
    protected $table         = 'payroll_professional_tax_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['state', 'min_gross', 'max_gross', 'tax_amount', 'effective_from', 'status'];

    protected $validationRules = [
        'state'          => 'required|max_length[50]',
        'min_gross'      => 'required|decimal',
        'tax_amount'     => 'required|decimal',
        'effective_from' => 'required|valid_date',
    ];

    public function slabsForState(string $state): array
    {
        return $this->where('state', $state)->where('status', 'active')->orderBy('min_gross')->findAll();
    }

    public function statesList(): array
    {
        return array_values(array_unique(array_column($this->select('state')->findAll(), 'state')));
    }

    /** The slab matching $grossAmount for $state — max_gross null means "and above". */
    public function slabFor(string $state, float $grossAmount): ?array
    {
        $query = $this->where('state', $state)->where('status', 'active')->where('min_gross <=', $grossAmount);
        $query->groupStart()->where('max_gross >=', $grossAmount)->orWhere('max_gross', null)->groupEnd();

        return $query->orderBy('min_gross', 'DESC')->first();
    }
}
