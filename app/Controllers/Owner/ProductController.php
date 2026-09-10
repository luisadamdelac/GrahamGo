<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\InventoryTransactionModel;
use App\Models\ProductModel;
use App\Models\StockBatchModel;

class ProductController extends BaseController
{
    protected ProductModel $productModel;
    protected StockBatchModel $stockBatchModel;
    protected InventoryTransactionModel $inventoryModel;

    public function __construct()
    {
        $this->productModel    = new ProductModel();
        $this->stockBatchModel = new StockBatchModel();
        $this->inventoryModel  = new InventoryTransactionModel();
    }

    public function index()
    {
        return view('owner/products/index', [
            'title'        => 'Inventory',
            'products'     => $this->productModel->orderBy('product_name', 'ASC')->findAll(),
            'transactions' => array_slice($this->inventoryModel->withProduct(), 0, 30),
        ]);
    }

    public function create()
    {
        return view('owner/products/form', [
            'title'   => 'Add Product',
            'product' => null,
        ]);
    }

    public function store()
    {
        $rules = [
            'product_name' => 'required|max_length[100]',
            'price'        => 'required|decimal',
            'stock'        => 'required|integer|greater_than_equal_to[0]',
            'reorder_level' => 'permit_empty|integer|greater_than_equal_to[0]',
            'image'        => 'permit_empty|is_image[image]|max_size[image,2048]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $initialStock = (int) $this->request->getPost('stock');

        $productId = $this->productModel->insert([
            'product_name'  => $this->request->getPost('product_name'),
            'description'   => $this->request->getPost('description'),
            'price'         => $this->request->getPost('price'),
            'stock'         => 0,
            'reorder_level' => $this->request->getPost('reorder_level') ?: 5,
            'status'        => $this->request->getPost('status') ?: 'Active',
            'image'         => save_product_image_upload($this->request->getFile('image')),
        ]);

        // Stock starts at 0 above and is brought up via receive() instead
        // of being written directly, so the initial stock becomes this
        // product's first FIFO batch rather than an untracked number.
        if ($initialStock > 0) {
            $this->stockBatchModel->receive($productId, $initialStock, 'Stock In', 'Initial stock');
        }

        return redirect()->to('owner/products')->with('success', 'Product added.');
    }

    public function edit($id)
    {
        $product = $this->productModel->find((int) $id);
        if (! $product) {
            return redirect()->to('owner/products')->with('error', 'Product not found.');
        }

        return view('owner/products/form', [
            'title'   => 'Edit Product',
            'product' => $product,
        ]);
    }

    public function update($id)
    {
        $product = $this->productModel->find((int) $id);
        if (! $product) {
            return redirect()->to('owner/products')->with('error', 'Product not found.');
        }

        $rules = [
            'product_name'  => 'required|max_length[100]',
            'price'         => 'required|decimal',
            'stock'         => 'required|integer|greater_than_equal_to[0]',
            'reorder_level' => 'permit_empty|integer|greater_than_equal_to[0]',
            'image'         => 'permit_empty|is_image[image]|max_size[image,2048]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // 'stock' is deliberately left out of $data below — it's applied
        // through receive()/deplete() instead, so an edit that raises or
        // lowers stock is recorded as a proper FIFO batch movement rather
        // than a silent number change with no batch trail.
        $newStock = (int) $this->request->getPost('stock');
        $diff     = $newStock - (int) $product['stock'];

        $data = [
            'product_name'  => $this->request->getPost('product_name'),
            'description'   => $this->request->getPost('description'),
            'price'         => $this->request->getPost('price'),
            'reorder_level' => $this->request->getPost('reorder_level') ?: 5,
            'status'        => $this->request->getPost('status') ?: 'Active',
        ];

        $newImage = save_product_image_upload($this->request->getFile('image'), $product['image'] ?? null);
        if ($newImage) {
            $data['image'] = $newImage;
        }

        $this->productModel->update($id, $data);

        if ($diff > 0) {
            $this->stockBatchModel->receive((int) $id, $diff, 'Adjustment', 'Manual stock edit');
        } elseif ($diff < 0) {
            $this->stockBatchModel->deplete((int) $id, abs($diff), 'Adjustment', 'Manual stock edit');
        }

        return redirect()->to('owner/products')->with('success', 'Product updated.');
    }

    public function toggleStatus($id)
    {
        $product = $this->productModel->find((int) $id);
        if (! $product) {
            return redirect()->to('owner/products')->with('error', 'Product not found.');
        }

        // Deactivating a product with stock still on hand would hide it
        // from customers while that stock just sits there unsold/
        // unaccounted for — restock it out (or let it sell through)
        // first. The button is disabled for this same reason in the
        // view; this is the server-side backstop for that.
        if ($product['status'] === 'Active' && (int) $product['stock'] > 0) {
            return redirect()->to('owner/products')->with('error', 'Cannot deactivate — this product still has ' . $product['stock'] . ' unit(s) in stock.');
        }

        $newStatus = $product['status'] === 'Active' ? 'Inactive' : 'Active';
        $this->productModel->update($id, ['status' => $newStatus]);

        return redirect()->to('owner/products')->with('success', 'Product is now ' . $newStatus . '.');
    }

    public function delete($id)
    {
        $db = db_connect();
        $usedInReservations = $db->table('reservation_details')->where('product_id', $id)->countAllResults();

        if ($usedInReservations > 0) {
            return redirect()->to('owner/products')->with('error', 'This product has reservation history and cannot be deleted. Deactivate it instead.');
        }

        $this->productModel->delete($id);

        return redirect()->to('owner/products')->with('success', 'Product deleted.');
    }
}
