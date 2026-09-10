<!--
  Shown right after picking a new profile photo (both customer and owner
  profile pages use the same `avatarInput` element ID). Choosing a file
  doesn't upload it right away — it previews the pick here first.
  Confirming ("Use This Photo") uploads it immediately via its own AJAX
  endpoint (profile/avatar or owner/profile/avatar — pass $uploadUrl),
  independent of the rest of the profile form, so it saves on the spot
  regardless of whether the other fields (barangay, password, ...)
  happen to be valid right now. Backing out (Cancel, backdrop click, or
  Escape) clears the file input instead, so nothing gets uploaded.
-->
<div class="modal fade" id="ggAvatarConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--gg-radius-lg); border:none;">
      <div class="modal-body text-center p-4">
        <img id="ggAvatarConfirmPreview" src="" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;" alt="New photo preview">
        <h5 class="mb-2">Use this photo?</h5>
        <p class="text-muted small mb-4">Your profile photo will be saved right away.</p>
        <div class="alert alert-danger small py-2 mb-3 d-none" id="ggAvatarConfirmError"></div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-dark flex-fill" id="ggAvatarConfirmCancel">Cancel</button>
          <button type="button" class="btn btn-gg-primary flex-fill" id="ggAvatarConfirmYes"><i class="bi bi-check2"></i> Use This Photo</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var input   = document.getElementById('avatarInput');
  var modalEl = document.getElementById('ggAvatarConfirmModal');
  if (! input || ! modalEl || typeof bootstrap === 'undefined') return;

  var uploadUrl      = '<?= esc(site_url($uploadUrl ?? 'profile/avatar'), 'js') ?>';
  var csrfName        = '<?= csrf_token() ?>';
  var csrfHash        = '<?= csrf_hash() ?>';
  var modal            = new bootstrap.Modal(modalEl);
  var previewImg        = document.getElementById('ggAvatarConfirmPreview');
  var yesBtn            = document.getElementById('ggAvatarConfirmYes');
  var cancelBtn          = document.getElementById('ggAvatarConfirmCancel');
  var errorEl            = document.getElementById('ggAvatarConfirmError');
  var pickedObjectUrl     = null;
  var confirmed           = false;
  var yesBtnDefaultHtml   = yesBtn.innerHTML;

  input.addEventListener('change', function () {
    var file = input.files[0];
    if (! file) return;

    if (pickedObjectUrl) URL.revokeObjectURL(pickedObjectUrl);
    pickedObjectUrl = URL.createObjectURL(file);
    previewImg.src  = pickedObjectUrl;
    confirmed       = false;
    if (errorEl) errorEl.classList.add('d-none');
    modal.show();
  });

  yesBtn.addEventListener('click', function () {
    var file = input.files[0];
    if (! file) return;

    yesBtn.disabled = true;
    yesBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    if (errorEl) errorEl.classList.add('d-none');

    var formData = new FormData();
    formData.append('avatar', file);
    formData.append(csrfName, csrfHash);

    fetch(uploadUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
      .then(function (res) {
        return res.json().then(function (data) { return { ok: res.ok, data: data }; });
      })
      .then(function (result) {
        if (result.ok && result.data.success) {
          confirmed = true;
          var bustedUrl = result.data.url + (result.data.url.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
          document.querySelectorAll('#avatarPreview').forEach(function (img) { img.src = bustedUrl; });
          modal.hide();
        } else {
          if (errorEl) {
            errorEl.textContent = (result.data && result.data.error) || 'Upload failed. Please try again.';
            errorEl.classList.remove('d-none');
          }
        }
      })
      .catch(function () {
        if (errorEl) {
          errorEl.textContent = 'Upload failed. Please check your connection and try again.';
          errorEl.classList.remove('d-none');
        }
      })
      .finally(function () {
        yesBtn.disabled = false;
        yesBtn.innerHTML = yesBtnDefaultHtml;
      });
  });

  cancelBtn.addEventListener('click', function () {
    modal.hide();
  });

  // Covers Cancel, backdrop click, and Escape — anything that isn't "Yes".
  modalEl.addEventListener('hidden.bs.modal', function () {
    if (! confirmed) {
      input.value = '';
    }
  });
});
</script>
