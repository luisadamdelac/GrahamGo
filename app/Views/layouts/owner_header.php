<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php
  $ggReservationModel  = new \App\Models\ReservationModel();
  $ggReservationAlerts = $ggReservationModel->whereIn('status', ['Pending', 'Confirmed', 'Ready'])->countAllResults();
  $ggLowStockAlerts    = (new \App\Models\ProductModel())->lowStockCount();
  $ggOverdueAlerts     = $ggReservationModel->overdueCount();
  $ggNewSignups        = (new \App\Models\UserModel())->recentCustomerSignupCount();
  $ggPendingReviews    = (new \App\Models\ReviewModel())->pendingCount();

  // Sidebar nav badges (Reservations/Inventory) always use the raw
  // counts above — they're live to-do counters, not part of the
  // notification inbox. The bell dropdown's own items/badge instead use
  // "unread" versions, gated by whatever count each category was at
  // when last dismissed via Mark as read/Mark all as read (see
  // DashboardController::markNotificationsRead).
  $ggDismissed = (new \App\Models\SettingModel())->getMany([
      'notif_dismissed_overdue', 'notif_dismissed_reservations',
      'notif_dismissed_stock', 'notif_dismissed_signup',
  ]);
  $ggUnreadOverdue      = max(0, $ggOverdueAlerts - (int) ($ggDismissed['notif_dismissed_overdue'] ?? 0));
  $ggUnreadReservations = max(0, $ggReservationAlerts - (int) ($ggDismissed['notif_dismissed_reservations'] ?? 0));
  $ggUnreadStock        = max(0, $ggLowStockAlerts - (int) ($ggDismissed['notif_dismissed_stock'] ?? 0));
  $ggUnreadSignups      = max(0, $ggNewSignups - (int) ($ggDismissed['notif_dismissed_signup'] ?? 0));
  $ggTotalAlerts        = $ggUnreadReservations + $ggUnreadStock + $ggUnreadSignups;
