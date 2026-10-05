<?php

namespace App\Controllers;

use App\Services\PayrollEsiService;
use App\Services\PayrollPfService;
use App\Services\PayrollProfessionalTaxService;
use App\Services\PayrollSettingsService;
use App\Services\PayrollTdsService;
use RuntimeException;

/** General + PF + ESI + Professional Tax slabs + TDS, one controller — all four statutory tabs live on the same settings.php view and post to their own action here. */
class PayrollSettingsController extends BaseController
{
    public function index()
    {
        return view('payroll/settings', [
            'title'    => 'Payroll Settings',
            'settings' => (new PayrollSettingsService())->current(),
            'pf'       => (new PayrollPfService())->current(),
            'esi'      => (new PayrollEsiService())->current(),
            'tds'      => (new PayrollTdsService())->current(),
            'ptSlabs'  => (new PayrollProfessionalTaxService())->allSlabs(),
        ]);
    }

    public function update()
    {
        $post = $this->request->getPost();

        (new PayrollSettingsService())->update([
            'payroll_start_day'          => (int) ($post['payroll_start_day'] ?? 1),
            'payroll_end_day'            => (int) ($post['payroll_end_day'] ?? 31),
            'salary_payment_day'         => (int) ($post['salary_payment_day'] ?? 7),
            'financial_year_start_month' => (int) ($post['financial_year_start_month'] ?? 4),
            'currency'                   => (string) ($post['currency'] ?? 'INR'),
            'working_days_basis'        => $post['working_days_basis'] ?? 'calendar',
            'fixed_working_days'         => (int) ($post['fixed_working_days'] ?? 30),
            'overtime_enabled'           => ! empty($post['overtime_enabled']) ? 1 : 0,
            'overtime_multiplier'       => (float) ($post['overtime_multiplier'] ?? 1.5),
            'overtime_rate_basis'       => $post['overtime_rate_basis'] ?? 'basic',
            'lop_enabled'                => ! empty($post['lop_enabled']) ? 1 : 0,
            'lop_deduction_basis'       => $post['lop_deduction_basis'] ?? 'gross',
            'pf_enabled'                 => ! empty($post['pf_enabled']) ? 1 : 0,
            'esi_enabled'                => ! empty($post['esi_enabled']) ? 1 : 0,
            'pt_enabled'                 => ! empty($post['pt_enabled']) ? 1 : 0,
            'tds_enabled'                => ! empty($post['tds_enabled']) ? 1 : 0,
            'payslip_prefix'             => (string) ($post['payslip_prefix'] ?? 'PAY'),
            'lock_after_approval'       => ! empty($post['lock_after_approval']) ? 1 : 0,
        ]);

        return redirect()->to(site_url('payroll/settings'))->with('success', 'Payroll settings updated.');
    }

    public function updatePf()
    {
        $post = $this->request->getPost();

        (new PayrollPfService())->update([
            'employee_percentage' => (float) ($post['employee_percentage'] ?? 12),
            'employer_percentage' => (float) ($post['employer_percentage'] ?? 12),
            'wage_ceiling'        => (float) ($post['wage_ceiling'] ?? 15000),
            'pf_wage_basis'       => $post['pf_wage_basis'] ?? 'basic',
        ]);

        return redirect()->to(site_url('payroll/settings') . '?tab=pf')->with('success', 'PF settings updated.');
    }

    public function updateEsi()
    {
        $post = $this->request->getPost();

        (new PayrollEsiService())->update([
            'employee_percentage' => (float) ($post['employee_percentage'] ?? 0.75),
            'employer_percentage' => (float) ($post['employer_percentage'] ?? 3.25),
            'wage_ceiling'        => (float) ($post['wage_ceiling'] ?? 21000),
        ]);

        return redirect()->to(site_url('payroll/settings') . '?tab=esi')->with('success', 'ESI settings updated.');
    }

    public function updateTds()
    {
        $post = $this->request->getPost();

        (new PayrollTdsService())->update([
            'default_percentage'     => (float) ($post['default_percentage'] ?? 0),
            'applicable_above_gross' => (float) ($post['applicable_above_gross'] ?? 0),
        ]);

        return redirect()->to(site_url('payroll/settings') . '?tab=tds')->with('success', 'TDS settings updated.');
    }

    public function storePtSlab()
    {
        $post = $this->request->getPost();

        (new PayrollProfessionalTaxService())->create([
            'state'          => (string) $post['state'],
            'min_gross'      => (float) ($post['min_gross'] ?? 0),
            'max_gross'      => $post['max_gross'] !== '' ? (float) $post['max_gross'] : null,
            'tax_amount'     => (float) ($post['tax_amount'] ?? 0),
            'effective_from' => (string) $post['effective_from'],
            'status'         => $post['status'] ?? 'active',
        ]);

        return redirect()->to(site_url('payroll/settings') . '?tab=pt')->with('success', 'Professional tax slab added.');
    }

    public function updatePtSlab($id)
    {
        $post = $this->request->getPost();

        try {
            (new PayrollProfessionalTaxService())->update((int) $id, [
                'state'          => (string) $post['state'],
                'min_gross'      => (float) ($post['min_gross'] ?? 0),
                'max_gross'      => $post['max_gross'] !== '' ? (float) $post['max_gross'] : null,
                'tax_amount'     => (float) ($post['tax_amount'] ?? 0),
                'effective_from' => (string) $post['effective_from'],
                'status'         => $post['status'] ?? 'active',
            ]);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/settings') . '?tab=pt')->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/settings') . '?tab=pt')->with('success', 'Professional tax slab updated.');
    }

    public function deletePtSlab($id)
    {
        try {
            (new PayrollProfessionalTaxService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('payroll/settings') . '?tab=pt')->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('payroll/settings') . '?tab=pt')->with('success', 'Professional tax slab deleted.');
    }
}
