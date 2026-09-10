<?= view('layouts/owner_header', ['title' => 'All Stock Batches']) ?>

<a href="<?= site_url('owner/products') ?>" class="small text-muted text-decoration-none d-inline-block mb-2"><i class="bi bi-arrow-left"></i> Back to Products</a>

<h4 class="mb-3 enter"><i class="bi bi-layers" style="color:var(--gg-primary-dark);"></i> All Stock Batches</h4>
<p class="text-muted small mb-3">Full batch history across every product, newest first — filter by the date a batch was received.</p>

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
    <a href="<?= site_url('owner/inventory/batches') ?>" class="btn btn-outline-secondary flex-fill">Reset</a>
  </div>
</form>

<?php if (empty($batches)): ?>
  <div class="card"><div class="card-body text-center text-muted py-5"><i class="bi bi-inbox"></i> No batches found<?= ($from || $to) ? ' for this date range.' : ' yet.' ?></div></div>
<?php else: ?>

  <!-- Mobile: card list -->
  <div class="d-lg-none d-flex flex-column gap-2">
    <?php foreach ($batches as $b): ?>
      <div class="card">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <span class="fw-semibold"><?= esc($b['product_name']) ?></span>
            <?= $b['remaining_quantity'] > 0 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Depleted</span>' ?>
          </div>
          <div class="small text-muted mb-1"><i class="bi bi-calendar-event"></i> Received <?= date('M d, Y g:i A', strtotime($b['received_at'])) ?></div>
          <div class="small">Remaining: <span class="fw-bold"><?= $b['remaining_quantity'] ?></span> / Received: <?= $b['quantity'] ?></div>
          <?php if ($b['notes']): ?><div class="small text-muted"><?= esc($b['notes']) ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Desktop: table -->
  <div class="table-responsive d-none d-lg-block">
    <table class="table align-middle mb-0 dg-table">
      <thead class="table-light"><tr><th>Product</th><th>Received</th><th>Qty Received</th><th>Remaining</th><th>Status</th><th>Notes</th></tr></thead>
      <tbody>
        <?php foreach ($batches as $b): ?>
          <tr>
            <td><?= esc($b['product_name']) ?></td>
            <td><?= date('M d, Y g:i A', strtotime($b['received_at'])) ?></td>
            <td><?= $b['quantity'] ?></td>
            <td class="fw-bold"><?= $b['remaining_quantity'] ?></td>
            <td><?= $b['remaining_quantity'] > 0 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Depleted</span>' ?></td>
            <td class="small text-muted"><?= esc($b['notes']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= view('layouts/owner_footer') ?>
