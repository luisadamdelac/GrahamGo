<?= view('layouts/auth_header', ['title' => 'Login']) ?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-8 col-md-6 col-lg-5">
    <div class="card auth-card">
      <div class="card-body p-4 p-md-5">
        <?= view('partials/auth_alerts') ?>
        <img src="<?= base_url('assets/img/logo.png') ?>" alt="GrahamGo" class="mb-3" style="width:50px;height:50px;border-radius:50%;object-fit:cover;box-shadow:0 8px 20px rgba(224,138,62,.35);">
        <h4 class="mb-1">Welcome back</h4>
        <p class="text-muted small mb-4">Log in to continue.</p>
        <?= form_open('login') ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
            <div class="text-end mt-1">
              <a href="<?= site_url('forgot-password') ?>" class="small text-muted">Forgot password?</a>
            </div>
          </div>
          <button type="submit" class="btn btn-gg-primary w-100 mt-2"><i class="bi bi-box-arrow-in-right"></i> Login</button>
        <?= form_close() ?>
        <p class="text-center mt-4 mb-0 small">No account yet? <a href="<?= site_url('register') ?>" class="fw-semibold">Register here</a></p>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/auth_footer') ?>
