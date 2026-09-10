<?= view('layouts/owner_header', ['title' => 'Settings']) ?>

<h4 class="mb-1 enter">Settings</h4>
<p class="text-muted small mb-4">Configure the Gmail account used to send password reset emails.</p>

<div class="row g-3 g-lg-4">
  <div class="col-12 col-lg-5">
    <div class="card h-100">
      <div class="card-body p-4">
        <h6 class="mb-3"><i class="bi bi-calendar-range-fill" style="color:var(--gg-primary-dark);"></i> Reservation Rules</h6>

        <?= form_open('owner/settings/reservation-rules') ?>
          <div class="mb-2">
            <label class="form-label">Max Days Ahead for Claim Date</label>
            <div class="input-group" style="max-width:200px;">
              <input type="number" name="max_reservation_days_ahead" class="form-control" min="1" max="90" value="<?= esc(old('max_reservation_days_ahead', $maxReservationDaysAhead)) ?>" required>
              <span class="input-group-text">days</span>
            </div>
            <div class="form-text">Customers can only pick a claim date up to this many days from today — keeps reservations within the product's shelf life.</div>
          </div>
          <button type="submit" class="btn btn-gg-primary"><i class="bi bi-check2"></i> Save Rule</button>
        <?= form_close() ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card h-100">
      <div class="card-body p-4">
        <h6 class="mb-3"><i class="bi bi-envelope-at-fill" style="color:var(--gg-primary-dark);"></i> Email (SMTP) Settings</h6>

        <?= form_open('owner/settings') ?>
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Gmail Address</label>
              <input type="email" name="smtp_email" class="form-control" placeholder="yourname@gmail.com" value="<?= esc(old('smtp_email', $settings['smtp_email'] ?? '')) ?>">
              <div class="form-text">This Gmail account will appear as the sender of reset-password emails.</div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Sender Name</label>
              <input type="text" name="smtp_from_name" class="form-control" placeholder="GrahamGo" value="<?= esc(old('smtp_from_name', $settings['smtp_from_name'] ?? 'GrahamGo')) ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Gmail App Password</label>
            <input type="password" name="smtp_app_password" class="form-control" style="max-width:420px;" placeholder="<?= $hasAppPassword ? '•••• •••• •••• •••• (already set — leave blank to keep)' : 'abcd efgh ijkl mnop' ?>">
            <div class="form-text">
              16-character App Password from
              <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">myaccount.google.com/apppasswords</a>
              &mdash; requires 2-Step Verification enabled on the Gmail account. Not your regular Gmail password.
            </div>
          </div>
          <button type="submit" class="btn btn-gg-primary"><i class="bi bi-check2"></i> Save Settings</button>
        <?= form_close() ?>

        <hr class="my-4">

        <h6 class="mb-2"><i class="bi bi-send-check"></i> Test Email</h6>
        <p class="text-muted small">Send a test email to confirm everything is working. Leave blank to send it to the Gmail address above.</p>
        <?= form_open('owner/settings/test-email') ?>
          <div class="row g-2 align-items-start">
            <div class="col-12 col-md-7">
              <input type="email" name="test_email" class="form-control" placeholder="Send test to (optional, e.g. a different inbox)">
            </div>
            <div class="col-12 col-md-5">
              <button type="submit" class="btn btn-outline-dark btn-sm w-100"><i class="bi bi-send"></i> Send Test Email</button>
            </div>
          </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</div>

<?= view('layouts/owner_footer') ?>
