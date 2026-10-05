<?php

namespace App\Controllers;

use App\Models\ModuleModel;
use App\Services\AuditService;

class ModulesController extends BaseController
{
    public function index()
    {
        return view('modules/index', [
            'title'   => 'Modules',
            'modules' => (new ModuleModel())->orderBy('name')->findAll(),
        ]);
    }

    public function create()
    {
        return view('modules/form', ['title' => 'Add module', 'module' => null]);
    }

    public function store()
    {
        $moduleModel = new ModuleModel();
        $rules = $moduleModel->getValidationRules();
        $rules['module_key'] .= '|is_unique[modules.module_key]';

        if (! $this->validate($rules)) {
            return view('modules/form', ['title' => 'Add module', 'module' => null, 'errors' => $this->validator->getErrors()]);
        }

        $data = [
            'module_key'  => strtolower($this->request->getPost('module_key')),
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_core'     => $this->request->getPost('is_core') ? 1 : 0,
        ];
        $id = $moduleModel->insert($data);

        (new AuditService())->log('create', 'module', 'module', $id, null, $data);

        return redirect()->to(site_url('modules'))->with('success', 'Module created.');
    }

    public function edit($id)
    {
        $module = (new ModuleModel())->find($id);
        if (! $module) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('modules/form', ['title' => 'Edit module', 'module' => $module]);
    }

    public function update($id)
    {
        $moduleModel = new ModuleModel();
        $module = $moduleModel->find($id);
        if (! $module) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[255]',
        ];
        if (! $this->validate($rules)) {
            return view('modules/form', ['title' => 'Edit module', 'module' => $module, 'errors' => $this->validator->getErrors()]);
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_core'     => $this->request->getPost('is_core') ? 1 : 0,
        ];
        $moduleModel->update($id, $data);

        (new AuditService())->log('update', 'module', 'module', (int) $id, $module, $data);

        return redirect()->to(site_url('modules'))->with('success', 'Module updated.');
    }
}
