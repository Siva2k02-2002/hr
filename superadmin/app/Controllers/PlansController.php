<?php

namespace App\Controllers;

use App\Models\ModuleModel;
use App\Models\PlanModel;
use App\Services\AuditService;

class PlansController extends BaseController
{
    public function index()
    {
        return view('plans/index', [
            'title' => 'Plans',
            'plans' => (new PlanModel())->orderBy('employee_limit', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('plans/form', [
            'title'   => 'Add plan',
            'plan'    => null,
            'modules' => (new ModuleModel())->orderBy('name')->findAll(),
            'selectedModuleIds' => [],
        ]);
    }

    public function store()
    {
        $rules = (new PlanModel())->getValidationRules();

        if (! $this->validate($rules)) {
            return view('plans/form', [
                'title'   => 'Add plan',
                'plan'    => null,
                'modules' => (new ModuleModel())->orderBy('name')->findAll(),
                'selectedModuleIds' => [],
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $planModel = new PlanModel();
        $planId = $planModel->insert([
            'code'             => strtolower($this->request->getPost('code')),
            'name'             => $this->request->getPost('name'),
            'employee_limit'   => (int) $this->request->getPost('employee_limit'),
            'branch_limit'     => (int) $this->request->getPost('branch_limit'),
            'storage_limit_mb' => (int) $this->request->getPost('storage_limit_mb'),
            'duration_days'    => (int) $this->request->getPost('duration_days'),
            'grace_days'       => (int) ($this->request->getPost('grace_days') ?: 7),
            'is_active'        => 1,
        ]);

        $moduleIds = array_map('intval', (array) $this->request->getPost('modules'));
        $planModel->syncModules($planId, $moduleIds);

        (new AuditService())->log('create', 'plan', 'plan', $planId, null, ['modules' => $moduleIds]);

        return redirect()->to(site_url('plans'))->with('success', 'Plan created.');
    }

    public function edit($id)
    {
        $planModel = new PlanModel();
        $plan = $planModel->find($id);
        if (! $plan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('plans/form', [
            'title'             => 'Edit plan',
            'plan'              => $plan,
            'modules'           => (new ModuleModel())->orderBy('name')->findAll(),
            'selectedModuleIds' => $planModel->moduleIds((int) $id),
        ]);
    }

    public function update($id)
    {
        $planModel = new PlanModel();
        $plan = $planModel->find($id);
        if (! $plan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name'             => 'required|min_length[2]|max_length[100]',
            'employee_limit'   => 'required|integer|greater_than[0]',
            'branch_limit'     => 'required|integer|greater_than[0]',
            'storage_limit_mb' => 'required|integer|greater_than[0]',
            'duration_days'    => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return view('plans/form', [
                'title'             => 'Edit plan',
                'plan'              => $plan,
                'modules'           => (new ModuleModel())->orderBy('name')->findAll(),
                'selectedModuleIds' => $planModel->moduleIds((int) $id),
                'errors'            => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'name'             => $this->request->getPost('name'),
            'employee_limit'   => (int) $this->request->getPost('employee_limit'),
            'branch_limit'     => (int) $this->request->getPost('branch_limit'),
            'storage_limit_mb' => (int) $this->request->getPost('storage_limit_mb'),
            'duration_days'    => (int) $this->request->getPost('duration_days'),
            'grace_days'       => (int) ($this->request->getPost('grace_days') ?: 0),
        ];
        $planModel->update($id, $data);

        $moduleIds = array_map('intval', (array) $this->request->getPost('modules'));
        $planModel->syncModules((int) $id, $moduleIds);

        (new AuditService())->log('update', 'plan', 'plan', (int) $id, $plan, $data + ['modules' => $moduleIds]);

        return redirect()->to(site_url('plans'))->with('success', 'Plan updated.');
    }

    public function toggle($id)
    {
        $planModel = new PlanModel();
        $plan = $planModel->find($id);
        if (! $plan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $newState = $plan['is_active'] ? 0 : 1;
        $planModel->update($id, ['is_active' => $newState]);

        (new AuditService())->log('status_change', 'plan', 'plan', (int) $id, ['is_active' => $plan['is_active']], ['is_active' => $newState]);

        return redirect()->to(site_url('plans'))->with('success', $newState ? 'Plan activated.' : 'Plan deactivated.');
    }
}
