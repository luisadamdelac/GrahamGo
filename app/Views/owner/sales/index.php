<?= view('layouts/owner_header', ['title' => 'Sales']) ?>

<div class="d-flex justify-content-between align-items-center mb-3 enter">
  <h4 class="mb-0">Sales</h4>
  <a href="<?= site_url('owner/walk-in-sale') ?>" class="btn btn-gg-primary"><i class="bi bi-cash-coin"></i> Walk-in Sale</a>
</div>

<form method="get" class="row g-2 mb-3">
  <div class="col-12 col-sm-6 col-md-auto">
    <label class="form-label small mb-1 d-md-none">From</label>
    <input type="text" name="from" class="form-control gg-date-picker" value="<?= esc($from) ?>" placeholder="From">
  </div>
  <div class="col-12 col-sm-6 col-md-auto">
    <label class="form-label small mb-1 d-md-none">To</label>
    <input type="text" name="to" class="form-control gg-date-picker" value="<?= esc($to) ?>" placeholder="To">
  </div>
  <div class="col-12 col-md-auto d-flex gap-2 align-self-md-end">
    <button type="submit" class="btn btn-dark flex-fill">Filter</button>
    <a href="<?= site_url('owner/sales') ?>" class="btn btn-outline-secondary flex-fill">Reset</a>
  </div>
</form>

<div class="mb-3"><span class="fw-bold fs-5">Total: ₱<?= number_format($total, 2) ?></span></div>

<!-- Mobile: card list -->
<div class="d-lg-none d-flex flex-column gap-2">
  <?php foreach ($sales as $s): ?>
    <div class="card"><div class="card-body p-3">
      <div class="d-flex justify-content-between mb-1">
        <span class="fw-semibold d-flex align-items-center gap-2"><?= avatar_chip($s['customer_name'], $s['customer_avatar'], 26) ?> #<?= $s['reservation_id'] ?> &middot; <?= esc($s['customer_name']) ?></span>
        <span class="fw-bold">₱<?= number_format($s['total_amount'], 2) ?></span>
      </div>
      <div class="small text-muted"><?= esc($s['product_names']) ?> (x<?= $s['total_quantity'] ?>)</div>
      <div class="small text-muted"><?= date('M d, Y g:i A', strtotime($s['sale_date'])) ?> &middot; <?= esc($s['payment_method']) ?></div>
    </div></div>
  <?php endforeach; ?>
  <?php if (empty($sales)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No sales recorded yet.</div></div>
  <?php endif; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-lg-block">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light"><tr><th>Sale Date</th><th>Reservation</th><th>Customer</th><th>Product(s)</th><th>Qty</th><th>Payment Method</th><th>Amount</th></tr></thead>
    <tbody>
      <?php foreach ($sales as $s): ?>
        <tr>
          <td><?= date('M d, Y g:i A', strtotime($s['sale_date'])) ?></td>
          <td>#<?= $s['reservation_id'] ?></td>
          <td><div class="d-flex align-items-center gap-2"><?= avatar_chip($s['customer_name'], $s['customer_avatar'], 30) ?> <?= esc($s['customer_name']) ?></div></td>
          <td><?= esc($s['product_names']) ?></td>
          <td><?= $s['total_quantity'] ?></td>
          <td><?= esc($s['payment_method']) ?></td>
          <td>₱<?= number_format($s['total_amount'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($sales)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No sales recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= view('layouts/owner_footer') ?>
