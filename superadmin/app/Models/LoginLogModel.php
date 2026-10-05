<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginLogModel extends Model
{
    protected $table         = 'login_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['platform_user_id', 'email', 'status', 'ip_address', 'user_agent', 'created_at'];

    public function recentFailures(string $email, int $minutes): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));

        return $this->where('email', $email)
            ->where('status', 'failed')
            ->where('created_at >=', $cutoff)
            ->countAllResults();
    }
}
