<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollAdjustmentModel extends Model
{
    protected $table         = 'payroll_adjustments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['payroll_run_item_id', 'type', 'label', 'amount', 'reason', 'created_by', 'created_at'];

    public function forRunItem(int $runItemId): array
    {
        return $this->where('payroll_run_item_id', $runItemId)->orderBy('id')->findAll();
    }
}
