<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollRunModel extends Model
{
    protected $table         = 'payroll_runs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'payroll_month_id', 'run_type', 'status', 'total_employees', 'total_gross', 'total_net', 'total_pf',
        'total_esi', 'total_pt', 'total_tds', 'generated_by', 'generated_at', 'approved_by', 'approved_at',
        'locked_by', 'locked_at', 'paid_by', 'paid_at',
    ];

    /** The one non-cancelled run for a payroll month, if any — this is the "no duplicate payroll generation" check. */
    public function activeForMonth(int $payrollMonthId): ?array
    {
        return $this->where('payroll_month_id', $payrollMonthId)->where('status !=', 'cancelled')->orderBy('id', 'DESC')->first();
    }

    public function withMonth(): static
    {
        return $this->select('payroll_runs.*, m.month, m.year, m.start_date, m.end_date')
            ->join('payroll_months m', 'm.id = payroll_runs.payroll_month_id');
    }
}
