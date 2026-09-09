<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\ReservationModel;
use App\Models\SaleModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $reservationModel = new ReservationModel();
        $productModel      = new ProductModel();
        $saleModel         = new SaleModel();

        $counts = $reservationModel->counts();

        $productsSold = (int) (db_connect()->table('reservation_details rd')
            ->join('sales s', 's.reservation_id = rd.reservation_id')
            ->selectSum('rd.quantity', 'total')
            ->get()
            ->getRowArray()['total'] ?? 0);

        // Pending is included here too — it still needs an admin decision
        // (confirm or reject), which is arguably more urgent than an
        // already-confirmed order that just needs prepping. Excluding it
        // made this list look empty even when there was a fresh
        // reservation sitting unreviewed.
        $upcoming = $reservationModel->withCustomer()
            ->whereIn('reservations.status', ['Pending', 'Confirmed', 'Ready'])
            ->orderBy('reservations.claim_date', 'ASC')
            ->limit(8)
            ->findAll();

        return view('owner/dashboard/index', [
            'title'        => 'Dashboard',
            'counts'       => $counts,
            'totalSales'   => $saleModel->totalSales(),
            'productsSold' => $productsSold,
            'lowStock'     => $productModel->lowStockCount(),
            'upcoming'     => $upcoming,
            'salesTrend'   => $saleModel->dailyTotals(7),
        ]);
    }

    /**
     * Polled from every owner page (see owner_footer.php) so the sidebar
     * badges, topbar notification dropdown, and browser tab title stay
     * current even while the admin is sitting on some other page, without
     * needing a full page reload. Covers everything that needs an admin
     * decision or action: unreviewed/unprepared/unclaimed reservations
     * (overdue ones called out separately, though they're a subset —
     * see ReservationModel::overdueCount()), products running low, and
     * customers who signed up recently.
     */
    public function alertsCount()
    {
        $reservationModel = new ReservationModel();
        $productModel     = new ProductModel();
        $userModel        = new UserModel();

        $reservations = $reservationModel->whereIn('status', ['Pending', 'Confirmed', 'Ready'])->countAllResults();
        $lowStock     = $productModel->lowStockCount();
        $overdue      = $reservationModel->overdueCount();
        $newSignups   = $userModel->recentCustomerSignupCount();

        return $this->response->setJSON([
            'reservations' => $reservations,
            'lowStock'     => $lowStock,
            'overdue'      => $overdue,
            'newSignups'   => $newSignups,
            'total'        => $reservations + $lowStock + $newSignups,
        ]);
    }
}
