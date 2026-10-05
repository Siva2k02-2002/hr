<?php

namespace App\Controllers;

use App\Models\AttendanceOvertimeModel;
use App\Models\EmployeeModel;
use App\Models\UserModel;

/** Foundation only — view + approve/reject the flag; no payroll math happens here. */
class AttendanceOvertimeController extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status') ?: 'pending';
        $model  = (new AttendanceOvertimeModel(service('tenantContext')->db()))
            ->select("attendance_overtime.*, e.employee_code, CONCAT(e.first_name,' ',e.last_name) as employee_name")
            ->join('employees e', 'e.id = attendance_overtime.employee_id');

        if ($status !== '') {
            $model->where('attendance_overtime.status', $status);
        }

        $records = $model->orderBy('attendance_overtime.attendance_date', 'DESC')->findAll();

        return view('attendance/overtime/index', ['title' => 'Overtime', 'records' => $records, 'filters' => ['status' => $status]]);
    }

    public function approve($id)
    {
        return $this->decide((int) $id, 'approved', 'Overtime approved.');
    }

    public function reject($id)
    {
        return $this->decide((int) $id, 'rejected', 'Overtime rejected.');
    }

    private function decide(int $id, string $status, string $message)
    {
        $model  = new AttendanceOvertimeModel(service('tenantContext')->db());
        $record = $model->find($id);
        if (! $record) {
            return redirect()->to(site_url('attendance/overtime'))->with('error', 'Overtime record not found.');
        }
        if ($record['status'] !== 'pending') {
            return redirect()->to(site_url('attendance/overtime'))->with('error', 'This overtime record has already been reviewed.');
        }
        if (! $this->canReview((int) $record['employee_id'])) {
            return redirect()->to(site_url('attendance/overtime'))->with('error', 'You are not authorized to review this record.');
        }

        $model->update($id, ['status' => $status]);

        return redirect()->to(site_url('attendance/overtime'))->with('success', $message);
    }

    /**
     * `attendance.approve` is granted company-wide to every Manager and HR Manager, so the
     * route permission alone doesn't stop a manager from reviewing overtime for an employee
     * who isn't one of their own reports — HR Manager/Company Admin act company-wide by
     * design; a plain Manager may only review employees who currently report to them.
     */
    private function canReview(int $employeeId): bool
    {
        $actorId = (int) session('tenant_user_id');
        $roles   = (new UserModel(service('tenantContext')->db()))->roleSlugs($actorId);
        if (array_intersect($roles, ['hr-manager', 'company-admin']) !== []) {
            return true;
        }

        $employeeModel = new EmployeeModel(service('tenantContext')->db());
        $employee      = $employeeModel->find($employeeId);
        $manager       = $employee && $employee['reporting_manager_id'] ? $employeeModel->find($employee['reporting_manager_id']) : null;

        return (int) ($manager['user_id'] ?? 0) === $actorId;
    }
}
