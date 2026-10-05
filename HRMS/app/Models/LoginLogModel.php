<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginLogModel extends Model
{
    protected $table         = 'login_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'email', 'status', 'ip_address', 'user_agent', 'created_at'];

    public function recentFailures(string $email, int $withinMinutes): int
    {
        return $this->where('email', $email)
            ->where('status', 'failed')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime("-{$withinMinutes} minutes")))
            ->countAllResults();
    }

    /** @param array{status?: string} $filters */
    public function forUser(int $userId, array $filters = [], int $perPage = 15)
    {
        $this->where('user_id', $userId);

        if (! empty($filters['status']) && in_array($filters['status'], ['success', 'failed'], true)) {
            $this->where('status', $filters['status']);
        }

        return $this->orderBy('id', 'DESC')->paginate($perPage, 'login_history');
    }

    public function recentFor(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)->orderBy('id', 'DESC')->limit($limit)->findAll();
    }
}
