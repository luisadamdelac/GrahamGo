<?= view('layouts/owner_header', ['title' => 'Reservation Report']) ?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('owner/reports') ?>">Reports</a> <i class="bi bi-chevron-right small"></i> Reservation Report</nav>
<h4 class="mb-3"><i class="bi bi-journal-text" style="color:var(--gg-primary-dark);"></i> Reservation Report</h4>

<form method="get" class="row g-2 mb-2" id="ggResFilterForm">
  <div class="col-12 col-sm-6 col-md-auto">
    <label class="form-label small mb-1 d-md-none">From</label>
    <input type="text" name="from" class="form-control gg-date-picker" value="<?= esc($from) ?>">
  </div>
  <div class="col-12 col-sm-6 col-md-auto">
    <label class="form-label small mb-1 d-md-none">To</label>
    <input type="text" name="to" class="form-control gg-date-picker" value="<?= esc($to) ?>">
  </div>
  <div class="col-12 col-sm-6 col-md-auto">
    <label class="form-label small mb-1 d-md-none">Status</label>
    <select name="status" class="form-select" data-role="gg-auto-filter">
      <option value="All" <?= $status === 'All' ? 'selected' : '' ?>>All Statuses</option>
      <?php foreach (\App\Models\ReservationModel::STATUSES as $s): ?>
        <option value="<?= esc($s, 'attr') ?>" <?= $status === $s ? 'selected' : '' ?>><?= esc($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-auto d-flex align-items-center align-self-md-end">
    <div class="form-check">
      <input type="checkbox" name="daily_breakdown" value="1" id="ggResDailyBreakdown" class="form-check-input" data-role="gg-auto-filter" <?= $byDay ? 'checked' : '' ?>>
      <label class="form-check-label small" for="ggResDailyBreakdown">Show daily breakdown</label>
    </div>
  </div>
  <div class="col-12 col-md-auto d-flex gap-2 align-self-md-end">
    <button type="submit" class="btn btn-dark flex-fill">Filter</button>
    <a href="<?= site_url('owner/reports/reservations') ?>" class="btn btn-outline-secondary flex-fill">Reset</a>
  </div>
</form>
<script>
document.querySelectorAll('#ggResFilterForm [data-role="gg-auto-filter"]').forEach(function (el) {
  el.addEventListener('change', function () { document.getElementById('ggResFilterForm').submit(); });
});
</script>

<!-- Own row, not squeezed alongside Filter/Reset — those two grow
     (flex-fill) to fill whatever width they're given, which was
     pushing these off-screen on narrow phones instead of just
     wrapping to a visible second line. -->
<?php $ggResLinkParams = ($byDay ? '&daily_breakdown=1' : '') . '&status=' . esc($status, 'url'); ?>
<div class="d-flex gap-2 mb-3">
  <a href="<?= site_url('owner/reports/reservations/pdf') ?>?from=<?= esc($from, 'url') ?>&to=<?= esc($to, 'url') ?><?= $ggResLinkParams ?>" target="_blank" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-pdf"></i> Preview PDF</a>
  <a href="<?= site_url('owner/reports/reservations/excel') ?>?from=<?= esc($from, 'url') ?>&to=<?= esc($to, 'url') ?><?= $ggResLinkParams ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
</div>

<?php if ($byDay): ?>
  <?php foreach ($dayGroups as $day => $group): ?>
    <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
      <h6 class="mb-0"><i class="bi bi-calendar-event" style="color:var(--gg-primary-dark);"></i> <?= esc(date('l, F j, Y', strtotime($day))) ?></h6>
      <span class="small text-muted"><?= $group['count'] ?> reservation<?= $group['count'] === 1 ? '' : 's' ?> &middot; ₱<?= number_format($group['total'], 2) ?></span>
    </div>

    <!-- Mobile: card list -->
    <div class="d-lg-none d-flex flex-column gap-2 mb-2">
      <?php foreach ($group['rows'] as $r): ?>
        <div class="card"><div class="card-body p-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="fw-semibold">#<?= $r['reservation_id'] ?> &middot; <?= esc($r['customer_name']) ?></span>
            <span class="badge bg-secondary"><?= esc($r['status']) ?></span>
          </div>
          <div class="small text-muted"><?= esc($r['product_name']) ?> (x<?= $r['quantity'] ?>) &middot; <?= esc($r['customer_type']) ?></div>
          <div class="small text-muted"><i class="bi bi-<?= $r['fulfillment_type'] === 'Delivery' ? 'bicycle' : 'shop' ?>"></i> <?= esc($r['fulfillment_type']) ?></div>
          <div class="d-flex justify-content-between mt-1">
            <span class="small text-muted"><?= date('M d, Y', strtotime($r['claim_date'])) ?></span>
            <span class="fw-bold">₱<?= number_format($r['total_amount'], 2) ?></span>
          </div>
        </div></div>
      <?php endforeach; ?>
    </div>

    <!-- Desktop: table -->
    <div class="table-responsive d-none d-lg-block mb-2">
      <table class="table align-middle mb-0 dg-table">
        <thead class="table-light">
          <tr><th>#</th><th>Customer</th><th>Type</th><th>Product</th><th>Qty</th><th>Claim Date</th><th>Fulfillment</th><th>Total</th><th>Payment</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($group['rows'] as $r): ?>
            <tr>
              <td>#<?= $r['reservation_id'] ?></td>
              <td><?= esc($r['customer_name']) ?></td>
              <td><?= esc($r['customer_type']) ?></td>
              <td><?= esc($r['product_name']) ?></td>
              <td><?= $r['quantity'] ?></td>
              <td><?= date('M d, Y', strtotime($r['claim_date'])) ?></td>
              <td><i class="bi bi-<?= $r['fulfillment_type'] === 'Delivery' ? 'bicycle' : 'shop' ?>"></i> <?= esc($r['fulfillment_type']) ?></td>
              <td>₱<?= number_format($r['total_amount'], 2) ?></td>
              <td><?= esc($r['payment_status']) ?></td>
              <td><?= esc($r['status']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
  <?php if (empty($dayGroups)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No records found.</div></div>
  <?php endif; ?>

<?php else: ?>
  <!-- Mobile: card list -->
  <div class="d-lg-none d-flex flex-column gap-2">
    <?php foreach ($reservations as $r): ?>
      <div class="card"><div class="card-body p-3">
        <div class="d-flex justify-content-between mb-1">
          <span class="fw-semibold">#<?= $r['reservation_id'] ?> &middot; <?= esc($r['customer_name']) ?></span>
          <span class="badge bg-secondary"><?= esc($r['status']) ?></span>
        </div>
        <div class="small text-muted"><?= esc($r['product_name']) ?> (x<?= $r['quantity'] ?>) &middot; <?= esc($r['customer_type']) ?></div>
        <div class="small text-muted"><i class="bi bi-<?= $r['fulfillment_type'] === 'Delivery' ? 'bicycle' : 'shop' ?>"></i> <?= esc($r['fulfillment_type']) ?></div>
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
        <tr><th>#</th><th>Customer</th><th>Type</th><th>Product</th><th>Qty</th><th>Claim Date</th><th>Fulfillment</th><th>Total</th><th>Payment</th><th>Status</th></tr>
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
            <td><i class="bi bi-<?= $r['fulfillment_type'] === 'Delivery' ? 'bicycle' : 'shop' ?>"></i> <?= esc($r['fulfillment_type']) ?></td>
            <td>₱<?= number_format($r['total_amount'], 2) ?></td>
            <td><?= esc($r['payment_status']) ?></td>
            <td><?= esc($r['status']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($reservations)): ?>
          <tr><td colspan="10" class="text-center text-muted py-4">No records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= view('layouts/owner_footer') ?>
