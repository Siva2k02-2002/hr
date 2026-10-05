<?php

namespace App\Controllers;

use App\Services\EmployeeFamilyService;
use RuntimeException;

class EmployeeFamilyController extends BaseController
{
    public function store($employeeId)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=family'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new EmployeeFamilyService())->create($this->payload($employeeId));

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=family'))->with('success', 'Family member added.');
    }

    public function update($employeeId, $id)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=family'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            (new EmployeeFamilyService())->update((int) $id, $this->payload($employeeId));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=family'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=family'))->with('success', 'Family member updated.');
    }

    public function delete($employeeId, $id)
    {
        try {
            (new EmployeeFamilyService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=family'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=family'))->with('success', 'Family member removed.');
    }

    private function rules(): array
    {
        return [
            'name'         => 'required|min_length[2]|max_length[150]',
            'relationship' => 'required|max_length[60]',
        ];
    }

    private function payload($employeeId): array
    {
        $post = $this->request->getPost();

        return [
            'employee_id'  => (int) $employeeId,
            'relationship' => $post['relationship'],
            'name'         => $post['name'],
            'dob'          => empty($post['dob']) ? null : $post['dob'],
            'occupation'   => empty($post['occupation']) ? null : $post['occupation'],
            'is_dependent' => ! empty($post['is_dependent']) ? 1 : 0,
            'is_nominee'   => ! empty($post['is_nominee']) ? 1 : 0,
        ];
    }
}
