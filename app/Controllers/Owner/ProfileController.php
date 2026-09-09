<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return view('owner/profile/index', [
            'title' => 'My Profile',
            'user'  => $this->userModel->find(current_owner()['user_id']),
        ]);
    }

    public function update()
    {
        $userId = current_owner()['user_id'];
        $user   = $this->userModel->find($userId);

        $rules = [
            'last_name'      => 'required|min_length[2]|max_length[100]',
            'first_name'     => 'required|min_length[2]|max_length[100]',
            'middle_name'    => 'permit_empty|max_length[100]',
            'street'         => 'permit_empty|max_length[150]',
            'barangay'       => 'required|max_length[100]',
            'barangay_other' => 'permit_empty|max_length[100]',
            'email'          => "required|valid_email|is_unique[users.email,user_id,{$userId}]",
            'contact_number' => 'permit_empty|max_length[20]',
            'password'       => 'permit_empty|' . UserModel::PASSWORD_RULE,
            'avatar'         => 'permit_empty|is_image[avatar]|max_size[avatar,2048]|mime_in[avatar,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        $messages = [
            'password' => ['regex_match' => 'New password must be ' . lcfirst(UserModel::PASSWORD_HINT)],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $barangay      = $this->request->getPost('barangay');
        $barangayOther = $this->request->getPost('barangay_other');
        if ($barangay === 'Other' && empty(trim((string) $barangayOther))) {
            return redirect()->back()->withInput()->with('error', 'Please specify your barangay.');
        }

        // city_municipality/province are fixed — Calapan City, Oriental
        // Mindoro — never taken from the request.
        $data = [
            'last_name'         => $this->request->getPost('last_name'),
            'first_name'        => $this->request->getPost('first_name'),
            'middle_name'       => $this->request->getPost('middle_name'),
            'street'            => $this->request->getPost('street'),
            'barangay'          => $barangay === 'Other' ? $barangayOther : $barangay,
            'city_municipality' => 'Calapan City',
            'province'          => 'Oriental Mindoro',
            'email'             => $this->request->getPost('email'),
            'contact_number'    => $this->request->getPost('contact_number'),
        ];

        $newPassword = $this->request->getPost('password');
        if (! empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $newAvatar = save_avatar_upload($this->request->getFile('avatar'), $user['avatar'] ?? null);
        if ($newAvatar) {
            $data['avatar'] = $newAvatar;
        }

        // UserModel's own 'email' rule is `is_unique[users.email,user_id,{user_id}]`
        // — that {user_id} placeholder only resolves against fields present
        // in the data being validated, and user_id is never one of them
        // (it's passed separately as update()'s $id). Without it here, the
        // uniqueness check has nothing to exclude, so saving your own
        // unchanged email always fails. user_id isn't in $allowedFields, so
        // it's stripped before the actual UPDATE query runs — it exists
        // only to make this placeholder resolve.
        $data['user_id'] = $userId;

        if (! $this->userModel->update($userId, $data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->userModel->errors()));
        }

        $updated = $this->userModel->find($userId);

        $owner = current_owner();
        $owner['name']   = $updated['name'];
        $owner['email']  = $updated['email'];
        $owner['avatar'] = $updated['avatar'];
        session()->set('owner', $owner);

        return redirect()->to('owner/profile')->with('success', 'Profile updated.');
    }
}
