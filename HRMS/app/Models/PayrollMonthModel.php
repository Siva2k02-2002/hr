<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollMonthModel extends Model
{
    protected $table         = 'payroll_months';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'month', 'year', 'start_date', 'end_date', 'status', 'approved_by', 'approved_at', 'locked_by', 'locked_at', 'paid_at',
    ];

    public function forMonthYear(int $month, int $year): ?array
    {
        return $this->where('month', $month)->where('year', $year)->first();
    }

    /** Lazily creates the payroll_months row for $month/$year if it doesn't exist yet — first call for a period is what opens it. */
    public function resolveOrCreate(int $month, int $year, string $startDate, string $endDate): array
    {
        $row = $this->forMonthYear($month, $year);
        if ($row) {
            return $row;
        }

        $id = $this->insert([
            'month' => $month, 'year' => $year, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => 'draft',
        ], true);

        return $this->find($id);
    }

    /**
     * Row-locked read, only a real lock when called inside a transaction — closes the
     * duplicate-payroll-run race (audit finding PAY-02): see PayrollRunService::generate(),
     * which locks this row before checking/creating the active run for the month.
     */
    public function lockForUpdate(int $id): ?array
    {
        return $this->db->query('SELECT * FROM payroll_months WHERE id = ? FOR UPDATE', [$id])->getRowArray();
    }
}
