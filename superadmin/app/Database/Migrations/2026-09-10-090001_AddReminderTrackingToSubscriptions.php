<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Tracks the last expiry-reminder email sent per subscription, so a daily reminder command doesn't re-send the same notice every run. */
class AddReminderTrackingToSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('subscriptions', [
            'last_reminder_sent_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'remarks'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('subscriptions', ['last_reminder_sent_at']);
    }
}
