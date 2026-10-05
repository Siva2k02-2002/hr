<?php

namespace App\Services;

use App\Models\PayrollSalaryComponentModel;
use App\Models\PayrollSalaryStructureItemModel;
use App\Models\PayrollSalaryStructureModel;
use RuntimeException;

/**
 * Owns salary structure CRUD, its component rows, and the live gross/net
 * calculation shared by the structure builder's preview panel and the actual
 * payroll earnings engine (PayrollEarningsService) — one calculation path,
 * never two implementations of the same math.
 */
class SalaryStructureService
{
    private PayrollSalaryStructureModel $structures;
    private PayrollSalaryStructureItemModel $items;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private PayrollFormulaEvaluator $formula = new PayrollFormulaEvaluator()
    ) {
        $this->structures = new PayrollSalaryStructureModel(service('tenantContext')->db());
        $this->items       = new PayrollSalaryStructureItemModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $data['created_by'] = session('tenant_user_id');
        $id                 = $this->structures->insert($data, true);
        $this->audit->log('salary_structure_create', 'payroll', 'payroll_salary_structure', $id, null, $data);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->structures->find($id);
        if (! $old) {
            throw new RuntimeException('Salary structure not found.');
        }

        $data['updated_by'] = session('tenant_user_id');
        $this->structures->update($id, $data);
        $this->audit->log('salary_structure_update', 'payroll', 'payroll_salary_structure', $id, $old, $data);
    }

    public function delete(int $id): void
    {
        $old = $this->structures->find($id);
        if (! $old) {
            throw new RuntimeException('Salary structure not found.');
        }

        $inUse = service('tenantContext')->db()->table('payroll_employee_salary')->where('salary_structure_id', $id)->countAllResults();
        if ($inUse > 0) {
            throw new RuntimeException('This salary structure is assigned to one or more employees and cannot be deleted.');
        }

        $this->structures->delete($id);
        $this->audit->log('salary_structure_delete', 'payroll', 'payroll_salary_structure', $id, $old, null);
    }

    public function items(int $structureId): array
    {
        return $this->items->forStructure($structureId);
    }

    /**
     * Full replace — the builder UI always posts the complete row set.
     * A component must be active to be newly attached to a structure; an
     * inactive component already present in this structure's prior row set
     * is allowed through unchanged (never silently dropped or swapped out —
     * that would corrupt an existing, already-payroll-relevant structure),
     * but a manipulated request can't use inactive-status as a backdoor to
     * attach it somewhere new.
     */
    public function saveItems(int $structureId, array $rows): void
    {
        $old               = $this->items->forStructure($structureId);
        $previouslyUsedIds = array_map(static fn ($item) => (int) $item['salary_component_id'], $old);

        $submittedIds = array_map(static fn ($row) => (int) $row['salary_component_id'], $rows);
        $components   = $submittedIds ? (new PayrollSalaryComponentModel(service('tenantContext')->db()))->whereIn('id', $submittedIds)->findAll() : [];
        $byId         = array_column($components, null, 'id');

        foreach ($rows as $row) {
            $componentId = (int) $row['salary_component_id'];
            $component   = $byId[$componentId] ?? null;
            if (! $component) {
                throw new RuntimeException('One of the selected salary components no longer exists.');
            }
            if ($component['status'] !== 'active' && ! in_array($componentId, $previouslyUsedIds, true)) {
                throw new RuntimeException(sprintf('"%s" is inactive and cannot be added to a salary structure.', $component['name']));
            }
        }

        $this->items->deleteForStructure($structureId);
        foreach ($rows as $order => $row) {
            $this->items->insert([
                'salary_structure_id' => $structureId,
                'salary_component_id' => (int) $row['salary_component_id'],
                'calculation_type'    => $row['calculation_type'],
                'value'               => (float) ($row['value'] ?? 0),
                'formula'             => $row['formula'] !== '' ? $row['formula'] : null,
                'is_editable'         => ! empty($row['is_editable']) ? 1 : 0,
                'display_order'       => $order,
            ]);
        }

        $this->audit->log('salary_structure_items_save', 'payroll', 'payroll_salary_structure', $structureId, ['items' => $old], ['items' => $rows]);
    }

    /**
     * Computes every item's amount against $grossSalary, in display_order so a
     * later row (e.g. HRA) can reference an earlier one (e.g. BASIC) by code.
     * @param array $items rows from PayrollSalaryStructureItemModel::forStructure()
     * @return array{items: array, gross_earnings: float, gross_deductions: float, net: float}
     */
    public function calculate(array $items, float $grossSalary): array
    {
        $values          = ['GROSS' => $grossSalary];
        $computed        = [];
        $grossEarnings   = 0.0;
        $grossDeductions = 0.0;

        foreach ($items as $item) {
            $amount = match ($item['calculation_type']) {
                'fixed'      => (float) $item['value'],
                'percentage' => round($grossSalary * (float) $item['value'] / 100, 2),
                'formula'    => $item['formula'] ? $this->formula->evaluate($item['formula'], $values) : 0.0,
                default      => 0.0,
            };

            // A percentage item can instead be "of" a specific earlier component (e.g. HRA = 40% of BASIC).
            if ($item['calculation_type'] === 'percentage' && ! empty($item['component_percentage_of']) && isset($values[$item['component_percentage_of']])) {
                $amount = round($values[$item['component_percentage_of']] * (float) $item['value'] / 100, 2);
            }

            $values[$item['component_code']] = $amount;

            if ($item['component_type'] === 'earning') {
                $grossEarnings += $amount;
            } else {
                $grossDeductions += $amount;
            }

            $computed[] = $item + ['amount' => $amount];
        }

        return [
            'items'            => $computed,
            'gross_earnings'   => round($grossEarnings, 2),
            'gross_deductions' => round($grossDeductions, 2),
            'net'              => round($grossEarnings - $grossDeductions, 2),
        ];
    }
}
