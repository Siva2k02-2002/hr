<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeDocumentModel extends Model
{
    protected $table          = 'employee_documents';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'employee_id', 'document_type', 'document_number', 'file_path', 'original_filename', 'mime_type',
        'file_size', 'expiry_date', 'status', 'remarks', 'uploaded_by',
    ];

    protected $validationRules = [
        'employee_id'    => 'required|integer',
        'document_type'  => 'required|in_list[aadhaar,pan,passport,driving_license,resume,appointment_letter,offer_letter,education_certificate,experience_certificate,other]',
        'file_path'      => 'required',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)->orderBy('created_at', 'DESC')->findAll();
    }
}
