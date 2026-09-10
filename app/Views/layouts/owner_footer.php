  </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function () {
    var sidebar = document.getElementById('ggSidebar');
    var backdrop = document.getElementById('ggSidebarBackdrop');
    var toggle = document.getElementById('ggSidebarToggle');

    function openSidebar() {
      sidebar.classList.add('show');
      backdrop.classList.add('show');
    }
    function closeSidebar() {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    }

    if (toggle) toggle.addEventListener('click', openSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', closeSidebar);
    });
  })();
</script>
<script>
  // Keeps the sidebar badges (Reservations, Inventory) and the browser
  // tab title in sync with everything that currently needs an admin
  // decision or action — unreviewed/unprepared/unclaimed reservations,
  // plus products running low on stock. Polled in the background so it
  // updates even if the admin is sitting on a different owner page (or a
  // different tab entirely) and never refreshes. Each page's own initial
  // render already has the correct counts baked in server-side; this
  // just keeps them fresh afterward.
  (function () {
    var titleEl        = document.getElementById('ggPageTitle');
    var reservationBadge = document.getElementById('ggReservationBadge');
    var lowStockBadge    = document.getElementById('ggLowStockBadge');
    var bellBadge         = document.getElementById('ggTopbarBellBadge');
    var notifOverdueItem      = document.getElementById('ggNotifOverdueItem');
    var notifOverdueCount     = document.getElementById('ggNotifOverdueCount');
    var notifReservationItem  = document.getElementById('ggNotifReservationItem');
    var notifReservationCount = document.getElementById('ggNotifReservationCount');
    var notifStockItem        = document.getElementById('ggNotifStockItem');
    var notifStockCount       = document.getElementById('ggNotifStockCount');
    var notifSignupItem       = document.getElementById('ggNotifSignupItem');
    var notifSignupCount      = document.getElementById('ggNotifSignupCount');
    var notifEmpty            = document.getElementById('ggNotifEmpty');
    var baseTitle       = titleEl ? titleEl.getAttribute('data-base-title') : document.title;

    function setBadge(el, count) {
      if (! el) return;
      el.textContent = count;
      el.style.display = count > 0 ? '' : 'none';
    }

    function toggleItem(el, show) {
      if (! el) return;
      el.style.display = show ? '' : 'none';
    }

    function poll() {
      fetch('<?= site_url('owner/notifications/alerts-count') ?>', { credentials: 'same-origin' })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) {
          if (! data) return;
          setBadge(reservationBadge, data.reservations);
          setBadge(lowStockBadge, data.lowStock);
          setBadge(bellBadge, data.total);

          if (notifOverdueCount) notifOverdueCount.textContent = data.overdue;
          if (notifReservationCount) notifReservationCount.textContent = data.reservations;
          if (notifStockCount) notifStockCount.textContent = data.lowStock;
          if (notifSignupCount) notifSignupCount.textContent = data.newSignups;
          toggleItem(notifOverdueItem, data.overdue > 0);
          toggleItem(notifReservationItem, data.reservations > 0);
          toggleItem(notifStockItem, data.lowStock > 0);
          toggleItem(notifSignupItem, data.newSignups > 0);
          toggleItem(notifEmpty, (data.overdue + data.reservations + data.lowStock + data.newSignups) === 0);

          document.title = data.total > 0 ? '(' + data.total + ') ' + baseTitle : baseTitle;
        })
        .catch(function () { /* silent — next poll will retry */ });
    }

    setInterval(poll, 30000);
  })();
</script>
<script>
  // Live results under the topbar search box — fetches matching
  // customers as you type (debounced) instead of only searching after
  // Enter is pressed and the whole Customers page reloads.
  (function () {
    var input   = document.getElementById('ggTopbarSearchInput');
    var results = document.getElementById('ggTopbarSearchResults');
    var form    = document.getElementById('ggTopbarSearchForm');
    if (! input || ! results || ! form) return;

    var debounceTimer = null;
    var currentRequest = null;

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }

    function initials(name) {
      var parts = name.trim().split(/\s+/);
      return ((parts[0] || '')[0] || '') + ((parts[parts.length - 1] || '')[0] || '');
    }

    function render(customers) {
      if (customers === null) {
        results.innerHTML = '<div class="text-center text-muted small py-3">Searching&hellip;</div>';
        results.classList.add('show');
        return;
      }
      if (customers.length === 0) {
        results.innerHTML = '<div class="text-center text-muted small py-3">No customers found.</div>';
        results.classList.add('show');
        return;
      }
      results.innerHTML = customers.map(function (c) {
        // A CSS background-image div, not an <img> tag — Edge overlays a
        // hover "image toolbar" icon on real <img> elements, which was
        // showing up right next to the photo in this dropdown.
        var avatar = c.avatar
          ? '<div style="width:32px;height:32px;border-radius:50%;background-image:url(\'' + escapeHtml(c.avatar) + '\');background-size:cover;background-position:center;flex-shrink:0;"></div>'
          : '<div style="display:flex;width:32px;height:32px;border-radius:50%;background:var(--gg-primary-light);color:var(--gg-primary-dark);align-items:center;justify-content:center;font-size:.75rem;font-weight:600;flex-shrink:0;">' + escapeHtml(initials(c.name).toUpperCase()) + '</div>';
        return '<a href="<?= site_url('owner/customers') ?>/' + c.id + '" class="topbar-search-result-item">'
          + avatar
          + '<div style="min-width:0;"><div class="fw-semibold small text-truncate">' + escapeHtml(c.name) + '</div>'
          + '<div class="text-muted text-truncate" style="font-size:.76rem;">' + escapeHtml(c.email) + '</div></div>'
          + '</a>';
      }).join('') + '<a href="<?= site_url('owner/customers') ?>?q=' + encodeURIComponent(input.value) + '" class="topbar-search-result-viewall">View all results <i class="bi bi-arrow-right"></i></a>';
      results.classList.add('show');
    }

    function search(term) {
      if (currentRequest) currentRequest.abort();
      var controller = new AbortController();
      currentRequest = controller;

      fetch('<?= site_url('owner/customers/quick-search') ?>?q=' + encodeURIComponent(term), { credentials: 'same-origin', signal: controller.signal })
        .then(function (res) { return res.ok ? res.json() : []; })
        .then(function (data) { render(data); })
        .catch(function (err) { if (err.name !== 'AbortError') results.classList.remove('show'); });
    }

    input.addEventListener('input', function () {
      var term = input.value.trim();
      clearTimeout(debounceTimer);
      if (term === '') {
        results.classList.remove('show');
        return;
      }
      render(null);
      debounceTimer = setTimeout(function () { search(term); }, 300);
    });

    input.addEventListener('focus', function () {
      if (input.value.trim() !== '' && results.innerHTML !== '') results.classList.add('show');
    });

    document.addEventListener('click', function (e) {
      if (! form.contains(e.target)) results.classList.remove('show');
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { results.classList.remove('show'); input.blur(); }
    });
  })();
</script>
<?= view('partials/datatables_init') ?>
<?= view('partials/confirm_modal') ?>
<?= view('partials/auto_dismiss_alerts') ?>
<?= view('partials/scroll_reveal') ?>
<?= view('partials/password_toggle') ?>
</body>
</html>
