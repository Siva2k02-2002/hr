<?php

namespace App\Controllers;

use App\Models\AttendanceShiftModel;
use App\Services\AttendanceShiftService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class AttendanceShiftsController extends BaseController
{
    public function index()
    {
        $model  = new AttendanceShiftModel(service('tenantContext')->db());
        $search = trim((string) $this->request->getGet('q'));

        if ($search !== '') {
            $model->groupStart()->like('name', $search)->orLike('code', $search)->groupEnd();
        }

        $shifts = $model->orderBy('name')->paginate(15, 'shifts');

        return view('attendance/shifts/index', ['title' => 'Shifts', 'shifts' => $shifts, 'pager' => $model->pager, 'filters' => ['q' => $search]]);
    }

    public function create()
    {
        return view('attendance/shifts/form', ['title' => 'Add shift', 'shift' => null]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('attendance/shifts/form', ['title' => 'Add shift', 'shift' => null, 'errors' => $this->validator->getErrors()]);
        }

        try {
            $id = (new AttendanceShiftService())->create($this->payload());
        } catch (RuntimeException $e) {
            return view('attendance/shifts/form', ['title' => 'Add shift', 'shift' => null, 'errors' => ['code' => $e->getMessage()]]);
        }

        return redirect()->to(site_url('attendance/shifts'))->with('success', 'Shift created.');
    }

    public function edit($id)
    {
        $shift = (new AttendanceShiftModel(service('tenantContext')->db()))->find($id);
        if (! $shift) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('attendance/shifts/form', ['title' => 'Edit shift', 'shift' => $shift]);
    }

    public function update($id)
    {
        $shift = (new AttendanceShiftModel(service('tenantContext')->db()))->find($id);
        if (! $shift) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return view('attendance/shifts/form', ['title' => 'Edit shift', 'shift' => $shift, 'errors' => $this->validator->getErrors()]);
        }

        try {
            (new AttendanceShiftService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return view('attendance/shifts/form', ['title' => 'Edit shift', 'shift' => $shift, 'errors' => ['code' => $e->getMessage()]]);
        }

        return redirect()->to(site_url('attendance/shifts'))->with('success', 'Shift updated.');
    }

    public function delete($id)
    {
        try {
            (new AttendanceShiftService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/shifts'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/shifts'))->with('success', 'Shift deleted.');
    }

    private function rules(): array
    {
        return [
            'name'       => 'required|min_length[2]|max_length[100]',
            'code'       => 'required|alpha_numeric_punct|max_length[20]',
            'start_time' => 'required',
            'end_time'   => 'required',
        ];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'             => $post['name'],
            'code'             => $post['code'],
            'start_time'       => $post['start_time'],
            'end_time'         => $post['end_time'],
            'break_start'      => empty($post['break_start']) ? null : $post['break_start'],
            'break_end'        => empty($post['break_end']) ? null : $post['break_end'],
            'grace_minutes'    => (int) ($post['grace_minutes'] ?? 0),
            'late_minutes'     => (int) ($post['late_minutes'] ?? 0),
            'half_day_minutes' => (int) ($post['half_day_minutes'] ?? 240),
            'full_day_minutes' => (int) ($post['full_day_minutes'] ?? 480),
            'is_night_shift'   => ! empty($post['is_night_shift']) ? 1 : 0,
            'status'           => $post['status'] ?? 'active',
        ];
    }
}
