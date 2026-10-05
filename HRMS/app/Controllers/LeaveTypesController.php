<?php

namespace App\Controllers;

use App\Models\LeaveTypeModel;
use App\Services\LeaveTypeService;
use RuntimeException;

class LeaveTypesController extends BaseController
{
    public function index()
    {
        $filters = [
            'q'       => (string) $this->request->getGet('q'),
            'status'  => (string) $this->request->getGet('status'),
            'is_paid' => (string) $this->request->getGet('is_paid'),
        ];

        $model = (new LeaveTypeModel(service('tenantContext')->db()))->applyFilters($filters);
        $types = $model->orderBy('sort_order')->orderBy('name')->paginate(15, 'leave_types');

        return view('leave/types/index', [
            'title'   => 'Leave Types',
            'types'   => $types,
            'pager'   => $model->pager,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('leave/types/form', ['title' => 'Add Leave Type', 'type' => null]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('leave/types/form', ['title' => 'Add Leave Type', 'type' => null, 'errors' => $this->validator->getErrors()]);
        }

        try {
            $id = (new LeaveTypeService())->create($this->payload());
        } catch (RuntimeException $e) {
            return view('leave/types/form', ['title' => 'Add Leave Type', 'type' => null, 'errors' => ['code' => $e->getMessage()]]);
        }

        return redirect()->to(site_url('leave/types'))->with('success', 'Leave type created.');
    }

    public function edit($id)
    {
        $type = (new LeaveTypeModel(service('tenantContext')->db()))->find($id);
        if (! $type) {
            return redirect()->to(site_url('leave/types'))->with('error', 'Leave type not found.');
        }

        return view('leave/types/form', ['title' => 'Edit Leave Type', 'type' => $type]);
    }

    public function update($id)
    {
        $type = (new LeaveTypeModel(service('tenantContext')->db()))->find($id);
        if (! $type) {
            return redirect()->to(site_url('leave/types'))->with('error', 'Leave type not found.');
        }

        if (! $this->validate($this->rules())) {
            return view('leave/types/form', ['title' => 'Edit Leave Type', 'type' => $type, 'errors' => $this->validator->getErrors()]);
        }

        try {
            (new LeaveTypeService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return view('leave/types/form', ['title' => 'Edit Leave Type', 'type' => $type, 'errors' => ['code' => $e->getMessage()]]);
        }

        return redirect()->to(site_url('leave/types'))->with('success', 'Leave type updated.');
    }

    public function delete($id)
    {
        try {
            (new LeaveTypeService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('leave/types'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('leave/types'))->with('success', 'Leave type deleted.');
    }

    private function rules(): array
    {
        return [
            'name'                  => 'required|max_length[100]',
            'code'                  => 'required|max_length[20]',
            'attendance_status_map' => 'required|in_list[leave,lop,work_from_home,on_duty]',
        ];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'                          => $post['name'],
            'code'                          => strtoupper((string) $post['code']),
            'description'                   => $post['description'] ?: null,
            'color'                         => $post['color'] ?: '#4B3FD1',
            'is_paid'                       => ! empty($post['is_paid']) ? 1 : 0,
            'annual_allocation'             => (float) ($post['annual_allocation'] ?? 0),
            'half_day_allowed'              => ! empty($post['half_day_allowed']) ? 1 : 0,
            'attachment_required'           => ! empty($post['attachment_required']) ? 1 : 0,
            'medical_certificate_required'  => ! empty($post['medical_certificate_required']) ? 1 : 0,
            'carry_forward_allowed'         => ! empty($post['carry_forward_allowed']) ? 1 : 0,
            'encashment_allowed'            => ! empty($post['encashment_allowed']) ? 1 : 0,
            'attendance_status_map'         => $post['attendance_status_map'],
            'sort_order'                    => (int) ($post['sort_order'] ?? 0),
            'status'                        => $post['status'] ?? 'active',
        ];
    }
}
