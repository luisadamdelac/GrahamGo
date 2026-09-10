<?= view('layouts/owner_header', ['title' => 'Reservation Report']) ?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('owner/reports') ?>">Reports</a> <i class="bi bi-chevron-right small"></i> Reservation Report</nav>
<h4 class="mb-3"><i class="bi bi-journal-text" style="color:var(--gg-primary-dark);"></i> Reservation Report</h4>

<form method="get" class="row g-2 mb-3">
  <div class="col-12 col-sm-6 col-md-auto">
    <label class="form-label small mb-1 d-md-none">From</label>
    <input type="text" name="from" class="form-control gg-date-picker" value="<?= esc($from) ?>">
  </div>
  <div class="col-12 col-sm-6 col-md-auto">
    <label class="form-label small mb-1 d-md-none">To</label>
    <input type="text" name="to" class="form-control gg-date-picker" value="<?= esc($to) ?>">
  </div>
  <div class="col-12 col-md-auto d-flex gap-2 align-self-md-end">
    <button type="submit" class="btn btn-dark flex-fill">Filter</button>
    <a href="<?= site_url('owner/reports/reservations') ?>" class="btn btn-outline-secondary flex-fill">Reset</a>
    <button type="button" class="btn btn-outline-dark d-none d-md-inline-block" onclick="window.print()"><i class="bi bi-printer"></i></button>
  </div>
</form>

<!-- Mobile: card list -->
<div class="d-lg-none d-flex flex-column gap-2">
  <?php foreach ($reservations as $r): ?>
    <div class="card"><div class="card-body p-3">
      <div class="d-flex justify-content-between mb-1">
        <span class="fw-semibold">#<?= $r['reservation_id'] ?> &middot; <?= esc($r['customer_name']) ?></span>
        <span class="badge bg-secondary"><?= esc($r['status']) ?></span>
      </div>
      <div class="small text-muted"><?= esc($r['product_name']) ?> (x<?= $r['quantity'] ?>) &middot; <?= esc($r['customer_type']) ?></div>
      <div class="d-flex justify-content-between mt-1">
        <span class="small text-muted"><?= date('M d, Y', strtotime($r['claim_date'])) ?></span>
        <span class="fw-bold">₱<?= number_format($r['total_amount'], 2) ?></span>
      </div>
    </div></div>
  <?php endforeach; ?>
  <?php if (empty($reservations)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No records found.</div></div>
  <?php endif; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-lg-block">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light">
      <tr><th>#</th><th>Customer</th><th>Type</th><th>Product</th><th>Qty</th><th>Claim Date</th><th>Total</th><th>Payment</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
        <tr>
          <td>#<?= $r['reservation_id'] ?></td>
          <td><?= esc($r['customer_name']) ?></td>
          <td><?= esc($r['customer_type']) ?></td>
          <td><?= esc($r['product_name']) ?></td>
          <td><?= $r['quantity'] ?></td>
          <td><?= date('M d, Y', strtotime($r['claim_date'])) ?></td>
          <td>₱<?= number_format($r['total_amount'], 2) ?></td>
          <td><?= esc($r['payment_status']) ?></td>
          <td><?= esc($r['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($reservations)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">No records found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= view('layouts/owner_footer') ?>
