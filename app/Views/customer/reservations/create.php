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
            <input type="text" id="claimDateInput" name="claim_date" class="form-control" value="<?= esc(old('claim_date', date('Y-m-d'))) ?>" required readonly>
            <div class="form-text">Must be on or before <?= esc(date('M j, Y', strtotime($maxClaimDate))) ?>.</div>
          </div>
          <button type="submit" class="btn btn-gg-primary w-100 mt-2" id="reserveSubmitBtn"><i class="bi bi-send-check-fill"></i> Submit Reservation</button>
        <?= form_close() ?>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
          // Native <input type="date"> displays in whatever format the
          // visitor's browser/OS locale happens to use (MM/DD vs DD/MM),
          // which reads as ambiguous — flatpickr always shows the same
          // unambiguous "Month Day, Year" format for everyone, and only
          // lets you pick from the calendar (no free-typing an unclear
          // date), while still submitting a plain Y-m-d value underneath.
          flatpickr('#claimDateInput', {
            altInput: true,
            altFormat: 'F j, Y',
            altInputClass: 'form-control',
            dateFormat: 'Y-m-d',
            minDate: '<?= date('Y-m-d') ?>',
            maxDate: '<?= esc($maxClaimDate) ?>',
            defaultDate: '<?= esc(old('claim_date', date('Y-m-d'))) ?>',
            disableMobile: true,
          });
        });
        </script>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/footer') ?>