?>
<title id="ggPageTitle" data-base-title="<?= esc($title ?? 'GrahamGo Admin') ?>"><?= $ggTotalAlerts > 0 ? "({$ggTotalAlerts}) " : '' ?><?= esc($title ?? 'GrahamGo Admin') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/app.css') ?>" rel="stylesheet">
<link rel="icon" href="<?= base_url('assets/img/logo.png') ?>">
<style>body { background:#faf7f3; }</style>
</head>
<body>

<div class="owner-topbar d-lg-none">
  <button class="menu-btn" id="ggSidebarToggle" type="button" aria-label="Open menu"><i class="bi bi-list"></i></button>
  <div class="brand flex-grow-1">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="GrahamGo" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <span class="brand-text">GrahamGo</span>
  </div>
  <div class="d-flex align-items-center gap-2">
    <button class="menu-btn" id="ggMobileSearchToggle" type="button" aria-label="Search customers"><i class="bi bi-search"></i></button>
    <?= view('partials/owner_notification_bell', [
      'ggTotalAlerts' => $ggTotalAlerts, 'ggOverdueAlerts' => $ggOverdueAlerts,
      'ggReservationAlerts' => $ggReservationAlerts, 'ggLowStockAlerts' => $ggLowStockAlerts, 'ggNewSignups' => $ggNewSignups,
      'ggUnreadOverdue' => $ggUnreadOverdue, 'ggUnreadReservations' => $ggUnreadReservations,
      'ggUnreadStock' => $ggUnreadStock, 'ggUnreadSignups' => $ggUnreadSignups,
    ]) ?>
    <?= view('partials/owner_account_dropdown') ?>
  </div>
</div>
<div class="owner-topbar-search d-lg-none d-none" id="ggMobileSearchRow">
  <form action="<?= site_url('owner/customers') ?>" method="get" class="topbar-search w-100" id="ggMobileSearchForm" autocomplete="off">
    <i class="bi bi-search"></i>
    <input type="search" name="q" id="ggMobileSearchInput" placeholder="Search customers by name or email&hellip;" autocomplete="off">
    <div class="topbar-search-results" id="ggMobileSearchResults"></div>
  </form>
</div>

<div class="sidebar-backdrop" id="ggSidebarBackdrop"></div>

<div class="d-flex">
  <div class="sidebar p-3" id="ggSidebar" style="width:250px;">
    <div class="brand mb-4 px-1">
      <img src="<?= base_url('assets/img/logo.png') ?>" alt="GrahamGo" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
      <span>GrahamGo <span style="color:var(--gg-primary);">Admin</span></span>
    </div>
    <nav class="nav flex-column gap-1">
      <a href="<?= site_url('owner/dashboard') ?>" class="<?= (uri_string() === 'owner/dashboard') ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
      <a href="<?= site_url('owner/reservations') ?>" class="<?= (str_starts_with(uri_string(), 'owner/reservations')) ? 'active' : '' ?>">
        <i class="bi bi-journal-check"></i> Reservations
        <span id="ggReservationBadge" class="badge bg-danger ms-auto" style="<?= $ggReservationAlerts > 0 ? '' : 'display:none;' ?>"><?= $ggReservationAlerts ?></span>
      </a>
      <a href="<?= site_url('owner/customers') ?>" class="<?= (str_starts_with(uri_string(), 'owner/customers')) ? 'active' : '' ?>"><i class="bi bi-people-fill"></i> Customers</a>
      <a href="<?= site_url('owner/products') ?>" class="<?= (str_starts_with(uri_string(), 'owner/products') || str_starts_with(uri_string(), 'owner/inventory')) ? 'active' : '' ?>">
        <i class="bi bi-clipboard-data-fill"></i> Inventory
        <span id="ggLowStockBadge" class="badge bg-danger ms-auto" style="<?= $ggLowStockAlerts > 0 ? '' : 'display:none;' ?>"><?= $ggLowStockAlerts ?></span>
      </a>
      <a href="<?= site_url('owner/sales') ?>" class="<?= (str_starts_with(uri_string(), 'owner/sales')) ? 'active' : '' ?>"><i class="bi bi-cash-coin"></i> Sales</a>
      <a href="<?= site_url('owner/walk-in-sale') ?>" class="<?= (uri_string() === 'owner/walk-in-sale') ? 'active' : '' ?>"><i class="bi bi-bag-check-fill"></i> Walk-in Sale</a>
      <a href="<?= site_url('owner/reports') ?>" class="<?= (str_starts_with(uri_string(), 'owner/reports')) ? 'active' : '' ?>"><i class="bi bi-bar-chart-fill"></i> Reports</a>
      <a href="<?= site_url('owner/reviews') ?>" class="<?= (str_starts_with(uri_string(), 'owner/reviews')) ? 'active' : '' ?>">
        <i class="bi bi-star-fill"></i> Reviews
        <span id="ggReviewsBadge" class="badge bg-danger ms-auto" style="<?= $ggPendingReviews > 0 ? '' : 'display:none;' ?>"><?= $ggPendingReviews ?></span>
      </a>
      <div class="nav-divider"></div>
      <a href="<?= site_url('owner/profile') ?>" class="<?= (str_starts_with(uri_string(), 'owner/profile')) ? 'active' : '' ?>">
        <?php if (! empty(current_owner()['avatar'])): ?>
          <img src="<?= cloudinary_resized(avatar_url(current_owner()['avatar']), 36) ?>" class="rounded-circle" style="width:18px;height:18px;object-fit:cover;" alt="">
        <?php else: ?>
          <i class="bi bi-person-circle"></i>
        <?php endif; ?>
        Profile
      </a>
      <a href="<?= site_url('owner/settings') ?>" class="<?= (str_starts_with(uri_string(), 'owner/settings')) ? 'active' : '' ?>"><i class="bi bi-gear-fill"></i> Settings</a>
      <a href="<?= site_url('owner/logout') ?>" class="logout-link" style="color:var(--gg-danger);"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
  </div>
  <div class="flex-grow-1 owner-content" style="min-width:0;">
    <div class="admin-topbar d-none d-lg-flex align-items-center justify-content-between">
      <form action="<?= site_url('owner/customers') ?>" method="get" class="topbar-search" id="ggTopbarSearchForm" autocomplete="off">
        <i class="bi bi-search"></i>
        <input type="search" name="q" id="ggTopbarSearchInput" placeholder="Search customers by name or email&hellip;" value="<?= esc($q ?? '') ?>" autocomplete="off">
        <div class="topbar-search-results" id="ggTopbarSearchResults"></div>
      </form>
      <div class="admin-topbar-actions">
        <?= view('partials/owner_notification_bell', [
          'ggTotalAlerts' => $ggTotalAlerts, 'ggOverdueAlerts' => $ggOverdueAlerts,
          'ggReservationAlerts' => $ggReservationAlerts, 'ggLowStockAlerts' => $ggLowStockAlerts, 'ggNewSignups' => $ggNewSignups,
        ]) ?>
        <?= view('partials/owner_account_dropdown') ?>
      </div>
    </div>
    <div class="p-3 p-lg-4">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success fade show d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> <?= esc(session()->getFlashdata('success')) ?>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger fade show d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>
