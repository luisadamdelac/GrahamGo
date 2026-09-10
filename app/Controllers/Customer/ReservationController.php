<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\InventoryTransactionModel;
use App\Models\ProductModel;
use App\Models\ReservationDetailModel;
use App\Models\ReservationModel;
use App\Models\ReviewModel;
use App\Models\SettingModel;

class ReservationController extends BaseController
{
    protected ReservationModel $reservationModel;
    protected ReservationDetailModel $detailModel;
    protected ProductModel $productModel;
    protected InventoryTransactionModel $inventoryModel;
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->reservationModel = new ReservationModel();
        $this->detailModel      = new ReservationDetailModel();
        $this->productModel     = new ProductModel();
        $this->inventoryModel   = new InventoryTransactionModel();
        $this->settingModel     = new SettingModel();
    }

    private function maxClaimDate(): string
    {
        $daysAhead = (int) $this->settingModel->getValue('max_reservation_days_ahead', '7');

        return date('Y-m-d', strtotime("+{$daysAhead} days"));
    }

    public function create($productId)
    {
        $product = $this->productModel->find((int) $productId);

        if (! $product || $product['status'] !== 'Active') {
            return redirect()->to('home')->with('error', 'Product not found or unavailable.');
        }

        return view('customer/reservations/create', [
            'title'        => 'Make a Reservation',
            'product'      => $product,
            'maxClaimDate' => $this->maxClaimDate(),
        ]);
    }

    public function store($productId)
    {
        $product = $this->productModel->find((int) $productId);

        if (! $product || $product['status'] !== 'Active') {
            return redirect()->to('home')->with('error', 'Product not found or unavailable.');
        }

        $rules = [
            'quantity'   => 'required|integer|greater_than[0]',
            'claim_date' => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $quantity  = (int) $this->request->getPost('quantity');
        $claimDate = $this->request->getPost('claim_date');

        if (strtotime($claimDate) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('error', 'Claim date cannot be in the past.');
        }

        $maxClaimDate = $this->maxClaimDate();
        if (strtotime($claimDate) > strtotime($maxClaimDate)) {
            return redirect()->back()->withInput()->with('error', 'Claim date cannot be later than ' . date('M j, Y', strtotime($maxClaimDate)) . '.');
        }

        if ($quantity > $product['stock']) {
            return redirect()->back()->withInput()->with('error', 'Sorry, only ' . $product['stock'] . ' item(s) available for this product.');
        }

        $subtotal = $product['price'] * $quantity;

        $reservationId = $this->reservationModel->insert([
            'user_id'          => current_customer()['user_id'],
            'reservation_date' => date('Y-m-d H:i:s'),
            'claim_date'       => $claimDate,
            'total_amount'     => $subtotal,
            'payment_status'   => 'Unpaid',
            'status'           => 'Pending',
        ]);

        $this->detailModel->insert([
            'reservation_id' => $reservationId,
            'product_id'     => $product['product_id'],
            'quantity'       => $quantity,
            'price'          => $product['price'],
            'subtotal'       => $subtotal,
        ]);

        $this->sendReservationSubmittedEmail(current_customer(), $product, $quantity, $claimDate, $subtotal, $reservationId);

        return redirect()->to('my-reservations/' . $reservationId)
            ->with('success', 'Reservation submitted. The owner will review and confirm it soon.');
    }

    /**
     * Confirmation email for a newly-submitted reservation. Best-effort:
     * a failed send is logged, but never blocks the reservation itself —
     * the reservation is already saved in the database by this point, so
     * the customer should never lose their spot just because Gmail SMTP
     * had a hiccup.
     */
    private function sendReservationSubmittedEmail(array $customer, array $product, int $quantity, string $claimDate, float $subtotal, int $reservationId): void
    {
        $emailService = mailer();
        $emailService->setTo($customer['email']);
        $emailService->setSubject('Reservation Received — #' . $reservationId);
        $emailService->setMessage(email_template($emailService,
            "<p style=\"margin:0 0 16px;\">Hi " . esc($customer['name']) . ",</p>" .
            "<p style=\"margin:0 0 16px;\">We received your reservation. The owner will review and confirm it soon — you'll get another email once it's ready to claim.</p>" .
            "<div style=\"background:#FBF3EA; border-radius:12px; padding:14px 16px;\">" .
            "<strong>" . esc($product['product_name']) . "</strong> &times; {$quantity}<br>" .
            "Claim date: " . esc(date('M j, Y', strtotime($claimDate))) . "<br>" .
            "Total: &#8369;" . number_format($subtotal, 2) .
            "</div>" .
            "<p style=\"margin:16px 0 0; color:#8A7A6A; font-size:13px;\">Reservation #{$reservationId} &middot; Status: Pending</p>"
        ));

        if (! $emailService->send()) {
            log_message('error', 'Reservation-submitted email failed to send to {email} for reservation #{id}', [
                'email' => $customer['email'],
                'id'    => $reservationId,
            ]);
        }
    }

    public function index()
    {
        return view('customer/reservations/index', [
            'title'        => 'My Reservations',
            'reservations' => $this->reservationModel->forUser(current_customer()['user_id']),
        ]);
    }

    public function show($id)
    {
        $reservation = $this->reservationModel->find((int) $id);

        if (! $reservation || $reservation['user_id'] != current_customer()['user_id']) {
            return redirect()->to('my-reservations')->with('error', 'Reservation not found.');
        }

        $details = $this->detailModel->forReservation($reservation['reservation_id']);

        // Reviewing only makes sense once you've actually claimed the
        // order, so myReview (for the pre-fill/edit state) is only worth
        // fetching here — see customer/reservations/show.php's "Rate
        // These Products" section.
        if ($reservation['status'] === 'Claimed') {
            $reviewModel = new ReviewModel();
            $userId      = current_customer()['user_id'];
            foreach ($details as &$d) {
                $d['myReview'] = $reviewModel->myReview($userId, $d['product_id']);
            }
            unset($d);
        }

        return view('customer/reservations/show', [
            'title'       => 'Reservation #' . $reservation['reservation_id'],
            'reservation' => $reservation,
            'details'     => $details,
        ]);
    }

    public function cancel($id)
    {
        $reservation = $this->reservationModel->find((int) $id);

        if (! $reservation || $reservation['user_id'] != current_customer()['user_id']) {
            return redirect()->to('my-reservations')->with('error', 'Reservation not found.');
        }

        if ($reservation['status'] !== 'Pending') {
            return redirect()->to('my-reservations/' . $id)->with('error', 'Only pending reservations can be cancelled.');
        }

        $this->reservationModel->update($id, ['status' => 'Cancelled']);

        return redirect()->to('my-reservations/' . $id)->with('success', 'Reservation cancelled.');
    }
}
