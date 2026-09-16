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

        <?php if ($reservation['status'] === 'Cancelled' && ! empty($reservation['cancel_reason'])): ?>
          <div class="alert alert-secondary d-flex align-items-start gap-2 mb-4">
            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
            <div><strong>Cancellation reason:</strong> <?= esc($reservation['cancel_reason']) ?></div>
          </div>
        <?php endif; ?>

        <?php if (in_array($reservation['status'], ['Confirmed', 'Ready', 'Claimed'], true) && ! empty($reservation['owner_note'])): ?>
          <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
            <i class="bi bi-chat-left-text-fill flex-shrink-0 mt-1"></i>
            <div><strong>Your note to the customer:</strong> <?= esc($reservation['owner_note']) ?></div>
          </div>
        <?php endif; ?>

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
          <div class="col-6"><p class="mb-1 small text-muted">Fulfillment</p><p class="mb-0 small"><i class="bi bi-<?= $reservation['fulfillment_type'] === 'Delivery' ? 'bicycle' : 'shop' ?>"></i> <?= esc($reservation['fulfillment_type']) ?></p></div>
          <div class="col-6"><p class="mb-1 small text-muted">Payment Status</p><p class="mb-0 small"><?= esc($reservation['payment_status']) ?></p></div>
          <div class="col-6"><p class="mb-1 small text-muted">Total Amount</p><p class="mb-0 fw-bold">₱<?= number_format($reservation['total_amount'], 2) ?></p></div>
          <?php if ($reservation['fulfillment_type'] === 'Delivery' && ! empty($reservation['delivery_address'])): ?>
            <div class="col-12"><p class="mb-1 small text-muted">Delivery Address</p><p class="mb-0 small"><?= esc($reservation['delivery_address']) ?></p></div>
          <?php endif; ?>
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
            <div class="mb-2">
              <label class="form-label small">Note to Customer (optional)</label>
              <div class="d-flex flex-wrap gap-1 mb-2" data-role="gg-note-chips" data-target="ggNoteConfirm">
                <button type="button" class="gg-note-chip" data-text="Nasa bahay ako, text/tawag niyo muna bago pumunta.">Nasa Bahay</button>
                <button type="button" class="gg-note-chip" data-text="Nasa school ako ngayon, text/tawag niyo muna bago pumunta.">Nasa School</button>
                <button type="button" class="gg-note-chip" data-text="Tawagan niyo muna ako bago pumunta.">Tawagan Muna</button>
              </div>
              <textarea name="owner_note" id="ggNoteConfirm" class="form-control" rows="2" placeholder="e.g. Pick up sa school ako today, text me before coming..."><?= esc($reservation['owner_note'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-gg-primary w-100 mb-2"><i class="bi bi-check2-circle"></i> Confirm Reservation</button>
          <?= form_close() ?>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/cancel') ?>
            <button type="submit" class="btn btn-outline-danger w-100" data-confirm="This reservation will be cancelled." data-confirm-title="Cancel this reservation?"><i class="bi bi-x-circle"></i> Cancel</button>
          <?= form_close() ?>
        <?php endif; ?>

        <?php if ($reservation['status'] === 'Confirmed'): ?>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/ready') ?>
            <div class="mb-2">
              <label class="form-label small">Note to Customer (optional)</label>
              <div class="d-flex flex-wrap gap-1 mb-2" data-role="gg-note-chips" data-target="ggNoteReady">
                <button type="button" class="gg-note-chip" data-text="Nasa bahay ako, text/tawag niyo muna bago pumunta.">Nasa Bahay</button>
                <button type="button" class="gg-note-chip" data-text="Nasa school ako ngayon, text/tawag niyo muna bago pumunta.">Nasa School</button>
                <button type="button" class="gg-note-chip" data-text="Tawagan niyo muna ako bago pumunta.">Tawagan Muna</button>
              </div>
              <textarea name="owner_note" id="ggNoteReady" class="form-control" rows="2" placeholder="e.g. Pick up sa school ako today, text me before coming..."><?= esc($reservation['owner_note'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-gg-primary w-100 mb-2"><i class="bi bi-bag-check-fill"></i> Mark as Ready</button>
          <?= form_close() ?>
          <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#ggCancelReasonModal"><i class="bi bi-x-circle"></i> Cancel</button>
        <?php endif; ?>

        <?php if ($reservation['status'] === 'Ready'): ?>
          <p class="small text-muted"><i class="bi bi-info-circle"></i> Record the payment once the customer claims the order.</p>
          <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/claim') ?>
            <div class="mb-2">
              <label class="form-label small">Payment Method</label>
              <div class="gg-custom-select" id="ggClaimPaymentCustom">
                <button type="button" class="form-select text-start" id="ggClaimPaymentTrigger">Cash</button>
                <div class="gg-custom-select-list d-none" id="ggClaimPaymentList">
                  <div class="gg-custom-select-option is-active" data-value="Cash">Cash</div>
                  <div class="gg-custom-select-option" data-value="GCash">GCash</div>
                </div>
              </div>
              <select name="payment_method" id="ggClaimPayment" class="gg-visually-hidden">
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small">Amount Paid (₱)</label>
              <input type="number" step="0.01" min="0" name="amount_paid" class="form-control" value="<?= $reservation['total_amount'] ?>" required>
            </div>
            <button type="submit" class="btn btn-gg-primary w-100 mb-2"><i class="bi bi-cash-coin"></i> Record Payment &amp; Mark Claimed</button>
          <?= form_close() ?>
          <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#ggCancelReasonModal"><i class="bi bi-x-circle"></i> Cancel</button>
        <?php endif; ?>

        <?php if (in_array($reservation['status'], ['Claimed', 'Cancelled'], true)): ?>
          <p class="text-muted small mb-0"><i class="bi bi-check-circle"></i> No further actions available for this reservation.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (in_array($reservation['status'], ['Confirmed', 'Ready'], true)): ?>
  <!-- Cancelling here means undoing something the customer was already
       counting on (owner had confirmed it, maybe marked it Ready), so —
       unlike a Pending cancel — this requires a reason on record rather
       than a plain confirm dialog. -->
  <div class="modal fade" id="ggCancelReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:var(--gg-radius-lg); border:none;">
        <?= form_open('owner/reservations/' . $reservation['reservation_id'] . '/cancel') ?>
          <div class="modal-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:var(--gg-danger-bg);">
                <i class="bi bi-x-circle-fill" style="color:var(--gg-danger);"></i>
              </div>
              <div>
                <h6 class="mb-0">Cancel Reservation #<?= $reservation['reservation_id'] ?></h6>
                <div class="small text-muted">Reserved stock will be returned to inventory.</div>
              </div>
            </div>
            <label class="form-label">Reason for cancelling</label>
            <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="e.g. Customer requested by phone, product unavailable, mistaken confirmation..."></textarea>
          </div>
          <div class="modal-body pt-0 d-flex gap-2">
            <button type="button" class="btn btn-outline-dark flex-fill" data-bs-dismiss="modal">Never mind</button>
            <button type="submit" class="btn btn-outline-danger flex-fill"><i class="bi bi-x-circle"></i> Cancel Reservation</button>
          </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var claimPayment = document.getElementById('ggClaimPayment');
  if (claimPayment) {
    ggInitCustomSelect('ggClaimPaymentCustom', claimPayment, 'ggClaimPaymentTrigger', 'ggClaimPaymentList');
  }

  document.querySelectorAll('[data-role="gg-note-chips"]').forEach(function (chipRow) {
    var target = document.getElementById(chipRow.getAttribute('data-target'));
    if (! target) return;
    chipRow.querySelectorAll('.gg-note-chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        target.value = chip.getAttribute('data-text');
        target.focus();
      });
    });
  });
});
</script>

<?= view('layouts/owner_footer') ?>
