<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= esc($title) ?></title>
<meta name="description" content="Reserve Graham Mango and Oreo Graham online, track your order status, and pick up right on campus.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700;700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/app.css') ?>" rel="stylesheet">
<link rel="icon" href="<?= base_url('assets/img/logo.png') ?>">
<style>
  /* Mobile-first: base rules below target small screens; each
     min-width query on top of them scales things up for larger ones. */

  .landing-hero {
    background:
      radial-gradient(circle at 12% 15%, var(--gg-primary-light), transparent 42%),
      radial-gradient(circle at 88% 85%, #ffe3c2, transparent 45%),
      var(--gg-bg);
    padding: 2rem 0 2.5rem;
  }
  .landing-hero h1 {
    font-size: 1.75rem;
    line-height: 1.2;
  }
  @media (min-width: 768px) {
    .landing-hero { padding: 3.5rem 0 4.5rem; }
    .landing-hero h1 { font-size: 2.4rem; line-height: 1.15; }
  }

  .landing-nav { background: rgba(255,250,244,.9); backdrop-filter: blur(10px); border-bottom: 1px solid var(--gg-border); position: sticky; top: 0; z-index: 1030; }

  .step-num {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-family: 'Poppins', sans-serif; font-weight: 700; flex-shrink: 0; font-size: .9rem;
  }
  @media (min-width: 768px) {
    .step-num { width: 36px; height: 36px; font-size: 1rem; }
  }

  .hero-badge-float {
    width: 64px; height: 64px; border-radius: 20px;
    background: linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark));
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 16px 40px rgba(224,138,62,.35);
    font-size: 1.8rem; color: #fff; flex-shrink: 0;
  }
  @media (min-width: 768px) {
    .hero-badge-float { width: 90px; height: 90px; border-radius: 28px; font-size: 2.5rem; }
  }

  /* Full-bleed sections lose their side padding on the narrowest phones
     otherwise; Bootstrap's .container already handles >=576px. */
  @media (max-width: 374.98px) {
    .landing-hero .btn-lg { font-size: 1rem; padding: .6rem 1.1rem; }
  }

  /* Landing-only interactivity — the shared .reveal/.enter/.float
     animation classes, the .btn-lg pulse, and the .card/.card-product/
     .stat-card hover lifts all live in app.css now, so every page gets
     them, not just this one. Only page-specific bits stay here. */
  .step-num { transition: transform .25s ease, box-shadow .25s ease; }
  .d-flex.gap-3:hover .step-num { transform: scale(1.1); box-shadow: 0 6px 16px rgba(224,138,62,.35); }

  .mini-step { transition: transform .2s ease; cursor: default; }
  .mini-step:hover { transform: translateY(-3px); }

  .why-card { transition: transform .25s ease, box-shadow .25s ease; }
  .why-card:hover { transform: translateY(-5px); box-shadow: var(--gg-shadow); }
  .why-card:hover .stat-icon { transform: scale(1.1); }
</style>
</head>
<body>

<nav class="navbar navbar-expand landing-nav py-2 py-md-3">
  <div class="container flex-nowrap">
    <a class="navbar-brand d-flex align-items-center gap-2 flex-shrink-0" href="<?= site_url('/') ?>">
      <img src="<?= base_url('assets/img/logo.png') ?>" alt="GrahamGo" style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;">
      <span class="d-none d-sm-inline">GrahamGo</span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-1 gap-md-3 flex-wrap justify-content-end">
      <a href="#contact" class="text-decoration-none small fw-medium d-none d-md-inline" style="color:var(--gg-cocoa-light);">Contact</a>
      <!-- Intentionally always shown as a guest, logged in or not: on a
           shared device, a "My Account" / "Admin Dashboard" shortcut
           sitting here would broadcast that a session is active to
           anyone who glances at the screen. The safety banner below
           still tells a signed-in visitor what's going on and gives
           them a one-click way out — this row just isn't the place
           for it. -->
      <a href="<?= site_url('login') ?>" class="btn btn-outline-dark btn-sm text-nowrap">Login</a>
      <a href="<?= site_url('register') ?>" class="btn btn-gg-primary btn-sm text-nowrap">Register</a>
    </div>
  </div>
</nav>

<?php if ($isCustomer && $isOwner): ?>
  <div class="container mt-2">
    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0 small">
      <i class="bi bi-shield-exclamation flex-shrink-0"></i>
      <span>Both a Customer and an Admin account are signed in on this browser. On a shared or public device, use
        <a href="<?= site_url('logout-all') ?>" class="alert-link">Log out of all accounts</a> when you're done.</span>
    </div>
  </div>
