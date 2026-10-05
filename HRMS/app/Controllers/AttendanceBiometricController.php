<?php

namespace App\Controllers;

use App\Models\AttendanceBiometricLogModel;
use App\Services\AttendanceBiometricImportService;
use Throwable;

/** The biometric-ready architecture's operator UI — stage an exported device file, then sync it through the normal punch pipeline. */
class AttendanceBiometricController extends BaseController
{
    public function index()
    {
        $logs = (new AttendanceBiometricLogModel(service('tenantContext')->db()))->orderBy('id', 'DESC')->findAll(100);

        return view('attendance/biometric/index', ['title' => 'Biometric Import', 'logs' => $logs]);
    }

    private const MAX_BYTES = 10 * 1024 * 1024;

    public function stage()
    {
        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(site_url('attendance/biometric'))->with('error', 'Please choose a valid .xlsx/.csv file.');
        }

        if ($file->getSize() > self::MAX_BYTES) {
            return redirect()->to(site_url('attendance/biometric'))->with('error', 'File must be 10MB or smaller.');
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to(site_url('attendance/biometric'))->with('error', 'Only .xlsx, .xls, or .csv files are supported.');
        }

        try {
            $result = (new AttendanceBiometricImportService())->stage($file->getTempName());
        } catch (Throwable $e) {
            return redirect()->to(site_url('attendance/biometric'))->with('error', 'Could not read that file: ' . $e->getMessage());
        }

        return redirect()->to(site_url('attendance/biometric'))->with('success', "Staged {$result['staged']} punch records. Review and sync below.");
    }

    public function sync()
    {
        $result = (new AttendanceBiometricImportService())->syncPending();

        return redirect()->to(site_url('attendance/biometric'))->with('success', "Synced {$result['synced']}, failed {$result['failed']}.");
    }
}
