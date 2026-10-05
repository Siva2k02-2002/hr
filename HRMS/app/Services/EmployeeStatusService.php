<?php

namespace App\Services;

use App\Models\EmployeeModel;
use App\Models\EmployeeStatusHistoryModel;
use App\Models\UserModel;
use RuntimeException;

/**
 * The single place employees.status ever changes. Every transition is
 * checked against ALLOWED_TRANSITIONS, written to employee_status_history
 * (the append-only lifecycle ledger), and audit-logged — matching the
 * spec's "EMPLOYEE STATUS HISTORY" and "AUDIT LOGGING" sections.
 */
class EmployeeStatusService
{
    /**
     * Employee status drives the linked login's status where the mapping is
     * unambiguous. Statuses not listed here (probation, notice_period) leave
     * the login untouched — access during those states isn't specified.
     */
    private const LOGIN_STATUS_MAP = [
        'active'     => 'active',
        'suspended'  => 'inactive',
        'terminated' => 'inactive',
        'resigned'   => 'inactive',
        'absconded'  => 'inactive',
        'relieved'   => 'inactive',
    ];

    /** @var array<string, array<string>> current status => statuses it may move to */
    private const ALLOWED_TRANSITIONS = [
        'probation'     => ['active', 'notice_period', 'resigned', 'terminated', 'absconded'],
        'active'        => ['probation', 'notice_period', 'suspended', 'resigned', 'terminated', 'retired', 'absconded', 'relieved'],
        'notice_period' => ['active', 'resigned', 'terminated', 'relieved'],
        'suspended'     => ['active', 'terminated', 'relieved'],
        'resigned'      => ['relieved', 'active'],
        'terminated'    => ['active'],
        'retired'       => ['active'],
        'absconded'     => ['active', 'terminated'],
        'relieved'      => ['active'],
    ];

    private EmployeeModel $employees;

    public function __construct(private AuditService $audit = new AuditService())
    {
        $this->employees = new EmployeeModel(service('tenantContext')->db());
    }

    public function activate(int $id, ?string $remarks = null): void
    {
        $this->transition($id, 'active', 'activated', $remarks);
    }

    public function suspend(int $id, ?string $remarks = null): void
    {
        $this->transition($id, 'suspended', 'suspended', $remarks);
    }

    public function relieve(int $id, ?string $remarks = null): void
    {
        $this->transition($id, 'relieved', 'relieved', $remarks);
    }

    /** Only meaningful from a terminal-ish state (resigned/terminated/retired/absconded/relieved). */
    public function rejoin(int $id, ?string $remarks = null): void
    {
        $this->transition($id, 'active', 'rejoined', $remarks);
    }

    /** Generic transition for statuses that have no dedicated quick action (notice_period, resigned, terminated, retired, absconded, probation). */
    public function changeStatus(int $id, string $newStatus, ?string $remarks = null): void
    {
        $this->transition($id, $newStatus, $newStatus === 'notice_period' ? 'on_notice' : 'transferred', $remarks);
    }

    private function transition(int $id, string $newStatus, string $eventType, ?string $remarks): void
    {
        $employee = $this->employees->find($id);
        if (! $employee) {
            throw new RuntimeException('Employee not found.');
        }

        $current = $employee['status'];
        if ($current === $newStatus) {
            throw new RuntimeException("Employee is already {$newStatus}.");
        }

        $allowed = self::ALLOWED_TRANSITIONS[$current] ?? [];
        if (! in_array($newStatus, $allowed, true)) {
            throw new RuntimeException("Cannot move an employee from '{$current}' to '{$newStatus}'.");
        }

        $userId = session('tenant_user_id');
        $now    = date('Y-m-d H:i:s');

        $this->employees->update($id, ['status' => $newStatus, 'updated_by' => $userId]);

        (new EmployeeStatusHistoryModel(service('tenantContext')->db()))->insert([
            'employee_id' => $id,
            'event_type'  => $eventType,
            'from_value'  => $current,
            'to_value'    => $newStatus,
            'remarks'     => $remarks,
            'changed_by'  => $userId,
            'created_at'  => $now,
        ]);

        $this->audit->log('status_change', 'employees', 'employee', $id, ['status' => $current], ['status' => $newStatus]);

        $this->syncLoginStatus($employee, $newStatus, $userId);
    }

    private function syncLoginStatus(array $employee, string $newStatus, ?int $actorId): void
    {
        if (empty($employee['user_id']) || ! isset(self::LOGIN_STATUS_MAP[$newStatus])) {
            return;
        }

        $desired   = self::LOGIN_STATUS_MAP[$newStatus];
        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->find($employee['user_id']);

        if ($user && $user['status'] !== $desired) {
            (new UserService())->setStatus((int) $employee['user_id'], $desired, (int) $actorId);
        }
    }
}
