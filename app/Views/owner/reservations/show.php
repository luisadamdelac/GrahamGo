<?= view('layouts/owner_header', ['title' => 'Reservation #' . $reservation['reservation_id']]) ?>

<?php
$statusColors = [
    'Pending' => 'warning', 'Confirmed' => 'info', 'Ready' => 'primary',
    'Claimed' => 'success', 'Cancelled' => 'secondary',
];
?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('owner/reservations') ?>">Reservations</a> <i class="bi bi-chevron-right small"></i> #<?= $reservation['reservation_id'] ?></nav>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-body p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="mb-1">Reservation #<?= $reservation['reservation_id'] ?></h5>
            <span class="badge bg-<?= $statusColors[$reservation['status']] ?? 'secondary' ?>"><?= esc($reservation['status']) ?></span>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-4">
          <?= avatar_chip($reservation['customer_name'], $reservation['customer_avatar'] ?? null, 40) ?>
          <div>
            <div class="fw-semibold small"><?= esc($reservation['customer_name']) ?></div>
            <div class="text-muted small"><?= esc($reservation['email']) ?></div>
          </div>
        </div>

        <div class="row mb-4 g-3">
          <div class="col-6"><p class="mb-1 small text-muted">Customer Type</p><p class="mb-0 small"><?= esc($reservation['customer_type']) ?></p></div>
          <div class="col-6"><p class="mb-1 small text-muted">Reservation Date</p><p class="mb-0 small"><?= date('M d, Y g:i A', strtotime($reservation['reservation_date'])) ?></p></div>
          <div class="col-6"><p class="mb-1 small text-muted">Claim Date</p><p class="mb-0 small"><?= date('M d, Y', strtotime($reservation['claim_date'])) ?></p></div>
          <div class="col-6"><p class="mb-1 small text-muted">Payment Status</p><p class="mb-0 small"><?= esc($reservation['payment_status']) ?></p></div>
          <div class="col-6"><p class="mb-1 small text-muted">Total Amount</p><p class="mb-0 fw-bold">₱<?= number_format($reservation['total_amount'], 2) ?></p></div>
        </div>

        <h6><i class="bi bi-basket-fill"></i> Items</h6>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($details as $d): ?>
                <tr>
                  <td><div class="d-flex align-items-center gap-2"><?= product_chip($d['image'] ?? null, 30) ?> <?= esc($d['product_name']) ?></div></td>
                  <td><?= $d['quantity'] ?></td>
                  <td>₱<?= number_format($d['price'], 2) ?></td>
                  <td>₱<?= number_format($d['subtotal'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if (! empty($payments)): ?>
          <h6 class="mt-4"><i class="bi bi-credit-card-fill"></i> Payment History</h6>
          <div class="table-responsive">
            <table class="table mb-0">
              <thead class="table-light"><tr><th>Date</th><th>Method</th><th>Amount</th></tr></thead>
              <tbody>
                <?php foreach ($payments as $p): ?>
                  <tr>
                    <td><?= date('M d, Y g:i A', strtotime($p['payment_date'])) ?></td>
                    <td><?= esc($p['payment_method']) ?></td>
                    <td>₱<?= number_format($p['amount_paid'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card">
      <div class="card-body p-3 p-md-4">
        <h6 class="mb-3"><i class="bi bi-lightning-charge-fill" style="color:var(--gg-primary-dark);"></i> Actions</h6>

        <?php if ($reservation['status'] === 'Pending'): ?>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/confirm') ?>
            <button type="submit" class="btn btn-gg-primary w-100 mb-2"><i class="bi bi-check2-circle"></i> Confirm Reservation</button>
          <?= form_close() ?>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/cancel') ?>
            <button type="submit" class="btn btn-outline-danger w-100" data-confirm="This reservation will be cancelled." data-confirm-title="Cancel this reservation?"><i class="bi bi-x-circle"></i> Cancel</button>
          <?= form_close() ?>
        <?php endif; ?>

        <?php if ($reservation['status'] === 'Confirmed'): ?>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/ready') ?>
            <button type="submit" class="btn btn-gg-primary w-100 mb-2"><i class="bi bi-bag-check-fill"></i> Mark as Ready</button>
          <?= form_close() ?>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/cancel') ?>
            <button type="submit" class="btn btn-outline-danger w-100" data-confirm="Reserved stock will be returned to inventory." data-confirm-title="Cancel this reservation?"><i class="bi bi-x-circle"></i> Cancel</button>
          <?= form_close() ?>
        <?php endif; ?>

        <?php if ($reservation['status'] === 'Ready'): ?>
          <p class="small text-muted"><i class="bi bi-info-circle"></i> Record the payment once the customer claims the order.</p>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/claim') ?>
            <div class="mb-2">
              <label class="form-label small">Payment Method</label>
              <select name="payment_method" class="form-select">
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small">Amount Paid (₱)</label>
              <input type="number" step="0.01" min="0" name="amount_paid" class="form-control" value="<?= $reservation['total_amount'] ?>" required>
            </div>
            <button type="submit" class="btn btn-gg-primary w-100 mb-2"><i class="bi bi-cash-coin"></i> Record Payment &amp; Mark Claimed</button>
          <?= form_close() ?>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/cancel') ?>
            <button type="submit" class="btn btn-outline-danger w-100" data-confirm="Reserved stock will be returned to inventory." data-confirm-title="Cancel this reservation?"><i class="bi bi-x-circle"></i> Cancel</button>
          <?= form_close() ?>
        <?php endif; ?>

        <?php if (in_array($reservation['status'], ['Claimed', 'Cancelled'], true)): ?>
          <p class="text-muted small mb-0"><i class="bi bi-check-circle"></i> No further actions available for this reservation.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/owner_footer') ?>
