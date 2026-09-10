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
 */
$ggUnreadOverdue      ??= $ggOverdueAlerts;
$ggUnreadReservations ??= $ggReservationAlerts;
$ggUnreadStock        ??= $ggLowStockAlerts;
$ggUnreadSignups      ??= $ggNewSignups;
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
          <div class="fw-semibold"><span data-role="notif-overdue-count"><?= $ggOverdueAlerts ?></span> reservation(s) overdue</div>
          <div class="text-muted" style="font-size:.78rem;">Claim date has already passed</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="overdue" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
    <li data-role="notif-reservation-item" class="notif-item d-flex align-items-stretch" style="<?= $ggUnreadReservations > 0 ? '' : 'display:none;' ?>">
      <a class="dropdown-item flex-grow-1" href="<?= site_url('owner/reservations') ?>">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-warning-bg);color:var(--gg-warning);"><i class="bi bi-journal-check"></i></div>
        <div>
          <div class="fw-semibold"><span data-role="notif-reservation-count"><?= $ggReservationAlerts ?></span> reservation(s) need attention</div>
          <div class="text-muted" style="font-size:.78rem;">Pending, confirmed, or ready to claim</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="reservations" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
    <li data-role="notif-stock-item" class="notif-item d-flex align-items-stretch" style="<?= $ggUnreadStock > 0 ? '' : 'display:none;' ?>">
      <a class="dropdown-item flex-grow-1" href="<?= site_url('owner/products') ?>">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-danger-bg);color:var(--gg-danger);"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>
          <div class="fw-semibold"><span data-role="notif-stock-count"><?= $ggLowStockAlerts ?></span> product(s) low on stock</div>
          <div class="text-muted" style="font-size:.78rem;">Restock before running out</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="stock" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
    <li data-role="notif-signup-item" class="notif-item d-flex align-items-stretch" style="<?= $ggUnreadSignups > 0 ? '' : 'display:none;' ?>">
      <a class="dropdown-item flex-grow-1" href="<?= site_url('owner/customers') ?>">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;background:var(--gg-success-bg);color:var(--gg-success);"><i class="bi bi-person-plus-fill"></i></div>
        <div>
          <div class="fw-semibold"><span data-role="notif-signup-count"><?= $ggNewSignups ?></span> new customer sign-up(s)</div>
          <div class="text-muted" style="font-size:.78rem;">In the last 48 hours</div>
        </div>
      </a>
      <button type="button" class="notif-mark-read-btn" data-role="notif-mark-read-btn" data-notif-type="signup" title="Mark as read"><i class="bi bi-check2"></i></button>
    </li>
    <?php $ggAnyUnread = $ggUnreadOverdue + $ggUnreadReservations + $ggUnreadStock + $ggUnreadSignups; ?>
    <li data-role="notif-empty" class="dropdown-item-text text-center text-muted py-3" style="<?= $ggAnyUnread > 0 ? 'display:none;' : '' ?>">
      <i class="bi bi-emoji-smile"></i> You're all caught up!
    </li>
  </ul>
</div>
