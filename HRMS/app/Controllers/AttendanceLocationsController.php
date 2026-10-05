<?php

namespace App\Controllers;

use App\Models\AttendanceLocationModel;
use App\Models\BranchModel;
use App\Services\AttendanceLocationService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class AttendanceLocationsController extends BaseController
{
    public function index()
    {
        $locations = (new AttendanceLocationModel(service('tenantContext')->db()))->withBranch()->orderBy('name')->findAll();

        return view('attendance/locations/index', ['title' => 'Office Locations', 'locations' => $locations]);
    }

    public function create()
    {
        return view('attendance/locations/form', ['title' => 'Add location', 'location' => null, ...$this->formOptions()]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('attendance/locations/form', ['title' => 'Add location', 'location' => null, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        (new AttendanceLocationService())->create($this->payload());

        return redirect()->to(site_url('attendance/locations'))->with('success', 'Location created.');
    }

    public function edit($id)
    {
        $location = (new AttendanceLocationModel(service('tenantContext')->db()))->find($id);
        if (! $location) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('attendance/locations/form', ['title' => 'Edit location', 'location' => $location, ...$this->formOptions()]);
    }

    public function update($id)
    {
        $location = (new AttendanceLocationModel(service('tenantContext')->db()))->find($id);
        if (! $location) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return view('attendance/locations/form', ['title' => 'Edit location', 'location' => $location, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        try {
            (new AttendanceLocationService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/locations'))->with('success', 'Location updated.');
    }

    public function delete($id)
    {
        try {
            (new AttendanceLocationService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/locations'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/locations'))->with('success', 'Location deleted.');
    }

    private function rules(): array
    {
        return [
            'name'          => 'required|max_length[150]',
            'branch_id'     => 'required|integer',
            'latitude'      => 'required|decimal',
            'longitude'     => 'required|decimal',
            'radius_meters' => 'required|integer|greater_than[0]',
        ];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'          => $post['name'],
            'branch_id'     => (int) $post['branch_id'],
            'latitude'      => $post['latitude'],
            'longitude'     => $post['longitude'],
            'radius_meters' => (int) $post['radius_meters'],
            'address'       => empty($post['address']) ? null : $post['address'],
            'status'        => $post['status'] ?? 'active',
        ];
    }

    private function formOptions(): array
    {
        return ['branches' => (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->findAll()];
    }
}
