<?= view('layouts/header', ['title' => $product['product_name']]) ?>

<nav class="small mb-3 text-muted"><a href="<?= site_url('home') ?>">Products</a> <i class="bi bi-chevron-right small"></i> <?= esc($product['product_name']) ?></nav>

<div class="row g-3">
  <div class="col-12 col-md-4">
    <div class="card h-100">
      <div class="card-thumb p-0 overflow-hidden" style="height:160px; border-radius:1.25rem;">
        <?php if (! empty($product['image'])): ?>
          <img src="<?= cloudinary_resized(product_image_url($product['image']), 800) ?>" alt="<?= esc($product['product_name']) ?>" style="width:100%;height:100%;object-fit:cover;">
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
        <?php if ($ratingSummary['count'] > 0): ?>
          <div class="mb-2" style="color:var(--gg-primary-dark);">
            <?php for ($i = 1; $i <= 5; $i++): ?><i class="bi bi-star<?= $i <= round($ratingSummary['avg']) ? '-fill' : '' ?>"></i><?php endfor; ?>
            <span class="text-muted small"><?= number_format($ratingSummary['avg'], 1) ?> (<?= $ratingSummary['count'] ?> review<?= $ratingSummary['count'] === 1 ? '' : 's' ?>)</span>
          </div>
        <?php endif; ?>
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

<div class="row g-3 mt-1">
  <div class="col-12 col-md-11 col-lg-9">
    <div class="card">
      <div class="card-body p-4">
        <h6 class="mb-3">Reviews <span class="text-muted">(<?= count($reviews) ?>)</span></h6>

        <?php if ($canReview): ?>
          <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-info-circle-fill"></i>
            You've claimed this product — <a href="<?= site_url('my-reservations') ?>">rate it from My Reservations</a>.
          </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
          <p class="text-muted small mb-0">No reviews yet.</p>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($reviews as $rv): ?>
              <div class="d-flex gap-2 pb-3" style="border-bottom:1px solid var(--gg-border);">
                <?= avatar_chip($rv['customer_name'], $rv['customer_avatar'], 36) ?>
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-semibold small"><?= esc($rv['customer_name']) ?></span>
                    <span style="color:var(--gg-primary-dark); font-size:.8rem;">
                      <?php for ($i = 1; $i <= 5; $i++): ?><i class="bi bi-star<?= $i <= $rv['rating'] ? '-fill' : '' ?>"></i><?php endfor; ?>
                    </span>
                    <span class="text-muted small"><?= date('M d, Y', strtotime($rv['created_at'])) ?></span>
                  </div>
                  <?php if ($rv['comment']): ?><p class="small mb-0 mt-1"><?= esc($rv['comment']) ?></p><?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/footer') ?>
