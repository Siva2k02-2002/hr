<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionHistoryModel extends Model
{
    protected $table         = 'subscription_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'subscription_id', 'action', 'old_values', 'new_values', 'performed_by', 'performed_at',
    ];

    public function forSubscription(int $subscriptionId): array
    {
        return $this->where('subscription_id', $subscriptionId)
            ->orderBy('performed_at', 'DESC')
            ->findAll();
    }
}
