<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\InventoryTransactionModel;
use App\Models\PaymentModel;
use App\Models\ProductModel;
use App\Models\ReservationDetailModel;
use App\Models\ReservationModel;
use App\Models\SaleModel;
use App\Models\StockBatchModel;

class ReservationController extends BaseController
{
    protected ReservationModel $reservationModel;
    protected ReservationDetailModel $detailModel;
    protected ProductModel $productModel;
    protected InventoryTransactionModel $inventoryModel;
    protected PaymentModel $paymentModel;
    protected SaleModel $saleModel;
    protected StockBatchModel $stockBatchModel;

    public function __construct()
    {
        $this->reservationModel = new ReservationModel();
        $this->detailModel      = new ReservationDetailModel();
        $this->productModel     = new ProductModel();
        $this->inventoryModel   = new InventoryTransactionModel();
        $this->paymentModel     = new PaymentModel();
        $this->saleModel        = new SaleModel();
        $this->stockBatchModel  = new StockBatchModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status');

        $builder = $this->reservationModel->withCustomer()->orderBy('reservations.created_at', 'DESC');
        if ($status && in_array($status, ReservationModel::STATUSES, true)) {
            $builder->where('reservations.status', $status);
        }

        return view('owner/reservations/index', [
            'title'        => 'Reservations',
            'reservations' => $builder->findAll(),
            'statusFilter' => $status,
        ]);
    }

    public function show($id)
    {
        $reservation = $this->reservationModel->withCustomer()->where('reservations.reservation_id', $id)->first();

        if (! $reservation) {
            return redirect()->to('owner/reservations')->with('error', 'Reservation not found.');
        }

        return view('owner/reservations/show', [
            'title'       => 'Reservation #' . $id,
            'reservation' => $reservation,
            'details'     => $this->detailModel->forReservation((int) $id),
            'payments'    => $this->paymentModel->where('reservation_id', $id)->findAll(),
        ]);
    }

    public function confirm($id)
    {
        $reservation = $this->reservationModel->find((int) $id);
        if (! $reservation || $reservation['status'] !== 'Pending') {
            return redirect()->to('owner/reservations/' . $id)->with('error', 'Only pending reservations can be confirmed.');
        }

        $details = $this->detailModel->forReservation((int) $id);
        foreach ($details as $d) {
            $product = $this->productModel->find($d['product_id']);
            if (! $product || $product['stock'] < $d['quantity']) {
                return redirect()->to('owner/reservations/' . $id)->with('error', 'Insufficient stock for ' . ($product['product_name'] ?? 'product') . '.');
            }
        }

        // FIFO: each line item draws from this product's oldest
        // remaining batch(es) first, not just an anonymous number.
        foreach ($details as $d) {
            $this->stockBatchModel->deplete($d['product_id'], $d['quantity'], 'Reserved', 'Reservation #' . $id . ' confirmed');
        }

        $this->reservationModel->update($id, ['status' => 'Confirmed']);

        return redirect()->to('owner/reservations/' . $id)->with('success', 'Reservation confirmed and stock reserved.');
    }

    public function ready($id)
    {
        $reservation = $this->reservationModel->withCustomer()->where('reservations.reservation_id', $id)->first();
        if (! $reservation || $reservation['status'] !== 'Confirmed') {
            return redirect()->to('owner/reservations/' . $id)->with('error', 'Only confirmed reservations can be marked ready.');
        }

        $this->reservationModel->update($id, ['status' => 'Ready']);

        $this->sendReadyForPickupEmail($reservation, $this->detailModel->forReservation((int) $id));

        return redirect()->to('owner/reservations/' . $id)->with('success', 'Order marked as ready for claiming.');
    }

