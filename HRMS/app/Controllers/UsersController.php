<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\EmployeeModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Services\EmailVerificationService;
use App\Services\UserService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class UsersController extends BaseController
{
    public function index()
    {
        $model = (new UserModel(service('tenantContext')->db()))->withPrimaryRole();

        $search = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        $roleId = (string) $this->request->getGet('role_id');
        $branchId = (string) $this->request->getGet('branch_id');

        if ($search !== '') {
            $model->groupStart()->like('users.name', $search)->orLike('users.email', $search)->orLike('users.username', $search)->groupEnd();
        }
        if ($status !== '') {
            $model->where('users.status', $status);
        }
        if ($roleId !== '') {
            $model->where('r.id', $roleId);
        }
        if ($branchId !== '') {
            $model->where('users.branch_id', $branchId);
        }

        $users = $model->orderBy('users.name', 'ASC')->paginate(15, 'users');

        return view('users/index', [
            'title'   => 'Users',
            'users'   => $users,
            'pager'   => $model->pager,
            'roles'   => (new RoleModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'branches'=> (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'filters' => ['q' => $search, 'status' => $status, 'role_id' => $roleId, 'branch_id' => $branchId],
        ]);
    }

    public function create()
    {
        return view('users/form', [
            'title'          => 'Add user',
            'user'           => null,
            'linkedEmployee' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'email'    => 'required|valid_email',
            'username' => 'permit_empty|alpha_numeric|max_length[60]',
            'role_id'  => 'required|integer',
            'status'   => 'required|in_list[active,inactive]',
        ];

        if (! $this->validate($rules) || ($errors = $this->uniquenessErrors(null)) !== []) {
            $errors = array_merge($this->validator->getErrors(), $errors ?? []);

            return view('users/form', ['title' => 'Add user', 'user' => null, 'linkedEmployee' => null, 'errors' => $errors, ...$this->formOptions()]);
        }

        $tempPassword   = bin2hex(random_bytes(8));
        $employeeRefId  = $this->employeeRefId();

        try {
            $userId = (new UserService())->create(
                $this->request->getPost(),
                (int) $this->request->getPost('role_id'),
                $tempPassword,
                (int) $this->currentUserId(),
                $employeeRefId
            );
        } catch (RuntimeException $e) {
            return view('users/form', ['title' => 'Add user', 'user' => null, 'linkedEmployee' => null, 'errors' => ['form' => $e->getMessage()], ...$this->formOptions()]);
        }

        session()->setFlashdata('tempPassword', $tempPassword);

        return redirect()->to(site_url('users/' . $userId))->with('success', 'User created.');
    }

    /**
     * CI4's built-in is_unique[table.field] rule only checks named
     * Config\Database groups — it can't see our ad-hoc, dynamically-built
     * tenant connections, so uniqueness is checked by hand against
     * service('tenantContext')->db() instead.
     */
    private function uniquenessErrors(?int $ignoreId): array
    {
        $userModel = new UserModel(service('tenantContext')->db());
        $errors    = [];

        $email = trim((string) $this->request->getPost('email'));
        $existingEmail = $userModel->where('email', $email)->first();
        if ($existingEmail && (int) $existingEmail['id'] !== (int) $ignoreId) {
            $errors['email'] = 'This email is already in use.';
        }

        $username = trim((string) $this->request->getPost('username'));
        if ($username !== '') {
            $existingUsername = $userModel->where('username', $username)->first();
            if ($existingUsername && (int) $existingUsername['id'] !== (int) $ignoreId) {
                $errors['username'] = 'This username is already in use.';
            }
        }

        return $errors;
    }

    public function view($id)
    {
        $user = (new UserModel(service('tenantContext')->db()))->withPrimaryRole()->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('users/view', [
            'title'          => $user['name'],
            'user'           => $user,
            'linkedEmployee' => (new EmployeeModel(service('tenantContext')->db()))->where('user_id', $id)->first(),
            'tempPassword'   => session()->getFlashdata('tempPassword'),
        ]);
    }

    public function edit($id)
    {
        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        $user['role_id'] = $userModel->primaryRole((int) $id)['id'] ?? null;
        $linkedEmployee  = (new EmployeeModel(service('tenantContext')->db()))->where('user_id', $id)->first();

        return view('users/form', ['title' => 'Edit user', 'user' => $user, 'linkedEmployee' => $linkedEmployee, ...$this->formOptions()]);
    }

    public function update($id)
    {
        $userModel = new UserModel(service('tenantContext')->db());
        $user      = $userModel->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'email'    => 'required|valid_email',
            'username' => 'permit_empty|alpha_numeric|max_length[60]',
            'role_id'  => 'required|integer',
            'status'   => 'required|in_list[active,inactive]',
        ];

        if (! $this->validate($rules) || ($uniqueErrors = $this->uniquenessErrors((int) $id)) !== []) {
            $user['role_id'] = $this->request->getPost('role_id');
            $errors = array_merge($this->validator->getErrors(), $uniqueErrors ?? []);

            return view('users/form', ['title' => 'Edit user', 'user' => $user, 'linkedEmployee' => (new EmployeeModel(service('tenantContext')->db()))->where('user_id', $id)->first(), 'errors' => $errors, ...$this->formOptions()]);
        }

        try {
            (new UserService())->update(
                $id,
                $this->request->getPost(),
                (int) $this->request->getPost('role_id'),
                (int) $this->currentUserId(),
                $this->employeeRefId(),
                true
            );
        } catch (RuntimeException $e) {
            $user['role_id'] = $this->request->getPost('role_id');

            return view('users/form', ['title' => 'Edit user', 'user' => $user, 'linkedEmployee' => (new EmployeeModel(service('tenantContext')->db()))->where('user_id', $id)->first(), 'errors' => ['form' => $e->getMessage()], ...$this->formOptions()]);
        }

        return redirect()->to(site_url('users/' . $id))->with('success', 'User updated.');
    }

    private function employeeRefId(): ?int
    {
        $value = trim((string) $this->request->getPost('employee_ref_id'));

        return $value !== '' ? (int) $value : null;
    }

    public function activate($id)
    {
        (new UserService())->setStatus((int) $id, 'active', (int) $this->currentUserId());

        return redirect()->back()->with('success', 'User activated.');
    }

    public function suspend($id)
    {
        if ((int) $id === (int) $this->currentUserId()) {
            return redirect()->back()->with('error', 'You cannot disable your own login.');
        }

        (new UserService())->setStatus((int) $id, 'inactive', (int) $this->currentUserId());

        return redirect()->back()->with('success', 'User suspended.');
    }

    public function resetPassword($id)
    {
        $password = (new UserService())->resetPassword((int) $id, (int) $this->currentUserId());
        session()->setFlashdata('tempPassword', $password);

        return redirect()->to(site_url('users/' . $id))->with('success', 'Password reset.');
    }

    public function unlock($id)
    {
        try {
            (new UserService())->unlock((int) $id, (int) $this->currentUserId());
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Account unlocked.');
    }

    public function lock($id)
    {
        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->back()->with('error', 'A reason is required to lock this account.');
        }

        try {
            (new UserService())->lock((int) $id, $reason, (int) $this->currentUserId());
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Account locked.');
    }

    public function setPassword($id)
    {
        $rules = ['password' => 'required|min_length[8]', 'password_confirm' => 'required|matches[password]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            (new UserService())->setPassword((int) $id, (string) $this->request->getPost('password'), (int) $this->currentUserId());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('users/' . $id))->with('success', 'Password updated. The user has been logged out everywhere and must set a new password to change it further.');
    }

    public function expirePassword($id)
    {
        (new UserService())->expirePassword((int) $id, (int) $this->currentUserId());

        return redirect()->back()->with('success', 'The user will be required to change their password at next login.');
    }

    public function resendVerification($id)
    {
        try {
            $sent = (new EmailVerificationService())->sendFor((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with(
            $sent ? 'success' : 'error',
            $sent ? 'Verification email resent.' : 'Could not send the verification email — check outbound mail configuration.'
        );
    }

    public function sendResetLink($id)
    {
        $sent = (new UserService())->sendResetLink((int) $id, (int) $this->currentUserId());

        return redirect()->to(site_url('users/' . $id))->with(
            $sent ? 'success' : 'error',
            $sent ? 'Reset link emailed to the user.' : 'Could not send the reset email — check outbound mail configuration.'
        );
    }

    public function revokeSessions($id)
    {
        try {
            (new UserService())->revokeAllSessions((int) $id, (int) $this->currentUserId());
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Logged out on all devices.');
    }

    public function revokeSession($id, $tokenId)
    {
        (new UserService())->revokeDeviceToken((int) $id, (int) $tokenId, (int) $this->currentUserId());

        return redirect()->back()->with('success', 'That device has been logged out.');
    }

    /** Lightweight role change from the Employee Login Account tab (Section D) — every other field is left untouched. */
    public function changeRole($id)
    {
        $roleId = (int) $this->request->getPost('role_id');
        if ($roleId <= 0) {
            return redirect()->back()->with('error', 'Select a role.');
        }

        $user = (new UserModel(service('tenantContext')->db()))->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound();
        }

        try {
            (new UserService())->update((int) $id, [
                'name' => $user['name'], 'username' => $user['username'], 'email' => $user['email'],
                'mobile' => $user['mobile'], 'employee_id' => $user['employee_id'], 'branch_id' => $user['branch_id'],
                'department_id' => $user['department_id'], 'designation_id' => $user['designation_id'], 'status' => $user['status'],
            ], $roleId, (int) $this->currentUserId());
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Role updated.');
    }

    public function toggles($id)
    {
        $toggles = [
            'allow_remember_me'  => (bool) $this->request->getPost('allow_remember_me'),
            'allow_mobile_login' => (bool) $this->request->getPost('allow_mobile_login'),
            'allow_web_login'    => (bool) $this->request->getPost('allow_web_login'),
            'require_2fa'        => (bool) $this->request->getPost('require_2fa'),
        ];

        (new UserService())->setToggles((int) $id, $toggles, (int) $this->currentUserId());

        return redirect()->back()->with('success', 'Account settings updated.');
    }

    public function delete($id)
    {
        (new UserService())->delete((int) $id);

        return redirect()->to(site_url('users'))->with('success', 'User deleted.');
    }

    private function formOptions(): array
    {
        return [
            'roles'        => (new RoleModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'branches'     => (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'departments'  => (new DepartmentModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'designations' => (new DesignationModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
        ];
    }

    private function validateAgainstTenant(array $rules): bool
    {
        return $this->validateData($this->request->getPost() ?? [], $rules);
    }
}
