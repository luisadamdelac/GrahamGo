<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\ReservationModel;
use App\Models\SaleModel;
use App\Models\SettingModel;
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

        $unread = $this->unreadAlertCounts([
            'overdue'      => $overdue,
            'reservations' => $reservations,
            'stock'        => $lowStock,
            'signup'       => $newSignups,
        ]);

        return $this->response->setJSON([
            'reservations' => $reservations,
            'lowStock'     => $lowStock,
            'overdue'      => $overdue,
            'newSignups'   => $newSignups,
            'unread'       => $unread,
            'total'        => $unread['reservations'] + $unread['stock'] + $unread['signup'],
        ]);
    }

    /**
     * Notification-bell items are just live counts (overdue/pending
     * reservations, low stock, recent signups), not discrete stored
     * records — so "read" is tracked as a dismissed-at-this-count
     * baseline per category in the settings table instead of a per-row
     * flag. An item counts as unread whenever its current count has
     * grown past whatever it was when last dismissed; sidebar badges
     * (Reservations/Inventory nav counters) intentionally keep using the
     * raw counts everywhere else and are unaffected by this.
     */
    private function unreadAlertCounts(array $current): array
    {
        $dismissed = (new SettingModel())->getMany([
            'notif_dismissed_overdue', 'notif_dismissed_reservations',
            'notif_dismissed_stock', 'notif_dismissed_signup',
        ]);

        $unread = [];
        foreach ($current as $type => $count) {
            $baseline    = (int) ($dismissed['notif_dismissed_' . $type] ?? 0);
            $unread[$type] = max(0, $count - $baseline);
        }

        return $unread;
    }

    public function markNotificationsRead()
    {
        $type = $this->request->getPost('type');

        $current = [
            'overdue'      => (new ReservationModel())->overdueCount(),
            'reservations' => (new ReservationModel())->whereIn('status', ['Pending', 'Confirmed', 'Ready'])->countAllResults(),
            'stock'        => (new ProductModel())->lowStockCount(),
            'signup'       => (new UserModel())->recentCustomerSignupCount(),
        ];

        if (! isset($current[$type]) && $type !== 'all') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Unknown notification type.']);
        }

        $settingModel = new SettingModel();
        $types        = $type === 'all' ? array_keys($current) : [$type];
        foreach ($types as $t) {
            $settingModel->setValue('notif_dismissed_' . $t, (string) $current[$t]);
        }

        return $this->response->setJSON(['success' => true]);
    }
}
