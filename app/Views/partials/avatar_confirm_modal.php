<!--
  Shown right after picking a new profile photo (both customer and owner
  profile pages use the same `avatarInput` element ID, inside the profile
  form). Choosing a file doesn't upload it right away — it previews the
  pick here first. Confirming ("Use This Photo") submits the profile form
  immediately, so the photo is saved on the spot without a separate "Save
  Changes" step. Backing out (Cancel, backdrop click, or Escape) clears
  the file input instead, so nothing gets uploaded.
-->
<div class="modal fade" id="ggAvatarConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--gg-radius-lg); border:none;">
      <div class="modal-body text-center p-4">
        <img id="ggAvatarConfirmPreview" src="" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;" alt="New photo preview">
        <h5 class="mb-2">Use this photo?</h5>
        <p class="text-muted small mb-4">Your profile photo will be saved right away.</p>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-dark flex-fill" id="ggAvatarConfirmCancel">Cancel</button>
          <button type="button" class="btn btn-gg-primary flex-fill" id="ggAvatarConfirmYes"><i class="bi bi-check2"></i> Use This Photo</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var input = document.getElementById('avatarInput');
  var modalEl = document.getElementById('ggAvatarConfirmModal');
  if (! input || ! modalEl || typeof bootstrap === 'undefined') return;

  var modal        = new bootstrap.Modal(modalEl);
  var previewImg    = document.getElementById('ggAvatarConfirmPreview');
  var yesBtn        = document.getElementById('ggAvatarConfirmYes');
  var cancelBtn      = document.getElementById('ggAvatarConfirmCancel');
  var pickedObjectUrl = null;
  var confirmed       = false;

  input.addEventListener('change', function () {
    var file = input.files[0];
    if (! file) return;

    if (pickedObjectUrl) URL.revokeObjectURL(pickedObjectUrl);
    pickedObjectUrl = URL.createObjectURL(file);
    previewImg.src  = pickedObjectUrl;
    confirmed       = false;
    modal.show();
  });

  yesBtn.addEventListener('click', function () {
    confirmed = true;
    yesBtn.disabled = true;
    yesBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    input.form.submit();
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
})();
</script>
