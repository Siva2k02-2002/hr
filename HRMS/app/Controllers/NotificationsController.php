<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationsController extends BaseController
{
    public function index()
    {
        $model = new NotificationModel(service('tenantContext')->db());

        return view('notifications/index', [
            'title'         => 'Notifications',
            'notifications' => $model->forUser((int) $this->currentUserId(), 100),
        ]);
    }

    /** Marks read, then redirects to wherever the notification points — the click target from the topbar dropdown. */
    public function open($id)
    {
        $model = new NotificationModel(service('tenantContext')->db());
        $notification = $model->where('id', $id)->where('user_id', $this->currentUserId())->first();

        $model->markRead((int) $id, (int) $this->currentUserId());

        return redirect()->to($notification['url'] ?? site_url('notifications'));
    }

    public function markAllRead()
    {
        (new NotificationModel(service('tenantContext')->db()))->markAllRead((int) $this->currentUserId());

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
