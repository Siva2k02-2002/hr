<?php

namespace App\Models;

use CodeIgniter\Model;

class ProvisioningLogModel extends Model
{
    protected $table         = 'provisioning_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['company_id', 'step', 'status', 'message', 'created_at'];

    public function record(int $companyId, string $step, string $status, ?string $message = null): void
    {
        $this->insert([
            'company_id' => $companyId,
            'step'       => $step,
            'status'     => $status,
            'message'    => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function forCompany(int $companyId): array
    {
        return $this->where('company_id', $companyId)->orderBy('id', 'asc')->findAll();
    }
}
