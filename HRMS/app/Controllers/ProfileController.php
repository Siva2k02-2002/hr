<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function index()
    {
        $userId = (int) $this->currentUserId();
        $user   = (new UserModel(service('tenantContext')->db()))->withPrimaryRole()->find($userId);

        $activity = (new AuditLogModel(service('tenantContext')->db()))
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->findAll(20);

        return view('profile/index', ['title' => 'My profile', 'user' => $user, 'activity' => $activity]);
    }

    public function update()
    {
        $userId = (int) $this->currentUserId();

        $rules = ['name' => 'required|min_length[2]|max_length[150]', 'mobile' => 'permit_empty|max_length[20]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Please check the form and try again.');
        }

        $userModel = new UserModel(service('tenantContext')->db());
        $userModel->update($userId, [
            'name'   => $this->request->getPost('name'),
            'mobile' => $this->request->getPost('mobile') ?: null,
        ]);
        session()->set('tenant_user_name', $this->request->getPost('name'));

        return redirect()->to(site_url('profile'))->with('success', 'Profile updated.');
    }
}
