<?php

namespace App\Controllers;

use App\Models\AttendanceLocationModel;
use App\Models\AttendanceLogModel;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Services\AttendancePunchService;
use App\Services\AttendanceShiftAssignmentService;
use App\Services\AttendanceWeeklyOffService;
use RuntimeException;

class MyAttendanceController extends BaseController
{
    public function index()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return view('attendance/my_unlinked', ['title' => 'My Attendance']);
        }

        $db       = service('tenantContext')->db();
        $today    = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $shiftAssignments = new AttendanceShiftAssignmentService();
        $weeklyOffs       = new AttendanceWeeklyOffService();
        $todayShift       = $shiftAssignments->resolveShiftFor((int) $employee['id'], $today);
        $tomorrowShift    = $shiftAssignments->resolveShiftFor((int) $employee['id'], $tomorrow);

        return view('attendance/my', [
            'title'              => 'My Attendance',
            'employee'           => $employee,
            'todayAttendance'    => (new AttendanceModel($db))->forEmployeeAndDate((int) $employee['id'], $today),
            'hasOpenPunchIn'     => (new AttendanceLogModel($db))->hasOpenPunchIn((int) $employee['id']),
            'recentLogs'         => (new AttendanceLogModel($db))->recentFor((int) $employee['id'], 10),
            'month'              => (new AttendanceModel($db))->monthFor((int) $employee['id'], (int) date('Y'), (int) date('n')),
            'locations'          => (new AttendanceLocationModel($db))->forBranch((int) $employee['branch_id']),
            'todayShift'         => $todayShift,
            'tomorrowShift'      => $tomorrowShift,
            'isWeeklyOffToday'   => $weeklyOffs->isWeeklyOff($employee['branch_id'] ?? null, $today, $todayShift['id'] ?? null),
            'isWeeklyOffTomorrow'=> $weeklyOffs->isWeeklyOff($employee['branch_id'] ?? null, $tomorrow, $tomorrowShift['id'] ?? null),
        ]);
    }

    /** JSON — the punch screen's JS drives this directly since navigator.geolocation can only be read client-side. */
    public function punch()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'No employee profile is linked to your account.']);
        }

        if ($this->rateLimited('attendance_punch', 20, MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON(['success' => false, 'message' => 'Too many punch attempts. Please slow down.']);
        }

        $punchType = (string) $this->request->getPost('punch_type');
        $lat       = $this->request->getPost('lat');
        $lng       = $this->request->getPost('lng');
        $accuracy  = $this->request->getPost('accuracy');

        try {
            $result = (new AttendancePunchService())->punch((int) $employee['id'], $punchType, [
                'lat'        => ($lat !== null && $lat !== '') ? (float) $lat : null,
                'lng'        => ($lng !== null && $lng !== '') ? (float) $lng : null,
                'accuracy'   => ($accuracy !== null && $accuracy !== '') ? (float) $accuracy : null,
                'deviceUid'  => (string) $this->request->getPost('device_uid') ?: null,
                'deviceName' => (string) $this->request->getPost('device_name') ?: null,
            ]);
        } catch (RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage(), 'csrf_hash' => csrf_hash()]);
        }

        return $this->response->setJSON([
            'success'     => true,
            'message'     => $punchType === 'in' ? 'Punched in.' : 'Punched out.',
            'geofence'    => $result['geofence'],
            'attendance'  => $result['attendance'],
            'csrf_hash'   => csrf_hash(),
        ]);
    }

    private function currentEmployee(): ?array
    {
        return (new EmployeeModel(service('tenantContext')->db()))->where('user_id', session('tenant_user_id'))->first();
    }
}
