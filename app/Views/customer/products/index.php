<?= view('layouts/header', ['title' => 'Products']) ?>

<div class="d-flex align-items-center justify-content-between mb-3 mb-md-4 enter">
  <div>
    <h4 class="mb-1">Available Products</h4>
    <p class="text-muted small mb-0 d-none d-sm-block">Reserve your favorite Graham dessert ahead of time.</p>
  </div>
</div>

<?php if (empty($products)): ?>
  <div class="alert alert-info d-flex align-items-center gap-2"><i class="bi bi-info-circle-fill"></i> No products are available right now. Please check back later.</div>
<?php else: ?>
<div class="row g-3 g-md-4">
  <?php foreach ($products as $i => $product): ?>
    <div class="col-6 col-md-4 reveal" style="--reveal-delay: <?= min($i * 0.06, 0.4) ?>s;">
      <div class="card card-product h-100">
        <div class="card-thumb p-0 overflow-hidden">
          <?php if (! empty($product['image'])): ?>
            <img src="<?= cloudinary_resized(product_image_url($product['image']), 400) ?>" alt="<?= esc($product['product_name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <i class="bi bi-cake-fill" style="color:var(--gg-primary-dark);"></i>
          <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column p-3">
          <h6 class="card-title mb-1"><?= esc($product['product_name']) ?></h6>
          <?php $ggSum = $ratingSummary[$product['product_id']] ?? null; ?>
          <?php if ($ggSum): ?>
            <div class="small mb-1" style="color:var(--gg-primary-dark);"><i class="bi bi-star-fill"></i> <?= number_format($ggSum['avg'], 1) ?> <span class="text-muted">(<?= $ggSum['count'] ?>)</span></div>
          <?php endif; ?>
          <p class="card-text text-muted small flex-grow-1 d-none d-sm-block"><?= esc(character_limiter($product['description'] ?? '', 70)) ?></p>
          <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3">
            <span class="fw-bold" style="color:var(--gg-primary-dark);">₱<?= number_format($product['price'], 2) ?></span>
            <?php if ($product['stock'] > 0): ?>
              <span class="badge bg-success"><?= $product['stock'] ?> left</span>
            <?php else: ?>
              <span class="badge bg-secondary">Out</span>
            <?php endif; ?>
          </div>
          <a href="<?= site_url('products/' . $product['product_id']) ?>" class="btn btn-outline-dark btn-sm mt-auto">View <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?= view('layouts/footer') ?>
