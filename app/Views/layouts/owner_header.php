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
  // Overdue is a subset of $ggReservationAlerts (an overdue reservation
  // is still Pending/Confirmed/Ready), so it's surfaced as its own
  // notification-dropdown item but deliberately left out of the total
  // to avoid double-counting the same reservations twice.
  $ggTotalAlerts = $ggReservationAlerts + $ggLowStockAlerts + $ggNewSignups;
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
  <div class="brand">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="GrahamGo" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    GrahamGo
  </div>
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
  <div class="flex-grow-1" style="min-width:0;">
    <div class="admin-topbar d-none d-lg-flex align-items-center justify-content-between">
      <form action="<?= site_url('owner/customers') ?>" method="get" class="topbar-search" id="ggTopbarSearchForm" autocomplete="off">
        <i class="bi bi-search"></i>
        <input type="search" name="q" id="ggTopbarSearchInput" placeholder="Search customers by name or email&hellip;" value="<?= esc($q ?? '') ?>" autocomplete="off">
        <div class="topbar-search-results" id="ggTopbarSearchResults"></div>
      </form>
      <div class="admin-topbar-actions">
        <div class="dropdown">
          <button class="topbar-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
            <i class="bi bi-bell-fill"></i>
            <span id="ggTopbarBellBadge" class="topbar-badge" style="<?= $ggTotalAlerts > 0 ? '' : 'display:none;' ?>"><?= $ggTotalAlerts ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" style="min-width:290px;">
            <li><h6 class="dropdown-header">Notifications</h6></li>
            <li id="ggNotifOverdueItem" style="<?= $ggOverdueAlerts > 0 ? '' : 'display:none;' ?>">
              <a class="dropdown-item" href="<?= site_url('owner/reservations') ?>">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-danger-bg);color:var(--gg-danger);"><i class="bi bi-alarm-fill"></i></div>
                <div>
                  <div class="fw-semibold"><span id="ggNotifOverdueCount"><?= $ggOverdueAlerts ?></span> reservation(s) overdue</div>
                  <div class="text-muted" style="font-size:.78rem;">Claim date has already passed</div>
                </div>
              </a>
            </li>
            <li id="ggNotifReservationItem" style="<?= $ggReservationAlerts > 0 ? '' : 'display:none;' ?>">
              <a class="dropdown-item" href="<?= site_url('owner/reservations') ?>">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-warning-bg);color:var(--gg-warning);"><i class="bi bi-journal-check"></i></div>
                <div>
                  <div class="fw-semibold"><span id="ggNotifReservationCount"><?= $ggReservationAlerts ?></span> reservation(s) need attention</div>
                  <div class="text-muted" style="font-size:.78rem;">Pending, confirmed, or ready to claim</div>
                </div>
              </a>
            </li>
            <li id="ggNotifStockItem" style="<?= $ggLowStockAlerts > 0 ? '' : 'display:none;' ?>">
              <a class="dropdown-item" href="<?= site_url('owner/products') ?>">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-danger-bg);color:var(--gg-danger);"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div>
                  <div class="fw-semibold"><span id="ggNotifStockCount"><?= $ggLowStockAlerts ?></span> product(s) low on stock</div>
                  <div class="text-muted" style="font-size:.78rem;">Restock before running out</div>
                </div>
              </a>
            </li>
            <li id="ggNotifSignupItem" style="<?= $ggNewSignups > 0 ? '' : 'display:none;' ?>">
              <a class="dropdown-item" href="<?= site_url('owner/customers') ?>">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-success-bg);color:var(--gg-success);"><i class="bi bi-person-plus-fill"></i></div>
                <div>
                  <div class="fw-semibold"><span id="ggNotifSignupCount"><?= $ggNewSignups ?></span> new customer sign-up(s)</div>
                  <div class="text-muted" style="font-size:.78rem;">In the last 48 hours</div>
                </div>
              </a>
            </li>
            <?php $ggAnyNotif = $ggOverdueAlerts + $ggReservationAlerts + $ggLowStockAlerts + $ggNewSignups; ?>
            <li id="ggNotifEmpty" class="dropdown-item-text text-center text-muted py-3" style="<?= $ggAnyNotif > 0 ? 'display:none;' : '' ?>">
              <i class="bi bi-emoji-smile"></i> You're all caught up!
            </li>
          </ul>
        </div>
        <div class="dropdown">
          <button class="topbar-account-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (! empty(current_owner()['avatar'])): ?>
              <img src="<?= cloudinary_resized(avatar_url(current_owner()['avatar']), 68) ?>" class="rounded-circle" style="width:34px;height:34px;object-fit:cover;" alt="">
            <?php else: ?>
              <div class="topbar-avatar-fallback"><i class="bi bi-person-fill"></i></div>
            <?php endif; ?>
            <span class="d-none d-xl-inline"><?= esc(current_owner()['name']) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= site_url('owner/profile') ?>"><i class="bi bi-person-circle"></i> Profile</a></li>
            <li><a class="dropdown-item" href="<?= site_url('owner/settings') ?>"><i class="bi bi-gear-fill"></i> Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= site_url('owner/logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </div>
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
