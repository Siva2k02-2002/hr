<?php

namespace App\Services;

use App\Models\EmployeeModel;
use App\Models\EmployeeStatusHistoryModel;
use App\Models\UserModel;
use RuntimeException;

class EmployeeService
{
    private EmployeeModel $employees;

    /** Set by attachLogin() when it just generated a temporary password — read by the controller to flash it once. */
    public ?string $lastTempPassword = null;

    /**
     * Set when a requested shift change conflicted with an existing assignment
     * (see applyShiftAssignment()) — the employee record still saves, this is
     * surfaced as a non-blocking warning rather than failing the whole request.
     */
    public ?string $shiftAssignmentWarning = null;

    public function __construct(
        private AuditService $audit = new AuditService(),
        private EmployeeCodeGenerator $codeGenerator = new EmployeeCodeGenerator()
    ) {
        $this->employees = new EmployeeModel(service('tenantContext')->db());
    }

    /**
     * $login, when not null, is ['email' => string, 'role_id' => int, 'username' => ?string] and
     * creates a linked users row (via UserService) inside the same transaction as the employee
     * insert, so an employee is never left half-created with a dangling login (or vice versa).
     *
     * $shiftAssignment, when not null, is ['shift_id' => int, 'effective_from' => string] and is
     * applied via AttendanceShiftAssignmentService — the single source of truth for shift history
     * (see applyShiftAssignment()). Never write a shift name into the employee record itself.
     */
    public function create(array $data, ?array $login = null, ?array $shiftAssignment = null): int
    {
        $this->assertUnderEmployeeLimit();
        $this->assertUnique($data, null);
        $this->assertNoCycle($data, null);

        if ($login !== null) {
            $this->assertLoginEmailAvailable($login['email']);
        }

        $data['employee_code'] = $this->codeGenerator->next();
        $data['created_by']    = session('tenant_user_id');

        $db = service('tenantContext')->db();
        $db->transStart();

        try {
            $id = $this->employees->insert($data);

            if (! $id) {
                throw new RuntimeException($this->firstModelError($this->employees) ?? 'Could not create employee.');
            }

            (new EmployeeStatusHistoryModel(service('tenantContext')->db()))->insert([
                'employee_id' => $id,
                'event_type'  => 'joined',
                'to_value'    => $data['status'],
                'changed_by'  => session('tenant_user_id'),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            $this->audit->log('create', 'employees', 'employee', $id, null, $data, $id);

            if ($login !== null) {
                $this->attachLogin($id, $data, $login);
            }

            if ($shiftAssignment !== null) {
                $this->applyShiftAssignment($id, $shiftAssignment);
            }
        } catch (RuntimeException $e) {
            $db->transRollback();

            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not create the employee (and login account, if requested).');
        }

        return $id;
    }

    public function update(int $id, array $data, ?array $login = null, ?array $shiftAssignment = null): void
    {
        $this->assertUnique($data, $id);
        $this->assertNoCycle($data, $id);

        $old = $this->employees->find($id);
        if (! $old) {
            throw new RuntimeException('Employee not found.');
        }

        $attachingLogin = $login !== null && empty($old['user_id']);
        if ($attachingLogin) {
            $this->assertLoginEmailAvailable($login['email']);
        }

        $data['updated_by'] = session('tenant_user_id');

        $db = service('tenantContext')->db();
        $db->transStart();

        try {
            if (! $this->employees->update($id, $data)) {
                throw new RuntimeException($this->firstModelError($this->employees) ?? 'Could not update employee.');
            }

            $this->logLifecycleChanges($id, $old, $data);
            $this->audit->log('update', 'employees', 'employee', $id, $old, $data, $id);

            if ($attachingLogin) {
                $this->attachLogin($id, array_merge($old, $data), $login);
            }

            if ($shiftAssignment !== null) {
                $this->applyShiftAssignment($id, $shiftAssignment);
            }
        } catch (RuntimeException $e) {
            $db->transRollback();

            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not update the employee (and login account, if requested).');
        }
    }

    /**
     * Routes a shift selection through AttendanceShiftAssignmentService — the only place shift
     * history is ever written. A no-op if the employee is already on that shift today (otherwise
     * every unrelated profile save would insert a redundant history row). A conflict (e.g. a
     * later assignment already scheduled) never fails the employee save itself — it's surfaced
     * as a warning so HR can resolve it from Attendance -> Shift Assignments instead.
     */
    private function applyShiftAssignment(int $employeeId, array $shiftAssignment): void
    {
        $shiftId       = (int) $shiftAssignment['shift_id'];
        $effectiveFrom = $shiftAssignment['effective_from'] ?: date('Y-m-d');

        $assignments = new AttendanceShiftAssignmentService($this->audit);
        $current     = $assignments->resolveShiftFor($employeeId, date('Y-m-d'));
        if ($current && (int) $current['id'] === $shiftId) {
            return;
        }

        try {
            $assignments->assignEmployee($employeeId, $shiftId, $effectiveFrom, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            $this->shiftAssignmentWarning = $e->getMessage();
        }
    }

    /** Model::insert()/update() return false on validation failure without throwing — surface the real reason instead of a generic message. */
    private function firstModelError(\CodeIgniter\Model $model): ?string
    {
        $errors = $model->errors();

        return $errors !== [] ? reset($errors) : null;
    }

    /**
     * Creates the linked login for a (just-inserted or existing) employee. Never called for an
     * employee that already has a user_id — the caller (create()/update()) guards that.
     */
    private function attachLogin(int $employeeId, array $employeeData, array $login): void
    {
        $password = bin2hex(random_bytes(8));

        $userId = (new UserService())->create([
            'name'           => trim($employeeData['first_name'] . ' ' . $employeeData['last_name']),
            'username'       => $this->generateUsername($employeeData),
            'email'          => $login['email'],
            'mobile'         => $employeeData['mobile'] ?? null,
            'employee_id'    => $employeeData['employee_code'],
            'branch_id'      => $employeeData['branch_id'] ?? null,
            'department_id'  => $employeeData['department_id'] ?? null,
            'designation_id' => $employeeData['designation_id'] ?? null,
            'status'         => in_array($employeeData['status'], ['active', 'probation'], true) ? 'active' : 'inactive',
        ], (int) $login['role_id'], $password, (int) session('tenant_user_id'));

        $this->employees->update($employeeId, ['user_id' => $userId]);

        $this->audit->log('login_enabled', 'employees', 'employee', $employeeId, null, ['user_id' => $userId], $employeeId);

        $this->lastTempPassword = $password;
    }

    /**
     * Username policy: lowercase, alphanumeric-only concatenation of first + middle + last
     * name — never typed by hand (Login Account tab keeps that field read-only). A literal
     * "first last" with a space is not achievable: users.username is validated alpha_numeric
     * (see UserModel) and the underlying column has no encoding scheme for spaces, so this is
     * the closest compatible format. Collisions get a numeric suffix; checked against the raw
     * tenant table (not the model's is_unique rule, which only sees the default DB group — see
     * UsersController::uniquenessErrors() for the same reasoning).
     */
    private function generateUsername(array $employeeData): string
    {
        $base = strtolower(preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            $employeeData['first_name'] . ($employeeData['middle_name'] ?? '') . $employeeData['last_name']
        ));
        $base = substr($base, 0, 55) ?: 'user' . bin2hex(random_bytes(3));

        $db        = service('tenantContext')->db();
        $candidate = $base;
        for ($suffix = 2; $db->table('users')->where('username', $candidate)->countAllResults() > 0; $suffix++) {
            $candidate = $base . $suffix;
        }

        return $candidate;
    }

    private function assertLoginEmailAvailable(string $email): void
    {
        if ((new UserModel(service('tenantContext')->db()))->where('email', $email)->first()) {
            throw new RuntimeException('This email is already used by another login account.');
        }
    }

    /**
     * Public entry point for the Employee → Login Account tab's "Create Login Account" CTA
     * (Section I) — an already-existing employee with no linked user yet. Reuses the exact same
     * attachLogin() path as create()/update() so there is only one place that ever builds a
     * users row from an employee, never a second parallel implementation.
     */
    public function createLoginFor(int $employeeId, array $login): void
    {
        $employee = $this->employees->find($employeeId);
        if (! $employee) {
            throw new RuntimeException('Employee not found.');
        }
        if (! empty($employee['user_id'])) {
            throw new RuntimeException('This employee already has a login account.');
        }

        $this->assertLoginEmailAvailable($login['email']);

        $db = service('tenantContext')->db();
        $db->transStart();

        try {
            $this->attachLogin($employeeId, $employee, $login);
        } catch (RuntimeException $e) {
            $db->transRollback();

            throw $e;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not create login account.');
        }
    }

    /**
     * Soft delete only (spec: "No hard delete"). Direct reports keep their
     * reporting_manager_id pointing at this now-inactive row — their profile
     * still shows the manager's name, it just won't surface in active-employee
     * pickers any more. Reassigning them is a manual follow-up action, not an
     * automatic side effect of deleting their manager.
     *
     * A linked login is disabled here too — otherwise an archived employee
     * keeps full portal access indefinitely, since TenantAuthFilter checks
     * the users row directly and never looks at the (now soft-deleted, and
     * so invisible to normal queries) employees row it came from.
     */
    public function delete(int $id): void
    {
        $old = $this->employees->find($id);
        if (! $old) {
            throw new RuntimeException('Employee not found.');
        }

        $this->employees->delete($id);
        $this->audit->log('delete', 'employees', 'employee', $id, $old, null, $id);

        if (! empty($old['user_id'])) {
            $userModel = new UserModel(service('tenantContext')->db());
            $user      = $userModel->find($old['user_id']);
            if ($user && $user['status'] !== 'inactive') {
                (new UserService())->setStatus((int) $old['user_id'], 'inactive', (int) session('tenant_user_id'));
            }
        }
    }

    /**
     * Undoes delete(): clears deleted_at and re-checks the same uniqueness rules a normal
     * update would — another employee may have since taken this one's email/mobile/etc.
     * Deliberately does NOT re-activate a linked login; that's a separate, explicit step
     * (UsersController::activate) so restoring the employee record can't silently hand
     * back portal access on its own.
     */
    public function restore(int $id): void
    {
        $old = $this->employees->onlyDeleted()->find($id);
        if (! $old) {
            throw new RuntimeException('Archived employee not found.');
        }

        $this->assertUnique($old, $id);

        $this->employees->update($id, ['deleted_at' => null]);
        $this->audit->log('restore', 'employees', 'employee', $id, ['deleted_at' => $old['deleted_at']], ['deleted_at' => null], $id);
    }

    private function logLifecycleChanges(int $id, array $old, array $new): void
    {
        $history = new EmployeeStatusHistoryModel(service('tenantContext')->db());
        $userId  = session('tenant_user_id');
        $now     = date('Y-m-d H:i:s');

        if (array_key_exists('department_id', $new) && (int) $new['department_id'] !== (int) $old['department_id']) {
            $history->insert([
                'employee_id' => $id, 'event_type' => 'department_changed',
                'from_value'  => (string) $old['department_id'], 'to_value' => (string) $new['department_id'],
                'changed_by'  => $userId, 'created_at' => $now,
            ]);
        }

        if (array_key_exists('reporting_manager_id', $new)
            && (int) ($new['reporting_manager_id'] ?? 0) !== (int) ($old['reporting_manager_id'] ?? 0)) {
            $history->insert([
                'employee_id' => $id, 'event_type' => 'manager_changed',
                'from_value'  => (string) ($old['reporting_manager_id'] ?? ''), 'to_value' => (string) ($new['reporting_manager_id'] ?? ''),
                'changed_by'  => $userId, 'created_at' => $now,
            ]);
        }
    }

    /**
     * The subscription's employee_limit is platform (superadmin) data — this app only
     * has read-only access to it via the 'master' DB group (see Config/Database.php).
     * Was stored and shown on the company's subscription screen but never actually
     * enforced anywhere: a company could add unlimited employees regardless of plan.
     * Fails open (no limit enforced) if the platform DB is unreachable, rather than
     * blocking every hire because of an unrelated outage.
     */
    private function assertUnderEmployeeLimit(): void
    {
        try {
            $company = db_connect('master')->table('companies')
                ->select('employee_limit')->where('id', tenant()->companyId())->get()->getRowArray();
        } catch (\Throwable $e) {
            log_message('error', 'Could not check employee limit against platform DB: {msg}', ['msg' => $e->getMessage()]);

            return;
        }

        $limit = (int) ($company['employee_limit'] ?? 0);
        if ($limit <= 0) {
            return; // 0/missing = unlimited
        }

        $current = $this->employees->where('deleted_at', null)->countAllResults();
        if ($current >= $limit) {
            throw new RuntimeException("This company's plan allows up to {$limit} employees. Contact your account manager to increase this limit before adding more.");
        }
    }

    private function assertUnique(array $data, ?int $ignoreId): void
    {
        $db = service('tenantContext')->db();

        $fieldLabels = [
            'company_email'  => 'Company email',
            'personal_email' => 'Personal email',
            'mobile'         => 'Mobile number',
            'aadhaar_number' => 'Aadhaar number',
            'pan_number'     => 'PAN number',
        ];

        foreach ($fieldLabels as $field => $label) {
            if (empty($data[$field])) {
                continue;
            }

            $query = $db->table('employees')->where($field, $data[$field])->where('deleted_at', null);
            if ($ignoreId !== null) {
                $query->where('id !=', $ignoreId);
            }

            if ($query->countAllResults() > 0) {
                throw new RuntimeException($label . ' is already in use by another employee.');
            }
        }
    }

    private function assertNoCycle(array $data, ?int $id): void
    {
        if (empty($data['reporting_manager_id'])) {
            return;
        }

        $managerId = (int) $data['reporting_manager_id'];

        if ($id === null) {
            return; // a brand-new employee has no direct reports yet — no cycle is possible
        }

        if ($managerId === $id) {
            throw new RuntimeException('An employee cannot report to themselves.');
        }

        if ($this->employees->wouldCreateCycle($id, $managerId)) {
            throw new RuntimeException('This reporting manager assignment would create a circular reporting chain.');
        }
    }
}
