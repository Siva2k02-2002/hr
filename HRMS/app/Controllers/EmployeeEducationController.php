<?php

namespace App\Controllers;

use App\Services\EmployeeEducationService;
use RuntimeException;

class EmployeeEducationController extends BaseController
{
    public function store($employeeId)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=education'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        (new EmployeeEducationService())->create($this->payload($employeeId));

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=education'))->with('success', 'Education record added.');
    }

    public function update($employeeId, $id)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=education'))->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            (new EmployeeEducationService())->update((int) $id, $this->payload($employeeId));
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=education'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=education'))->with('success', 'Education record updated.');
    }

    public function delete($employeeId, $id)
    {
        try {
            (new EmployeeEducationService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $employeeId . '?tab=education'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId . '?tab=education'))->with('success', 'Education record removed.');
    }

    private function rules(): array
    {
        return [
            'qualification' => 'required|max_length[150]',
            'institution'   => 'required|max_length[200]',
        ];
    }

    private function payload($employeeId): array
    {
        $post = $this->request->getPost();

        return [
            'employee_id'      => (int) $employeeId,
            'qualification'    => $post['qualification'],
            'institution'      => $post['institution'],
            'board_university' => empty($post['board_university']) ? null : $post['board_university'],
            'percentage_cgpa'  => empty($post['percentage_cgpa']) ? null : $post['percentage_cgpa'],
            'year_of_passing'  => empty($post['year_of_passing']) ? null : $post['year_of_passing'],
        ];
    }
}
