<?= view('layouts/header', ['title' => 'My Reservations']) ?>

<h4 class="mb-3 enter">My Reservations</h4>

<?php
$statusColors = [
    'Pending' => 'warning', 'Confirmed' => 'info', 'Ready' => 'primary',
    'Claimed' => 'success', 'Cancelled' => 'secondary',
];
$statusIcons = [
    'Pending' => 'clock-fill', 'Confirmed' => 'check2-circle', 'Ready' => 'bag-check-fill',
    'Claimed' => 'check-circle-fill', 'Cancelled' => 'x-circle-fill',
];
?>

<?php if (empty($reservations)): ?>
  <div class="alert alert-info d-flex align-items-center gap-2"><i class="bi bi-info-circle-fill"></i> You have no reservations yet. <a href="<?= site_url('home') ?>">Browse products</a> to make one.</div>
<?php else: ?>

<!-- Mobile: card list -->
<div class="d-md-none d-flex flex-column gap-2">
  <?php foreach ($reservations as $r): ?>
    <a href="<?= site_url('my-reservations/' . $r['reservation_id']) ?>" class="card text-decoration-none text-reset">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <span class="fw-semibold">#<?= $r['reservation_id'] ?></span>
          <span class="badge bg-<?= $statusColors[$r['status']] ?? 'secondary' ?>"><i class="bi bi-<?= $statusIcons[$r['status']] ?? 'circle-fill' ?>"></i> <?= esc($r['status']) ?></span>
        </div>
        <div class="small text-muted mb-1"><i class="bi bi-calendar-event"></i> Claim: <?= date('M d, Y', strtotime($r['claim_date'])) ?></div>
        <div class="d-flex justify-content-between">
          <span class="fw-bold">₱<?= number_format($r['total_amount'], 2) ?></span>
          <span class="small text-muted"><?= esc($r['payment_status']) ?></span>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-md-block">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light">
      <tr><th>#</th><th>Claim Date</th><th>Total</th><th>Payment</th><th>Status</th><th class="no-sort"></th></tr>
    </thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
        <tr>
          <td class="fw-semibold">#<?= $r['reservation_id'] ?></td>
          <td><?= date('M d, Y', strtotime($r['claim_date'])) ?></td>
          <td>₱<?= number_format($r['total_amount'], 2) ?></td>
          <td><?= esc($r['payment_status']) ?></td>
          <td><span class="badge bg-<?= $statusColors[$r['status']] ?? 'secondary' ?>"><i class="bi bi-<?= $statusIcons[$r['status']] ?? 'circle-fill' ?>"></i> <?= esc($r['status']) ?></span></td>
          <td><a href="<?= site_url('my-reservations/' . $r['reservation_id']) ?>" class="btn btn-sm btn-outline-dark">View</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?= view('layouts/footer') ?>
