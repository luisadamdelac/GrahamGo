<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table            = 'sales';
    protected $primaryKey       = 'sale_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'reservation_id', 'payment_id', 'sale_date', 'total_amount',
    ];

    public function withDetails(?string $from = null, ?string $to = null): array
    {
        $builder = $this->select('sales.*, reservations.claim_date, reservations.user_id, reservations.status AS reservation_status, users.name AS customer_name, users.customer_type, users.avatar AS customer_avatar, payments.payment_method, GROUP_CONCAT(products.product_name SEPARATOR ", ") AS product_names, SUM(reservation_details.quantity) AS total_quantity')
            ->join('reservations', 'reservations.reservation_id = sales.reservation_id')
            ->join('users', 'users.user_id = reservations.user_id')
            ->join('payments', 'payments.payment_id = sales.payment_id')
            ->join('reservation_details', 'reservation_details.reservation_id = sales.reservation_id')
            ->join('products', 'products.product_id = reservation_details.product_id')
            ->groupBy('sales.sale_id')
            ->orderBy('sales.sale_date', 'DESC');

        if ($from) {
            $builder->where('sales.sale_date >=', $from . ' 00:00:00');
        }
        if ($to) {
            $builder->where('sales.sale_date <=', $to . ' 23:59:59');
        }

        return $builder->findAll();
    }

    public function totalSales(): float
    {
        return (float) ($this->selectSum('total_amount')->first()['total_amount'] ?? 0);
    }

    /**
     * Day-by-day sales totals for the last $days days (including today),
     * oldest first — for the Dashboard's sales trend chart. Days with no
     * sales are filled in as 0 rather than skipped, so the chart's x-axis
     * stays evenly spaced.
     */
    public function dailyTotals(int $days = 7): array
    {
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        $rows = $this->select('DATE(sale_date) AS sale_day, SUM(total_amount) AS total')
            ->where('sale_date >=', $from . ' 00:00:00')
            ->groupBy('sale_day')
            ->findAll();

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[$row['sale_day']] = (float) $row['total'];
        }

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['date' => $day, 'total' => $byDay[$day] ?? 0.0];
        }

        return $result;
    }
}
