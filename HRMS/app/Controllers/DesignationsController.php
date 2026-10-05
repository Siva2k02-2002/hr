<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Services\DesignationService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class DesignationsController extends BaseController
{
    public function index()
    {
        $model  = (new DesignationModel(service('tenantContext')->db()))->withDepartmentName();
        $search = trim((string) $this->request->getGet('q'));

        if ($search !== '') {
            $model->like('designations.name', $search);
        }

        $designations = $model->orderBy('designations.name', 'ASC')->paginate(15, 'designations');

        return view('designations/index', [
            'title'        => 'Designations',
            'designations' => $designations,
            'pager'        => $model->pager,
            'filters'      => ['q' => $search],
        ]);
    }

    public function create()
    {
        return view('designations/form', ['title' => 'Add designation', 'designation' => null, 'departments' => $this->departments()]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('designations/form', ['title' => 'Add designation', 'designation' => null, 'errors' => $this->validator->getErrors(), 'departments' => $this->departments()]);
        }

        try {
            (new DesignationService())->create($this->payload());
        } catch (RuntimeException $e) {
            return view('designations/form', ['title' => 'Add designation', 'designation' => null, 'errors' => ['form' => $e->getMessage()], 'departments' => $this->departments()]);
        }

        return redirect()->to(site_url('designations'))->with('success', 'Designation created.');
    }

    public function edit($id)
    {
        $designation = (new DesignationModel(service('tenantContext')->db()))->find($id);
        if (! $designation) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('designations/form', ['title' => 'Edit designation', 'designation' => $designation, 'departments' => $this->departments()]);
    }

    public function update($id)
    {
        $designation = (new DesignationModel(service('tenantContext')->db()))->find($id);
        if (! $designation) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return view('designations/form', ['title' => 'Edit designation', 'designation' => $designation, 'errors' => $this->validator->getErrors(), 'departments' => $this->departments()]);
        }

        try {
            (new DesignationService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return view('designations/form', ['title' => 'Edit designation', 'designation' => $designation, 'errors' => ['form' => $e->getMessage()], 'departments' => $this->departments()]);
        }

        return redirect()->to(site_url('designations'))->with('success', 'Designation updated.');
    }

    public function delete($id)
    {
        try {
            (new DesignationService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('designations'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('designations'))->with('success', 'Designation archived.');
    }

    public function archived()
    {
        $model = (new DesignationModel(service('tenantContext')->db()))->onlyDeleted()->withDepartmentName();

        $designations = $model->orderBy('designations.deleted_at', 'DESC')->paginate(15, 'designations');

        return view('designations/archived', ['title' => 'Archived Designations', 'designations' => $designations, 'pager' => $model->pager]);
    }

    public function restore($id)
    {
        try {
            (new DesignationService())->restore((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('designations/archived'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('designations/archived'))->with('success', 'Designation restored.');
    }

    private function rules(): array
    {
        return [
            'name'          => 'required|min_length[2]|max_length[150]',
            'department_id' => 'required|integer',
        ];
    }

    private function payload(): array
    {
        return [
            'name'          => $this->request->getPost('name'),
            'department_id' => (int) $this->request->getPost('department_id'),
            'level'         => (int) $this->request->getPost('level'),
            'status'        => $this->request->getPost('status') ?: 'active',
        ];
    }

    private function departments(): array
    {
        return (new DepartmentModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll();
    }
}