<?php endif; ?>

<!-- Hero -->
<section class="landing-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7 enter">
        <h1 class="mb-3">Reserve your Graham Mango &amp; Oreo Graham online</h1>
        <p class="text-muted mb-4" style="font-size:1.05rem;">
          Skip the back and forth messages. Browse what's available, pick your quantity and claim date,
          and track your order status in real time.
        </p>
        <!-- Same reasoning as the nav bar above: always the guest CTAs
             here, regardless of who's actually signed in on this
             browser, so the hero doesn't double as a "you're logged
             in" indicator either. -->
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= site_url('register') ?>" class="btn btn-gg-primary btn-lg"><i class="bi bi-bag-heart-fill"></i> Reserve Now</a>
          <a href="<?= site_url('login') ?>" class="btn btn-outline-dark btn-lg">I have an account</a>
        </div>
        <div class="d-flex flex-wrap gap-4 mt-4 small text-muted">
          <span><i class="bi bi-check-circle-fill text-success"></i> No fees</span>
          <span><i class="bi bi-check-circle-fill text-success"></i> Real-time status</span>
          <span><i class="bi bi-check-circle-fill text-success"></i> Campus pickup</span>
        </div>
      </div>
      <div class="col-lg-5 enter-delay-1">
        <div class="card p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="hero-badge-float float p-0 overflow-hidden"><img src="<?= base_url('assets/img/logo.png') ?>" alt="GrahamGo" style="width:100%;height:100%;object-fit:cover;"></div>
            <div>
              <h5 class="mb-0">GrahamGo</h5>
              <p class="text-muted small mb-0">Reservation &amp; Sales System</p>
            </div>
          </div>
          <div class="row g-2 text-center">
            <div class="col-4 mini-step">
              <div class="p-2 rounded-3" style="background:var(--gg-primary-light);">
                <div class="fw-bold" style="color:var(--gg-primary-dark);">1</div>
                <div class="small text-muted">Browse</div>
              </div>
            </div>
            <div class="col-4 mini-step">
              <div class="p-2 rounded-3" style="background:var(--gg-primary-light);">
                <div class="fw-bold" style="color:var(--gg-primary-dark);">2</div>
                <div class="small text-muted">Reserve</div>
              </div>
            </div>
            <div class="col-4 mini-step">
              <div class="p-2 rounded-3" style="background:var(--gg-primary-light);">
                <div class="fw-bold" style="color:var(--gg-primary-dark);">3</div>
                <div class="small text-muted">Claim</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Products preview -->
<?php if (! empty($products)): ?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h3 class="mb-1">What's Available Today</h3>
      <p class="text-muted small">Fresh batches, made to order.</p>
    </div>
    <div class="row g-3 g-md-4 justify-content-center">
      <?php foreach ($products as $i => $product): ?>
        <div class="col-6 col-md-4 col-lg-3 reveal" style="--reveal-delay: <?= $i * 0.1 ?>s;">
          <!-- Signed-in customers go straight to the real product page;
               everyone else leads to registration instead of a 404, since
               product details require a customer account to view. -->
          <a href="<?= $isCustomer ? site_url('products/' . $product['product_id']) : site_url('register') ?>" class="card card-product h-100 text-decoration-none text-reset d-block">
            <div class="card-thumb p-0 overflow-hidden">
              <?php if (! empty($product['image'])): ?>
                <img src="<?= product_image_url($product['image']) ?>" alt="<?= esc($product['product_name']) ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <i class="bi bi-cake-fill" style="color:var(--gg-primary-dark);"></i>
              <?php endif; ?>
            </div>
            <div class="card-body text-center p-3">
              <h6 class="mb-1"><?= esc($product['product_name']) ?></h6>
              <p class="fw-bold mb-0" style="color:var(--gg-primary-dark);">₱<?= number_format($product['price'], 2) ?></p>
              <?php if ($product['stock'] > 0): ?>
                <span class="badge bg-success mt-2">In Stock</span>
              <?php else: ?>
                <span class="badge bg-secondary mt-2">Out of Stock</span>
              <?php endif; ?>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= site_url('register') ?>" class="btn btn-gg-primary">Create an Account to Reserve <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- How it works -->
