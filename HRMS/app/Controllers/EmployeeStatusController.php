<?php

namespace App\Controllers;

use App\Services\EmployeeStatusService;
use RuntimeException;

class EmployeeStatusController extends BaseController
{
    public function activate($id)
    {
        return $this->run($id, 'activate');
    }

    public function suspend($id)
    {
        return $this->run($id, 'suspend');
    }

    public function relieve($id)
    {
        return $this->run($id, 'relieve');
    }

    public function rejoin($id)
    {
        return $this->run($id, 'rejoin');
    }

    public function change($id)
    {
        $newStatus = (string) $this->request->getPost('status');
        $remarks   = (string) $this->request->getPost('remarks') ?: null;

        try {
            (new EmployeeStatusService())->changeStatus((int) $id, $newStatus, $remarks);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $id . '?tab=activity'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $id . '?tab=activity'))->with('success', 'Employee status updated.');
    }

    private function run($id, string $action)
    {
        $remarks = (string) $this->request->getPost('remarks') ?: null;

        try {
            (new EmployeeStatusService())->{$action}((int) $id, $remarks);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('employees/' . $id))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $id))->with('success', 'Employee status updated.');
    }
}
