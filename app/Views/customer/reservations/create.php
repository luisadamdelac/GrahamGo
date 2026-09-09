<?= view('layouts/header', ['title' => 'Make a Reservation']) ?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('home') ?>">Products</a> <i class="bi bi-chevron-right small"></i> <a href="<?= site_url('products/' . $product['product_id']) ?>"><?= esc($product['product_name']) ?></a> <i class="bi bi-chevron-right small"></i> Reserve</nav>

<div class="row justify-content-center">
  <div class="col-12 col-md-7 col-lg-6">
    <div class="card">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-2 mb-1">
          <i class="bi bi-bag-heart-fill fs-4" style="color:var(--gg-primary-dark);"></i>
          <h5 class="mb-0"><?= esc($product['product_name']) ?></h5>
        </div>
        <p class="text-muted small mb-4">Price: ₱<?= number_format($product['price'], 2) ?> &middot; Available: <?= $product['stock'] ?> pcs</p>

        <?= form_open('reserve/' . $product['product_id']) ?>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-123"></i> Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" max="<?= $product['stock'] ?>" value="<?= esc(old('quantity', 1)) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-calendar-event"></i> Claim Date</label>
            <input type="date" id="claimDateInput" name="claim_date" class="form-control" min="<?= date('Y-m-d') ?>" max="<?= esc($maxClaimDate) ?>" value="<?= esc(old('claim_date', date('Y-m-d'))) ?>" required>
            <div class="form-text">Must be on or before <?= esc(date('M j, Y', strtotime($maxClaimDate))) ?>.</div>
            <div class="invalid-feedback d-block d-none" id="claimDateError">Claim date cannot be later than <?= esc(date('M j, Y', strtotime($maxClaimDate))) ?>.</div>
          </div>
          <button type="submit" class="btn btn-gg-primary w-100 mt-2" id="reserveSubmitBtn"><i class="bi bi-send-check-fill"></i> Submit Reservation</button>
        <?= form_close() ?>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
          var input   = document.getElementById('claimDateInput');
          var errorEl = document.getElementById('claimDateError');
          var minDate = input.min;
          var maxDate = input.max;

          function isOutOfRange() {
            return input.value !== '' && (input.value < minDate || input.value > maxDate);
          }

          // Single source of truth for the error UI — always re-derives
          // it from the current value instead of only ever turning it on,
          // so it correctly clears again once the date is back in range
          // (including right after clamp() below corrects it).
          function updateErrorState() {
            var invalid = isOutOfRange();
            errorEl.classList.toggle('d-none', ! invalid);
            input.classList.toggle('is-invalid', invalid);
            return invalid;
          }

          function clamp() {
            if (isOutOfRange()) {
              input.value = input.value > maxDate ? maxDate : minDate;
            }
            updateErrorState();
          }

          input.addEventListener('input', updateErrorState);
          input.addEventListener('change', clamp);

          input.form.addEventListener('submit', function (e) {
            if (isOutOfRange()) {
              e.preventDefault();
              clamp();
              input.focus();
            }
          });
        });
        </script>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/footer') ?>
