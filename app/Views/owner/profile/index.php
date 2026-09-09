<?= view('layouts/owner_header', ['title' => 'My Profile']) ?>

<h4 class="mb-4 enter">My Profile</h4>

<?= form_open_multipart('owner/profile') ?>
  <div class="row g-3 g-lg-4">
    <!-- Left: avatar summary -->
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body p-4 text-center">
          <?php if ($user['avatar']): ?>
            <img id="avatarPreview" src="<?= avatar_url($user['avatar']) ?>" class="rounded-circle mb-3" style="width:96px;height:96px;object-fit:cover;" alt="Avatar">
          <?php else: ?>
            <div id="avatarPreview" class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:96px;height:96px;background:var(--gg-primary-light);">
              <i class="bi bi-person-fill" style="font-size:2.4rem; color:var(--gg-primary-dark);"></i>
            </div>
          <?php endif; ?>
          <h6 class="mb-1"><?= esc($user['name']) ?></h6>
          <span class="badge bg-secondary mb-2">Admin</span>
          <p class="text-muted small mb-3">
            <i class="bi bi-calendar-check"></i> Managing since <?= $user['created_at'] ? date('M Y', strtotime($user['created_at'])) : '—' ?>
          </p>
          <div class="mb-0">
            <label class="btn btn-sm btn-outline-dark mb-0" for="avatarInput"><i class="bi bi-camera-fill"></i> Change Photo</label>
            <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg,image/webp" class="d-none">
            <div class="form-text mb-0">JPG, PNG, or WEBP. Max 2MB.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: editable details -->
    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-body p-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" class="form-control" value="<?= esc(old('last_name', $user['last_name'])) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" class="form-control" value="<?= esc(old('first_name', $user['first_name'])) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Middle Name <span class="text-muted small">(optional)</span></label>
              <input type="text" name="middle_name" class="form-control" value="<?= esc(old('middle_name', $user['middle_name'])) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Number</label>
              <input type="text" name="contact_number" class="form-control" value="<?= esc(old('contact_number', $user['contact_number'])) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-muted small">(login email)</span></label>
              <input type="email" name="email" class="form-control" value="<?= esc(old('email', $user['email'])) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">New Password <span class="text-muted small">(optional)</span></label>
              <input type="password" name="password" class="form-control" minlength="8" data-password-strength="ggPwStrength">
              <?= view('partials/password_strength') ?>
            </div>
          </div>

          <hr class="my-3">
          <h6 class="mb-3 small text-muted text-uppercase">Address</h6>
          <div class="row g-3">
            <?php $currentBarangay = old('barangay', $user['barangay']); ?>
            <div class="col-md-6">
              <label class="form-label">Province <i class="bi bi-lock-fill text-muted small" title="Cannot be changed"></i></label>
              <input type="text" class="form-control" value="Oriental Mindoro" disabled>
              <input type="hidden" name="province" value="Oriental Mindoro">
            </div>
            <div class="col-md-6">
              <label class="form-label">City/Municipality <i class="bi bi-lock-fill text-muted small" title="Cannot be changed"></i></label>
              <input type="text" class="form-control" value="Calapan City" disabled>
              <input type="hidden" name="city_municipality" value="Calapan City">
            </div>
            <div class="col-md-6">
              <label class="form-label">Barangay</label>
              <select name="barangay" id="profBarangay" class="form-select" required>
                <option value="">Select barangay</option>
                <?php foreach (calapan_barangays() as $brgy): ?>
                  <option value="<?= esc($brgy) ?>" <?= $currentBarangay === $brgy ? 'selected' : '' ?>><?= esc($brgy) ?></option>
                <?php endforeach; ?>
                <option value="Other" <?= ($currentBarangay && ! in_array($currentBarangay, calapan_barangays(), true)) ? 'selected' : '' ?>>Other (not listed / outside Calapan)</option>
              </select>
            </div>
            <div class="col-md-6 <?= ($currentBarangay && ! in_array($currentBarangay, calapan_barangays(), true)) ? '' : 'd-none' ?>" id="profBarangayOtherWrap">
              <label class="form-label">Please specify your barangay</label>
              <input type="text" name="barangay_other" id="profBarangayOther" class="form-control" value="<?= esc(old('barangay_other', ($currentBarangay && ! in_array($currentBarangay, calapan_barangays(), true)) ? $currentBarangay : '')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Street <span class="text-muted small">(optional)</span></label>
              <input type="text" name="street" class="form-control" placeholder="e.g. 123 Rizal St." value="<?= esc(old('street', $user['street'])) ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-gg-primary mt-4"><i class="bi bi-check2"></i> Save Changes</button>
        </div>
      </div>
    </div>
  </div>
<?= form_close() ?>

<?= view('partials/avatar_confirm_modal') ?>

<?= view('layouts/owner_footer') ?>
