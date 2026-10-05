<?php

namespace App\Controllers;

use App\Models\AttendanceShiftAssignmentModel;
use App\Models\AttendanceShiftModel;
use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\EmployeeModel;
use App\Services\AttendanceShiftAssignmentService;
use CodeIgniter\Exceptions\PageNotFoundException;

class AttendanceShiftAssignmentsController extends BaseController
{
    private const EMPLOYMENT_TYPES = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'intern' => 'Intern', 'consultant' => 'Consultant'];

    public function form()
    {
        $categories = array_filter(array_column(
            (new EmployeeModel(service('tenantContext')->db()))->select('employment_category')->distinct()->where('employment_category IS NOT NULL')->findAll(),
            'employment_category'
        ));

        return view('attendance/shift_assignments/form', [
            'title'               => 'Assign Shift',
            'shifts'              => (new AttendanceShiftModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'branches'            => (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'departments'         => (new DepartmentModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'designations'        => (new DesignationModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'employmentTypes'     => self::EMPLOYMENT_TYPES,
            'employmentCategories'=> $categories,
        ]);
    }

    public function assign()
    {
        $shiftId         = (int) $this->request->getPost('shift_id');
        $effectiveFrom   = (string) $this->request->getPost('effective_from') ?: date('Y-m-d');
        $selectionType   = (string) $this->request->getPost('selection_type');
        $replaceExisting = (bool) $this->request->getPost('replace_existing');
        $userId          = (int) session('tenant_user_id');
        $service         = new AttendanceShiftAssignmentService();

        if ($shiftId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please select a shift.');
        }
        if (! $this->request->getPost('effective_from')) {
            return redirect()->back()->withInput()->with('error', 'Effective from date is required.');
        }

        $result = match ($selectionType) {
            'department'          => $service->assignByDepartment((int) $this->request->getPost('department_id'), $shiftId, $effectiveFrom, $userId, $replaceExisting),
            'branch'              => $service->assignByBranch((int) $this->request->getPost('branch_id'), $shiftId, $effectiveFrom, $userId, $replaceExisting),
            'designation'         => $service->assignByDesignation((int) $this->request->getPost('designation_id'), $shiftId, $effectiveFrom, $userId, $replaceExisting),
            'employment_type'     => $service->assignByEmploymentType((string) $this->request->getPost('employment_type'), $shiftId, $effectiveFrom, $userId, $replaceExisting),
            'employment_category' => $service->assignByEmploymentCategory((string) $this->request->getPost('employment_category'), $shiftId, $effectiveFrom, $userId, $replaceExisting),
            default               => $service->assignBulk(array_map('intval', (array) $this->request->getPost('employee_ids')), $shiftId, $effectiveFrom, $userId, $replaceExisting),
        };

        $message = "Shift assigned to {$result['assigned']} employee(s), effective {$effectiveFrom}.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} employee(s) were skipped — they already have a shift assignment starting on or after that date."
                . ($replaceExisting ? '' : ' Use "Replace existing" to override them instead.');
        }

        return redirect()->to(site_url('attendance/shift-assignments'))->with('success', $message);
    }

    public function history($employeeId)
    {
        $history = (new AttendanceShiftAssignmentModel(service('tenantContext')->db()))->historyFor((int) $employeeId);

        return view('attendance/shift_assignments/history', ['title' => 'Shift Assignment History', 'history' => $history, 'employeeId' => $employeeId]);
    }
}
