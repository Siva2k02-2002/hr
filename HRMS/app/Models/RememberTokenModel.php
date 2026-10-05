<?php

namespace App\Models;

use CodeIgniter\Model;

class RememberTokenModel extends Model
{
    protected $table         = 'remember_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'selector', 'validator_hash', 'ip_address', 'user_agent', 'expires_at', 'created_at'];

    public function findValid(string $selector): ?array
    {
        return $this->where('selector', $selector)->where('expires_at >=', date('Y-m-d H:i:s'))->first();
    }

    /** "Remembered devices" list for the IAM page — each row is a persistent-login token, our best proxy for a device session. */
    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)->orderBy('id', 'DESC')->findAll();
    }

    public function countForUser(int $userId): int
    {
        return $this->where('user_id', $userId)->countAllResults();
    }
}
