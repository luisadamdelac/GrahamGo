<?= view('layouts/auth_header', ['title' => 'Register']) ?>

<style>
  .reg-progress { display: flex; align-items: center; justify-content: center; gap: .5rem; }
  .reg-dot {
    width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--gg-border); color: var(--gg-muted); font-weight: 600; font-size: .85rem;
    transition: background .25s var(--gg-ease), color .25s var(--gg-ease);
  }
  .reg-dot.active { background: linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark)); color: #fff; }
  .reg-dot-label { font-size: .68rem; color: var(--gg-muted); text-align: center; margin-top: .25rem; }
  .reg-dot-wrap.active .reg-dot-label { color: var(--gg-primary-dark); font-weight: 600; }
  .reg-line { flex: 1; height: 2px; background: var(--gg-border); margin-bottom: 1.1rem; }
  .reg-line.active { background: var(--gg-primary); }
</style>

<div class="row justify-content-center">
  <div class="col-12 col-sm-10 col-md-7 col-lg-6">
    <div class="card auth-card">
      <div class="card-body p-4 p-md-5">
        <?= view('partials/auth_alerts') ?>
        <div class="auth-badge mb-3"><i class="bi bi-person-plus-fill"></i></div>
        <h4 class="mb-1">Create your account</h4>
        <p class="text-muted small mb-4">Register to view products and submit reservations.</p>

        <div class="reg-progress mb-4">
          <div class="reg-dot-wrap active text-center" data-dot="1">
            <div class="reg-dot active">1</div>
            <div class="reg-dot-label">Personal</div>
          </div>
          <div class="reg-line" data-line="1"></div>
          <div class="reg-dot-wrap text-center" data-dot="2">
            <div class="reg-dot">2</div>
            <div class="reg-dot-label">Account</div>
          </div>
          <div class="reg-line" data-line="2"></div>
          <div class="reg-dot-wrap text-center" data-dot="3">
            <div class="reg-dot">3</div>
            <div class="reg-dot-label">Security</div>
          </div>
        </div>

        <?= form_open('register') ?>

          <!-- Step 1: Personal info -->
          <div class="reg-step" data-step="1">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?= esc(old('last_name')) ?>" required autofocus>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?= esc(old('first_name')) ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Middle Name <span class="text-muted small">(optional)</span></label>
              <input type="text" name="middle_name" class="form-control" value="<?= esc(old('middle_name')) ?>">
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Province <i class="bi bi-lock-fill text-muted small" title="Cannot be changed"></i></label>
                <input type="text" class="form-control" value="Oriental Mindoro" disabled>
                <input type="hidden" name="province" value="Oriental Mindoro">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">City/Municipality <i class="bi bi-lock-fill text-muted small" title="Cannot be changed"></i></label>
                <input type="text" class="form-control" value="Calapan City" disabled>
                <input type="hidden" name="city_municipality" value="Calapan City">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Barangay</label>
              <select name="barangay" id="regBarangay" class="form-select" required>
                <option value="">Select barangay</option>
                <?php foreach (calapan_barangays() as $brgy): ?>
                  <option value="<?= esc($brgy) ?>" <?= old('barangay') === $brgy ? 'selected' : '' ?>><?= esc($brgy) ?></option>
                <?php endforeach; ?>
                <option value="Other" <?= old('barangay') === 'Other' ? 'selected' : '' ?>>Other (not listed / outside Calapan)</option>
              </select>
            </div>
            <div class="mb-3 <?= old('barangay') === 'Other' ? '' : 'd-none' ?>" id="regBarangayOtherWrap">
              <label class="form-label">Please specify your barangay</label>
              <input type="text" name="barangay_other" id="regBarangayOther" class="form-control" value="<?= esc(old('barangay_other')) ?>" <?= old('barangay') === 'Other' ? 'required' : '' ?>>
            </div>
            <div class="mb-3">
              <label class="form-label">Street</label>
              <input type="text" name="street" class="form-control" placeholder="e.g. 123 Rizal St." value="<?= esc(old('street')) ?>" required>
            </div>
            <button type="button" class="btn btn-gg-primary w-100 mt-2 reg-next">Next <i class="bi bi-arrow-right"></i></button>
          </div>

          <!-- Step 2: Account info -->
          <div class="reg-step d-none" data-step="2">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <input type="text" name="contact_number" class="form-control" value="<?= esc(old('contact_number')) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">You are a</label>
              <select name="customer_type" id="regCustomerType" class="form-select" required>
                <option value="">Select type</option>
                <?php foreach (['Student', 'Faculty', 'Staff', 'Other'] as $type): ?>
                  <option value="<?= $type ?>" <?= old('customer_type') === $type ? 'selected' : '' ?>><?= $type ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3 <?= old('customer_type') === 'Other' ? '' : 'd-none' ?>" id="regCustomerTypeOtherWrap">
              <label class="form-label">Please specify</label>
              <input type="text" name="customer_type_other" id="regCustomerTypeOther" class="form-control" placeholder="e.g. Carpenter" value="<?= esc(old('customer_type_other')) ?>" <?= old('customer_type') === 'Other' ? 'required' : '' ?>>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-outline-dark flex-fill reg-prev"><i class="bi bi-arrow-left"></i> Back</button>
              <button type="button" class="btn btn-gg-primary flex-fill reg-next">Next <i class="bi bi-arrow-right"></i></button>
            </div>
          </div>

          <!-- Step 3: Security -->
          <div class="reg-step d-none" data-step="3">
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required minlength="8" data-password-strength="ggPwStrength">
              <?= view('partials/password_strength') ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            <div class="form-check mb-3">
              <input type="checkbox" name="terms_accepted" id="regTerms" class="form-check-input" required>
              <label class="form-check-label small" for="regTerms">
                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#ggTermsModal">Terms and Conditions</a>.
              </label>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-outline-dark flex-fill reg-prev"><i class="bi bi-arrow-left"></i> Back</button>
              <button type="submit" class="btn btn-gg-primary flex-fill"><i class="bi bi-check2-circle"></i> Create Account</button>
            </div>
          </div>

        <?= form_close() ?>

        <!-- Terms and Conditions modal -->
        <div class="modal fade" id="ggTermsModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content" style="border-radius:var(--gg-radius-lg); border:none;">
              <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body small">
                <p><strong>1. Reservations.</strong> Placing a reservation reserves the item(s) for you but does not guarantee availability until the owner confirms it. Payment is collected only when you claim your order.</p>
                <p><strong>2. Claiming &amp; No-shows.</strong> Please claim confirmed orders on the date you selected. Repeated no-shows may result in your reservation privileges being restricted.</p>
                <p><strong>3. Cancellations.</strong> You may cancel a reservation yourself only while it is still Pending. Once confirmed, please contact the owner directly to cancel.</p>
                <p><strong>4. Account accuracy.</strong> You agree to provide accurate personal and contact information, and to keep it up to date via your Profile.</p>
                <p><strong>5. Data privacy.</strong> Your information (name, contact number, address, order history) is used only to process your reservations and to contact you about them. It is not sold or shared with third parties.</p>
                <p class="mb-0"><strong>6. Changes.</strong> These terms may be updated from time to time as the system evolves.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-gg-primary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        <p class="text-center mt-4 mb-0 small">Already have an account? <a href="<?= site_url('login') ?>" class="fw-semibold">Login here</a></p>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var steps = document.querySelectorAll('.reg-step');
  var dotWraps = document.querySelectorAll('.reg-dot-wrap');
  var lines = document.querySelectorAll('.reg-line');
  var current = 1;

  function showStep(n) {
    steps.forEach(function (s) { s.classList.toggle('d-none', parseInt(s.dataset.step, 10) !== n); });
    dotWraps.forEach(function (w) {
      var isDone = parseInt(w.dataset.dot, 10) <= n;
      w.classList.toggle('active', isDone);
      w.querySelector('.reg-dot').classList.toggle('active', isDone);
    });
    lines.forEach(function (l) { l.classList.toggle('active', parseInt(l.dataset.line, 10) < n); });
    current = n;
    var firstField = steps[n - 1] && steps[n - 1].querySelector('input, select');
    if (firstField) firstField.focus();
  }

  document.querySelectorAll('.reg-next').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var stepEl = btn.closest('.reg-step');
      var inputs = stepEl.querySelectorAll('input, select');
      var firstInvalid = null;
      inputs.forEach(function (inp) {
        if (! firstInvalid && ! inp.checkValidity()) firstInvalid = inp;
      });
      if (firstInvalid) { firstInvalid.reportValidity(); return; }
      showStep(current + 1);
    });
  });

  document.querySelectorAll('.reg-prev').forEach(function (btn) {
    btn.addEventListener('click', function () { showStep(current - 1); });
  });

  var typeSelect = document.getElementById('regCustomerType');
  var otherWrap  = document.getElementById('regCustomerTypeOtherWrap');
  var otherInput = document.getElementById('regCustomerTypeOther');
  if (typeSelect) {
    typeSelect.addEventListener('change', function () {
      var isOther = typeSelect.value === 'Other';
      otherWrap.classList.toggle('d-none', ! isOther);
      otherInput.required = isOther;
      if (! isOther) otherInput.value = '';
    });
  }

  var barangaySelect = document.getElementById('regBarangay');
  var barangayOtherWrap  = document.getElementById('regBarangayOtherWrap');
  var barangayOtherInput = document.getElementById('regBarangayOther');
  if (barangaySelect) {
    barangaySelect.addEventListener('change', function () {
      var isOther = barangaySelect.value === 'Other';
      barangayOtherWrap.classList.toggle('d-none', ! isOther);
      barangayOtherInput.required = isOther;
      if (! isOther) barangayOtherInput.value = '';
    });
  }

  // If a server-side validation error sent us back here, reopen on the
  // step that actually has the problem (see Auth::backToRegisterStep())
  // instead of resetting to step 1 — every field's value is already
  // restored via old() regardless, this just avoids losing your place.
  var initialStep = <?= (int) (session()->getFlashdata('step') ?? 1) ?>;
  if (initialStep > 1) showStep(initialStep);
})();
</script>

<?= view('layouts/auth_footer') ?>
