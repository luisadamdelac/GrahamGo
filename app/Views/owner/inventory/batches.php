<?= view('layouts/owner_header', ['title' => 'Stock Batches']) ?>

<a href="<?= site_url('owner/products') ?>" class="small text-muted text-decoration-none d-inline-block mb-2"><i class="bi bi-arrow-left"></i> Back to Products</a>

<div class="d-flex justify-content-between align-items-start mb-1">
  <h4 class="mb-0 enter"><?= esc($product['product_name']) ?></h4>
  <span class="fw-bold fs-5" style="color:var(--gg-primary-dark);"><?= $product['stock'] ?> <span class="fs-6 fw-normal text-muted">in stock</span></span>
</div>
<p class="text-muted small mb-3">FIFO queue — oldest batch is consumed first when a reservation is confirmed or stock is manually removed.</p>
<button type="button" class="btn btn-gg-primary mb-4" data-bs-toggle="modal" data-bs-target="#ggRestockModal"><i class="bi bi-box-arrow-in-down"></i> Restock</button>

<?php if (empty($batches)): ?>
  <div class="card"><div class="card-body text-center text-muted py-5"><i class="bi bi-inbox"></i> No stock batches on hand right now.</div></div>
<?php else: ?>

  <!-- Mobile: card list -->
  <div class="d-lg-none d-flex flex-column gap-2">
    <?php foreach ($batches as $i => $b): ?>
      <div class="card">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <span class="fw-semibold"><?= $i === 0 ? '<span class="badge bg-warning me-1">Next to use</span>' : '' ?>Batch #<?= $b['batch_id'] ?></span>
            <span class="fw-bold"><?= $b['remaining_quantity'] ?> / <?= $b['quantity'] ?></span>
          </div>
          <div class="small text-muted mb-1"><i class="bi bi-calendar-event"></i> Received <?= date('M d, Y g:i A', strtotime($b['received_at'])) ?></div>
          <?php if ($b['notes']): ?><div class="small text-muted"><?= esc($b['notes']) ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Desktop: table -->
  <div class="table-responsive d-none d-lg-block">
    <table class="table align-middle mb-0 dg-table">
      <thead class="table-light"><tr><th>Batch</th><th>Received</th><th>Remaining / Received Qty</th><th>Notes</th></tr></thead>
      <tbody>
        <?php foreach ($batches as $i => $b): ?>
          <tr>
            <td class="fw-semibold">
              #<?= $b['batch_id'] ?>
              <?php if ($i === 0): ?><span class="badge bg-warning ms-1">Next to use</span><?php endif; ?>
            </td>
            <td><?= date('M d, Y g:i A', strtotime($b['received_at'])) ?></td>
            <td><?= $b['remaining_quantity'] ?> / <?= $b['quantity'] ?></td>
            <td class="small text-muted"><?= esc($b['notes']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<!-- Restock modal — fixed to this product, so no picker needed. -->
<div class="modal fade" id="ggRestockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--gg-radius-lg); border:none;">
      <?= form_open('owner/inventory/' . $product['product_id'] . '/adjust') ?>
        <div class="modal-body p-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:var(--gg-primary-light);">
              <i class="bi bi-box-arrow-in-down" style="color:var(--gg-primary-dark);"></i>
            </div>
            <div>
              <h6 class="mb-0">Restock</h6>
              <div class="small text-muted"><?= esc($product['product_name']) ?></div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity to Add</label>
            <input type="number" name="quantity" class="form-control" min="1" required autofocus>
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

<?= view('layouts/owner_footer') ?>
