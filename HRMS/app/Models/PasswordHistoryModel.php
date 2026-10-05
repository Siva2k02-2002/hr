<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordHistoryModel extends Model
{
    protected $table         = 'password_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'password_hash', 'created_at'];

    /** @return list<string> most recent hashes first */
    public function recentHashes(int $userId, int $limit): array
    {
        return array_column(
            $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll($limit),
            'password_hash'
        );
    }

    public function record(int $userId, string $hash, int $keep): void
    {
        $this->insert(['user_id' => $userId, 'password_hash' => $hash, 'created_at' => date('Y-m-d H:i:s')]);

        $stale = $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll(1000);
        foreach (array_slice($stale, $keep) as $row) {
            $this->delete($row['id']);
        }
    }
}
