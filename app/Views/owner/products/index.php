<?= view('layouts/owner_header', ['title' => 'Inventory']) ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0 enter">Inventory</h4>
  <div class="d-flex gap-2">
    <a href="<?= site_url('owner/inventory/batches') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-layers"></i> <span class="d-none d-sm-inline">All Batches</span></a>
    <a href="<?= site_url('owner/products/new') ?>" class="btn btn-gg-primary btn-sm"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Add Product</span></a>
  </div>
</div>

<!-- Mobile: card list -->
<div class="d-lg-none d-flex flex-column gap-2 mb-4">
  <?php foreach ($products as $p): ?>
    <div class="card"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-1">
        <a href="<?= site_url('owner/inventory/' . $p['product_id'] . '/batches') ?>" class="d-flex align-items-center gap-2 text-reset text-decoration-none">
          <?= product_chip($p['image'], 36) ?>
          <span class="fw-semibold"><?= esc($p['product_name']) ?> <i class="bi bi-layers small text-muted"></i></span>
        </a>
        <span class="badge bg-<?= $p['status'] === 'Active' ? 'success' : 'secondary' ?>"><?= esc($p['status']) ?></span>
      </div>
      <div class="small text-muted mb-2">₱<?= number_format($p['price'], 2) ?> &middot; Stock: <span class="<?= $p['stock'] <= $p['reorder_level'] ? 'text-danger fw-bold' : '' ?>"><?= $p['stock'] ?></span> &middot; Alert at <?= $p['reorder_level'] ?></div>
      <?php $cannotDeactivate = $p['status'] === 'Active' && $p['stock'] > 0; ?>
      <div class="d-flex gap-2 mb-2">
        <a href="<?= site_url('owner/products/' . $p['product_id'] . '/edit') ?>" class="btn btn-sm btn-outline-dark flex-fill">Edit</a>
        <?= form_open('owner/products/' . $p['product_id'] . '/toggle', ['class' => 'flex-fill']) ?>
          <button type="submit" class="btn btn-sm btn-outline-secondary w-100 <?= $cannotDeactivate ? 'btn-blocked' : '' ?>" <?= $cannotDeactivate ? 'data-blocked="1" title="Restock out to 0 before deactivating"' : '' ?>><?= $p['status'] === 'Active' ? 'Deactivate' : 'Activate' ?></button>
        <?= form_close() ?>
      </div>
      <button type="button" class="btn btn-sm btn-gg-primary w-100" data-restock-id="<?= $p['product_id'] ?>" data-restock-name="<?= esc($p['product_name']) ?>"><i class="bi bi-box-arrow-in-down"></i> Restock</button>
    </div></div>
  <?php endforeach; ?>
  <?php if (empty($products)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No products yet.</div></div>
  <?php endif; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-lg-block mb-4">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light"><tr><th>Product</th><th>Price</th><th>Stock</th><th>Low Stock Alert</th><th>Status</th><th class="no-sort">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td>
            <a href="<?= site_url('owner/inventory/' . $p['product_id'] . '/batches') ?>" class="d-flex align-items-center gap-2 text-reset text-decoration-none">
              <?= product_chip($p['image'], 40) ?>
              <?= esc($p['product_name']) ?> <i class="bi bi-layers small text-muted"></i>
            </a>
          </td>
          <td>₱<?= number_format($p['price'], 2) ?></td>
          <td class="<?= $p['stock'] <= $p['reorder_level'] ? 'text-danger fw-bold' : '' ?>"><?= $p['stock'] ?></td>
          <td><?= $p['reorder_level'] ?></td>
          <td><span class="badge bg-<?= $p['status'] === 'Active' ? 'success' : 'secondary' ?>"><?= esc($p['status']) ?></span></td>
          <td class="text-nowrap">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-gg-primary" data-restock-id="<?= $p['product_id'] ?>" data-restock-name="<?= esc($p['product_name']) ?>"><i class="bi bi-box-arrow-in-down"></i> Restock</button>
              <a href="<?= site_url('owner/products/' . $p['product_id'] . '/edit') ?>" class="btn btn-sm btn-outline-dark">Edit</a>
              <?php $cannotDeactivate = $p['status'] === 'Active' && $p['stock'] > 0; ?>
              <?= form_open('owner/products/' . $p['product_id'] . '/toggle') ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap <?= $cannotDeactivate ? 'btn-blocked' : '' ?>" <?= $cannotDeactivate ? 'data-blocked="1" title="Restock out to 0 before deactivating"' : '' ?>><?= $p['status'] === 'Active' ? 'Deactivate' : 'Activate' ?></button>
              <?= form_close() ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($products)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No products yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<h6 class="mb-3">Recent Inventory Transactions</h6>

<!-- Mobile: card list -->
<div class="d-lg-none d-flex flex-column gap-2">
  <?php foreach ($transactions as $t): ?>
    <div class="card"><div class="card-body p-3">
      <div class="d-flex justify-content-between">
        <span class="fw-semibold"><?= esc($t['product_name']) ?></span>
        <span class="<?= $t['quantity'] < 0 ? 'text-danger' : 'text-success' ?> fw-bold"><?= $t['quantity'] > 0 ? '+' : '' ?><?= $t['quantity'] ?></span>
      </div>
      <div class="small text-muted"><?= esc($t['transaction_type']) ?> &middot; <?= date('M d, Y g:i A', strtotime($t['transaction_date'])) ?></div>
      <?php if ($t['notes']): ?><div class="small text-muted"><?= esc($t['notes']) ?></div><?php endif; ?>
    </div></div>
  <?php endforeach; ?>
  <?php if (empty($transactions)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No transactions yet.</div></div>
  <?php endif; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-lg-block">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light"><tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>Notes</th></tr></thead>
    <tbody>
      <?php foreach ($transactions as $t): ?>
        <tr>
          <td><?= date('M d, Y g:i A', strtotime($t['transaction_date'])) ?></td>
          <td><?= esc($t['product_name']) ?></td>
          <td><?= esc($t['transaction_type']) ?></td>
          <td class="<?= $t['quantity'] < 0 ? 'text-danger' : 'text-success' ?>"><?= $t['quantity'] > 0 ? '+' : '' ?><?= $t['quantity'] ?></td>
          <td class="small text-muted"><?= esc($t['notes']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($transactions)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No transactions yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Shared Restock modal — one form re-bound to whichever product's
     "Restock" button was clicked, so stock-in doesn't need typing a +
     sign or guessing the quantity direction. -->
<div class="modal fade" id="ggRestockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--gg-radius-lg); border:none;">
      <?= form_open('owner/inventory', ['id' => 'ggRestockForm']) ?>
        <div class="modal-body p-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:var(--gg-primary-light);">
              <i class="bi bi-box-arrow-in-down" style="color:var(--gg-primary-dark);"></i>
            </div>
            <div>
              <h6 class="mb-0">Restock</h6>
              <div class="small text-muted" id="ggRestockProductName">&nbsp;</div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity to Add</label>
            <input type="number" name="quantity" id="ggRestockQuantity" class="form-control" min="1" required autofocus>
          </div>
          <div class="mb-0">
            <label class="form-label">Notes <span class="text-muted small">(optional)</span></label>
            <input type="text" name="notes" class="form-control" placeholder="e.g. Supplier delivery">
          </div>
        </div>
        <div class="modal-body pt-0 d-flex gap-2">
          <button type="button" class="btn btn-outline-dark flex-fill" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-gg-primary flex-fill"><i class="bi bi-check2"></i> Add Stock</button>
        </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<script>
// Bootstrap's JS bundle loads in the shared footer, AFTER this block —
// deferring to DOMContentLoaded guarantees it's available by the time
// this runs (see app/Views/owner/inventory/index.php for the bug this
// once caused when checked immediately instead).
document.addEventListener('DOMContentLoaded', function () {
  // Deactivate buttons for a product that still has stock aren't a real
  // <button disabled> — disabled elements don't reliably fire hover in
  // every browser, which meant the "why can't I click this" tooltip
  // never showed. They're styled to look disabled (.btn-blocked) but
  // stay real, hoverable buttons; this just stops the actual submit
  // (the server rejects it anyway — see ProductController::toggleStatus).
  document.querySelectorAll('[data-blocked]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
    });
  });

  if (typeof bootstrap === 'undefined') return;

  var restockModalEl = document.getElementById('ggRestockModal');
  if (! restockModalEl) return;

  var restockModal = new bootstrap.Modal(restockModalEl);
  var restockForm   = document.getElementById('ggRestockForm');
  var restockNameEl = document.getElementById('ggRestockProductName');
  var restockQtyEl  = document.getElementById('ggRestockQuantity');

  document.querySelectorAll('[data-restock-id]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      restockForm.action = '<?= site_url('owner/inventory') ?>/' + btn.getAttribute('data-restock-id') + '/adjust';
      restockNameEl.textContent = btn.getAttribute('data-restock-name');
      restockQtyEl.value = '';
      restockModal.show();
    });
  });
});
</script>

<?= view('layouts/owner_footer') ?>
