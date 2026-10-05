<?php

namespace App\Controllers;

use App\Services\EmployeeEmergencyContactService;
use RuntimeException;

class EmployeeEmergencyContactsController extends BaseController
{
    public function store($employeeId)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=emergency'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new EmployeeEmergencyContactService())->create($this->payload($employeeId));

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=emergency'))->with('success', 'Emergency contact added.');
    }

    public function update($employeeId, $id)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=emergency'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            (new EmployeeEmergencyContactService())->update((int) $id, $this->payload($employeeId));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=emergency'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=emergency'))->with('success', 'Emergency contact updated.');
    }

    public function delete($employeeId, $id)
    {
        try {
            (new EmployeeEmergencyContactService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=emergency'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=emergency'))->with('success', 'Emergency contact removed.');
    }

    private function rules(): array
    {
        return [
            'name'         => 'required|min_length[2]|max_length[150]',
            'relationship' => 'required|max_length[60]',
            'phone'        => 'required|regex_match[/^[0-9]{10}$/]',
        ];
    }

    private function payload($employeeId): array
    {
        $post = $this->request->getPost();

        return [
            'employee_id'     => (int) $employeeId,
            'name'            => $post['name'],
            'relationship'    => $post['relationship'],
            'phone'           => $post['phone'],
            'alternate_phone' => empty($post['alternate_phone']) ? null : $post['alternate_phone'],
            'address'         => empty($post['address']) ? null : $post['address'],
            'priority'        => (int) ($post['priority'] ?? 1),
        ];
    }
}
