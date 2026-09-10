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

        $rules = [
            'rating'  => 'required|in_list[1,2,3,4,5]',
            'comment' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('products/' . $productId)->with('error', implode(' ', $this->validator->getErrors()));
        }

        $data = [
            'user_id'    => $userId,
            'product_id' => $productId,
            'rating'     => (int) $this->request->getPost('rating'),
            'comment'    => $this->request->getPost('comment'),
            // Re-reviewing (or editing) always resets to Pending — an
            // already-approved review's text shouldn't stay public
            // unmoderated once it's been edited.
            'status'     => 'Pending',
        ];

        $existing = $reviewModel->myReview($userId, $productId);
        if ($existing) {
            $reviewModel->update($existing['review_id'], $data);
        } else {
            $reviewModel->insert($data);
        }

        return redirect()->to('products/' . $productId)->with('success', 'Thanks! Your review was submitted and will show once approved.');
    }
}
