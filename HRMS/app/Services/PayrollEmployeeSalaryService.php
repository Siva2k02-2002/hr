<?php

namespace App\Services;

use App\Models\PayrollEmployeeSalaryModel;
use RuntimeException;

/** Assignment history is append-only: assign() closes the previous active row's effective_to and inserts a new one — never overwrites an existing row's economics in place. */
class PayrollEmployeeSalaryService
{
    private PayrollEmployeeSalaryModel $assignments;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->assignments = new PayrollEmployeeSalaryModel(service('tenantContext')->db());
    }

    /** CTC (annual) is derived, never trusted from input: gross monthly x 12. */
    public function assign(int $employeeId, int $structureId, string $effectiveFrom, float $grossSalary): int
    {
        $ctc = round($grossSalary * 12, 2);

        $db = service('tenantContext')->db();
        $db->transStart();

        $current = $this->assignments->where('employee_id', $employeeId)->where('status', 'active')->orderBy('effective_from', 'DESC')->first();
        if ($current) {
            $closeDate = date('Y-m-d', strtotime($effectiveFrom . ' -1 day'));
            $this->assignments->update((int) $current['id'], ['effective_to' => $closeDate, 'status' => 'superseded']);
        }

        $status = $effectiveFrom > date('Y-m-d') ? 'scheduled' : 'active';
        $id     = $this->assignments->insert([
            'employee_id'         => $employeeId,
            'salary_structure_id' => $structureId,
            'effective_from'      => $effectiveFrom,
            'gross_salary'        => $grossSalary,
            'ctc'                 => $ctc,
            'status'              => $status,
            'created_by'          => session('tenant_user_id'),
        ], true);

        $db->transComplete();

        $this->audit->log('employee_salary_assign', 'payroll', 'payroll_employee_salary', $id, $current, [
            'employee_id' => $employeeId, 'salary_structure_id' => $structureId, 'effective_from' => $effectiveFrom,
            'gross_salary' => $grossSalary, 'ctc' => $ctc,
        ]);

        return $id;
    }

    public function history(int $employeeId): array
    {
        return $this->assignments->forEmployee($employeeId);
    }

    public function activeFor(int $employeeId, ?string $date = null): ?array
    {
        return $this->assignments->activeFor($employeeId, $date);
    }

    public function updateNote(int $id, array $data): void
    {
        $old = $this->assignments->find($id);
        if (! $old) {
            throw new RuntimeException('Salary assignment not found.');
        }
        $this->assignments->update($id, $data);
        $this->audit->log('employee_salary_update', 'payroll', 'payroll_employee_salary', $id, $old, $data);
    }
}
