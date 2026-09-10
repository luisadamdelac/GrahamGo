<?php
/**
 * The notification bell + dropdown, factored out so it can be included
 * in both the mobile topbar and the desktop topbar without duplicate
 * element IDs (this partial can legally appear more than once on the
 * same page — every element that owner_footer.php's live poll updates
 * uses a `data-role` attribute instead of an id, and the poll script
 * updates every match via querySelectorAll, not getElementById).
 *
 * Expects $ggTotalAlerts, $ggOverdueAlerts, $ggReservationAlerts,
 * $ggLowStockAlerts, $ggNewSignups, $ggUnreadOverdue,
 * $ggUnreadReservations, $ggUnreadStock, $ggUnreadSignups to already be
 * set (owner_header.php computes these before including this). The
 * "Unread*" values gate whether an item shows at all (see
 * DashboardController::markNotificationsRead) — the counts themselves
 * always display the true live number so the text stays accurate even
 * right after it's been dismissed once and something new adds to it.
 *
 * Each item's whole sentence (not just the number) is rebuilt with
 * correct singular/plural — see the matching phrasing in
 * owner_footer.php's poll(), which has to reproduce the same wording
 * client-side for live updates between page loads.
 */
$ggUnreadOverdue      ??= $ggOverdueAlerts;
$ggUnreadReservations ??= $ggReservationAlerts;
$ggUnreadStock        ??= $ggLowStockAlerts;
$ggUnreadSignups      ??= $ggNewSignups;

$ggOverdueText     = $ggOverdueAlerts === 1 ? '1 reservation is overdue' : $ggOverdueAlerts . ' reservations are overdue';
$ggReservationText = $ggReservationAlerts === 1 ? '1 reservation needs your attention' : $ggReservationAlerts . ' reservations need your attention';
$ggStockText       = $ggLowStockAlerts === 1 ? '1 product is running low on stock' : $ggLowStockAlerts . ' products are running low on stock';
$ggSignupText      = $ggNewSignups === 1 ? '1 new customer signed up' : $ggNewSignups . ' new customers signed up';
?>
<div class="dropdown">
  <button class="topbar-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
    <i class="bi bi-bell-fill"></i>
    <span data-role="notif-bell-badge" class="topbar-badge" style="<?= $ggTotalAlerts > 0 ? '' : 'display:none;' ?>"><?= $ggTotalAlerts ?></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-end" style="min-width:290px;">
    <li class="dropdown-header d-flex align-items-center justify-content-between">
      <span>Notifications</span>
      <button type="button" class="btn btn-link btn-sm p-0 notif-mark-all-btn" data-role="notif-mark-all-btn" style="<?= $ggTotalAlerts > 0 ? '' : 'display:none;' ?>">Mark all as read</button>
    </li>
    <li data-role="notif-overdue-item" class="notif-item d-flex align-items-stretch" style="<?= $ggUnreadOverdue > 0 ? '' : 'display:none;' ?>">
      <a class="dropdown-item flex-grow-1" href="<?= site_url('owner/reservations') ?>">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-danger-bg);color:var(--gg-danger);"><i class="bi bi-alarm-fill"></i></div>
        <div>
          <div class="fw-semibold" data-role="notif-overdue-text"><?= esc($ggOverdueText) ?></div>
          <div class="text-muted" style="font-size:.78rem;">Claim date has already passed — check on these first</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="overdue" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
    <li data-role="notif-reservation-item" class="notif-item d-flex align-items-stretch" style="<?= $ggUnreadReservations > 0 ? '' : 'display:none;' ?>">
      <a class="dropdown-item flex-grow-1" href="<?= site_url('owner/reservations') ?>">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-warning-bg);color:var(--gg-warning);"><i class="bi bi-journal-check"></i></div>
        <div>
          <div class="fw-semibold" data-role="notif-reservation-text"><?= esc($ggReservationText) ?></div>
          <div class="text-muted" style="font-size:.78rem;">Waiting to be confirmed, prepared, or claimed</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="reservations" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
    <li data-role="notif-stock-item" class="notif-item d-flex align-items-stretch" style="<?= $ggUnreadStock > 0 ? '' : 'display:none;' ?>">
      <a class="dropdown-item flex-grow-1" href="<?= site_url('owner/products') ?>">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-danger-bg);color:var(--gg-danger);"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>
          <div class="fw-semibold" data-role="notif-stock-text"><?= esc($ggStockText) ?></div>
          <div class="text-muted" style="font-size:.78rem;">Restock soon before it runs out completely</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="stock" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
    <li data-role="notif-signup-item" class="notif-item d-flex align-items-stretch" style="<?= $ggUnreadSignups > 0 ? '' : 'display:none;' ?>">
      <a class="dropdown-item flex-grow-1" href="<?= site_url('owner/customers') ?>">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-success-bg);color:var(--gg-success);"><i class="bi bi-person-plus-fill"></i></div>
        <div>
          <div class="fw-semibold" data-role="notif-signup-text"><?= esc($ggSignupText) ?></div>
          <div class="text-muted" style="font-size:.78rem;">Joined GrahamGo in the last 48 hours</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="signup" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
  </ul>
</div>
