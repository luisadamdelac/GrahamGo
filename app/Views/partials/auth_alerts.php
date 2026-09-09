<?php // Flash alerts for auth pages — rendered inside the card (not floating
    // above it on the gradient backdrop) so they stay visually grounded to
    // the same card shadow/border as the rest of the form. ?>
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success fade show d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill"></i> <?= esc(session()->getFlashdata('success')) ?>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger fade show d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill"></i> <?= esc(session()->getFlashdata('error')) ?>
  </div>
<?php endif; ?>
