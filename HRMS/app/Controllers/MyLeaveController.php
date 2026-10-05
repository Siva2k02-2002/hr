<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\LeaveApplicationModel;
use App\Models\LeaveApprovalHistoryModel;
use App\Models\LeaveDelegationModel;
use App\Models\LeaveTypeModel;
use App\Services\LeaveApplicationService;
use App\Services\LeaveBalanceService;
use RuntimeException;

class MyLeaveController extends BaseController
{
    public function index()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return view('leave/my/index', ['title' => 'My Leave', 'employee' => null]);
        }

        $db            = service('tenantContext')->db();
        $financialYear = leave_financial_year();
        $applications  = (new LeaveApplicationModel($db))->withEmployee()
            ->where('leave_applications.employee_id', $employee['id'])
            ->orderBy('leave_applications.created_at', 'DESC')
            ->findAll();

        $balances = (new LeaveBalanceService())->resolveOrCreateAllVisible($employee, $financialYear);

        return view('leave/my/index', [
            'title'    => 'My Leave',
            'employee' => $employee,
            'cards'    => [
                'available' => array_sum(array_column($balances, 'closing_balance')),
                'pending'   => count(array_filter($applications, static fn ($a) => $a['status'] === 'pending')),
                'approved'  => count(array_filter($applications, static fn ($a) => $a['status'] === 'approved')),
                'rejected'  => count(array_filter($applications, static fn ($a) => $a['status'] === 'rejected')),
                'upcoming'  => count(array_filter($applications, static fn ($a) => $a['status'] === 'approved' && $a['from_date'] >= date('Y-m-d'))),
            ],
            'recent' => array_slice($applications, 0, 5),
        ]);
    }

    public function apply()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-leave'))->with('error', 'No employee profile is linked to your account.');
        }

        $db    = service('tenantContext')->db();
        $types = (new LeaveTypeModel($db))->where('status', 'active')->orderBy('sort_order')->findAll();
        $peers = (new EmployeeModel($db))->select('id, employee_code, first_name, last_name')
            ->where('branch_id', $employee['branch_id'])->where('id !=', $employee['id'])->where('status', 'active')->findAll();

        return view('leave/my/apply', ['title' => 'Apply Leave', 'employee' => $employee, 'types' => $types, 'peers' => $peers]);
    }

    public function store()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-leave'))->with('error', 'No employee profile is linked to your account.');
        }

        if ($this->rateLimited('leave_apply', 10, MINUTE)) {
            return $this->rateLimitedResponse('Too many leave applications submitted. Please slow down.');
        }

        if (! $this->validate([
            'leave_type_id' => 'required|integer',
            'from_date'     => 'required|valid_date',
            'to_date'       => 'required|valid_date',
            'reason'        => 'required|max_length[500]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $attachmentPath = null;
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid()) {
            $dir = WRITEPATH . 'uploads/tenants/' . tenant()->companyCode() . '/leave';
            if (! is_dir($dir)) {
                mkdir($dir, 0750, true);
            }
            $name = bin2hex(random_bytes(16)) . '.' . $file->getExtension();
            $file->move($dir, $name);
            $attachmentPath = 'tenants/' . tenant()->companyCode() . '/leave/' . $name;
        }

        $post = $this->request->getPost();

        try {
            (new LeaveApplicationService())->submit([
                'employee_id'             => (int) $employee['id'],
                'leave_type_id'           => (int) $post['leave_type_id'],
                'from_date'               => $post['from_date'],
                'to_date'                 => $post['to_date'],
                'is_half_day'             => ! empty($post['is_half_day']),
                'half_day_session'        => $post['half_day_session'] ?? null,
                'reason'                  => $post['reason'],
                'emergency_contact_name'  => $post['emergency_contact_name'] ?: null,
                'emergency_contact_phone' => $post['emergency_contact_phone'] ?: null,
                'attachment_path'         => $attachmentPath,
                'is_emergency'            => ! empty($post['is_emergency']),
                'delegate_employee_id'    => $post['delegate_employee_id'] ?: null,
                'delegation_notes'        => $post['delegation_notes'] ?: null,
                'created_by'              => session('tenant_user_id'),
            ]);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('my-leave/applications'))->with('success', 'Leave application submitted.');
    }

    public function myApplications()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-leave'))->with('error', 'No employee profile is linked to your account.');
        }

        $status = (string) $this->request->getGet('status');
        $model  = (new LeaveApplicationModel(service('tenantContext')->db()))->withEmployee()->where('leave_applications.employee_id', $employee['id']);
        if ($status !== '') {
            $model->where('leave_applications.status', $status);
        }
        $applications = $model->orderBy('leave_applications.created_at', 'DESC')->paginate(15, 'my_leave');

        return view('leave/my/my_applications', ['title' => 'My Applications', 'applications' => $applications, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function balance()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-leave'))->with('error', 'No employee profile is linked to your account.');
        }

        $financialYear = (int) ($this->request->getGet('fy') ?: leave_financial_year());
        $balances      = (new LeaveBalanceService())->resolveOrCreateAllVisible($employee, $financialYear);

        return view('leave/my/balance', ['title' => 'My Leave Balance', 'balances' => $balances, 'financialYear' => $financialYear]);
    }

    public function calendar()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-leave'))->with('error', 'No employee profile is linked to your account.');
        }

        return redirect()->to(site_url('leave/calendar?employee_id=' . $employee['id']));
    }

    public function history()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-leave'))->with('error', 'No employee profile is linked to your account.');
        }

        $applications = (new LeaveApplicationModel(service('tenantContext')->db()))->withEmployee()
            ->where('leave_applications.employee_id', $employee['id'])
            ->whereIn('leave_applications.status', ['approved', 'rejected', 'cancelled'])
            ->orderBy('leave_applications.updated_at', 'DESC')
            ->findAll();

        $historyByApp = [];
        $historyModel = new LeaveApprovalHistoryModel(service('tenantContext')->db());
        foreach ($applications as $app) {
            $historyByApp[$app['id']] = $historyModel->forApplication((int) $app['id']);
        }

        return view('leave/my/history', ['title' => 'Leave History', 'applications' => $applications, 'historyByApp' => $historyByApp]);
    }

    public function cancel($id)
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-leave'))->with('error', 'No employee profile is linked to your account.');
        }

        $application = (new LeaveApplicationModel(service('tenantContext')->db()))->find($id);
        if (! $application || (int) $application['employee_id'] !== (int) $employee['id']) {
            return redirect()->to(site_url('my-leave/applications'))->with('error', 'Leave application not found.');
        }

        // Employee self-service may only cancel a still-pending application — once Manager/HR
        // approves it, it's an approved HR record and cancellation is an admin-side action
        // (LeaveApplicationsController::cancel, gated by leave.cancel.any). Rejected here in
        // the controller means the service's balance-restore/attendance-rollback/history side
        // effects never run for an approved leave via this self-service path, regardless of
        // what the button shows.
        if ($application['status'] !== 'pending') {
            return redirect()->to(site_url('my-leave/applications'))->with('error', 'Only a pending leave application can be cancelled.');
        }

        try {
            (new LeaveApplicationService())->cancel((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('reason') ?: 'Cancelled by employee');
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('my-leave/applications'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('my-leave/applications'))->with('success', 'Leave application cancelled.');
    }

    private function currentEmployee(): ?array
    {
        return (new EmployeeModel(service('tenantContext')->db()))->where('user_id', session('tenant_user_id'))->first();
    }
}
