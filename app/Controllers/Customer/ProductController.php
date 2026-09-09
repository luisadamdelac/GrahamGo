<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductController extends BaseController
{
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        return view('customer/products/index', [
            'title'    => 'Products',
            'products' => $this->productModel->activeProducts(),
        ]);
    }

    public function show($id)
    {
        $product = $this->productModel->find((int) $id);

        if (! $product || $product['status'] !== 'Active') {
            return redirect()->to('home')->with('error', 'Product not found or unavailable.');
        }

        return view('customer/products/show', [
            'title'   => $product['product_name'],
            'product' => $product,
        ]);
    }
}
