<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\EmployeeModel;
use App\Services\AttendanceService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class AttendanceController extends BaseController
{
    private const STATUSES = ['present', 'absent', 'half_day', 'holiday', 'weekly_off', 'leave', 'on_duty', 'work_from_home', 'late', 'missed_punch'];

    public function index()
    {
        $model = (new AttendanceModel(service('tenantContext')->db()))->withRelations();

        $filters = [
            'date_from'      => (string) $this->request->getGet('date_from') ?: date('Y-m-01'),
            'date_to'        => (string) $this->request->getGet('date_to') ?: date('Y-m-d'),
            'employee_id'    => $this->request->getGet('employee_id'),
            'branch_id'      => $this->request->getGet('branch_id'),
            'department_id'  => $this->request->getGet('department_id'),
            'status'         => $this->request->getGet('status'),
        ];

        $model->where('attendance.attendance_date >=', $filters['date_from'])->where('attendance.attendance_date <=', $filters['date_to']);
        if (! empty($filters['employee_id'])) {
            $model->where('attendance.employee_id', $filters['employee_id']);
        }
        if (! empty($filters['branch_id'])) {
            $model->where('e.branch_id', $filters['branch_id']);
        }
        if (! empty($filters['department_id'])) {
            $model->where('e.department_id', $filters['department_id']);
        }
        if (! empty($filters['status'])) {
            $model->where('attendance.status', $filters['status']);
        }

        $records = $model->orderBy('attendance.attendance_date', 'DESC')->orderBy('e.first_name')->paginate(20, 'attendance');

        return view('attendance/index', [
            'title'       => 'Attendance',
            'records'     => $records,
            'pager'       => $model->pager,
            'filters'     => $filters,
            'statuses'    => self::STATUSES,
            'branches'    => (new BranchModel(service('tenantContext')->db()))->findAll(),
            'departments' => (new DepartmentModel(service('tenantContext')->db()))->findAll(),
            'selectedEmployee' => $filters['employee_id'] ? (new \App\Models\EmployeeModel(service('tenantContext')->db()))->find($filters['employee_id']) : null,
        ]);
    }

    public function edit($id)
    {
        $record = (new AttendanceModel(service('tenantContext')->db()))->withRelations()->find($id);
        if (! $record) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('attendance/edit', ['title' => 'Edit Attendance', 'record' => $record, 'statuses' => self::STATUSES]);
    }

    public function update($id)
    {
        $record = (new AttendanceModel(service('tenantContext')->db()))->find($id);
        if (! $record) {
            throw PageNotFoundException::forPageNotFound();
        }

        $status = (string) $this->request->getPost('status');
        if (! in_array($status, self::STATUSES, true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        (new AttendanceService())->markManual((int) $record['employee_id'], $record['attendance_date'], $status, (int) session('tenant_user_id'));

        return redirect()->to(site_url('attendance'))->with('success', 'Attendance updated.');
    }

    public function delete($id)
    {
        try {
            (new AttendanceService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance'))->with('success', 'Attendance record archived.');
    }

    public function bulkForm()
    {
        return view('attendance/bulk', [
            'title'       => 'Bulk Mark Attendance',
            'statuses'    => ['present', 'absent', 'half_day', 'holiday', 'work_from_home', 'on_duty'],
            'branches'    => (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'departments' => (new DepartmentModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
        ]);
    }

    public function bulkMark()
    {
        $date          = (string) $this->request->getPost('date');
        $status        = (string) $this->request->getPost('status');
        $selectionType = (string) $this->request->getPost('selection_type');

        if (! $date || ! in_array($status, ['present', 'absent', 'half_day', 'holiday', 'work_from_home', 'on_duty'], true)) {
            return redirect()->back()->with('error', 'Date and a valid status are required.');
        }

        $employeeModel = new EmployeeModel(service('tenantContext')->db());
        $employeeModel->select('id')->where('status !=', 'terminated');

        $employeeIds = match ($selectionType) {
            'branch'     => array_column($employeeModel->where('branch_id', (int) $this->request->getPost('branch_id'))->findAll(), 'id'),
            'department' => array_column($employeeModel->where('department_id', (int) $this->request->getPost('department_id'))->findAll(), 'id'),
            'individual' => (array) $this->request->getPost('employee_ids'),
            default      => array_column($employeeModel->findAll(), 'id'),
        };

        if ($employeeIds === []) {
            return redirect()->back()->with('error', 'No employees matched that selection.');
        }

        $count = (new AttendanceService())->markBulk(array_map('intval', $employeeIds), $date, $status, (int) session('tenant_user_id'));

        return redirect()->to(site_url('attendance'))->with('success', "Marked {$count} employees as " . str_replace('_', ' ', $status) . ' for ' . $date . '.');
    }
}
