<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\SaleModel;

class SaleController extends BaseController
{
    protected SaleModel $saleModel;

    public function __construct()
    {
        $this->saleModel = new SaleModel();
    }

    public function index()
    {
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $sales = $this->saleModel->withDetails($from ?: null, $to ?: null);

        return view('owner/sales/index', [
            'title' => 'Sales',
            'sales' => $sales,
            'total' => array_sum(array_column($sales, 'total_amount')),
            'from'  => $from,
            'to'    => $to,
        ]);
    }
}
