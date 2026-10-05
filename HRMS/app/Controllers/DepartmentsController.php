<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\UserModel;
use App\Services\DepartmentService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class DepartmentsController extends BaseController
{
    public function index()
    {
        $model  = (new DepartmentModel(service('tenantContext')->db()))->withRelations();
        $search = trim((string) $this->request->getGet('q'));

        if ($search !== '') {
            $model->groupStart()->like('departments.name', $search)->orLike('departments.code', $search)->groupEnd();
        }

        $departments = $model->orderBy('departments.name', 'ASC')->paginate(15, 'departments');

        return view('departments/index', [
            'title'       => 'Departments',
            'departments' => $departments,
            'pager'       => $model->pager,
            'filters'     => ['q' => $search],
        ]);
    }

    public function create()
    {
        return view('departments/form', ['title' => 'Add department', 'department' => null, ...$this->formOptions()]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('departments/form', ['title' => 'Add department', 'department' => null, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        try {
            $id = (new DepartmentService())->create($this->payload());
        } catch (RuntimeException $e) {
            return view('departments/form', ['title' => 'Add department', 'department' => null, 'errors' => ['code' => $e->getMessage()], ...$this->formOptions()]);
        }

        return redirect()->to(site_url('departments/' . $id . '/edit'))->with('success', 'Department created.');
    }

    public function edit($id)
    {
        $department = (new DepartmentModel(service('tenantContext')->db()))->find($id);
        if (! $department) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('departments/form', ['title' => 'Edit department', 'department' => $department, ...$this->formOptions()]);
    }

    public function update($id)
    {
        $department = (new DepartmentModel(service('tenantContext')->db()))->find($id);
        if (! $department) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return view('departments/form', ['title' => 'Edit department', 'department' => $department, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        try {
            (new DepartmentService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return view('departments/form', ['title' => 'Edit department', 'department' => $department, 'errors' => ['code' => $e->getMessage()], ...$this->formOptions()]);
        }

        return redirect()->to(site_url('departments'))->with('success', 'Department updated.');
    }

    public function delete($id)
    {
        try {
            (new DepartmentService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('departments'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('departments'))->with('success', 'Department deleted.');
    }

    public function archived()
    {
        $model       = (new DepartmentModel(service('tenantContext')->db()))->onlyDeleted()->withRelations();
        $departments = $model->orderBy('departments.deleted_at', 'DESC')->paginate(15, 'departments');

        return view('departments/archived', ['title' => 'Archived Departments', 'departments' => $departments, 'pager' => $model->pager]);
    }

    public function restore($id)
    {
        try {
            (new DepartmentService())->restore((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('departments/archived'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('departments/archived'))->with('success', 'Department restored.');
    }

    private function rules(): array
    {
        return [
            'name'      => 'required|min_length[2]|max_length[150]',
            'code'      => 'required|alpha_numeric_punct|max_length[30]',
            'branch_id' => 'required|integer',
        ];
    }

    private function payload(): array
    {
        return [
            'name'         => $this->request->getPost('name'),
            'code'         => $this->request->getPost('code'),
            'branch_id'    => (int) $this->request->getPost('branch_id'),
            'head_user_id' => $this->request->getPost('head_user_id') ?: null,
            'status'       => $this->request->getPost('status') ?: 'active',
        ];
    }

    private function formOptions(): array
    {
        return [
            'branches' => (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll(),
            'users'    => (new UserModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll(),
        ];
    }
}
