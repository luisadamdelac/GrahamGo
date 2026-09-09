<?= view('layouts/owner_header', ['title' => 'Reservations']) ?>

<?php
$statusColors = [
    'Pending' => 'warning', 'Confirmed' => 'info', 'Ready' => 'primary',
    'Claimed' => 'success', 'Cancelled' => 'secondary',
];
$statuses = ['Pending', 'Confirmed', 'Ready', 'Claimed', 'Cancelled'];
?>

<h4 class="mb-3 enter">Reservations</h4>

<div class="mb-3 d-flex flex-nowrap gap-2 overflow-auto pb-1">
  <a href="<?= site_url('owner/reservations') ?>" class="btn btn-sm text-nowrap btn-<?= ! $statusFilter ? 'dark' : 'outline-dark' ?>">All</a>
  <?php foreach ($statuses as $s): ?>
    <a href="<?= site_url('owner/reservations') ?>?status=<?= $s ?>" class="btn btn-sm text-nowrap btn-<?= $statusFilter === $s ? 'dark' : 'outline-dark' ?>"><?= $s ?></a>
  <?php endforeach; ?>
</div>

<!-- Mobile: card list -->
<?= view('partials/mobile_card_search', ['target' => 'mobileReservationsList']) ?>
<div id="mobileReservationsList" class="d-lg-none d-flex flex-column gap-2">
  <?php foreach ($reservations as $r): ?>
    <div class="card">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <span class="fw-semibold">#<?= $r['reservation_id'] ?></span>
          <span class="badge bg-<?= $statusColors[$r['status']] ?? 'secondary' ?>"><?= esc($r['status']) ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 small text-muted mb-1">
          <?= avatar_chip($r['customer_name'], $r['customer_avatar'], 24) ?>
          <?= esc($r['customer_name']) ?> <span class="text-muted">(<?= esc($r['customer_type']) ?>)</span>
        </div>
        <div class="small text-muted mb-2"><i class="bi bi-calendar-event"></i> <?= date('M d, Y', strtotime($r['claim_date'])) ?></div>
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-bold">₱<?= number_format($r['total_amount'], 2) ?></span>
            <span class="small text-muted ms-1"><?= esc($r['payment_status']) ?></span>
          </div>
          <a href="<?= site_url('owner/reservations/' . $r['reservation_id']) ?>" class="btn btn-sm <?= $r['status'] === 'Pending' ? 'btn-gg-primary' : 'btn-outline-dark' ?>">Manage</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($reservations)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No reservations found.</div></div>
  <?php endif; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-lg-block">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Claim Date</th><th>Total</th><th>Payment</th><th>Status</th><th class="no-sort"></th></tr></thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
        <tr>
          <td>#<?= $r['reservation_id'] ?></td>
          <td><div class="d-flex align-items-center gap-2"><?= avatar_chip($r['customer_name'], $r['customer_avatar'], 30) ?> <?= esc($r['customer_name']) ?> <span class="text-muted small">(<?= esc($r['customer_type']) ?>)</span></div></td>
          <td><?= date('M d, Y', strtotime($r['claim_date'])) ?></td>
          <td>₱<?= number_format($r['total_amount'], 2) ?></td>
          <td><?= esc($r['payment_status']) ?></td>
          <td><span class="badge bg-<?= $statusColors[$r['status']] ?? 'secondary' ?>"><?= esc($r['status']) ?></span></td>
          <td><a href="<?= site_url('owner/reservations/' . $r['reservation_id']) ?>" class="btn btn-sm <?= $r['status'] === 'Pending' ? 'btn-gg-primary' : 'btn-outline-dark' ?>">Manage</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($reservations)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No reservations found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= view('layouts/owner_footer') ?>
