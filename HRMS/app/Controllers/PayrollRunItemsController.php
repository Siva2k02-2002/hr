<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\PayrollAdjustmentModel;
use App\Models\PayrollRunItemModel;
use App\Services\PayrollAdjustmentService;
use RuntimeException;

class PayrollRunItemsController extends BaseController
{
    public function show($id)
    {
        $item = (new PayrollRunItemModel(service('tenantContext')->db()))->find($id);
        if (! $item) {
            return redirect()->to(site_url('payroll/runs'))->with('error', 'Payroll line not found.');
        }

        return view('payroll/run_items/show', [
            'title'       => 'Payroll Line',
            'item'        => $item,
            'employee'    => (new EmployeeModel(service('tenantContext')->db()))->withRelations()->find($item['employee_id']),
            'earnings'    => json_decode((string) $item['earnings_breakdown'], true) ?: ['items' => []],
            'deductions'  => json_decode((string) $item['deductions_breakdown'], true) ?: [],
            'adjustments' => (new PayrollAdjustmentModel(service('tenantContext')->db()))->forRunItem((int) $id),
        ]);
    }

    public function addAdjustment($id)
    {
        $post = $this->request->getPost();

        try {
            (new PayrollAdjustmentService())->add(
                (int) $id,
                (string) $post['type'],
                (string) $post['label'],
                (float) $post['amount'],
                $post['reason'] ?: null
            );
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/run-items/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/run-items/' . $id))->with('success', 'Adjustment added.');
    }

    public function deleteAdjustment($id, $adjustmentId)
    {
        try {
            (new PayrollAdjustmentService())->delete((int) $adjustmentId);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/run-items/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/run-items/' . $id))->with('success', 'Adjustment removed.');
    }
}
