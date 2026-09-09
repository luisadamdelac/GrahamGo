<?= view('layouts/owner_header', ['title' => 'Reports']) ?>

<h4 class="mb-4 enter">Reports</h4>

<div class="row g-3 g-md-4">
  <div class="col-12 col-md-4">
    <div class="card h-100"><div class="card-body p-4">
      <div class="stat-icon mb-3" style="background:var(--gg-info);"><i class="bi bi-journal-text"></i></div>
      <h5>Reservation Report</h5>
      <p class="text-muted small">Customer, product, quantity, claim date, total amount, payment and reservation status.</p>
      <a href="<?= site_url('owner/reports/reservations') ?>" class="btn btn-outline-dark btn-sm">Open Report <i class="bi bi-arrow-right"></i></a>
    </div></div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card h-100"><div class="card-body p-4">
      <div class="stat-icon mb-3" style="background:linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark));"><i class="bi bi-cash-coin"></i></div>
      <h5>Sales Report</h5>
      <p class="text-muted small">Completed transactions with date, customer, product, quantity, amount, and payment method.</p>
      <a href="<?= site_url('owner/reports/sales') ?>" class="btn btn-outline-dark btn-sm">Open Report <i class="bi bi-arrow-right"></i></a>
    </div></div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card h-100"><div class="card-body p-4">
      <div class="stat-icon mb-3" style="background:var(--gg-success);"><i class="bi bi-clipboard-data-fill"></i></div>
      <h5>Inventory Report</h5>
      <p class="text-muted small">Prepared, reserved, sold, and available quantities per product.</p>
      <a href="<?= site_url('owner/reports/inventory') ?>" class="btn btn-outline-dark btn-sm">Open Report <i class="bi bi-arrow-right"></i></a>
    </div></div>
  </div>
</div>

<?= view('layouts/owner_footer') ?>
