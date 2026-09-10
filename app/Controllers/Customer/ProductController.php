<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\ReviewModel;

class ProductController extends BaseController
{
    protected ProductModel $productModel;
    protected ReviewModel $reviewModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->reviewModel  = new ReviewModel();
    }

    public function index()
    {
        return view('customer/products/index', [
            'title'          => 'Products',
            'products'       => $this->productModel->activeProducts(),
            'ratingSummary'  => $this->reviewModel->summaryForAllProducts(),
        ]);
    }

    public function show($id)
    {
        $id      = (int) $id;
        $product = $this->productModel->find($id);

        if (! $product || $product['status'] !== 'Active') {
            return redirect()->to('home')->with('error', 'Product not found or unavailable.');
        }

        $userId = current_customer()['user_id'];

        return view('customer/products/show', [
            'title'         => $product['product_name'],
            'product'       => $product,
            'reviews'       => $this->reviewModel->approvedForProduct($id),
            'ratingSummary' => $this->reviewModel->summaryForProduct($id),
            'canReview'     => $this->reviewModel->hasClaimed($userId, $id),
            'myReview'      => $this->reviewModel->myReview($userId, $id),
        ]);
    }
}
