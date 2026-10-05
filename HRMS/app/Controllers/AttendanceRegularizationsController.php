<?php

namespace App\Controllers;

use App\Models\AttendanceRegularizationModel;
use App\Models\EmployeeModel;
use App\Services\AttendanceRegularizationService;
use RuntimeException;

class AttendanceRegularizationsController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status') ?: 'pending';
        $model  = (new AttendanceRegularizationModel(service('tenantContext')->db()))->withEmployee();

        if ($status !== '') {
            $model->where('attendance_regularizations.status', $status);
        }

        $requests = $model->orderBy('attendance_regularizations.created_at', 'DESC')->findAll();

        return view('attendance/regularizations/index', ['title' => 'Regularizations', 'requests' => $requests, 'filters' => ['status' => $status]]);
    }

    public function form()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-attendance'))->with('error', 'No employee profile is linked to your account.');
        }

        $myRequests = (new AttendanceRegularizationModel(service('tenantContext')->db()))
            ->where('employee_id', (int) $employee['id'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('attendance/regularizations/form', ['title' => 'Request Regularization', 'myRequests' => $myRequests]);
    }

    public function store()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->to(site_url('my-attendance'))->with('error', 'No employee profile is linked to your account.');
        }

        if (! $this->validate([
            'attendance_date' => 'required|valid_date',
            'reason'          => 'required|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $attachmentPath = null;
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid()) {
            $dir = WRITEPATH . 'uploads/tenants/' . tenant()->companyCode() . '/regularizations';
            if (! is_dir($dir)) {
                mkdir($dir, 0750, true);
            }
            $name = bin2hex(random_bytes(16)) . '.' . $file->getExtension();
            $file->move($dir, $name);
            $attachmentPath = 'tenants/' . tenant()->companyCode() . '/regularizations/' . $name;
        }

        (new AttendanceRegularizationService())->request([
            'employee_id'          => (int) $employee['id'],
            'attendance_date'      => $this->request->getPost('attendance_date'),
            'reason'               => $this->request->getPost('reason'),
            'requested_punch_in'   => $this->request->getPost('requested_punch_in') ?: null,
            'requested_punch_out'  => $this->request->getPost('requested_punch_out') ?: null,
            'attachment_path'      => $attachmentPath,
            'created_by'           => (int) session('tenant_user_id'),
        ]);

        return redirect()->to(site_url('my-attendance'))->with('success', 'Regularization request submitted.');
    }

    public function approve($id)
    {
        try {
            (new AttendanceRegularizationService())->approve((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('remarks') ?: null);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/regularizations'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/regularizations'))->with('success', 'Regularization approved and attendance updated.');
    }

    public function reject($id)
    {
        try {
            (new AttendanceRegularizationService())->reject((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('remarks') ?: null);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/regularizations'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/regularizations'))->with('success', 'Regularization rejected.');
    }

    private function currentEmployee(): ?array
    {
        return (new EmployeeModel(service('tenantContext')->db()))->where('user_id', session('tenant_user_id'))->first();
    }
}
