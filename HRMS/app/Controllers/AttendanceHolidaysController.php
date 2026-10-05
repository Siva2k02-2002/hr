<?php

namespace App\Controllers;

use App\Models\AttendanceHolidayModel;
use App\Models\BranchModel;
use App\Services\AttendanceHolidayService;
use CodeIgniter\Exceptions\PageNotFoundException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Throwable;

class AttendanceHolidaysController extends BaseController
{
    public function index()
    {
        $model = (new AttendanceHolidayModel(service('tenantContext')->db()))
            ->select('attendance_holidays.*, b.name as branch_name')
            ->join('branches b', 'b.id = attendance_holidays.branch_id', 'left');

        $year = (int) ($this->request->getGet('year') ?: date('Y'));
        $holidays = $model->where('YEAR(date)', $year)->orderBy('date')->findAll();

        return view('attendance/holidays/index', ['title' => 'Holidays', 'holidays' => $holidays, 'year' => $year]);
    }

    public function calendar()
    {
        $year  = (int) ($this->request->getGet('year') ?: date('Y'));
        $month = (int) ($this->request->getGet('month') ?: date('n'));
        $holidays = (new AttendanceHolidayModel(service('tenantContext')->db()))->forMonth($year, $month);

        return view('attendance/holidays/calendar', ['title' => 'Holiday Calendar', 'year' => $year, 'month' => $month, 'holidays' => $holidays]);
    }

    public function create()
    {
        return view('attendance/holidays/form', ['title' => 'Add holiday', 'holiday' => null, ...$this->formOptions()]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return view('attendance/holidays/form', ['title' => 'Add holiday', 'holiday' => null, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        try {
            (new AttendanceHolidayService())->create($this->payload());
        } catch (RuntimeException $e) {
            return view('attendance/holidays/form', ['title' => 'Add holiday', 'holiday' => null, 'errors' => ['form' => $e->getMessage()], ...$this->formOptions()]);
        }

        return redirect()->to(site_url('attendance/holidays'))->with('success', 'Holiday created.');
    }

    public function edit($id)
    {
        $holiday = (new AttendanceHolidayModel(service('tenantContext')->db()))->find($id);
        if (! $holiday) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('attendance/holidays/form', ['title' => 'Edit holiday', 'holiday' => $holiday, ...$this->formOptions()]);
    }

    public function update($id)
    {
        $holiday = (new AttendanceHolidayModel(service('tenantContext')->db()))->find($id);
        if (! $holiday) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules())) {
            return view('attendance/holidays/form', ['title' => 'Edit holiday', 'holiday' => $holiday, 'errors' => $this->validator->getErrors(), ...$this->formOptions()]);
        }

        try {
            (new AttendanceHolidayService())->update((int) $id, $this->payload());
        } catch (RuntimeException $e) {
            return view('attendance/holidays/form', ['title' => 'Edit holiday', 'holiday' => $holiday, 'errors' => ['form' => $e->getMessage()], ...$this->formOptions()]);
        }

        return redirect()->to(site_url('attendance/holidays'))->with('success', 'Holiday updated.');
    }

    public function delete($id)
    {
        try {
            (new AttendanceHolidayService())->delete((int) $id);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/holidays'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/holidays'))->with('success', 'Holiday deleted.');
    }

    /** GET preview for "Generate Next Year" — computes proposed rows, writes nothing. */
    public function generateNextYearPreview()
    {
        $sourceYear = (int) ($this->request->getGet('source_year') ?: date('Y'));
        $preview = (new AttendanceHolidayService())->proposeNextYear($sourceYear);

        return view('attendance/holidays/generate', ['title' => 'Generate ' . $preview['target_year'] . ' holidays'] + $preview);
    }

    /** POST — creates only the rows the admin kept checked, with their (possibly hand-edited) dates. */
    public function generateNextYearCommit()
    {
        $sourceYear = (int) $this->request->getPost('source_year');
        if ($sourceYear < 1) {
            return redirect()->to(site_url('attendance/holidays'))->with('error', 'Invalid source year.');
        }

        $rows = $this->request->getPost('rows') ?? [];
        $items = [];
        foreach ($rows as $row) {
            if (empty($row['include'])) {
                continue;
            }
            $items[] = [
                'name'         => $row['name'] ?? '',
                'date'         => $row['date'] ?? '',
                'holiday_type' => $row['holiday_type'] ?? 'public',
                'branch_id'    => empty($row['branch_id']) ? null : (int) $row['branch_id'],
                'description'  => empty($row['description']) ? null : $row['description'],
                'is_optional'  => ! empty($row['is_optional']) ? 1 : 0,
                'is_annual'    => ! empty($row['is_annual']) ? 1 : 0,
            ];
        }

        if ($items === []) {
            return redirect()->back()->with('error', 'No holidays were selected to generate.');
        }

        try {
            $result = (new AttendanceHolidayService())->generateNextYear($sourceYear, $items);
        } catch (RuntimeException $e) {
            return redirect()->to(site_url('attendance/holidays/generate-next-year?source_year=' . $sourceYear))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('attendance/holidays?year=' . $result['target_year']))
            ->with('success', "Generated {$result['created']} holidays for {$result['target_year']}" . ($result['skipped'] ? ", skipped {$result['skipped']} (already existed)." : '.'));
    }

    public function importForm()
    {
        return view('attendance/holidays/import', ['title' => 'Import Holidays']);
    }

    public function importTemplate()
    {
        ob_start();
        (new Xlsx((new AttendanceHolidayService())->template()))->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="holiday_import_template.xlsx"')
            ->setBody($content);
    }

    public function importCommit()
    {
        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('attendance/holidays/import'))->with('error', 'Please choose a valid .xlsx/.csv file.');
        }

        try {
            $result = (new AttendanceHolidayService())->import($file->getTempName());
        } catch (Throwable $e) {
            return redirect()->to(site_url('attendance/holidays/import'))->with('error', 'Could not read that file: ' . $e->getMessage());
        }

        return redirect()->to(site_url('attendance/holidays'))
            ->with('success', "Imported {$result['imported']} holidays" . ($result['skipped'] ? ", skipped {$result['skipped']}." : '.'));
    }

    private function rules(): array
    {
        return ['name' => 'required|max_length[150]', 'date' => 'required|valid_date'];
    }

    private function payload(): array
    {
        $post = $this->request->getPost();

        return [
            'name'         => $post['name'],
            'date'         => $post['date'],
            'holiday_type' => $post['holiday_type'] ?? 'public',
            'branch_id'    => empty($post['branch_id']) ? null : $post['branch_id'],
            'description'  => empty($post['description']) ? null : $post['description'],
            'is_optional'  => ! empty($post['is_optional']) ? 1 : 0,
            'is_annual'    => ! empty($post['is_annual']) ? 1 : 0,
            'status'       => $post['status'] ?? 'active',
        ];
    }

    private function formOptions(): array
    {
        return ['branches' => (new BranchModel(service('tenantContext')->db()))->where('status', 'active')->findAll()];
    }
}
