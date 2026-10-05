<?php

namespace App\Controllers;

use App\Models\PayrollSalaryComponentModel;
use App\Services\SalaryComponentService;
use RuntimeException;

class SalaryComponentsController extends BaseController
{
    public function index()
    {
        $filters = [
            'q'      => (string) $this->request->getGet('q'),
            'type'   => (string) $this->request->getGet('type'),
            'status' => (string) $this->request->getGet('status'),
        ];

        $model      = (new PayrollSalaryComponentModel(service('tenantContext')->db()))->applyFilters($filters);
        $components = $model->orderBy('display_order')->orderBy('name')->paginate(15, 'components');

        return view('payroll/components/index', [
            'title'      => 'Salary Components',
            'components' => $components,
            'pager'      => $model->pager,
            'filters'    => $filters,
        ]);
    }

    public function create()
    {
        return view('payroll/components/form', ['title' => 'Add Salary Component', 'component' => null]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('payroll/components/form', ['title' => 'Add Salary Component', 'component' => null, 'errors' => $this->validator->getErrors()]);
        }

        try {
            (new SalaryComponentService())->create($this->payload());
        } catch (RuntimeException $e) {
            return view('payroll/components/form', ['title' => 'Add Salary Component', 'component' => null, 'errors' => ['code' => $e->getMessage()]]);
        }

        return redirect()->to(site_url('payroll/components'))->with('success', 'Salary component created.');
    }

    public function edit($id)
    {
        $component = (new PayrollSalaryComponentModel(service('tenantContext')->db()))->find($id);
        if (! $component) {
            return redirect()->to(site_url('payroll/components'))->with('error', 'Salary component not found.');
        }

        return view('payroll/components/form', ['title' => 'Edit Salary Component', 'component' => $component]);
    }

    public function update($id)
    {
        $component = (new PayrollSalaryComponentModel(service('tenantContext')->db()))->find($id);
        if (! $component) {
            return redirect()->to(site_url('payroll/components'))->with('error', 'Salary component not found.');
        }

        if (! $this->validate($this->rules())) {
            return view('payroll/components/form', ['title' => 'Edit Salary Component', 'component' => $component, 'errors' => $this->validator->getErrors()]);
        }

        try {
            (new SalaryComponentService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return view('payroll/components/form', ['title' => 'Edit Salary Component', 'component' => $component, 'errors' => ['code' => $e->getMessage()]]);
        }

        return redirect()->to(site_url('payroll/components'))->with('success', 'Salary component updated.');
    }

    public function delete($id)
    {
        try {
            (new SalaryComponentService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/components'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/components'))->with('success', 'Salary component deleted.');
    }

    private function rules(): array
    {
        return [
            'name'             => 'required|max_length[100]',
            'code'             => 'required|max_length[30]',
            'type'             => 'required|in_list[earning,deduction]',
            'calculation_type' => 'required|in_list[fixed,percentage,formula]',
        ];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'             => $post['name'],
            'code'             => strtoupper((string) $post['code']),
            'type'             => $post['type'],
            'calculation_type' => $post['calculation_type'],
            'percentage_of'    => $post['percentage_of'] !== '' ? strtoupper((string) $post['percentage_of']) : null,
            'formula'          => $post['formula'] !== '' ? $post['formula'] : null,
            'is_taxable'       => ! empty($post['is_taxable']) ? 1 : 0,
            'pf_applicable'    => ! empty($post['pf_applicable']) ? 1 : 0,
            'esi_applicable'   => ! empty($post['esi_applicable']) ? 1 : 0,
            'display_order'    => (int) ($post['display_order'] ?? 0),
            'status'           => $post['status'] ?? 'active',
        ];
    }
}
