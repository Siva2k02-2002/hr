<?php

namespace App\Controllers;

use App\Models\AttendanceWeeklyOffModel;
use App\Models\BranchModel;
use App\Models\AttendanceShiftModel;
use App\Services\AttendanceWeeklyOffService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class AttendanceWeeklyOffsController extends BaseController
{
    public function index()
    {
        $model = (new AttendanceWeeklyOffModel(service('tenantContext')->db()))
            ->select('attendance_weekly_offs.*, b.name as branch_name')
            ->join('branches b', 'b.id = attendance_weekly_offs.branch_id', 'left');

        $rules = $model->orderBy('day_of_week')->findAll();

        return view('attendance/weekly_offs/index', ['title' => 'Weekly Off', 'rules' => $rules]);
    }

    public function create()
    {
        return view('attendance/weekly_offs/form', ['title' => 'Add weekly-off rule', 'rule' => null, ...$this->formOptions()]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('attendance/weekly_offs/form', ['title' => 'Add weekly-off rule', 'rule' => null, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        (new AttendanceWeeklyOffService())->create($this->payload());

        return redirect()->to(site_url('attendance/weekly-offs'))->with('success', 'Weekly-off rule created.');
    }

    public function edit($id)
    {
        $rule = (new AttendanceWeeklyOffModel(service('tenantContext')->db()))->find($id);
        if (! $rule) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('attendance/weekly_offs/form', ['title' => 'Edit weekly-off rule', 'rule' => $rule, ...$this->formOptions()]);
    }

    public function update($id)
    {
        $rule = (new AttendanceWeeklyOffModel(service('tenantContext')->db()))->find($id);
        if (! $rule) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return view('attendance/weekly_offs/form', ['title' => 'Edit weekly-off rule', 'rule' => $rule, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        (new AttendanceWeeklyOffService())->update((int) $id, $this->payload());

        return redirect()->to(site_url('attendance/weekly-offs'))->with('success', 'Weekly-off rule updated.');
    }

    public function delete($id)
    {
        try {
            (new AttendanceWeeklyOffService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/weekly-offs'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/weekly-offs'))->with('success', 'Weekly-off rule deleted.');
    }

    private function rules(): array
    {
        return [
            'name'        => 'required|max_length[100]',
            'day_of_week' => 'required|in_list[sunday,monday,tuesday,wednesday,thursday,friday,saturday]',
            'week_pattern'=> 'required|in_list[every,first,second,third,fourth,fifth,alternate]',
        ];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'         => $post['name'],
            'day_of_week'  => $post['day_of_week'],
            'week_pattern' => $post['week_pattern'],
            'branch_id'    => empty($post['branch_id']) ? null : $post['branch_id'],
            'shift_id'     => empty($post['shift_id']) ? null : $post['shift_id'],
            'status'       => $post['status'] ?? 'active',
        ];
    }

    private function formOptions(): array
    {
        return [
            'branches' => (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
            'shifts'   => (new AttendanceShiftModel(service('tenantContext')->db()))->where('status', 'active')->findAll(),
        ];
    }
}
