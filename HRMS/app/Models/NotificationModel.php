<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'type', 'title', 'body', 'url', 'data', 'read_at', 'created_at'];

    public function forUser(int $userId, int $limit = 20): array
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll($limit);
    }

    public function unreadCountFor(int $userId): int
    {
        return $this->where('user_id', $userId)->where('read_at', null)->countAllResults();
    }

    public function markRead(int $id, int $userId): void
    {
        $this->where('id', $id)->where('user_id', $userId)->set(['read_at' => date('Y-m-d H:i:s')])->update();
    }

    public function markAllRead(int $userId): void
    {
        $this->where('user_id', $userId)->where('read_at', null)->set(['read_at' => date('Y-m-d H:i:s')])->update();
    }
}
