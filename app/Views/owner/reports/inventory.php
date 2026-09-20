<?= view('layouts/owner_header', ['title' => 'Inventory Report']) ?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('owner/reports') ?>">Reports</a> <i class="bi bi-chevron-right small"></i> Inventory Report</nav>
<h4 class="mb-3"><i class="bi bi-clipboard-data-fill" style="color:var(--gg-primary-dark);"></i> Inventory Report</h4>

<form method="get" class="row g-2 mb-2" id="ggInvFilterForm">
  <input type="hidden" name="filtered" value="1">
  <div class="col-12 col-sm-6 col-md-auto gg-date-col">
    <label class="form-label small mb-1 d-md-none">From</label>
    <input type="text" name="from" class="form-control gg-date-picker" data-role="gg-auto-filter" value="<?= esc($from) ?>">
  </div>
  <div class="col-12 col-sm-6 col-md-auto gg-date-col">
    <label class="form-label small mb-1 d-md-none">To</label>
    <input type="text" name="to" class="form-control gg-date-picker" data-role="gg-auto-filter" value="<?= esc($to) ?>">
  </div>
  <div class="col-12 col-md-auto d-flex align-items-center align-self-md-end">
    <div class="form-check">
      <input type="checkbox" name="daily_breakdown" value="1" id="ggInvDailyBreakdown" class="form-check-input" data-role="gg-auto-filter" <?= $byDay ? 'checked' : '' ?>>
      <label class="form-check-label small" for="ggInvDailyBreakdown">Show daily breakdown</label>
    </div>
  </div>
  <div class="col-12 col-md-auto d-flex gap-2 align-self-md-end">
    <button type="submit" class="btn btn-dark flex-fill">Filter</button>
    <a href="<?= site_url('owner/reports/inventory') ?>" class="btn btn-outline-secondary flex-fill">Reset</a>
  </div>
</form>
<script>
document.querySelectorAll('#ggInvFilterForm [data-role="gg-auto-filter"]').forEach(function (el) {
  el.addEventListener('change', function () { document.getElementById('ggInvFilterForm').submit(); });
});
</script>

<?php $ggInvLinkParams = 'filtered=1&from=' . esc($from, 'url') . '&to=' . esc($to, 'url') . '&daily_breakdown=' . ($byDay ? '1' : '0'); ?>
<div class="d-flex gap-2 mb-3">
  <a href="<?= site_url('owner/reports/inventory/pdf') ?>?<?= $ggInvLinkParams ?>" target="_blank" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-pdf"></i> Preview PDF</a>
  <a href="<?= site_url('owner/reports/inventory/excel') ?>?<?= $ggInvLinkParams ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
</div>

<?php if ($byDay): ?>
  <?php foreach ($dayGroups as $day => $group): ?>
    <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
      <h6 class="mb-0"><i class="bi bi-calendar-event" style="color:var(--gg-primary-dark);"></i> <?= esc(date('l, F j, Y', strtotime($day))) ?></h6>
      <span class="small text-muted"><?= $group['count'] ?> transaction<?= $group['count'] === 1 ? '' : 's' ?> &middot; Stock Change: <?= sprintf('%+d', (int) round($group['total'])) ?></span>
    </div>

    <!-- Mobile: card list -->
    <div class="d-lg-none d-flex flex-column gap-2 mb-2">
      <?php foreach ($group['rows'] as $t): ?>
        <div class="card"><div class="card-body p-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="fw-semibold"><?= esc($t['product_name']) ?></span>
            <span class="badge bg-secondary"><?= esc($t['transaction_type']) ?></span>
          </div>
          <div class="small text-muted">Qty: <?= (int) $t['quantity'] ?> &middot; <?= date('g:i A', strtotime($t['transaction_date'])) ?></div>
          <?php if (! empty($t['notes'])): ?><div class="small text-muted"><?= esc($t['notes']) ?></div><?php endif; ?>
        </div></div>
      <?php endforeach; ?>
    </div>

    <!-- Desktop: table -->
    <div class="table-responsive d-none d-lg-block mb-2">
      <table class="table align-middle mb-0 dg-table">
        <thead class="table-light">
          <tr><th>Time</th><th>Product</th><th>Type</th><th>Qty</th><th>Notes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($group['rows'] as $t): ?>
            <tr>
              <td><?= date('g:i A', strtotime($t['transaction_date'])) ?></td>
              <td><?= esc($t['product_name']) ?></td>
              <td><?= esc($t['transaction_type']) ?></td>
              <td><?= (int) $t['quantity'] ?></td>
              <td><?= esc($t['notes'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
  <?php if (empty($dayGroups)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No stock activity for this period.</div></div>
  <?php endif; ?>

<?php else: ?>
  <!-- Mobile: card list -->
  <div class="d-lg-none d-flex flex-column gap-2">
    <?php foreach ($summary as $row): ?>
      <div class="card"><div class="card-body p-3">
        <div class="d-flex justify-content-between mb-1">
          <span class="fw-semibold"><?= esc($row['product']['product_name']) ?></span>
          <?= $row['available'] <= $row['product']['reorder_level'] ? '<span class="badge bg-danger">Low Stock</span>' : '<span class="badge bg-success">OK</span>' ?>
        </div>
        <div class="small text-muted">Reserved: <?= $row['reserved'] ?> &middot; Sold: <?= $row['sold'] ?></div>
        <div class="small">Available: <span class="<?= $row['available'] <= $row['product']['reorder_level'] ? 'text-danger fw-bold' : 'fw-bold' ?>"><?= $row['available'] ?></span></div>
      </div></div>
    <?php endforeach; ?>
    <?php if (empty($summary)): ?>
      <div class="card"><div class="card-body text-center text-muted py-4">No products yet.</div></div>
    <?php endif; ?>
  </div>

  <!-- Desktop: table -->
  <div class="table-responsive d-none d-lg-block">
    <table class="table align-middle mb-0 dg-table">
      <thead class="table-light">
        <tr><th>Product</th><th>Reserved (to date)</th><th>Sold (to date)</th><th>Currently Available</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($summary as $row): ?>
          <tr>
            <td><?= esc($row['product']['product_name']) ?></td>
            <td><?= $row['reserved'] ?></td>
            <td><?= $row['sold'] ?></td>
            <td class="<?= $row['available'] <= $row['product']['reorder_level'] ? 'text-danger fw-bold' : '' ?>"><?= $row['available'] ?></td>
            <td><?= $row['available'] <= $row['product']['reorder_level'] ? '<span class="badge bg-danger">Low Stock</span>' : '<span class="badge bg-success">OK</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($summary)): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No products yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= view('layouts/owner_footer') ?>