<section class="py-5" style="background:#fff;">
  <div class="container">
    <div class="text-center mb-5">
      <h3 class="mb-1">How GrahamGo Works</h3>
      <p class="text-muted small">From reservation to pickup, in four simple steps.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3 reveal" style="--reveal-delay: 0s;">
        <div class="d-flex gap-3">
          <div class="step-num">1</div>
          <div>
            <h6 class="mb-1">Browse Products</h6>
            <p class="text-muted small mb-0">View available Graham Mango and Oreo Graham, with live stock and pricing.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal" style="--reveal-delay: .1s;">
        <div class="d-flex gap-3">
          <div class="step-num">2</div>
          <div>
            <h6 class="mb-1">Submit a Reservation</h6>
            <p class="text-muted small mb-0">Pick your quantity and preferred claim date in a few taps.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal" style="--reveal-delay: .2s;">
        <div class="d-flex gap-3">
          <div class="step-num">3</div>
          <div>
            <h6 class="mb-1">Track the Status</h6>
            <p class="text-muted small mb-0">Watch your order move from Pending to Confirmed to Ready.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal" style="--reveal-delay: .3s;">
        <div class="d-flex gap-3">
          <div class="step-num">4</div>
          <div>
            <h6 class="mb-1">Claim &amp; Pay</h6>
            <p class="text-muted small mb-0">Pick up your order on campus and settle payment on claim.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Why GrahamGo -->
<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4 reveal" style="--reveal-delay: 0s;">
        <div class="card why-card h-100 p-4">
          <div class="stat-icon mb-3" style="background:linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark));"><i class="bi bi-lightning-charge-fill"></i></div>
          <h6>Fast &amp; Simple</h6>
          <p class="text-muted small mb-0">No more back-and-forth chat messages just to place an order.</p>
        </div>
      </div>
      <div class="col-md-4 reveal" style="--reveal-delay: .1s;">
        <div class="card why-card h-100 p-4">
          <div class="stat-icon mb-3" style="background:var(--gg-info);"><i class="bi bi-graph-up"></i></div>
          <h6>Always Up to Date</h6>
          <p class="text-muted small mb-0">Stock and reservation status update in real time.</p>
        </div>
      </div>
      <div class="col-md-4 reveal" style="--reveal-delay: .2s;">
        <div class="card why-card h-100 p-4">
          <div class="stat-icon mb-3" style="background:var(--gg-success);"><i class="bi bi-shield-check"></i></div>
          <h6>Made for the Campus</h6>
          <p class="text-muted small mb-0">Built for students, faculty, and staff.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-5" style="background:linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark));">
  <div class="container text-center text-white reveal">
    <h3 class="text-white mb-2">Ready to reserve your Graham dessert?</h3>
    <p class="mb-4" style="opacity:.9;">Create your free account and place your first reservation in minutes.</p>
    <a href="<?= site_url('register') ?>" class="btn btn-light btn-lg fw-semibold">Get Started <i class="bi bi-arrow-right"></i></a>
  </div>
</section>

<!-- Contact -->
<section class="py-5" id="contact" style="background:#fff;">
  <div class="container">
    <div class="row g-4 justify-content-center">
      <div class="col-lg-7 reveal">
        <div class="text-center text-lg-start mb-4">
          <h3 class="mb-1">Get in Touch</h3>
          <p class="text-muted small mb-0">Questions about an order or the system? Send a message and we'll reply by email.</p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success fade show d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i> <?= esc(session()->getFlashdata('success')) ?>
          </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger fade show d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i> <?= esc(session()->getFlashdata('error')) ?>
          </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-body p-4">
            <?= form_open('contact') ?>
              <div class="mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" name="contact_name" class="form-control" value="<?= esc(old('contact_name')) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Your Email</label>
                <input type="email" name="contact_email" class="form-control" value="<?= esc(old('contact_email')) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="contact_message" class="form-control" rows="4" required><?= esc(old('contact_message')) ?></textarea>
              </div>
              <button type="submit" class="btn btn-gg-primary"><i class="bi bi-send-fill"></i> Send Message</button>
            <?= form_close() ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="text-center text-muted py-4 small">
  <img src="<?= base_url('assets/img/logo.png') ?>" alt="" style="width:16px;height:16px;border-radius:50%;object-fit:cover;vertical-align:-2px;"> &copy; <?= date('Y') ?> GrahamGo, Graham Mango &amp; Oreo Graham
</footer>

<?php if (session()->getFlashdata('scroll') === 'contact'): ?>
<script>document.addEventListener('DOMContentLoaded', function () { document.getElementById('contact').scrollIntoView({ behavior: 'smooth', block: 'center' }); });</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= view('partials/auto_dismiss_alerts') ?>
<?= view('partials/scroll_reveal') ?>
</body>
</html>
