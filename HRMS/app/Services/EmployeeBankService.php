<?php

namespace App\Services;

use App\Models\EmployeeBankAccountModel;
use RuntimeException;

/** Never logs the raw account number in old/new audit payloads unmasked beyond what AuditService already stores — see mask_account_number() for display. */
class EmployeeBankService
{
    private EmployeeBankAccountModel $accounts;

    public function __construct(private AuditService $audit = new AuditService())
    {
        helper('employee');
        $this->accounts = new EmployeeBankAccountModel(service('tenantContext')->db());
    }

    public function create(array $data): int
    {
        $this->assertNotDuplicate($data['account_number'], $data['ifsc_code'], null);

        if (! empty($data['is_primary'])) {
            $this->clearPrimary((int) $data['employee_id']);
        }

        $id = $this->accounts->insert($data);
        $this->audit->log('create', 'employees', 'employee_bank_account', $id, null, $this->redacted($data));

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $old = $this->accounts->find($id);
        if (! $old) {
            throw new RuntimeException('Bank account not found.');
        }

        $this->assertNotDuplicate($data['account_number'], $data['ifsc_code'], $id);

        if (! empty($data['is_primary'])) {
            $this->clearPrimary((int) $old['employee_id'], $id);
        }

        $this->accounts->update($id, $data);
        $this->audit->log('update', 'employees', 'employee_bank_account', $id, $this->redacted($old), $this->redacted($data));
    }

    /** Same account number at the same bank branch (IFSC) should never be on file for two different employees. */
    private function assertNotDuplicate(string $accountNumber, string $ifscCode, ?int $ignoreId): void
    {
        $query = $this->accounts->where('account_number', $accountNumber)->where('ifsc_code', $ifscCode);
        if ($ignoreId !== null) {
            $query->where('id !=', $ignoreId);
        }

        if ($query->countAllResults() > 0) {
            throw new RuntimeException('This account number and IFSC combination is already registered to another employee.');
        }
    }

    public function delete(int $id): void
    {
        $old = $this->accounts->find($id);
        if (! $old) {
            throw new RuntimeException('Bank account not found.');
        }

        $this->accounts->delete($id);
        $this->audit->log('delete', 'employees', 'employee_bank_account', $id, $this->redacted($old), null);
    }

    private function clearPrimary(int $employeeId, ?int $exceptId = null): void
    {
        $query = $this->accounts->where('employee_id', $employeeId);
        if ($exceptId !== null) {
            $query->where('id !=', $exceptId);
        }
        foreach ($query->findAll() as $row) {
            $this->accounts->update($row['id'], ['is_primary' => 0]);
        }
    }

    private function redacted(array $data): array
    {
        if (isset($data['account_number'])) {
            $data['account_number'] = mask_account_number($data['account_number']);
        }

        return $data;
    }
}
