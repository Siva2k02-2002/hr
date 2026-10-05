<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Services\EmployeePhotoService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class EmployeePhotoController extends BaseController
{
    public function upload($employeeId)
    {
        $file = $this->request->getFile('photo');
        if (! $file) {
            return redirect()->back()->with('error', 'Please choose a photo to upload.');
        }

        try {
            (new EmployeePhotoService())->upload((int) $employeeId, $file);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('employees/' . $employeeId))->with('success', 'Photo updated.');
    }

    /** Outside the public webroot — only reachable through this permission-gated route. */
    public function show($employeeId)
    {
        $employee = (new EmployeeModel(service('tenantContext')->db()))->find($employeeId);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }

        $path = (new EmployeePhotoService())->absolutePathFor($employee);
        if (! $path) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', 'image/jpeg')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($path));
    }
}
