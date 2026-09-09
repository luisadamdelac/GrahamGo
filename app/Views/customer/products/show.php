<?= view('layouts/header', ['title' => $product['product_name']]) ?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('home') ?>">Products</a> <i class="bi bi-chevron-right small"></i> <?= esc($product['product_name']) ?></nav>

<div class="row g-3">
  <div class="col-12 col-md-4">
    <div class="card h-100">
      <div class="card-thumb p-0 overflow-hidden" style="height:160px; border-radius:1.25rem;">
        <?php if (! empty($product['image'])): ?>
          <img src="<?= product_image_url($product['image']) ?>" alt="<?= esc($product['product_name']) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
          <i class="bi bi-cake-fill" style="font-size:3rem; color:var(--gg-primary-dark);"></i>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-7">
    <div class="card h-100">
      <div class="card-body p-4">
        <h4 class="mb-1"><?= esc($product['product_name']) ?></h4>
        <p class="text-muted"><?= esc($product['description']) ?></p>
        <h4 class="fw-bold mb-3" style="color:var(--gg-primary-dark);">₱<?= number_format($product['price'], 2) ?></h4>
        <p>
          <?php if ($product['stock'] > 0): ?>
            <span class="badge bg-success status-badge"><i class="bi bi-check-circle-fill"></i> Available: <?= $product['stock'] ?> pcs</span>
          <?php else: ?>
            <span class="badge bg-secondary status-badge">Out of Stock</span>
          <?php endif; ?>
        </p>

        <?php if ($product['stock'] > 0): ?>
          <a href="<?= site_url('reserve/' . $product['product_id']) ?>" class="btn btn-gg-primary w-100 w-md-auto"><i class="bi bi-bag-plus-fill"></i> Make Reservation</a>
        <?php else: ?>
          <button class="btn btn-secondary w-100 w-md-auto" disabled>Currently Unavailable</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/footer') ?>
