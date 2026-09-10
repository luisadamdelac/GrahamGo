<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\ProductModel;
use App\Models\ReservationDetailModel;
use App\Models\ReservationModel;
use App\Models\SaleModel;
use App\Models\StockBatchModel;
use App\Models\UserModel;
use RuntimeException;

/**
 * Over-the-counter sales — a customer buys and pays on the spot, with no
 * prior online reservation. Recorded as a reservation that's already
 * Claimed/Paid (attributed to a shared "Walk-in Customer" account) so it
 * automatically deducts stock the same FIFO way as a reservation, and
 * shows up correctly in Sales/Reports/Dashboard totals alongside
 * reservation-based sales, with no schema change needed.
 */
class WalkInSaleController extends BaseController
{
    protected ProductModel $productModel;
    protected ReservationModel $reservationModel;
    protected ReservationDetailModel $detailModel;
    protected PaymentModel $paymentModel;
    protected SaleModel $saleModel;
    protected StockBatchModel $stockBatchModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->productModel     = new ProductModel();
        $this->reservationModel = new ReservationModel();
        $this->detailModel      = new ReservationDetailModel();
        $this->paymentModel     = new PaymentModel();
        $this->saleModel        = new SaleModel();
        $this->stockBatchModel  = new StockBatchModel();
        $this->userModel        = new UserModel();
    }

    public function create()
    {
        return view('owner/walkin/create', [
            'title'    => 'Walk-in Sale',
            'products' => $this->productModel->activeProducts(),
        ]);
    }

    public function store()
    {
        $rules = [
            'product_id'     => 'required|integer',
            'quantity'       => 'required|integer|greater_than[0]',
            'payment_method' => 'required|in_list[Cash,GCash]',
            'amount_paid'    => 'required|decimal|greater_than_equal_to[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $product = $this->productModel->find((int) $this->request->getPost('product_id'));
        if (! $product || $product['status'] !== 'Active') {
            return redirect()->back()->withInput()->with('error', 'Product not found or unavailable.');
        }

        $quantity = (int) $this->request->getPost('quantity');
        if ($quantity > $product['stock']) {
            return redirect()->back()->withInput()->with('error', 'Only ' . $product['stock'] . ' item(s) available for this product.');
        }

        $totalAmount = $product['price'] * $quantity;
        $amountPaid  = (float) $this->request->getPost('amount_paid');
        if ($amountPaid < $totalAmount) {
            return redirect()->back()->withInput()->with('error', 'Amount paid is less than the total amount due.');
        }

        $walkInCustomer = $this->userModel->getOrCreateWalkInCustomer();
        $now            = date('Y-m-d H:i:s');

        $reservationId = $this->reservationModel->insert([
            'user_id'          => $walkInCustomer['user_id'],
            'reservation_date' => $now,
            'claim_date'       => date('Y-m-d'),
            'total_amount'     => $totalAmount,
            'payment_status'   => 'Paid',
            'status'           => 'Claimed',
        ]);

        $this->detailModel->insert([
            'reservation_id' => $reservationId,
            'product_id'     => $product['product_id'],
            'quantity'       => $quantity,
            'price'          => $product['price'],
            'subtotal'       => $totalAmount,
        ]);

        try {
            $this->stockBatchModel->deplete($product['product_id'], $quantity, 'Walk-in Sale', 'Walk-in sale #' . $reservationId);
        } catch (RuntimeException $e) {
            $this->reservationModel->delete($reservationId, true);
            $this->detailModel->where('reservation_id', $reservationId)->delete();

            return redirect()->back()->withInput()->with('error', 'Insufficient batched stock: ' . $e->getMessage());
        }

        $paymentId = $this->paymentModel->insert([
            'reservation_id' => $reservationId,
            'amount_paid'    => $amountPaid,
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_date'   => $now,
            'status'         => 'Paid',
        ]);

        $this->saleModel->insert([
            'reservation_id' => $reservationId,
            'payment_id'     => $paymentId,
            'sale_date'      => $now,
            'total_amount'   => $totalAmount,
        ]);

        return redirect()->to('owner/sales')->with('success', 'Walk-in sale recorded — ' . $product['product_name'] . ' x' . $quantity . '.');
    }
}
