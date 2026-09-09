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

    public function update()
    {
        $userId = current_customer()['user_id'];
        $user   = $this->userModel->find($userId);

        $rules = [
            'last_name'      => 'required|min_length[2]|max_length[100]',
            'first_name'     => 'required|min_length[2]|max_length[100]',
            'middle_name'    => 'permit_empty|max_length[100]',
            'street'         => 'permit_empty|max_length[150]',
            'barangay'       => 'required|max_length[100]',
            'barangay_other' => 'permit_empty|max_length[100]',
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
            'barangay'          => $barangay === 'Other' ? $barangayOther : $barangay,
            'city_municipality' => 'Calapan City',
            'province'          => 'Oriental Mindoro',
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

        $this->userModel->update($userId, $data);
        $updated = $this->userModel->find($userId);

        $customer = current_customer();
        $customer['name']   = $updated['name'];
        $customer['avatar'] = $updated['avatar'];
        session()->set('customer', $customer);

        return redirect()->to('profile')->with('success', 'Profile updated.');
    }
}
