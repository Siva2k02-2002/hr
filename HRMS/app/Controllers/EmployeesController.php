<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\EmployeeAddressModel;
use App\Models\EmployeeBankAccountModel;
use App\Models\EmployeeDocumentModel;
use App\Models\EmployeeEducationModel;
use App\Models\EmployeeEmergencyContactModel;
use App\Models\EmployeeExperienceModel;
use App\Models\EmployeeFamilyMemberModel;
use App\Models\EmployeeModel;
use App\Models\EmployeeStatusHistoryModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\AttendanceShiftAssignmentModel;
use App\Models\AttendanceShiftModel;
use App\Models\AuditLogModel;
use App\Models\LoginLogModel;
use App\Models\RememberTokenModel;
use App\Services\AttendanceShiftAssignmentService;
use App\Services\AttendanceWeeklyOffService;
use App\Services\EmployeeService;
use App\Services\PermissionService;
use App\Services\UserService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class EmployeesController extends BaseController
{
    private const TABS = [
        'overview', 'personal', 'organization', 'bank', 'documents',
        'family', 'emergency', 'education', 'experience', 'activity', 'login',
    ];

    public function index()
    {
        $model = (new EmployeeModel(service('tenantContext')->db()))->withRelations();

        $filters = [
            'q'              => trim((string) $this->request->getGet('q')),
            'branch_id'      => $this->request->getGet('branch_id'),
            'department_id'  => $this->request->getGet('department_id'),
            'designation_id' => $this->request->getGet('designation_id'),
            'status'         => $this->request->getGet('status'),
            'employment_type'=> $this->request->getGet('employment_type'),
            'manager_id'     => $this->request->getGet('manager_id'),
            'gender'         => $this->request->getGet('gender'),
            'blood_group'    => $this->request->getGet('blood_group'),
            'joined_from'    => $this->request->getGet('joined_from'),
            'joined_to'      => $this->request->getGet('joined_to'),
        ];

        $model->applyFilters($filters);

        $employees = $model->orderBy('employees.first_name', 'ASC')->paginate(15, 'employees');

        $selectedManager = $filters['manager_id'] ? (new EmployeeModel(service('tenantContext')->db()))->find($filters['manager_id']) : null;

        return view('employees/index', [
            'title'           => 'Employees',
            'employees'       => $employees,
            'pager'           => $model->pager,
            'filters'         => $filters,
            'selectedManager' => $selectedManager,
            ...$this->filterOptions(),
        ]);
    }

    public function create()
    {
        $errors = session()->getFlashdata('errors') ?? [];

        return view('employees/form', [
            'title' => 'Add employee', 'employee' => null,
            'manager' => $errors ? $this->oldManager() : null,
            'linkedUser' => null, 'linkedUserRole' => null, 'errors' => $errors,
            ...$this->formOptions(),
        ]);
    }

    public function store()
    {
        $login  = $this->loginPayload();
        $errors = $this->validate($this->rules()) ? [] : $this->validator->getErrors();
        $errors = array_merge($errors, $this->validateLogin($login));

        // Redirect (not a same-request render) so old()/withInput() has flashdata to
        // repopulate every tab from — see EmployeesController create()/edit() audit.
        if ($errors !== []) {
            return redirect()->to(site_url('employees/create'))->withInput()->with('errors', $errors);
        }

        $service = new EmployeeService();

        try {
            $id = $service->create($this->payload(), $login, $this->shiftAssignmentPayload());
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/create'))->withInput()->with('errors', ['form' => $e->getMessage()]);
        }

        if ($service->lastTempPassword !== null) {
            session()->setFlashdata('tempPassword', $service->lastTempPassword);
        }

        $redirect = redirect()->to(site_url('employees/' . $id))->with('success', 'Employee created.');

        return $service->shiftAssignmentWarning !== null ? $redirect->with('warning', $service->shiftAssignmentWarning) : $redirect;
    }

    public function edit($id)
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }

        $errors  = session()->getFlashdata('errors') ?? [];
        $manager = $errors
            ? $this->oldManager()
            : ($employee['reporting_manager_id'] ? (new EmployeeModel(service('tenantContext')->db()))->find($employee['reporting_manager_id']) : null);

        [$linkedUser, $linkedUserRole] = $this->linkedLogin($employee);

        return view('employees/form', [
            'title' => 'Edit employee', 'employee' => $employee, 'manager' => $manager,
            'linkedUser' => $linkedUser, 'linkedUserRole' => $linkedUserRole, 'errors' => $errors,
            'currentShift' => (new AttendanceShiftAssignmentService())->resolveShiftFor((int) $id, date('Y-m-d')),
            ...$this->formOptions(),
        ]);
    }

    public function update($id)
    {
        $employeeModel = new EmployeeModel(service('tenantContext')->db());
        $employee      = $employeeModel->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$linkedUser, $linkedUserRole] = $this->linkedLogin($employee);
        $login  = $linkedUser ? null : $this->loginPayload();
        $errors = $this->validate($this->rules()) ? [] : $this->validator->getErrors();
        $errors = array_merge($errors, $this->validateLogin($login));
        $newUsername = $linkedUser && can('users.edit') ? $this->usernameChangeErrors($linkedUser, $errors) : null;

        // Redirect (not a same-request render) so old()/withInput() has flashdata to
        // repopulate every tab from — see EmployeesController create()/edit() audit.
        if ($errors !== []) {
            return redirect()->to(site_url('employees/' . $id . '/edit'))->withInput()->with('errors', $errors);
        }

        $service = new EmployeeService();

        try {
            $service->update((int) $id, $this->payload(), $login, $this->shiftAssignmentPayload());

            if ($newUsername !== null) {
                (new UserService())->renameUsername((int) $linkedUser['id'], $newUsername, (int) session('tenant_user_id'));
            }
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $id . '/edit'))->withInput()->with('errors', ['form' => $e->getMessage()]);
        }

        if ($service->lastTempPassword !== null) {
            session()->setFlashdata('tempPassword', $service->lastTempPassword);
        }

        $redirect = redirect()->to(site_url('employees/' . $id))->with('success', 'Employee updated.');

        return $service->shiftAssignmentWarning !== null ? $redirect->with('warning', $service->shiftAssignmentWarning) : $redirect;
    }

    public function view($id)
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->withRelations()->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('employees/profile', [
            'title'       => trim($employee['first_name'] . ' ' . $employee['last_name']),
            'employee'    => $employee,
            'initialTab'  => in_array($this->request->getGet('tab'), self::TABS, true) ? $this->request->getGet('tab') : 'overview',
            'overviewHtml'=> $this->renderTab((int) $id, 'overview', $employee),
            'tempPassword'=> session()->getFlashdata('tempPassword'),
            'resetLink'   => session()->getFlashdata('resetLink'),
        ]);
    }

    /**
     * Formal, print-ready employee profile document (all sections, not just the Overview tab)
     * — the profile page's Print button opens this instead of window.print()-ing the app UI.
     */
    public function pdf($id)
    {
        $db       = service('tenantContext')->db();
        $employee = (new EmployeeModel($db))->withRelations()->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }

        $photoPath = (new \App\Services\EmployeePhotoService())->absolutePathFor($employee);
        $settings  = company_branding_settings();
        $logoPath  = ! empty($settings['logo_path']) ? WRITEPATH . 'uploads/' . $settings['logo_path'] : null;

        $html = view('employees/_profile_pdf', [
            'employee'   => $employee,
            'addresses'  => (new EmployeeAddressModel($db))->forEmployee((int) $id),
            'family'     => (new EmployeeFamilyMemberModel($db))->forEmployee((int) $id),
            'emergency'  => (new EmployeeEmergencyContactModel($db))->forEmployee((int) $id),
            'education'  => (new EmployeeEducationModel($db))->forEmployee((int) $id),
            'experience' => (new EmployeeExperienceModel($db))->forEmployee((int) $id),
            'documents'  => can('employee.documents') ? (new EmployeeDocumentModel($db))->forEmployee((int) $id) : [],
            'banks'      => can('employee.bank.view') ? (new EmployeeBankAccountModel($db))->forEmployee((int) $id) : null,
            'shift'      => $this->currentShiftData((int) $id, $employee)['currentShift'] ?? null,
            'photoUri'   => $this->imageDataUri($photoPath),
            'logoUri'    => $this->imageDataUri($logoPath),
            'companyName'=> company_name(),
            'generatedBy'=> session('tenant_user_name') ?? '',
        ]);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        (new \App\Services\AuditService())->log('export', 'employees', 'employee', (int) $id, null, ['format' => 'pdf', 'type' => 'profile']);

        $filename = 'Employee_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $employee['employee_code']) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /** Re-encodes to PNG via GD so any stored format (incl. WebP) embeds cleanly in Dompdf. */
    private function imageDataUri(?string $path): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }
        $img = @imagecreatefromstring((string) file_get_contents($path));
        if (! $img) {
            return null;
        }
        imagesavealpha($img, true);
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /** "Create Login Account" CTA on the Login Account tab (Section I) — an existing employee with no linked user yet. */
    public function createLogin($id)
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->find($id);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }

        $login  = [
            'email'   => trim((string) $this->request->getPost('login_email')),
            'role_id' => (int) $this->request->getPost('login_role_id'),
        ];
        $errors = $this->validateLogin($login);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        $service = new EmployeeService();

        try {
            $service->createLoginFor((int) $id, $login);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        session()->setFlashdata('tempPassword', $service->lastTempPassword);

        return redirect()->to(site_url('employees/' . $id))->with('success', 'Login account created.');
    }

    /** AJAX-lazy-loaded tab content — see profile.php's tab JS. */
    public function tab($id, $name)
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->withRelations()->find($id);
        if (! $employee || ! in_array($name, self::TABS, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($name === 'bank' && ! can('employee.bank.view')) {
            return $this->response->setStatusCode(403)->setBody('<div class="alert alert-warning mb-0">You do not have permission to view bank details.</div>');
        }
        if ($name === 'login' && ! can('users.view')) {
            return $this->response->setStatusCode(403)->setBody('<div class="alert alert-warning mb-0">You do not have permission to view login account details.</div>');
        }

        return $this->response->setBody($this->renderTab((int) $id, $name, $employee));
    }

    public function delete($id)
    {
        try {
            (new EmployeeService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees'))->with('success', 'Employee archived.');
    }

    public function archived()
    {
        $model = (new EmployeeModel(service('tenantContext')->db()))->onlyDeleted()->withRelations();

        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $model->groupStart()
                ->like('employees.employee_code', $q)
                ->orLike('employees.first_name', $q)
                ->orLike('employees.last_name', $q)
                ->groupEnd();
        }

        $employees = $model->orderBy('employees.deleted_at', 'DESC')->paginate(15, 'employees');

        return view('employees/archived', ['title' => 'Archived Employees', 'employees' => $employees, 'pager' => $model->pager, 'q' => $q]);
    }

    public function restore($id)
    {
        try {
            (new EmployeeService())->restore((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/archived'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/archived'))->with('success', 'Employee restored.');
    }

    private function renderTab(int $id, string $name, array $employee): string
    {
        $data = ['employee' => $employee];

        $data += match ($name) {
            'bank'       => ['accounts' => (new EmployeeBankAccountModel(service('tenantContext')->db()))->forEmployee($id)],
            'documents'  => ['documents' => (new EmployeeDocumentModel(service('tenantContext')->db()))->forEmployee($id)],
            'family'     => ['members' => (new EmployeeFamilyMemberModel(service('tenantContext')->db()))->forEmployee($id)],
            'emergency'  => ['contacts' => (new EmployeeEmergencyContactModel(service('tenantContext')->db()))->forEmployee($id)],
            'education'  => ['records' => (new EmployeeEducationModel(service('tenantContext')->db()))->forEmployee($id)],
            'experience' => ['records' => (new EmployeeExperienceModel(service('tenantContext')->db()))->forEmployee($id)],
            'personal'   => ['addresses' => (new EmployeeAddressModel(service('tenantContext')->db()))->forEmployee($id)],
            'organization' => $this->currentShiftData($id, $employee),
            'activity'   => ['history' => (new EmployeeStatusHistoryModel(service('tenantContext')->db()))->forEmployee($id), 'auditLogs' => $this->auditLogsFor($id)],
            'login'      => $this->iamDataFor($employee),
            default      => [],
        };

        return view('employees/_tab_' . $name, $data);
    }

    /** Actual assigned shift (not the legacy free-text $employee['shift'] field) plus weekly off and history link. */
    private function currentShiftData(int $employeeId, array $employee): array
    {
        $today = date('Y-m-d');
        $shift = (new AttendanceShiftAssignmentService())->resolveShiftFor($employeeId, $today);

        return [
            'currentShift'   => $shift,
            'isWeeklyOffToday' => (new AttendanceWeeklyOffService())->isWeeklyOff($employee['branch_id'] ?? null, $today, $shift['id'] ?? null),
            'shiftHistory'   => (new AttendanceShiftAssignmentModel(service('tenantContext')->db()))->historyFor($employeeId),
        ];
    }

    /** ESS module label => gating permission slug (null = always available, e.g. self-profile). */
    private const ESS_MODULES = [
        'Dashboard'     => 'dashboard.view',
        'My Attendance' => 'attendance.view',
        'My Leave'      => 'leave.view',
        'My Payroll'    => 'payslip.download',
        'My Profile'    => null,
        'Documents'     => 'employee.documents',
    ];

    /** Builds every section of the Employee → Login Account IAM tab (Phase 7.1, Sections A-N). */
    private function iamDataFor(array $employee): array
    {
        [$linkedUser, $linkedUserRole] = $this->linkedLogin($employee);
        $roles = (new RoleModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll();

        if (! $linkedUser) {
            return ['linkedUser' => null, 'roles' => $roles];
        }

        $userId          = (int) $linkedUser['id'];
        $roleModel       = new RoleModel(service('tenantContext')->db());
        $userPermissions = (new PermissionService())->slugsForUser($userId);

        $essAccess = [];
        foreach (self::ESS_MODULES as $label => $slug) {
            $essAccess[$label] = $slug === null || in_array($slug, $userPermissions, true);
        }

        return [
            'linkedUser'           => $linkedUser,
            'linkedUserRole'       => $linkedUserRole,
            'roles'                => $roles,
            'rolePermissionCounts' => array_combine(
                array_column($roles, 'id'),
                array_map(static fn (array $r) => count($roleModel->permissionSlugs((int) $r['id'])), $roles)
            ),
            'sessions'     => (new RememberTokenModel(service('tenantContext')->db()))->forUser($userId),
            'loginHistory' => (new LoginLogModel(service('tenantContext')->db()))->recentFor($userId, 10),
            'userAuditLogs'=> (new AuditLogModel(service('tenantContext')->db()))->forRecord('users', $userId, 30),
            'essAccess'    => $essAccess,
        ];
    }

    /**
     * The full cross-module activity timeline for one employee (Phase 17): a leave-approval
     * event's own record_id is the leave_application's id, not this employee's, so matching on
     * employee_id (present on events written since that column was added) is what actually
     * surfaces those. The module='employees' AND record_id=$employeeId branch is kept for
     * history written before employee_id existed, and stays correct for that module either way.
     */
    private function auditLogsFor(int $employeeId): array
    {
        return service('tenantContext')->db()->table('audit_logs al')
            ->select('al.*, u.name as user_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->groupStart()
                ->where('al.employee_id', $employeeId)
                ->orGroupStart()
                    ->where('al.module', 'employees')
                    ->where('al.record_id', $employeeId)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('al.id', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();
    }

    private function applyFilters(EmployeeModel $model, array $f): void
    {
        if ($f['q'] !== '') {
            $model->groupStart()
                ->like('employees.employee_code', $f['q'])
                ->orLike('employees.first_name', $f['q'])
                ->orLike('employees.last_name', $f['q'])
                ->orLike('employees.company_email', $f['q'])
                ->orLike('employees.personal_email', $f['q'])
                ->orLike('employees.mobile', $f['q'])
                ->orLike('employees.pan_number', $f['q'])
                ->orLike('employees.aadhaar_number', $f['q'])
                ->groupEnd();
        }
        foreach (['branch_id', 'department_id', 'designation_id', 'status', 'employment_type', 'gender', 'blood_group'] as $field) {
            if (! empty($f[$field])) {
                $model->where('employees.' . $field, $f[$field]);
            }
        }
        if (! empty($f['manager_id'])) {
            $model->where('employees.reporting_manager_id', $f['manager_id']);
        }
        if (! empty($f['joined_from'])) {
            $model->where('employees.date_of_joining >=', $f['joined_from']);
        }
        if (! empty($f['joined_to'])) {
            $model->where('employees.date_of_joining <=', $f['joined_to']);
        }
    }

    private function rules(): array
    {
        return [
            'first_name'      => 'required|min_length[1]|max_length[100]',
            'last_name'       => 'required|min_length[1]|max_length[100]',
            'mobile'          => 'required|regex_match[/^[0-9]{10}$/]',
            'personal_email'  => 'permit_empty|valid_email|max_length[150]',
            'company_email'   => 'permit_empty|valid_email|max_length[150]',
            'aadhaar_number'  => 'permit_empty|regex_match[/^[0-9]{12}$/]',
            'pan_number'      => 'permit_empty|regex_match[/^[A-Z]{5}[0-9]{4}[A-Z]$/]',
            'branch_id'       => 'required|integer',
            'department_id'   => 'required|integer',
            'designation_id'  => 'required|integer',
            'date_of_joining' => 'required|valid_date',
            'status'          => 'required|in_list[active,probation,notice_period,suspended,resigned,terminated,retired,absconded,relieved]',
        ];
    }

    private function payload(): array
    {
        // empty() not ?? — these fields are present-but-blank ('') as often as
        // missing entirely (an unfilled <input> still submits an empty string),
        // and unlike ??, empty() treats both cases as "not set" safely, with no
        // undefined-array-key warning on a missing key either. This matters
        // beyond cosmetics: an empty string in company_email/personal_email
        // collides with every other blank one under the unique index (MySQL
        // treats '' as a real, non-distinct value — only NULL is exempt from
        // uniqueness), and an empty string in a date/FK column fails outright.
        $post = $this->request->getPost();
        $opt  = static fn (string $key) => empty($post[$key]) ? null : $post[$key];

        return [
            'first_name'              => $post['first_name'],
            'middle_name'             => $opt('middle_name'),
            'last_name'               => $post['last_name'],
            'gender'                  => $opt('gender'),
            'date_of_birth'           => $opt('date_of_birth'),
            'blood_group'             => $opt('blood_group'),
            'marital_status'          => $opt('marital_status'),
            'nationality'             => $opt('nationality'),
            'aadhaar_number'          => $opt('aadhaar_number'),
            'pan_number'              => $opt('pan_number') ? strtoupper($post['pan_number']) : null,
            'passport_number'         => $opt('passport_number'),
            'driving_license_number'  => $opt('driving_license_number'),
            'mobile'                  => $post['mobile'],
            'alternate_mobile'        => $opt('alternate_mobile'),
            'personal_email'          => $opt('personal_email'),
            'company_email'           => $opt('company_email'),
            'branch_id'               => (int) $post['branch_id'],
            'department_id'           => (int) $post['department_id'],
            'designation_id'          => (int) $post['designation_id'],
            'reporting_manager_id'    => $opt('reporting_manager_id'),
            'employment_type'         => $post['employment_type'] ?? 'full_time',
            'employment_category'     => $opt('employment_category'),
            'work_location'           => $opt('work_location'),
            'date_of_joining'         => $post['date_of_joining'],
            'date_of_confirmation'    => $opt('date_of_confirmation'),
            'probation_period_months' => $opt('probation_period_months'),
            'status'                  => $post['status'] ?? 'probation',
        ];
    }

    /**
     * Employee → Edit → Login Account tab: validates an admin's manual override of the
     * auto-generated username for an already-linked login. Unchanged (or blank, since the
     * field is always pre-filled) is not an error — see rule "unchanged must not report a
     * duplicate". Returns the new username to apply, or null when there's nothing to change
     * (either unchanged, or invalid — in which case $errors already carries the reason and
     * the caller's existing $errors !== [] check blocks the save).
     */
    private function usernameChangeErrors(array $linkedUser, array &$errors): ?string
    {
        $new     = trim((string) $this->request->getPost('login_username'));
        $current = (string) ($linkedUser['username'] ?? '');

        if ($new === '' || $new === $current) {
            return null;
        }

        if (! ctype_alnum($new) || strlen($new) > 60) {
            $errors['login_username'] = 'Username may only contain letters and numbers (max 60 characters).';

            return null;
        }

        $existing = (new UserModel(service('tenantContext')->db()))->where('username', $new)->first();
        if ($existing && (int) $existing['id'] !== (int) $linkedUser['id']) {
            $errors['login_username'] = 'This username is already in use.';

            return null;
        }

        return $new;
    }

    /**
     * The reporting-manager Select2 field is AJAX-searched, so the form can only pre-render
     * its selected <option> from a resolved employee row, never from old('reporting_manager_id')
     * alone — this resolves that id back to a row after a failed submit, same as $manager
     * already does for edit() from the DB.
     */
    private function oldManager(): ?array
    {
        $id = old('reporting_manager_id');

        return $id ? (new EmployeeModel(service('tenantContext')->db()))->find((int) $id) : null;
    }

    /** @return array{0: ?array, 1: ?array} [linked user row, linked user's primary role row] */
    private function linkedLogin(array $employee): array
    {
        if (empty($employee['user_id'])) {
            return [null, null];
        }

        $userModel  = new UserModel(service('tenantContext')->db());
        $linkedUser = $userModel->find($employee['user_id']);

        return [$linkedUser, $linkedUser ? $userModel->primaryRole((int) $linkedUser['id']) : null];
    }

    /** Null when the "Enable Login" toggle wasn't checked — i.e. "create/keep this employee without a login". */
    private function loginPayload(): ?array
    {
        $post = $this->request->getPost();
        if (empty($post['login_enabled'])) {
            return null;
        }

        return [
            'email'   => trim((string) ($post['login_email'] ?? '')),
            'role_id' => (int) ($post['login_role_id'] ?? 0),
        ];
    }

    private function validateLogin(?array $login): array
    {
        if ($login === null) {
            return [];
        }

        $errors = [];
        if ($login['email'] === '' || ! filter_var($login['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['login_email'] = 'A valid login email is required to enable login.';
        }
        if ($login['role_id'] <= 0) {
            $errors['login_role_id'] = 'Select a role to enable login.';
        }
        // Username is no longer user-supplied — EmployeeService::attachLogin() always
        // generates it from the employee's name, so there's nothing left to validate here.

        return $errors;
    }

    private function formOptions(): array
    {
        $cache = new \App\Services\LookupCacheService();

        return [
            'branches'      => $cache->branches(),
            'departments'   => $cache->departments(),
            'designations'  => $cache->designations(),
            'roles'         => (new RoleModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll(),
            'shifts'        => (new AttendanceShiftModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll(),
        ];
    }

    /** Shift the employee form's "Current Shift" select posted, or null if left blank — never written to the employees table itself, see EmployeeService::update(). */
    private function shiftAssignmentPayload(): ?array
    {
        $shiftId = (int) $this->request->getPost('shift_id');
        if ($shiftId <= 0) {
            return null;
        }

        return [
            'shift_id'       => $shiftId,
            'effective_from' => (string) $this->request->getPost('shift_effective_from') ?: date('Y-m-d'),
        ];
    }

    private function filterOptions(): array
    {
        return [
            'branches'     => (new BranchModel(service('tenantContext')->db()))->orderBy('name')->findAll(),
            'departments'  => (new DepartmentModel(service('tenantContext')->db()))->orderBy('name')->findAll(),
            'designations' => (new DesignationModel(service('tenantContext')->db()))->orderBy('name')->findAll(),
        ];
    }
}
