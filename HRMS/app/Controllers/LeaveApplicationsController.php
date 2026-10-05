<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\LeaveApplicationDayModel;
use App\Models\LeaveApplicationModel;
use App\Models\LeaveApprovalHistoryModel;
use App\Models\LeaveDelegationModel;
use App\Models\LeaveTypeModel;
use App\Services\LeaveApplicationService;
use App\Services\LeaveApprovalService;
use RuntimeException;

/** The HR/manager-facing list + approval workflow — mirrors AttendanceRegularizationsController's request->approve/reject shape, extended with the 2-level dispatch and bulk actions. */
class LeaveApplicationsController extends BaseController
{
    public function index()
    {
        $filters = [
            'employee_id'    => (string) $this->request->getGet('employee_id'),
            'leave_type_id'  => (string) $this->request->getGet('leave_type_id'),
            'status'         => (string) $this->request->getGet('status'),
            'branch_id'      => (string) $this->request->getGet('branch_id'),
            'department_id'  => (string) $this->request->getGet('department_id'),
            'date_from'      => (string) $this->request->getGet('date_from'),
            'date_to'        => (string) $this->request->getGet('date_to'),
            'level'          => (string) $this->request->getGet('level'),
        ];

        $model        = (new LeaveApplicationModel(service('tenantContext')->db()))->withEmployee()->applyFilters($filters);
        $applications = $model->orderBy('leave_applications.created_at', 'DESC')->paginate(15, 'leave_applications');

        $db = service('tenantContext')->db();

        return view('leave/applications/index', [
            'title'         => 'Leave Applications',
            'applications'  => $applications,
            'pager'         => $model->pager,
            'filters'       => $filters,
            'types'         => (new LeaveTypeModel($db))->where('status', 'active')->orderBy('sort_order')->findAll(),
            'branches'      => (new BranchModel($db))->where('status', 'active')->findAll(),
            'departments'   => (new DepartmentModel($db))->where('status', 'active')->findAll(),
        ]);
    }

    public function view($id)
    {
        $application = (new LeaveApplicationModel(service('tenantContext')->db()))->withEmployee()->where('leave_applications.id', $id)->first();
        if (! $application) {
            return redirect()->to(site_url('leave'))->with('error', 'Leave application not found.');
        }

        $db = service('tenantContext')->db();

        return view('leave/applications/show', [
            'title'       => 'Leave Application #' . $id,
            'application' => $application,
            'days'        => (new LeaveApplicationDayModel($db))->forApplication((int) $id),
            'history'     => (new LeaveApprovalHistoryModel($db))->forApplication((int) $id),
            'delegation'  => (new LeaveDelegationModel($db))->forApplication((int) $id),
        ]);
    }

    public function approve($id)
    {
        try {
            $this->dispatchByLevel((int) $id, 'approveLevel1', 'approveLevel2');
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/' . $id))->with('success', 'Leave application approved at this level.');
    }

    public function reject($id)
    {
        try {
            $this->dispatchByLevel((int) $id, 'rejectLevel1', 'rejectLevel2');
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/' . $id))->with('success', 'Leave application rejected.');
    }

    public function cancel($id)
    {
        try {
            (new LeaveApplicationService())->cancel((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('reason') ?: 'Cancelled by HR');
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave'))->with('success', 'Leave application cancelled.');
    }

    public function overrideApprove($id)
    {
        try {
            (new LeaveApprovalService())->overrideApprove((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('reason'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/' . $id))->with('success', 'Leave application approved (override).');
    }

    public function overrideReject($id)
    {
        try {
            (new LeaveApprovalService())->overrideReject((int) $id, (int) session('tenant_user_id'), (string) $this->request->getPost('reason'));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/' . $id))->with('success', 'Leave application rejected (override).');
    }

    public function bulkApprove()
    {
        [$ok, $errors] = $this->bulkAct((array) $this->request->getPost('ids'), 'approveLevel1', 'approveLevel2');

        return redirect()->to(site_url('leave'))->with($errors === [] ? 'success' : 'error', "{$ok} application(s) approved." . ($errors ? ' Skipped: ' . implode('; ', $errors) : ''));
    }

    public function bulkReject()
    {
        [$ok, $errors] = $this->bulkAct((array) $this->request->getPost('ids'), 'rejectLevel1', 'rejectLevel2');

        return redirect()->to(site_url('leave'))->with($errors === [] ? 'success' : 'error', "{$ok} application(s) rejected." . ($errors ? ' Skipped: ' . implode('; ', $errors) : ''));
    }

    public function bulkCancel()
    {
        $ids     = (array) $this->request->getPost('ids');
        $service = new LeaveApplicationService();
        $ok      = 0;
        $errors  = [];

        foreach ($ids as $id) {
            try {
                $service->cancel((int) $id, (int) session('tenant_user_id'), 'Bulk cancelled by HR');
                $ok++;
            } catch (RuntimeException $e) {
                $errors[] = "#{$id}: " . $e->getMessage();
            }
        }

        return redirect()->to(site_url('leave'))->with($errors === [] ? 'success' : 'error', "{$ok} application(s) cancelled." . ($errors ? ' Skipped: ' . implode('; ', $errors) : ''));
    }

    private function dispatchByLevel(int $id, string $level1Method, string $level2Method): void
    {
        $application = (new LeaveApplicationModel(service('tenantContext')->db()))->find($id);
        if (! $application) {
            throw new RuntimeException('Leave application not found.');
        }

        $remarks = (string) $this->request->getPost('remarks') ?: null;
        $service = new LeaveApprovalService();
        $method  = $application['current_level'] === 'level1' ? $level1Method : $level2Method;
        $service->{$method}($id, (int) session('tenant_user_id'), $remarks);
    }

    /** @return array{0:int, 1:array<int,string>} */
    private function bulkAct(array $ids, string $level1Method, string $level2Method): array
    {
        $service = new LeaveApprovalService();
        $model   = new LeaveApplicationModel(service('tenantContext')->db());
        $ok      = 0;
        $errors  = [];

        foreach ($ids as $id) {
            try {
                $application = $model->find((int) $id);
                if (! $application) {
                    continue;
                }
                $method = $application['current_level'] === 'level1' ? $level1Method : $level2Method;
                $service->{$method}((int) $id, (int) session('tenant_user_id'));
                $ok++;
            } catch (RuntimeException $e) {
                $errors[] = "#{$id}: " . $e->getMessage();
            }
        }

        return [$ok, $errors];
    }
}
