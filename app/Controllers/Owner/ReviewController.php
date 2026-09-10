<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\ReviewModel;

class ReviewController extends BaseController
{
    protected ReviewModel $reviewModel;

    public function __construct()
    {
        $this->reviewModel = new ReviewModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status') ?: 'Pending';
        $query  = $this->reviewModel->withProductAndCustomer();
        if (in_array($status, ['Pending', 'Approved', 'Rejected'], true)) {
            $query->where('reviews.status', $status);
        }

        return view('owner/reviews/index', [
            'title'   => 'Reviews',
            'reviews' => $query->findAll(),
            'status'  => $status,
            'counts'  => [
                'Pending'  => $this->reviewModel->where('status', 'Pending')->countAllResults(),
                'Approved' => $this->reviewModel->where('status', 'Approved')->countAllResults(),
                'Rejected' => $this->reviewModel->where('status', 'Rejected')->countAllResults(),
            ],
        ]);
    }

    public function approve($id)
    {
        $this->reviewModel->update($id, ['status' => 'Approved']);

        return redirect()->back()->with('success', 'Review approved.');
    }

    public function reject($id)
    {
        $this->reviewModel->update($id, ['status' => 'Rejected']);

        return redirect()->back()->with('success', 'Review rejected.');
    }
}
