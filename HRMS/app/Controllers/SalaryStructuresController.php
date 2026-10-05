<?php

namespace App\Controllers;

use App\Models\PayrollSalaryComponentModel;
use App\Models\PayrollSalaryStructureItemModel;
use App\Models\PayrollSalaryStructureModel;
use App\Services\SalaryStructureService;
use RuntimeException;

class SalaryStructuresController extends BaseController
{
    public function index()
    {
        $filters = [
            'q'      => (string) $this->request->getGet('q'),
            'status' => (string) $this->request->getGet('status'),
        ];

        $model      = (new PayrollSalaryStructureModel(service('tenantContext')->db()))->applyFilters($filters);
        $structures = $model->orderBy('name')->paginate(15, 'structures');

        return view('payroll/structures/index', [
            'title'      => 'Salary Structures',
            'structures' => $structures,
            'pager'      => $model->pager,
            'filters'    => $filters,
        ]);
    }

    public function create()
    {
        return view('payroll/structures/form', ['title' => 'Add Salary Structure', 'structure' => null]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('payroll/structures/form', ['title' => 'Add Salary Structure', 'structure' => null, 'errors' => $this->validator->getErrors()]);
        }

        $id = (new SalaryStructureService())->create($this->payload());

        return redirect()->to(site_url('payroll/structures/' . $id . '/builder'))->with('success', 'Salary structure created — add its components below.');
    }

    public function edit($id)
    {
        $structure = (new PayrollSalaryStructureModel(service('tenantContext')->db()))->find($id);
        if (! $structure) {
            return redirect()->to(site_url('payroll/structures'))->with('error', 'Salary structure not found.');
        }

        return view('payroll/structures/form', ['title' => 'Edit Salary Structure', 'structure' => $structure]);
    }

    public function update($id)
    {
        $structure = (new PayrollSalaryStructureModel(service('tenantContext')->db()))->find($id);
        if (! $structure) {
            return redirect()->to(site_url('payroll/structures'))->with('error', 'Salary structure not found.');
        }

        if (! $this->validate($this->rules())) {
            return view('payroll/structures/form', ['title' => 'Edit Salary Structure', 'structure' => $structure, 'errors' => $this->validator->getErrors()]);
        }

        (new SalaryStructureService())->update((int) $id, $this->payload());

        return redirect()->to(site_url('payroll/structures'))->with('success', 'Salary structure updated.');
    }

    public function delete($id)
    {
        try {
            (new SalaryStructureService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/structures'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/structures'))->with('success', 'Salary structure deleted.');
    }

    public function builder($id)
    {
        $structure = (new PayrollSalaryStructureModel(service('tenantContext')->db()))->find($id);
        if (! $structure) {
            return redirect()->to(site_url('payroll/structures'))->with('error', 'Salary structure not found.');
        }

        return view('payroll/structures/builder', [
            'title'      => 'Build: ' . $structure['name'],
            'structure'  => $structure,
            'items'      => (new SalaryStructureService())->items((int) $id),
            // All statuses, not just active — an existing row may reference a component
            // that has since gone inactive, and the builder still needs its name/code to
            // render that row correctly instead of losing track of what's selected. The
            // view itself keeps inactive components out of the picker for new rows.
            'components' => (new PayrollSalaryComponentModel(service('tenantContext')->db()))->orderBy('display_order')->findAll(),
        ]);
    }

    public function saveBuilder($id)
    {
        $rows = json_decode((string) $this->request->getPost('items'), true) ?: [];

        try {
            (new SalaryStructureService())->saveItems((int) $id, $rows);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/structures/' . $id . '/builder'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/structures/' . $id . '/builder'))->with('success', 'Salary structure components saved.');
    }

    /** Live gross/net preview — same calculation path SalaryStructureService::calculate() uses at actual payroll generation time. */
    public function preview($id)
    {
        $rows       = json_decode((string) $this->request->getPost('items'), true) ?: [];
        $grossInput = (float) $this->request->getPost('gross_salary');

        $components = (new PayrollSalaryComponentModel(service('tenantContext')->db()))->findAll();
        $byId       = array_column($components, null, 'id');

        $items = [];
        foreach ($rows as $order => $row) {
            $component = $byId[(int) $row['salary_component_id']] ?? null;
            if (! $component) {
                continue;
            }
            $items[] = [
                'calculation_type'         => $row['calculation_type'],
                'value'                    => (float) ($row['value'] ?? 0),
                'formula'                  => $row['formula'] ?? null,
                'component_code'           => $component['code'],
                'component_type'           => $component['type'],
                'component_percentage_of'  => $component['percentage_of'],
                'is_taxable'               => $component['is_taxable'],
                'pf_applicable'            => $component['pf_applicable'],
                'esi_applicable'           => $component['esi_applicable'],
                'display_order'            => $order,
            ];
        }

        try {
            $result = (new SalaryStructureService())->calculate($items, $grossInput);
        } catch (RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->response->setJSON(['success' => true] + $result);
    }

    private function rules(): array
    {
        return [
            'name'           => 'required|max_length[150]',
            'effective_from' => 'required|valid_date',
        ];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'           => $post['name'],
            'description'    => $post['description'] ?: null,
            'effective_from' => $post['effective_from'],
            'status'         => $post['status'] ?? 'active',
        ];
    }
}
