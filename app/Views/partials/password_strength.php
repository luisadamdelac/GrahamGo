<?php
/**
 * Live password-strength checklist. Include this once per page, then on
 * the password <input> you want it attached to, add:
 *   data-password-strength="ggPwStrength"
 * Each rule lights up green with a check as soon as it's satisfied,
 * instead of only finding out after submitting that the password was
 * rejected.
 */
?>
<div class="pw-strength small mt-2" id="ggPwStrength" style="display:none;">
  <div class="pw-check" data-rule="length"><i class="bi bi-circle"></i> At least 8 characters</div>
  <div class="pw-check" data-rule="lower"><i class="bi bi-circle"></i> Lowercase letter (a-z)</div>
  <div class="pw-check" data-rule="upper"><i class="bi bi-circle"></i> Uppercase letter (A-Z)</div>
  <div class="pw-check" data-rule="number"><i class="bi bi-circle"></i> Number (0-9)</div>
  <div class="pw-check" data-rule="special"><i class="bi bi-circle"></i> Special character (!@#$...)</div>
</div>

<style>
  .pw-check { color: var(--gg-muted); transition: color .15s var(--gg-ease, ease); }
  .pw-check.met { color: var(--gg-success); font-weight: 500; }
  .pw-check i { margin-right: .3rem; }
</style>

<script>
(function () {
  var rules = {
    length:  function (v) { return v.length >= 8; },
    lower:   function (v) { return /[a-z]/.test(v); },
    upper:   function (v) { return /[A-Z]/.test(v); },
    number:  function (v) { return /[0-9]/.test(v); },
    special: function (v) { return /[^a-zA-Z0-9]/.test(v); },
  };

  document.querySelectorAll('[data-password-strength]').forEach(function (input) {
    var wrap = document.getElementById(input.getAttribute('data-password-strength'));
    if (! wrap) return;

    function update() {
      var value = input.value;
      Object.keys(rules).forEach(function (key) {
        var row = wrap.querySelector('[data-rule="' + key + '"]');
        if (! row) return;
        var met = rules[key](value);
        row.classList.toggle('met', met);
        row.querySelector('i').className = met ? 'bi bi-check-circle-fill' : 'bi bi-circle';
      });
    }

    input.addEventListener('focus', function () { wrap.style.display = 'block'; });
    input.addEventListener('input', update);
    update();
  });
})();
</script>
