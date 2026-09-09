<?php
/**
 * A simple, dependency-free search box for mobile card lists (which
 * DataTables doesn't touch — see partials/datatables_init.php, mobile
 * intentionally keeps its own separate card layout). Usage:
 *
 *   <?= view('partials/mobile_card_search', ['target' => 'someId']) ?>
 *   <div id="someId" class="d-lg-none ...">
 *     <div class="card">...</div>
 *     ...
 *   </div>
 *
 * Filters by showing/hiding each direct .card child based on whether its
 * text contains what was typed — plain vanilla JS, no library, so it
 * doesn't depend on any external script having loaded first.
 */
?>
<div class="d-lg-none mb-3">
  <input type="text" class="form-control mobile-card-search" data-filter-target="#<?= esc($target) ?>" placeholder="Search...">
</div>
<script>
// This partial is included BEFORE the target card list further down the
// page, so document.querySelector(target) run immediately here would
// always find nothing — the target element hasn't been parsed into the
// DOM yet at this point in the page source. DOMContentLoaded defers the
// lookup until the whole page (target included) is ready, same fix as
// the Restock button binding elsewhere.
document.addEventListener('DOMContentLoaded', function () {
  var inputs = document.querySelectorAll('.mobile-card-search[data-filter-target="#<?= esc($target) ?>"]');

  inputs.forEach(function (input) {
    var target = document.querySelector(input.getAttribute('data-filter-target'));
    if (! target || input.dataset.bound) return;
    input.dataset.bound = '1';

    input.addEventListener('input', function () {
      var term = input.value.trim().toLowerCase();
      Array.prototype.forEach.call(target.children, function (card) {
        var text = card.textContent.toLowerCase();
        card.style.display = (term === '' || text.indexOf(term) !== -1) ? '' : 'none';
      });
    });
  });
});
</script>
