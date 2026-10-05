<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\EmployeeModel;
use App\Models\LeavePolicyModel;
use App\Models\LeavePolicyRuleModel;
use App\Models\LeaveTypeModel;
use App\Services\LeavePolicyRuleService;
use App\Services\LeavePolicyService;
use RuntimeException;

class LeavePoliciesController extends BaseController
{
    public function index()
    {
        $model    = new LeavePolicyModel(service('tenantContext')->db());
        $policies = $model->select('leave_policies.*, b.name as branch_name, d.name as department_name, dg.name as designation_name')
            ->join('branches b', 'b.id = leave_policies.branch_id', 'left')
            ->join('departments d', 'd.id = leave_policies.department_id', 'left')
            ->join('designations dg', 'dg.id = leave_policies.designation_id', 'left')
            ->orderBy('leave_policies.priority', 'DESC')
            ->paginate(15, 'leave_policies');

        return view('leave/policies/index', ['title' => 'Leave Policies', 'policies' => $policies, 'pager' => $model->pager]);
    }

    public function create()
    {
        return view('leave/policies/form', ['title' => 'Add Leave Policy', 'policy' => null] + $this->scopeOptions(null));
    }

    public function store()
    {
        if (! $this->validate(['name' => 'required|max_length[150]'])) {
            return view('leave/policies/form', ['title' => 'Add Leave Policy', 'policy' => null, 'errors' => $this->validator->getErrors()] + $this->scopeOptions(null));
        }

        $id = (new LeavePolicyService())->create($this->payload());

        return redirect()->to(site_url('leave/policies/' . $id . '/rules'))->with('success', 'Leave policy created. Now set its per-leave-type rules.');
    }

    public function edit($id)
    {
        $policy = (new LeavePolicyModel(service('tenantContext')->db()))->find($id);
        if (! $policy) {
            return redirect()->to(site_url('leave/policies'))->with('error', 'Leave policy not found.');
        }

        return view('leave/policies/form', ['title' => 'Edit Leave Policy', 'policy' => $policy] + $this->scopeOptions($policy));
    }

    public function update($id)
    {
        $policy = (new LeavePolicyModel(service('tenantContext')->db()))->find($id);
        if (! $policy) {
            return redirect()->to(site_url('leave/policies'))->with('error', 'Leave policy not found.');
        }

        if (! $this->validate(['name' => 'required|max_length[150]'])) {
            return view('leave/policies/form', ['title' => 'Edit Leave Policy', 'policy' => $policy, 'errors' => $this->validator->getErrors()] + $this->scopeOptions($policy));
        }

        try {
            (new LeavePolicyService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/policies'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/policies'))->with('success', 'Leave policy updated.');
    }

    public function delete($id)
    {
        try {
            (new LeavePolicyService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/policies'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/policies'))->with('success', 'Leave policy deleted.');
    }

    public function rules($id)
    {
        $policy = (new LeavePolicyModel(service('tenantContext')->db()))->find($id);
        if (! $policy) {
            return redirect()->to(site_url('leave/policies'))->with('error', 'Leave policy not found.');
        }

        $types        = (new LeaveTypeModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('sort_order')->findAll();
        $existingRules = [];
        foreach ((new LeavePolicyRuleModel(service('tenantContext')->db()))->forPolicy((int) $id) as $rule) {
            $existingRules[$rule['leave_type_id']] = $rule;
        }

        return view('leave/policies/rules', [
            'title'  => 'Leave Policy Rules — ' . $policy['name'],
            'policy' => $policy,
            'types'  => $types,
            'rules'  => $existingRules,
        ]);
    }

    public function saveRules($id)
    {
        $policy = (new LeavePolicyModel(service('tenantContext')->db()))->find($id);
        if (! $policy) {
            return redirect()->to(site_url('leave/policies'))->with('error', 'Leave policy not found.');
        }

        $post   = $this->request->getPost('rules') ?? [];
        $rules  = [];
        foreach ($post as $leaveTypeId => $r) {
            if (empty($r['enabled'])) {
                continue;
            }

            $rules[(int) $leaveTypeId] = [
                'annual_allocation'         => (float) ($r['annual_allocation'] ?? 0),
                'accrual_method'            => in_array($r['accrual_method'] ?? 'annual', ['annual', 'monthly'], true) ? $r['accrual_method'] : 'annual',
                'monthly_accrual_days'      => (float) ($r['monthly_accrual_days'] ?? 0),
                'carry_forward_allowed'     => ! empty($r['carry_forward_allowed']) ? 1 : 0,
                'carry_forward_limit'       => $r['carry_forward_limit'] === '' || ! isset($r['carry_forward_limit']) ? null : (float) $r['carry_forward_limit'],
                'carry_forward_unlimited'   => ! empty($r['carry_forward_unlimited']) ? 1 : 0,
                'encashment_allowed'        => ! empty($r['encashment_allowed']) ? 1 : 0,
                'max_consecutive_days'      => empty($r['max_consecutive_days']) ? null : (int) $r['max_consecutive_days'],
                'min_days_per_application'  => (float) ($r['min_days_per_application'] ?? 0.5),
                'max_applications_per_year' => empty($r['max_applications_per_year']) ? null : (int) $r['max_applications_per_year'],
                'sandwich_rule_applicable'  => ! empty($r['sandwich_rule_applicable']) ? 1 : 0,
                'notice_period_days'        => $r['notice_period_days'] === '' || ! isset($r['notice_period_days']) ? null : (int) $r['notice_period_days'],
                'status'                    => 'active',
            ];
        }

        (new LeavePolicyRuleService())->saveAll((int) $id, $rules);

        return redirect()->to(site_url('leave/policies/' . $id . '/rules'))->with('success', 'Policy rules saved.');
    }

    /** $policy's employee_id (edit mode) is fetched individually rather than pulling every active employee just to pre-select one option — the picker itself is populated live via api/employees/search. */
    private function scopeOptions(?array $policy): array
    {
        $db = service('tenantContext')->db();

        $employees = [];
        if (! empty($policy['employee_id'])) {
            $row = (new EmployeeModel($db))->select('id, employee_code, first_name, last_name')->find($policy['employee_id']);
            if ($row) {
                $employees = [$row];
            }
        }

        return [
            'branches'     => (new BranchModel($db))->where('status', 'active')->findAll(),
            'departments'  => (new DepartmentModel($db))->where('status', 'active')->findAll(),
            'designations' => (new DesignationModel($db))->where('status', 'active')->findAll(),
            'employees'    => $employees,
        ];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'            => $post['name'],
            'description'     => $post['description'] ?: null,
            'branch_id'       => $post['branch_id'] ?: null,
            'department_id'   => $post['department_id'] ?: null,
            'designation_id'  => $post['designation_id'] ?: null,
            'employment_type' => $post['employment_type'] ?: null,
            'employee_id'     => $post['employee_id'] ?: null,
            'is_default'      => ! empty($post['is_default']) ? 1 : 0,
            'effective_from'  => $post['effective_from'] ?: null,
            'effective_to'    => $post['effective_to'] ?: null,
            'priority'        => (int) ($post['priority'] ?? 0),
            'status'          => $post['status'] ?? 'active',
        ];
    }
}
