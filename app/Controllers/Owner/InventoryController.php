<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\StockBatchModel;

class InventoryController extends BaseController
{
    protected ProductModel $productModel;
    protected StockBatchModel $stockBatchModel;

    public function __construct()
    {
        $this->productModel    = new ProductModel();
        $this->stockBatchModel = new StockBatchModel();
    }

    /**
     * Products and Inventory used to be separate sidebar tabs — merged
     * into one (see Owner\ProductController::index()) since they were
     * both really "manage this product's stock" in two different
     * places. This route is kept only so old bookmarks/links still land
     * somewhere sensible.
     */
    public function index()
    {
        return redirect()->to('owner/products');
    }

    public function allBatches()
    {
        // Defaults to the current month instead of blank date fields
        // (and every batch ever) on first visit — still overridable.
        $from = $this->request->getGet('from') ?: date('Y-m-01');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');

        return view('owner/inventory/all_batches', [
            'title'   => 'All Stock Batches',
            'batches' => $this->stockBatchModel->allWithProduct($from ?: null, $to ?: null),
            'from'    => $from,
            'to'      => $to,
        ]);
    }

    public function batches($id)
    {
        $product = $this->productModel->find((int) $id);
        if (! $product) {
            return redirect()->to('owner/products')->with('error', 'Product not found.');
        }

        return view('owner/inventory/batches', [
            'title'   => 'Stock Batches — ' . $product['product_name'],
            'product' => $product,
            'batches' => $this->stockBatchModel->breakdown((int) $id),
        ]);
    }

    public function adjust($id)
    {
        $product = $this->productModel->find((int) $id);
        if (! $product) {
            return redirect()->to('owner/products')->with('error', 'Product not found.');
        }

        $qty = (int) $this->request->getPost('quantity');
        if ($qty === 0) {
            return redirect()->to('owner/products')->with('error', 'Adjustment quantity cannot be zero.');
        }

        if ($qty < 0 && abs($qty) > (int) $product['stock']) {
            return redirect()->to('owner/products')->with('error', 'Cannot remove more stock than currently available.');
        }

        $notes = $this->request->getPost('notes') ?: 'Manual adjustment';

        if ($qty > 0) {
            $this->stockBatchModel->receive((int) $id, $qty, 'Adjustment', $notes);
        } else {
            $this->stockBatchModel->deplete((int) $id, abs($qty), 'Adjustment', $notes);
        }

        return redirect()->to('owner/products')->with('success', 'Stock adjusted for ' . $product['product_name'] . '.');
    }
}
