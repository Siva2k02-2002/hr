<?php

namespace App\Controllers;

use App\Models\AttendanceDeviceModel;
use App\Services\AttendanceDeviceService;
use RuntimeException;

class AttendanceDevicesController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $model  = (new AttendanceDeviceModel(service('tenantContext')->db()))->withEmployee();

        if ($status !== '') {
            $model->where('attendance_devices.status', $status);
        }

        $devices = $model->orderBy('attendance_devices.last_login_at', 'DESC')->findAll();

        return view('attendance/devices/index', ['title' => 'Devices', 'devices' => $devices, 'filters' => ['status' => $status]]);
    }

    public function approve($id)
    {
        return $this->transition($id, 'approve');
    }

    public function reject($id)
    {
        return $this->transition($id, 'reject');
    }

    public function block($id)
    {
        return $this->transition($id, 'block');
    }

    private function transition($id, string $action)
    {
        try {
            (new AttendanceDeviceService())->{$action}((int) $id, (int) session('tenant_user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/devices'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/devices'))->with('success', 'Device ' . $action . 'd.');
    }
}
