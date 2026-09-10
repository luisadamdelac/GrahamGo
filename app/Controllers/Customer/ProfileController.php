<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ReservationModel;
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
        $userId = current_customer()['user_id'];

        $reservationModel = new ReservationModel();
        $reservations     = $reservationModel->forUser($userId);
        $claimed          = array_filter($reservations, static fn ($r) => $r['status'] === 'Claimed');

        return view('customer/profile/index', [
            'title' => 'My Profile',
            'user'  => $this->userModel->find($userId),
            'stats' => [
                'total_reservations' => count($reservations),
                'claimed'            => count($claimed),
                'total_spent'        => array_sum(array_column($claimed, 'total_amount')),
            ],
        ]);
    }

    /**
     * Avatar-only upload, called via AJAX from partials/avatar_confirm_modal
     * instead of bundling the photo into the full profile form submit —
     * so it saves the instant it's confirmed, independent of whatever
     * state the other fields (barangay, password, ...) happen to be in.
     */
    public function updateAvatar()
    {
        $userId = current_customer()['user_id'];
        $user   = $this->userModel->find($userId);

        $rules = ['avatar' => 'uploaded[avatar]|is_image[avatar]|max_size[avatar,2048]|mime_in[avatar,image/jpg,image/jpeg,image/png,image/webp]'];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => implode(' ', $this->validator->getErrors())]);
        }

        $newAvatar = save_avatar_upload($this->request->getFile('avatar'), $user['avatar'] ?? null);
        if (! $newAvatar) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Upload failed. Please try again.']);
        }

        $this->userModel->update($userId, ['avatar' => $newAvatar]);

        $customer            = current_customer();
        $customer['avatar']  = $newAvatar;
        session()->set('customer', $customer);

        return $this->response->setJSON(['success' => true, 'url' => avatar_url($newAvatar)]);
    }

    public function update()
    {
        $userId = current_customer()['user_id'];

        $rules = [
            'last_name'      => 'required|min_length[2]|max_length[100]',
            'first_name'     => 'required|min_length[2]|max_length[100]',
            'middle_name'    => 'permit_empty|max_length[100]',
            'street'         => 'permit_empty|max_length[150]',
            'barangay'       => 'required|in_list[' . implode(',', calapan_barangays()) . ']',
            'contact_number' => 'permit_empty|max_length[20]',
            'password'       => 'permit_empty|' . UserModel::PASSWORD_RULE,
        ];

        $messages = [
            'password' => ['regex_match' => 'New password must be ' . lcfirst(UserModel::PASSWORD_HINT)],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // customer_type is deliberately not editable here — set once at
        // registration and locked afterward (the field is disabled/not
        // submitted on this form), so it's never overwritten on update.
        // city_municipality/province are likewise fixed — Calapan City,
        // Oriental Mindoro — never taken from the request.
        $data = [
            'last_name'         => $this->request->getPost('last_name'),
            'first_name'        => $this->request->getPost('first_name'),
            'middle_name'       => $this->request->getPost('middle_name'),
            'street'            => $this->request->getPost('street'),
            'barangay'          => $this->request->getPost('barangay'),
            'city_municipality' => 'Calapan City',
            'province'          => 'Oriental Mindoro',
            'contact_number'    => $this->request->getPost('contact_number'),
        ];

        $newPassword = $this->request->getPost('password');
        if (! empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $this->userModel->update($userId, $data);
        $updated = $this->userModel->find($userId);

        $customer = current_customer();
        $customer['name']   = $updated['name'];
        $customer['avatar'] = $updated['avatar'];
        session()->set('customer', $customer);

        return redirect()->to('profile')->with('success', 'Profile updated.');
    }
}
