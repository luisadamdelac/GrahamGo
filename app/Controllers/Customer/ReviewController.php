<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ReviewModel;

class ReviewController extends BaseController
{
    public function store($productId)
    {
        $productId = (int) $productId;
        $userId    = current_customer()['user_id'];
        $reviewModel = new ReviewModel();

        if (! $reviewModel->hasClaimed($userId, $productId)) {
            return redirect()->to('products/' . $productId)->with('error', 'You can only review a product you\'ve claimed.');
        }

        // One submission per (customer, product), final — no editing
        // afterward. The view only ever shows the form when myReview()
        // is empty, so reaching this with an existing review means a
        // direct/repeat POST; reject it the same way either way.
        if ($reviewModel->myReview($userId, $productId)) {
            return redirect()->to('products/' . $productId)->with('error', 'You\'ve already reviewed this product — reviews can\'t be edited once submitted.');
        }

        $rules = [
            'rating'  => 'required|in_list[1,2,3,4,5]',
            'comment' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('products/' . $productId)->with('error', implode(' ', $this->validator->getErrors()));
        }

        $reviewModel->insert([
            'user_id'    => $userId,
            'product_id' => $productId,
            'rating'     => (int) $this->request->getPost('rating'),
            'comment'    => $this->request->getPost('comment'),
            'status'     => 'Pending',
        ]);

        return redirect()->to('products/' . $productId)->with('success', 'Thanks! Your review was submitted and will show once approved.');
    }
}
