<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\DepartmentModel;
use App\Models\EmployeeModel;
use App\Services\LeaveBalanceService;
use RuntimeException;

class LeaveBalancesController extends BaseController
{
    public function index()
    {
        $db            = service('tenantContext')->db();
        $financialYear = (int) ($this->request->getGet('fy') ?: leave_financial_year());
        $filters       = [
            'branch_id'     => (string) $this->request->getGet('branch_id'),
            'department_id' => (string) $this->request->getGet('department_id'),
            'q'             => (string) $this->request->getGet('q'),
        ];

        $employeeModel = (new EmployeeModel($db))->where('status', 'active');
        if ($filters['branch_id'] !== '') {
            $employeeModel->where('branch_id', $filters['branch_id']);
        }
        if ($filters['department_id'] !== '') {
            $employeeModel->where('department_id', $filters['department_id']);
        }
        if ($filters['q'] !== '') {
            $employeeModel->groupStart()->like('first_name', $filters['q'])->orLike('last_name', $filters['q'])->orLike('employee_code', $filters['q'])->groupEnd();
        }
        $employees = $employeeModel->orderBy('first_name')->paginate(15, 'leave_balances');

        $balanceService = new LeaveBalanceService();
        $rows           = [];
        foreach ($employees as $employee) {
            $rows[] = ['employee' => $employee, 'balances' => $balanceService->resolveOrCreateAllVisible($employee, $financialYear)];
        }

        return view('leave/balances/index', [
            'title'         => 'Leave Balances',
            'rows'          => $rows,
            'pager'         => $employeeModel->pager,
            'filters'       => $filters,
            'financialYear' => $financialYear,
            'branches'      => (new BranchModel($db))->where('status', 'active')->findAll(),
            'departments'   => (new DepartmentModel($db))->where('status', 'active')->findAll(),
        ]);
    }

    public function adjustForm($employeeId)
    {
        $db       = service('tenantContext')->db();
        $employee = (new EmployeeModel($db))->find($employeeId);
        if (! $employee) {
            return redirect()->to(site_url('leave/balances'))->with('error', 'Employee not found.');
        }

        $financialYear = leave_financial_year();
        $balances      = (new LeaveBalanceService())->resolveOrCreateAllVisible($employee, $financialYear);

        return view('leave/balances/adjust', ['title' => 'Adjust Balance — ' . $employee['first_name'] . ' ' . $employee['last_name'], 'employee' => $employee, 'balances' => $balances, 'financialYear' => $financialYear]);
    }

    public function adjust($employeeId)
    {
        $post = $this->request->getPost();
        if (empty($post['leave_type_id']) || ! isset($post['delta']) || $post['delta'] === '' || empty($post['reason'])) {
            return redirect()->back()->with('error', 'Leave type, adjustment amount, and reason are all required.');
        }

        try {
            (new LeaveBalanceService())->adjustManually(
                (int) $employeeId,
                (int) $post['leave_type_id'],
                (int) ($post['financial_year'] ?: leave_financial_year()),
                (float) $post['delta'],
                (string) $post['reason'],
                (string) ($post['effective_date'] ?: date('Y-m-d')),
                (int) session('tenant_user_id')
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/balances/' . $employeeId . '/adjust'))->with('success', 'Balance adjusted.');
    }
}
