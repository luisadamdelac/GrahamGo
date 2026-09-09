<?= view('layouts/auth_header', ['title' => 'Forgot Password']) ?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-8 col-md-6 col-lg-5">
    <div class="card auth-card">
      <div class="card-body p-4 p-md-5">
        <?= view('partials/auth_alerts') ?>
        <div class="auth-badge mb-3"><i class="bi bi-envelope-check-fill"></i></div>
        <h4 class="mb-1">Forgot your password?</h4>
        <p class="text-muted small mb-4">Enter the email on your account and we'll send you a 6-digit code to reset your password.</p>
        <?= form_open('forgot-password') ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required autofocus>
          </div>
          <button type="submit" class="btn btn-gg-primary w-100 mt-2"><i class="bi bi-send-fill"></i> Send Code</button>
        <?= form_close() ?>
        <p class="text-center mt-4 mb-0 small"><a href="<?= site_url('login') ?>" class="text-muted"><i class="bi bi-arrow-left"></i> Back to login</a></p>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/auth_footer') ?>
