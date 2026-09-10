<?php
/**
 * Adds a reliable show/hide eye-icon button to every password field on
 * the page — built ourselves rather than relying on the browser's own
 * native reveal icon (Edge/Chrome sometimes show one, inconsistently,
 * only in some conditions), so it always works the same way regardless
 * of browser. Include once per layout footer; it finds and enhances
 * every input[type="password"] already on the page, no per-field markup
 * changes needed anywhere.
 */
?>
<style>
  .pw-eye-toggle {
    position: absolute; right: .6rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; padding: .3rem .4rem;
    display: flex; align-items: center; color: var(--gg-muted, #7A6858);
    z-index: 5; cursor: pointer;
  }
  .pw-eye-toggle:hover { color: var(--gg-cocoa, #4A3324); }
  .pw-eye-toggle:focus-visible { outline: 2px solid var(--gg-primary, #E08A3E); outline-offset: 2px; border-radius: 4px; }

  /* Hides the browser's own built-in reveal icon so only ours shows —
     Edge/IE add theirs via this pseudo-element (inconsistently, only
     under some conditions), which was showing up alongside ours,
     looking like two overlapping icons. Chrome/Firefox don't render a
     native one on a plain input, so this has no effect there. */
  input[type="password"]::-ms-reveal,
  input[type="password"]::-ms-clear {
    display: none;
  }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    if (input.dataset.eyeToggled) return;
    input.dataset.eyeToggled = '1';

    var wrapper = document.createElement('div');
    wrapper.style.position = 'relative';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pw-eye-toggle';
    btn.setAttribute('aria-label', 'Show password');
    btn.innerHTML = '<i class="bi bi-eye-fill"></i>';
    wrapper.appendChild(btn);

    var existingPadding = window.getComputedStyle(input).paddingRight;
    input.style.paddingRight = 'calc(' + existingPadding + ' + 1.9rem)';

    btn.addEventListener('click', function () {
      var willShow = input.type === 'password';
      input.type = willShow ? 'text' : 'password';
      btn.innerHTML = willShow ? '<i class="bi bi-eye-slash-fill"></i>' : '<i class="bi bi-eye-fill"></i>';
      btn.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
    });
  });
});
</script>
