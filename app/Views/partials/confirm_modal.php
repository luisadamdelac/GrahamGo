<!--
  One shared confirmation modal per page, reused by every "dangerous"
  button. Instead of the browser's plain confirm() popup, any button or
  link gets `data-confirm="Message to show"` (and optionally
  `data-confirm-title="..."` / `data-confirm-variant="danger|primary"`)
  and this modal intercepts the click, asks, then re-submits the original
  form (or follows the original link) only after the user taps Yes.
-->
<div class="modal fade" id="ggConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--gg-radius-lg); border:none;">
      <div class="modal-body text-center p-4">
        <div id="ggConfirmModalIcon" class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:56px;height:56px;">
          <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        </div>
        <h5 id="ggConfirmModalTitle" class="mb-2">Are you sure?</h5>
        <p id="ggConfirmModalMessage" class="text-muted small mb-4">This action cannot be undone.</p>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-dark flex-fill" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger flex-fill" id="ggConfirmModalYes">Yes, Continue</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var modalEl = document.getElementById('ggConfirmModal');
  if (! modalEl || typeof bootstrap === 'undefined') return;

  var modal      = new bootstrap.Modal(modalEl);
  var titleEl    = document.getElementById('ggConfirmModalTitle');
  var messageEl  = document.getElementById('ggConfirmModalMessage');
  var iconWrapEl = document.getElementById('ggConfirmModalIcon');
  var yesBtn     = document.getElementById('ggConfirmModalYes');
  var pendingTarget = null; // the form (or link) waiting on the user's answer

  var variants = {
    danger:  { bg: 'var(--gg-danger-bg)',  fg: 'var(--gg-danger)',  btn: 'btn-danger' },
    primary: { bg: 'var(--gg-primary-light)', fg: 'var(--gg-primary-dark)', btn: 'btn-gg-primary' }
  };

  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      pendingTarget = el.closest('form') || el;

      titleEl.textContent   = el.getAttribute('data-confirm-title') || 'Are you sure?';
      messageEl.textContent = el.getAttribute('data-confirm');

      var variant = variants[el.getAttribute('data-confirm-variant') || 'danger'];
      iconWrapEl.style.background = variant.bg;
      iconWrapEl.querySelector('i').style.color = variant.fg;
      yesBtn.className = 'btn flex-fill ' + variant.btn;

      modal.show();
    });
  });

  yesBtn.addEventListener('click', function () {
    modal.hide();
    if (! pendingTarget) return;

    if (pendingTarget.tagName === 'FORM') {
      pendingTarget.submit();
    } else {
      window.location.href = pendingTarget.href;
    }
    pendingTarget = null;
  });
})();
</script>
