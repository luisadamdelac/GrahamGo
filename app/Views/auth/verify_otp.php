<?= view('layouts/auth_header', ['title' => 'Verify Code']) ?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-8 col-md-6 col-lg-5">
    <div class="card auth-card">
      <div class="card-body p-4 p-md-5">
        <?= view('partials/auth_alerts') ?>
        <div class="auth-badge mb-3"><i class="bi bi-shield-check"></i></div>
        <h4 class="mb-1">Enter your code</h4>
        <p class="text-muted small mb-4">We emailed a 6-digit code to your address. It expires in 10 minutes.</p>
        <?= form_open('verify-otp') ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email', $email ?? '')) ?>" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">6-Digit Code</label>
            <input type="text" name="otp" class="form-control text-center" style="letter-spacing:.5em; font-size:1.4rem;" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required>
          </div>
          <button type="submit" class="btn btn-gg-primary w-100 mt-2"><i class="bi bi-check2-circle"></i> Verify Code</button>
        <?= form_close() ?>
        <p class="text-center mt-4 mb-0 small">Didn't get a code? <a href="<?= site_url('forgot-password') ?>" class="fw-semibold">Send again</a></p>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/auth_footer') ?>
