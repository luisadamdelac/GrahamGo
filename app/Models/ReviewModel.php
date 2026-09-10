<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table         = 'reviews';
    protected $primaryKey    = 'review_id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'product_id', 'rating', 'comment', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * A customer can only review a product they've actually claimed —
     * checked via reservation_details/reservations rather than trusting
     * the client, same pattern as everywhere else stock/orders are
     * enforced server-side.
     */
    public function hasClaimed(int $userId, int $productId): bool
    {
        return db_connect()->table('reservation_details rd')
            ->join('reservations r', 'r.reservation_id = rd.reservation_id')
            ->where('r.user_id', $userId)
            ->where('rd.product_id', $productId)
            ->where('r.status', 'Claimed')
            ->countAllResults() > 0;
    }

    public function myReview(int $userId, int $productId): ?array
    {
        return $this->where('user_id', $userId)->where('product_id', $productId)->first();
    }

    /** Approved reviews for a product, newest first, with the reviewer's name/avatar. */
    public function approvedForProduct(int $productId): array
    {
        return $this->select('reviews.*, users.name AS customer_name, users.avatar AS customer_avatar')
            ->join('users', 'users.user_id = reviews.user_id')
            ->where('reviews.product_id', $productId)
            ->where('reviews.status', 'Approved')
            ->orderBy('reviews.created_at', 'DESC')
            ->findAll();
    }

    /** ['avg' => float, 'count' => int] over approved reviews only. */
    public function summaryForProduct(int $productId): array
    {
        $row = $this->selectAvg('rating', 'avg')->selectCount('review_id', 'count')
            ->where('product_id', $productId)->where('status', 'Approved')
            ->first();

        return [
            'avg'   => $row && $row['count'] > 0 ? round((float) $row['avg'], 1) : 0.0,
            'count' => (int) ($row['count'] ?? 0),
        ];
    }

    /** Same as summaryForProduct() but for every product at once — one query for a whole listing page. */
    public function summaryForAllProducts(): array
    {
        $rows = $this->select('product_id, AVG(rating) AS avg, COUNT(review_id) AS count')
            ->where('status', 'Approved')
            ->groupBy('product_id')
            ->findAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['product_id']] = ['avg' => round((float) $row['avg'], 1), 'count' => (int) $row['count']];
        }

        return $map;
    }

    public function pendingCount(): int
    {
        return $this->where('status', 'Pending')->countAllResults();
    }

    public function withProductAndCustomer()
    {
        return $this->select('reviews.*, users.name AS customer_name, products.product_name')
            ->join('users', 'users.user_id = reviews.user_id')
            ->join('products', 'products.product_id = reviews.product_id')
            ->orderBy('reviews.created_at', 'DESC');
    }
}
