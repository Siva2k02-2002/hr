<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\LeaveEncashmentModel;
use App\Models\LeaveTypeModel;
use App\Services\LeaveEncashmentService;
use RuntimeException;

class LeaveEncashmentsController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new LeaveEncashmentModel(service('tenantContext')->db()))->withEmployee();
        if ($status !== '') {
            $model->where('leave_encashments.status', $status);
        }
        $encashments = $model->orderBy('leave_encashments.created_at', 'DESC')->paginate(15, 'leave_encashments');

        return view('leave/encashments/index', ['title' => 'Leave Encashments', 'encashments' => $encashments, 'pager' => $model->pager, 'filters' => ['status' => $status]]);
    }

    public function create()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('leave/encashments'))->with('error', 'No employee profile is linked to your account.');
        }

        $types = (new LeaveTypeModel(service('tenantContext')->db()))->where('status', 'active')->where('encashment_allowed', 1)->findAll();

        return view('leave/encashments/form', ['title' => 'Request Encashment', 'types' => $types]);
    }

    public function store()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('leave/encashments'))->with('error', 'No employee profile is linked to your account.');
        }

        try {
            (new LeaveEncashmentService())->request($employee, (int) $this->request->getPost('leave_type_id'), (float) $this->request->getPost('days_encashed'), (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/encashments'))->with('success', 'Encashment request submitted.');
    }

    public function approve($id)
    {
        try {
            (new LeaveEncashmentService())->approve((int) $id, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/encashments'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/encashments'))->with('success', 'Encashment approved.');
    }

    public function reject($id)
    {
        try {
            (new LeaveEncashmentService())->reject((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('remarks') ?: null);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/encashments'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/encashments'))->with('success', 'Encashment rejected.');
    }

    private function currentEmployee(): ?array
    {
        return (new EmployeeModel(service('tenantContext')->db()))->where('user_id', session('tenant_user_id'))->first();
    }
}
