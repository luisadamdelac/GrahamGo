<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\InventoryTransactionModel;
use App\Models\ProductModel;
use App\Models\SaleModel;

class ReportController extends BaseController
{
    public function index()
    {
        return view('owner/reports/index', ['title' => 'Reports']);
    }

    public function reservations()
    {
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $builder = db_connect()->table('reservations r')
            ->select('r.reservation_id, r.claim_date, r.total_amount, r.payment_status, r.status, u.name AS customer_name, u.customer_type, p.product_name, rd.quantity')
            ->join('users u', 'u.user_id = r.user_id')
            ->join('reservation_details rd', 'rd.reservation_id = r.reservation_id')
            ->join('products p', 'p.product_id = rd.product_id')
            ->orderBy('r.claim_date', 'DESC');

        if ($from) {
            $builder->where('r.claim_date >=', $from);
        }
        if ($to) {
            $builder->where('r.claim_date <=', $to);
        }

        return view('owner/reports/reservations', [
            'title'        => 'Reservation Report',
            'reservations' => $builder->get()->getResultArray(),
            'from'         => $from,
            'to'           => $to,
        ]);
    }

    public function sales()
    {
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $model = new SaleModel();
        $sales = $model->withDetails($from ?: null, $to ?: null);

        return view('owner/reports/sales', [
            'title' => 'Sales Report',
            'sales' => $sales,
            'total' => array_sum(array_column($sales, 'total_amount')),
            'from'  => $from,
            'to'    => $to,
        ]);
    }

    public function inventory()
    {
        $productModel     = new ProductModel();
        $transactionModel = new InventoryTransactionModel();

        $products = $productModel->orderBy('product_name', 'ASC')->findAll();

        $summary = [];
        foreach ($products as $p) {
            $reserved = (int) abs((float) (db_connect()->table('inventory_transactions')
                ->selectSum('quantity', 'total')
                ->where('product_id', $p['product_id'])
                ->where('transaction_type', 'Reserved')
                ->get()->getRowArray()['total'] ?? 0));

            $sold = (int) abs((float) (db_connect()->table('inventory_transactions')
                ->selectSum('quantity', 'total')
                ->where('product_id', $p['product_id'])
                ->where('transaction_type', 'Sold')
                ->get()->getRowArray()['total'] ?? 0));

            $summary[] = [
                'product'  => $p,
                'reserved' => $reserved,
                'sold'     => $sold,
                'available' => $p['stock'],
            ];
        }

        return view('owner/reports/inventory', [
            'title'   => 'Inventory Report',
            'summary' => $summary,
        ]);
    }
}
