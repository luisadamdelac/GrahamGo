<?= view('layouts/owner_header', ['title' => 'Inventory Report']) ?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('owner/reports') ?>">Reports</a> <i class="bi bi-chevron-right small"></i> Inventory Report</nav>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h4 class="mb-0"><i class="bi bi-clipboard-data-fill" style="color:var(--gg-primary-dark);"></i> Inventory Report</h4>
  <div class="d-flex gap-2">
    <a href="<?= site_url('owner/reports/inventory/pdf') ?>" target="_blank" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-pdf"></i> Preview PDF</a>
    <a href="<?= site_url('owner/reports/inventory/excel') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
  </div>
</div>

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

<?= view('layouts/owner_footer') ?>
