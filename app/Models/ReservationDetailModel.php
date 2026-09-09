<?php

namespace App\Models;

use CodeIgniter\Model;

class ReservationDetailModel extends Model
{
    protected $table            = 'reservation_details';
    protected $primaryKey       = 'detail_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'reservation_id', 'product_id', 'quantity', 'price', 'subtotal',
    ];

    public function forReservation(int $reservationId): array
    {
        return $this->select('reservation_details.*, products.product_name, products.image')
            ->join('products', 'products.product_id = reservation_details.product_id')
            ->where('reservation_id', $reservationId)
            ->findAll();
    }
}
