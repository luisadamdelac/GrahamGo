<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\ReservationModel;
use App\Models\UserModel;

class CustomerController extends BaseController
{
    protected UserModel $userModel;
    protected ReservationModel $reservationModel;

    public function __construct()
    {
        $this->userModel        = new UserModel();
        $this->reservationModel = new ReservationModel();
    }

    public function index()
    {
        $q       = trim((string) $this->request->getGet('q'));
        $builder = $this->userModel->where('role', 'customer');

        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
                ->groupEnd();
        }

        return view('owner/customers/index', [
            'title'     => 'Customers',
            'customers' => $builder->orderBy('name', 'ASC')->findAll(),
            'q'         => $q,
        ]);
    }

    /**
     * AJAX endpoint behind the topbar search box — returns a short list of
     * matching customers as JSON so results can show up live, under the
     * search box, while typing (instead of only after pressing Enter and
     * leaving whatever page you were on for the full Customers list).
     */
    public function quickSearch()
    {
        $q = trim((string) $this->request->getGet('q'));
        if ($q === '') {
            return $this->response->setJSON([]);
        }

        $matches = $this->userModel->where('role', 'customer')
            ->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->limit(6)
            ->findAll();

        return $this->response->setJSON(array_map(static fn ($c) => [
            'id'     => $c['user_id'],
            'name'   => $c['name'],
            'email'  => $c['email'],
            'avatar' => avatar_url($c['avatar']),
        ], $matches));
    }

    public function show($id)
    {
        $customer = $this->userModel->find((int) $id);
        if (! $customer || $customer['role'] !== 'customer') {
            return redirect()->to('owner/customers')->with('error', 'Customer not found.');
        }

        $reservations = $this->reservationModel->forUser((int) $id);
        $claimed      = array_filter($reservations, static fn ($r) => $r['status'] === 'Claimed');

        return view('owner/customers/show', [
            'title'        => $customer['name'],
            'customer'     => $customer,
            'reservations' => $reservations,
            'stats'        => [
                'total_reservations' => count($reservations),
                'claimed'            => count($claimed),
                'total_spent'        => array_sum(array_column($claimed, 'total_amount')),
            ],
        ]);
    }
}
