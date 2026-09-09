<!--
  Shared scroll-reveal trigger. Any element with class="reveal" starts
  hidden/offset (see the .reveal rules in app.css) and fades/slides into
  place the moment it scrolls into view. An optional inline
  --reveal-delay custom property staggers a group of them (product
  grids, stat cards, table rows) instead of having them all pop in at
  once. Used across the whole app — landing page, customer pages, and
  the owner/admin dashboard alike — not just one page.
-->
<script>
(function () {
  var targets = document.querySelectorAll('.reveal');
  if (! targets.length) return;

  if (! ('IntersectionObserver' in window)) {
    // No IO support: just show everything immediately rather than
    // leaving content permanently invisible.
    targets.forEach(function (el) { el.classList.add('is-visible'); });
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  targets.forEach(function (el) { observer.observe(el); });
})();
</script>
