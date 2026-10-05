<?php

namespace App\Controllers;

use App\Services\EmployeeBankService;
use RuntimeException;

class EmployeeBankController extends BaseController
{
    public function store($employeeId)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            (new EmployeeBankService())->create($this->payload($employeeId));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('success', 'Bank account added.');
    }

    public function update($employeeId, $id)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            (new EmployeeBankService())->update((int) $id, $this->payload($employeeId));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('success', 'Bank account updated.');
    }

    public function delete($employeeId, $id)
    {
        try {
            (new EmployeeBankService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=bank'))->with('success', 'Bank account removed.');
    }

    private function rules(): array
    {
        return [
            'account_holder_name' => 'required|max_length[150]',
            'bank_name'           => 'required|max_length[150]',
            'account_number'      => 'required|alpha_numeric|max_length[30]',
            'ifsc_code'           => 'required|regex_match[/^[A-Z]{4}0[A-Z0-9]{6}$/i]',
        ];
    }

    private function payload($employeeId): array
    {
        $post = $this->request->getPost();

        return [
            'employee_id'         => (int) $employeeId,
            'account_holder_name' => $post['account_holder_name'],
            'bank_name'           => $post['bank_name'],
            'branch_name'         => empty($post['branch_name']) ? null : $post['branch_name'],
            'account_number'      => $post['account_number'],
            'ifsc_code'           => strtoupper($post['ifsc_code']),
            'upi_id'              => empty($post['upi_id']) ? null : $post['upi_id'],
            'is_primary'          => ! empty($post['is_primary']) ? 1 : 0,
        ];
    }
}
