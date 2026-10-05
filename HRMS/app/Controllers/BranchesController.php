<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\UserModel;
use App\Services\BranchService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class BranchesController extends BaseController
{
    public function index()
    {
        $model  = (new BranchModel(service('tenantContext')->db()))->withManagerName();
        $search = trim((string) $this->request->getGet('q'));

        if ($search !== '') {
            $model->groupStart()->like('branches.name', $search)->orLike('branches.code', $search)->groupEnd();
        }

        $branches = $model->orderBy('branches.name', 'ASC')->paginate(15, 'branches');

        return view('branches/index', [
            'title'    => 'Branches',
            'branches' => $branches,
            'pager'    => $model->pager,
            'filters'  => ['q' => $search],
        ]);
    }

    public function create()
    {
        return view('branches/form', ['title' => 'Add branch', 'branch' => null, 'users' => $this->activeUsers()]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('branches/form', ['title' => 'Add branch', 'branch' => null, 'errors' => $this->validator->getErrors(), 'users' => $this->activeUsers()]);
        }

        try {
            $id = (new BranchService())->create($this->payload());
        } catch (RuntimeException $e) {
            return view('branches/form', ['title' => 'Add branch', 'branch' => null, 'errors' => ['code' => $e->getMessage()], 'users' => $this->activeUsers()]);
        }

        return redirect()->to(site_url('branches/' . $id . '/edit'))->with('success', 'Branch created.');
    }

    public function edit($id)
    {
        $branch = (new BranchModel(service('tenantContext')->db()))->find($id);
        if (! $branch) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('branches/form', ['title' => 'Edit branch', 'branch' => $branch, 'users' => $this->activeUsers()]);
    }

    public function update($id)
    {
        $branch = (new BranchModel(service('tenantContext')->db()))->find($id);
        if (! $branch) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return view('branches/form', ['title' => 'Edit branch', 'branch' => $branch, 'errors' => $this->validator->getErrors(), 'users' => $this->activeUsers()]);
        }

        try {
            (new BranchService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return view('branches/form', ['title' => 'Edit branch', 'branch' => $branch, 'errors' => ['code' => $e->getMessage()], 'users' => $this->activeUsers()]);
        }

        return redirect()->to(site_url('branches'))->with('success', 'Branch updated.');
    }

    public function delete($id)
    {
        try {
            (new BranchService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('branches'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('branches'))->with('success', 'Branch deleted.');
    }

    public function archived()
    {
        $model    = (new BranchModel(service('tenantContext')->db()))->onlyDeleted()->withManagerName();
        $branches = $model->orderBy('branches.deleted_at', 'DESC')->paginate(15, 'branches');

        return view('branches/archived', ['title' => 'Archived Branches', 'branches' => $branches, 'pager' => $model->pager]);
    }

    public function restore($id)
    {
        try {
            (new BranchService())->restore((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('branches/archived'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('branches/archived'))->with('success', 'Branch restored.');
    }

    private function rules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[150]',
            'code' => 'required|alpha_numeric_punct|max_length[30]',
            'email'=> 'permit_empty|valid_email',
        ];
    }

    private function payload(): array
    {
        return [
            'name'            => $this->request->getPost('name'),
            'code'            => $this->request->getPost('code'),
            'address'         => $this->request->getPost('address') ?: null,
            'state'           => $this->request->getPost('state') ?: null,
            'phone'           => $this->request->getPost('phone') ?: null,
            'email'           => $this->request->getPost('email') ?: null,
            'manager_user_id' => $this->request->getPost('manager_user_id') ?: null,
            'status'          => $this->request->getPost('status') ?: 'active',
        ];
    }

    private function activeUsers(): array
    {
        return (new UserModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll();
    }
}
