<!--
  Flash messages (login/logout welcome messages, "Profile updated", form
  errors, etc.) auto-dismiss after a few seconds — no manual "x" button,
  so this timer is the only way they go away.
-->
<script>
(function () {
  document.querySelectorAll('.alert').forEach(function (alertEl) {
    setTimeout(function () {
      alertEl.style.transition = 'opacity .3s ease';
      alertEl.style.opacity = '0';
      setTimeout(function () { alertEl.remove(); }, 300);
    }, 4000);
  });
})();
</script>
