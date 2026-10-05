<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\PayrollEmployeeSalaryModel;
use App\Models\PayrollSalaryStructureModel;
use App\Services\PayrollEmployeeSalaryService;

class PayrollEmployeeSalaryController extends BaseController
{
    public function index()
    {
        $filters = [
            'q'      => (string) $this->request->getGet('q'),
            'status' => (string) $this->request->getGet('status'),
        ];

        $model = (new PayrollEmployeeSalaryModel(service('tenantContext')->db()))->withEmployee()->where('payroll_employee_salary.status !=', 'superseded');
        if ($filters['q'] !== '') {
            $model->groupStart()->like('e.first_name', $filters['q'])->orLike('e.last_name', $filters['q'])->orLike('e.employee_code', $filters['q'])->groupEnd();
        }
        if ($filters['status'] !== '') {
            $model->where('payroll_employee_salary.status', $filters['status']);
        }

        $assignments = $model->orderBy('e.first_name')->paginate(15, 'assignments');

        return view('payroll/employee_salary/index', [
            'title'       => 'Employee Salary',
            'assignments' => $assignments,
            'pager'       => $model->pager,
            'filters'     => $filters,
        ]);
    }

    public function assignForm($employeeId = null)
    {
        return view('payroll/employee_salary/assign', [
            'title'      => 'Assign Salary Structure',
            'employee'   => $employeeId ? (new EmployeeModel(service('tenantContext')->db()))->find($employeeId) : null,
            'structures' => (new PayrollSalaryStructureModel(service('tenantContext')->db()))->where('status', 'active')->orderBy('name')->findAll(),
        ]);
    }

    public function assign()
    {
        $post = $this->request->getPost();

        (new PayrollEmployeeSalaryService())->assign(
            (int) $post['employee_id'],
            (int) $post['salary_structure_id'],
            (string) $post['effective_from'],
            (float) $post['gross_salary']
        );

        return redirect()->to(site_url('payroll/employee-salary'))->with('success', 'Salary structure assigned.');
    }

    public function history($employeeId)
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->find($employeeId);
        if (! $employee) {
            return redirect()->to(site_url('payroll/employee-salary'))->with('error', 'Employee not found.');
        }

        return view('payroll/employee_salary/history', [
            'title'    => 'Salary History',
            'employee' => $employee,
            'history'  => (new PayrollEmployeeSalaryService())->history((int) $employeeId),
        ]);
    }
}