    /**
     * "Ready to claim" notification. Best-effort like the other system
     * emails — a failed send is logged but never rolls back or blocks
     * the status change itself.
     */
    private function sendReadyForPickupEmail(array $reservation, array $details): void
    {
        $emailService = mailer();
        $emailService->setTo($reservation['email']);
        $emailService->setSubject('Your Order is Ready — #' . $reservation['reservation_id']);

        $itemsHtml = '';
        foreach ($details as $d) {
            $itemsHtml .= '<strong>' . esc($d['product_name']) . '</strong> &times; ' . $d['quantity'] . '<br>';
        }

        $emailService->setMessage(email_template($emailService,
            "<p style=\"margin:0 0 16px;\">Hi " . esc($reservation['customer_name']) . ",</p>" .
            "<p style=\"margin:0 0 16px;\">Good news — your order is ready for pickup!</p>" .
            "<div style=\"background:#FBF3EA; border-radius:12px; padding:14px 16px;\">" .
            $itemsHtml .
            "Total: &#8369;" . number_format($reservation['total_amount'], 2) .
            "</div>" .
            "<p style=\"margin:16px 0 0; color:#8A7A6A; font-size:13px;\">Reservation #{$reservation['reservation_id']} &middot; Please bring your name or this confirmation when claiming.</p>"
        ));

        if (! $emailService->send()) {
            log_message('error', 'Ready-for-pickup email failed to send to {email} for reservation #{id}', [
                'email' => $reservation['email'],
                'id'    => $reservation['reservation_id'],
            ]);
        }
    }

    public function claim($id)
    {
        $reservation = $this->reservationModel->find((int) $id);
        if (! $reservation || $reservation['status'] !== 'Ready') {
            return redirect()->to('owner/reservations/' . $id)->with('error', 'Only ready orders can be claimed.');
        }

        $rules = [
            'payment_method' => 'required|in_list[Cash,GCash]',
            'amount_paid'    => 'required|decimal|greater_than_equal_to[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('owner/reservations/' . $id)->with('error', implode(' ', $this->validator->getErrors()));
        }

        $amountPaid = (float) $this->request->getPost('amount_paid');
        if ($amountPaid < (float) $reservation['total_amount']) {
            return redirect()->to('owner/reservations/' . $id)->with('error', 'Amount paid is less than the total amount due.');
        }

        $paymentId = $this->paymentModel->insert([
            'reservation_id' => $id,
            'amount_paid'    => $amountPaid,
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_date'   => date('Y-m-d H:i:s'),
            'status'         => 'Paid',
        ]);

        $this->reservationModel->update($id, [
            'status'         => 'Claimed',
            'payment_status' => 'Paid',
        ]);

        $this->saleModel->insert([
            'reservation_id' => $id,
            'payment_id'     => $paymentId,
            'sale_date'      => date('Y-m-d H:i:s'),
            'total_amount'   => $reservation['total_amount'],
        ]);

        $details = $this->detailModel->forReservation((int) $id);
        foreach ($details as $d) {
            $this->inventoryModel->log($d['product_id'], 'Sold', -$d['quantity'], 'Reservation #' . $id . ' claimed');
        }

        return redirect()->to('owner/reservations/' . $id)->with('success', 'Order claimed, payment recorded, and sale logged.');
    }

    public function cancel($id)
    {
        $reservation = $this->reservationModel->find((int) $id);
        if (! $reservation || in_array($reservation['status'], ['Claimed', 'Cancelled'], true)) {
            return redirect()->to('owner/reservations/' . $id)->with('error', 'This reservation cannot be cancelled.');
        }

        if (in_array($reservation['status'], ['Confirmed', 'Ready'], true)) {
            $details = $this->detailModel->forReservation((int) $id);
            foreach ($details as $d) {
                // Returned stock re-enters as a new batch dated now — it
                // physically wasn't sitting in inventory in the meantime,
                // so it correctly queues behind whatever's already on hand.
                $this->stockBatchModel->restore($d['product_id'], $d['quantity'], 'Reservation #' . $id . ' cancelled');
            }
        }

        $this->reservationModel->update($id, ['status' => 'Cancelled']);

        return redirect()->to('owner/reservations/' . $id)->with('success', 'Reservation cancelled.');
    }
}
