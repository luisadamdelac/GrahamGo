<?= view('layouts/header', ['title' => 'Reservation #' . $reservation['reservation_id']]) ?>

<?php
$statusColors = [
    'Pending' => 'warning', 'Confirmed' => 'info', 'Ready' => 'primary',
    'Claimed' => 'success', 'Cancelled' => 'secondary',
];
$statusIcons = [
    'Pending' => 'clock-fill', 'Confirmed' => 'check2-circle', 'Ready' => 'bag-check-fill',
    'Claimed' => 'check-circle-fill', 'Cancelled' => 'x-circle-fill',
];
$steps = ['Pending', 'Confirmed', 'Ready', 'Claimed'];
$stepIcons = ['clock-fill', 'check2-circle', 'bag-check-fill', 'check-circle-fill'];
$currentIndex = array_search($reservation['status'], $steps, true);
?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('my-reservations') ?>">My Reservations</a> <i class="bi bi-chevron-right small"></i> #<?= $reservation['reservation_id'] ?></nav>

<div class="card">
  <div class="card-body p-3 p-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2 mb-3">
      <div>
        <h5 class="mb-1">Reservation #<?= $reservation['reservation_id'] ?></h5>
        <span class="badge bg-<?= $statusColors[$reservation['status']] ?? 'secondary' ?> status-badge"><i class="bi bi-<?= $statusIcons[$reservation['status']] ?? 'circle-fill' ?>"></i> <?= esc($reservation['status']) ?></span>
      </div>
      <?php if ($reservation['status'] === 'Pending'): ?>
        <?= form_open('my-reservations/' . $reservation['reservation_id'] . '/cancel') ?>
          <button type="submit" class="btn btn-outline-danger btn-sm w-100" data-confirm="This will cancel your reservation. This can't be undone." data-confirm-title="Cancel this reservation?"><i class="bi bi-x-circle"></i> Cancel Reservation</button>
        <?= form_close() ?>
      <?php endif; ?>
    </div>

    <?php if ($reservation['status'] !== 'Cancelled'): ?>
      <div class="progress mb-3" style="height:6px;">
        <?php $pct = $currentIndex !== false ? (($currentIndex + 1) / count($steps)) * 100 : 0; ?>
        <div class="progress-bar" style="width: <?= $pct ?>%;"></div>
      </div>
      <div class="d-flex justify-content-between small text-muted mb-4 flex-wrap gap-1">
        <?php foreach ($steps as $i => $s): ?>
          <span class="<?= $currentIndex !== false && $i <= $currentIndex ? 'fw-bold' : '' ?>" style="<?= $currentIndex !== false && $i <= $currentIndex ? 'color:var(--gg-primary-dark);' : '' ?>">
            <i class="bi bi-<?= $stepIcons[$i] ?>"></i> <span class="d-none d-sm-inline"><?= $s ?></span>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="row mb-4 g-3">
      <div class="col-6">
        <p class="mb-1 small text-muted"><i class="bi bi-calendar-plus"></i> Reservation Date</p>
        <p class="mb-0 small"><?= date('M d, Y g:i A', strtotime($reservation['reservation_date'])) ?></p>
      </div>
      <div class="col-6">
        <p class="mb-1 small text-muted"><i class="bi bi-calendar-event"></i> Claim Date</p>
        <p class="mb-0 small"><?= date('M d, Y', strtotime($reservation['claim_date'])) ?></p>
      </div>
      <div class="col-6">
        <p class="mb-1 small text-muted"><i class="bi bi-credit-card"></i> Payment Status</p>
        <p class="mb-0 small"><?= esc($reservation['payment_status']) ?></p>
      </div>
      <div class="col-6">
        <p class="mb-1 small text-muted"><i class="bi bi-cash-stack"></i> Total Amount</p>
        <p class="mb-0 fw-bold">₱<?= number_format($reservation['total_amount'], 2) ?></p>
      </div>
    </div>

    <h6><i class="bi bi-basket-fill"></i> Items</h6>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php foreach ($details as $d): ?>
            <tr>
              <td><?= esc($d['product_name']) ?></td>
              <td><?= $d['quantity'] ?></td>
              <td>₱<?= number_format($d['price'], 2) ?></td>
              <td>₱<?= number_format($d['subtotal'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($reservation['status'] === 'Claimed'): ?>
  <div class="card mt-3">
    <div class="card-body p-3 p-md-4">
      <h6 class="mb-3"><i class="bi bi-star-fill" style="color:var(--gg-primary-dark);"></i> Rate These Products</h6>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($details as $d): ?>
          <div class="p-3" style="background:var(--gg-bg); border-radius:var(--gg-radius-lg);">
            <p class="fw-semibold mb-2"><?= esc($d['product_name']) ?></p>
            <?= form_open('products/' . $d['product_id'] . '/review') ?>
              <div class="gg-star-input mb-2">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" name="rating" id="ggStar<?= $d['product_id'] ?>_<?= $i ?>" value="<?= $i ?>" <?= (int) ($d['myReview']['rating'] ?? 0) === $i ? 'checked' : '' ?> required>
                  <label for="ggStar<?= $d['product_id'] ?>_<?= $i ?>"><i class="bi bi-star-fill"></i></label>
                <?php endfor; ?>
              </div>
              <textarea name="comment" class="form-control mb-2" rows="2" maxlength="1000" placeholder="Share your thoughts about this product (optional)"><?= esc($d['myReview']['comment'] ?? '') ?></textarea>
              <button type="submit" class="btn btn-gg-primary btn-sm"><i class="bi bi-send-fill"></i> <?= $d['myReview'] ? 'Update Review' : 'Submit Review' ?></button>
              <?php if ($d['myReview'] && $d['myReview']['status'] === 'Pending'): ?>
                <span class="small text-muted ms-2">Awaiting approval</span>
              <?php endif; ?>
            <?= form_close() ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<?= view('layouts/footer') ?>
