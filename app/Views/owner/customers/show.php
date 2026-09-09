<?= view('layouts/owner_header', ['title' => $customer['name']]) ?>

<nav class="small mb-3 text-muted enter"><a href="<?= site_url('owner/customers') ?>">Customers</a> <i class="bi bi-chevron-right small"></i> <?= esc($customer['name']) ?></nav>

<div class="card mb-3 mb-md-4 enter">
  <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center gap-3">
    <?= avatar_chip($customer['name'], $customer['avatar'], 64) ?>
    <div class="flex-grow-1" style="min-width:0;">
      <h5 class="mb-1"><?= esc($customer['name']) ?></h5>
      <p class="text-muted small mb-2"><i class="bi bi-envelope"></i> <?= esc($customer['email']) ?> &middot; <i class="bi bi-telephone"></i> <?= esc($customer['contact_number'] ?: '—') ?></p>
      <span class="badge bg-secondary"><?= esc($customer['customer_type'] === 'Other' ? $customer['customer_type_other'] . ' (Other)' : $customer['customer_type']) ?></span>
      <?php if (! empty($customer['location'])): ?>
        <span class="text-muted small ms-2"><i class="bi bi-geo-alt"></i> <?= esc($customer['location']) ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-2 g-md-3 mb-3 mb-md-4">
  <div class="col-4 reveal" style="--reveal-delay: 0s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-info);"><i class="bi bi-journal-text"></i></div>
      <div>
        <div class="stat-value"><?= $stats['total_reservations'] ?></div>
        <div class="stat-label">Reservations</div>
      </div>
    </div></div>
  </div>
  <div class="col-4 reveal" style="--reveal-delay: .05s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-success);"><i class="bi bi-check-circle-fill"></i></div>
      <div>
        <div class="stat-value"><?= $stats['claimed'] ?></div>
        <div class="stat-label">Claimed</div>
      </div>
    </div></div>
  </div>
  <div class="col-4 reveal" style="--reveal-delay: .1s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark));"><i class="bi bi-cash-coin"></i></div>
      <div>
        <div class="stat-value">₱<?= number_format($stats['total_spent'], 0) ?></div>
        <div class="stat-label">Total Spent</div>
      </div>
    </div></div>
  </div>
</div>

<h6 class="mb-3">Reservation History</h6>
<?php
$statusColors = [
    'Pending' => 'warning', 'Confirmed' => 'info', 'Ready' => 'primary',
    'Claimed' => 'success', 'Cancelled' => 'secondary',
];
?>
<div class="table-responsive shadow-sm">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light"><tr><th>#</th><th>Claim Date</th><th>Total</th><th>Payment</th><th>Status</th><th class="no-sort"></th></tr></thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
        <tr>
          <td>#<?= $r['reservation_id'] ?></td>
          <td><?= date('M d, Y', strtotime($r['claim_date'])) ?></td>
          <td>₱<?= number_format($r['total_amount'], 2) ?></td>
          <td><?= esc($r['payment_status']) ?></td>
          <td><span class="badge bg-<?= $statusColors[$r['status']] ?? 'secondary' ?>"><?= esc($r['status']) ?></span></td>
          <td><a href="<?= site_url('owner/reservations/' . $r['reservation_id']) ?>" class="btn btn-sm btn-outline-dark">View</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($reservations)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No reservations yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= view('layouts/owner_footer') ?>
