<?= view('layouts/auth_header', ['title' => 'Reset Password']) ?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-8 col-md-6 col-lg-5">
    <div class="card auth-card">
      <div class="card-body p-4 p-md-5">
        <?= view('partials/auth_alerts') ?>
        <div class="auth-badge mb-3"><i class="bi bi-shield-lock-fill"></i></div>
        <h4 class="mb-1">Set a new password</h4>
        <p class="text-muted small mb-4">Choose a new password for your account.</p>
        <?= form_open('reset-password') ?>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required minlength="8" autofocus data-password-strength="ggPwStrength">
            <?= view('partials/password_strength') ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="8">
          </div>
          <button type="submit" class="btn btn-gg-primary w-100 mt-2"><i class="bi bi-check2-circle"></i> Reset Password</button>
        <?= form_close() ?>
        <p class="text-center mt-4 mb-0 small"><a href="<?= site_url('login') ?>" class="text-muted"><i class="bi bi-arrow-left"></i> Back to login</a></p>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/auth_footer') ?>
