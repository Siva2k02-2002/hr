<?php

namespace App\Controllers;

use App\Services\EmployeeExperienceService;
use RuntimeException;

class EmployeeExperienceController extends BaseController
{
    public function store($employeeId)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=experience'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new EmployeeExperienceService())->create($this->payload($employeeId));

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=experience'))->with('success', 'Experience record added.');
    }

    public function update($employeeId, $id)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=experience'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            (new EmployeeExperienceService())->update((int) $id, $this->payload($employeeId));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=experience'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=experience'))->with('success', 'Experience record updated.');
    }

    public function delete($employeeId, $id)
    {
        try {
            (new EmployeeExperienceService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=experience'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=experience'))->with('success', 'Experience record removed.');
    }

    private function rules(): array
    {
        return [
            'company_name' => 'required|max_length[200]',
            'from_date'    => 'required|valid_date',
        ];
    }

    private function payload($employeeId): array
    {
        $post = $this->request->getPost();

        return [
            'employee_id'         => (int) $employeeId,
            'company_name'        => $post['company_name'],
            'designation'         => empty($post['designation']) ? null : $post['designation'],
            'from_date'           => $post['from_date'],
            'to_date'             => empty($post['to_date']) ? null : $post['to_date'],
            'years_experience'    => empty($post['years_experience']) ? null : $post['years_experience'],
            'reason_for_leaving'  => empty($post['reason_for_leaving']) ? null : $post['reason_for_leaving'],
        ];
    }
}
