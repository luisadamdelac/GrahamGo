<?= view('layouts/owner_header', ['title' => 'Dashboard']) ?>

<div class="mb-3 mb-md-4 enter">
  <h4 class="mb-1">Dashboard</h4>
  <p class="text-muted small mb-0">Welcome back, <?= esc(current_owner()['name']) ?>.</p>
</div>

<div class="row g-2 g-md-3 mb-2 mb-md-3">
  <div class="col-6 col-lg-3 reveal" style="--reveal-delay: 0s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-info);"><i class="bi bi-journal-text"></i></div>
      <div>
        <div class="stat-value"><?= $counts['total'] ?></div>
        <div class="stat-label">Total Reservations</div>
      </div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3 reveal" style="--reveal-delay: .05s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-warning);"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="stat-value"><?= $counts['pending'] ?></div>
        <div class="stat-label">Pending</div>
      </div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3 reveal" style="--reveal-delay: .1s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-info);"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="stat-value"><?= $counts['confirmed'] ?></div>
        <div class="stat-label">Confirmed</div>
      </div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3 reveal" style="--reveal-delay: .15s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-success);"><i class="bi bi-check-circle-fill"></i></div>
      <div>
        <div class="stat-value"><?= $counts['claimed'] ?></div>
        <div class="stat-label">Claimed</div>
      </div>
    </div></div>
  </div>
</div>

<div class="row g-2 g-md-3 mb-3 mb-md-4">
  <div class="col-6 col-lg-4 reveal" style="--reveal-delay: .2s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:linear-gradient(135deg, var(--gg-primary), var(--gg-primary-dark));"><i class="bi bi-cash-coin"></i></div>
      <div>
        <div class="stat-value">₱<?= number_format($totalSales, 2) ?></div>
        <div class="stat-label">Total Sales</div>
      </div>
    </div></div>
  </div>
  <div class="col-6 col-lg-4 reveal" style="--reveal-delay: .25s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-secondary);"><i class="bi bi-box-seam-fill"></i></div>
      <div>
        <div class="stat-value"><?= $productsSold ?></div>
        <div class="stat-label">Products Sold</div>
      </div>
    </div></div>
  </div>
  <div class="col-12 col-lg-4 reveal" style="--reveal-delay: .3s;">
    <div class="card stat-card"><div class="card-body p-3">
      <div class="stat-icon" style="background:var(--gg-danger);"><i class="bi bi-exclamation-triangle-fill"></i></div>
      <div>
        <div class="stat-value" style="color:var(--gg-danger);"><?= $lowStock ?></div>
        <div class="stat-label">Low-Stock Products</div>
      </div>
    </div></div>
  </div>
</div>

<div class="row g-2 g-md-3 mb-3 mb-md-4">
  <div class="col-12 col-lg-8 reveal" style="--reveal-delay: .35s;">
    <div class="card h-100"><div class="card-body p-3 p-md-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h6 class="mb-0">Sales Trend</h6>
        <div class="btn-group btn-group-sm" role="group" id="ggSalesTrendRange">
          <button type="button" class="btn btn-dark" data-range="week">Week</button>
          <button type="button" class="btn btn-outline-dark" data-range="month">Month</button>
          <button type="button" class="btn btn-outline-dark" data-range="year">Year</button>
        </div>
      </div>
      <div style="position:relative; height:240px;">
        <canvas id="ggSalesTrendChart"
          data-labels='<?= esc(json_encode(array_map(static fn ($d) => date('M j', strtotime($d['date'])), $salesTrend)), 'attr') ?>'
          data-values='<?= esc(json_encode(array_map(static fn ($d) => $d['total'], $salesTrend)), 'attr') ?>'></canvas>
      </div>
    </div></div>
  </div>
  <div class="col-12 col-lg-4 reveal" style="--reveal-delay: .4s;">
    <div class="card h-100"><div class="card-body p-3 p-md-4">
      <h6 class="mb-3">Reservation Status</h6>
      <div style="position:relative; height:200px;">
        <canvas id="ggReservationStatusChart"
          data-labels='<?= esc(json_encode(['Pending', 'Confirmed', 'Ready', 'Claimed', 'Cancelled']), 'attr') ?>'
          data-values='<?= esc(json_encode([$counts['pending'], $counts['confirmed'], $counts['ready'], $counts['claimed'], $counts['cancelled']])) ?>'></canvas>
      </div>
      <?php if (array_sum([$counts['pending'], $counts['confirmed'], $counts['ready'], $counts['claimed'], $counts['cancelled']]) === 0): ?>
        <p class="text-center text-muted small mb-0 mt-2">No reservations yet.</p>
      <?php endif; ?>
    </div></div>
  </div>
</div>

<?php $upcomingStatusColors = ['Pending' => 'warning', 'Confirmed' => 'info', 'Ready' => 'primary']; ?>

<h6 class="mb-3"><i class="bi bi-bag-check-fill" style="color:var(--gg-primary-dark);"></i> Needs Attention</h6>

<!-- Mobile: card list -->
<div class="d-lg-none d-flex flex-column gap-2">
  <?php if (empty($upcoming)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4"><i class="bi bi-emoji-smile"></i> Nothing needs attention right now.</div></div>
  <?php else: foreach ($upcoming as $r): ?>
    <div class="card">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <span class="fw-semibold">#<?= $r['reservation_id'] ?> &middot; <?= esc($r['customer_name']) ?></span>
          <span class="badge bg-<?= $upcomingStatusColors[$r['status']] ?? 'secondary' ?>"><?= esc($r['status']) ?></span>
        </div>
        <div class="small text-muted mb-2"><?= date('M d, Y', strtotime($r['claim_date'])) ?></div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="fw-bold">₱<?= number_format($r['total_amount'], 2) ?></span>
          <div class="d-flex gap-2">
            <?php if ($r['status'] === 'Pending'): ?>
              <?= form_open('owner/reservations/' . $r['reservation_id'] . '/confirm') ?>
                <button type="submit" class="btn btn-sm btn-gg-primary">Confirm</button>
              <?= form_close() ?>
            <?php elseif ($r['status'] === 'Confirmed'): ?>
              <?= form_open('owner/reservations/' . $r['reservation_id'] . '/ready') ?>
                <button type="submit" class="btn btn-sm btn-gg-primary">Mark Ready</button>
              <?= form_close() ?>
            <?php endif; ?>
            <a href="<?= site_url('owner/reservations/' . $r['reservation_id']) ?>" class="btn btn-sm btn-outline-dark">View</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-lg-block">
  <table class="table align-middle mb-0 dg-table-compact">
    <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Claim Date</th><th>Total</th><th>Status</th><th class="no-sort">Action</th></tr></thead>
    <tbody>
      <?php if (empty($upcoming)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-emoji-smile"></i> Nothing needs attention right now.</td></tr>
      <?php else: foreach ($upcoming as $r): ?>
        <tr>
          <td class="fw-semibold">#<?= $r['reservation_id'] ?></td>
          <td><?= esc($r['customer_name']) ?></td>
          <td><?= date('M d, Y', strtotime($r['claim_date'])) ?></td>
          <td>₱<?= number_format($r['total_amount'], 2) ?></td>
          <td><span class="badge bg-<?= $upcomingStatusColors[$r['status']] ?? 'secondary' ?>"><?= esc($r['status']) ?></span></td>
          <td class="text-nowrap">
            <div class="d-flex gap-2">
              <a href="<?= site_url('owner/reservations/' . $r['reservation_id']) ?>" class="btn btn-sm btn-outline-dark">View</a>
              <?php if ($r['status'] === 'Pending'): ?>
                <?= form_open('owner/reservations/' . $r['reservation_id'] . '/confirm') ?>
                  <button type="submit" class="btn btn-sm btn-gg-primary text-nowrap"><i class="bi bi-check2"></i> Confirm</button>
                <?= form_close() ?>
              <?php elseif ($r['status'] === 'Confirmed'): ?>
                <?= form_open('owner/reservations/' . $r['reservation_id'] . '/ready') ?>
                  <button type="submit" class="btn btn-sm btn-gg-primary text-nowrap"><i class="bi bi-bag-check-fill"></i> Mark Ready</button>
                <?= form_close() ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof Chart === 'undefined') return;

  var css = getComputedStyle(document.documentElement);
  var v = function (name, fallback) { var val = css.getPropertyValue(name).trim(); return val || fallback; };

  var trendEl = document.getElementById('ggSalesTrendChart');
  if (trendEl) {
    var labels = JSON.parse(trendEl.getAttribute('data-labels'));
    var values = JSON.parse(trendEl.getAttribute('data-values'));
    var primary = v('--gg-primary', '#E08A3E');

    var trendChart = new Chart(trendEl, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Sales (₱)',
          data: values,
          borderColor: primary,
          backgroundColor: primary + '26',
          fill: true,
          tension: .35,
          pointRadius: 3,
          pointBackgroundColor: primary,
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          borderWidth: 2.5,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: v('--gg-border', '#F0E4D6') }, ticks: { callback: function (val) { return '₱' + val; } } },
          x: { grid: { display: false } },
        },
      },
    });

    // Week/Month/Year toggle — refetches labels/values for the chosen
    // range and swaps them into the existing chart instead of rebuilding
    // it, so the transition animates instead of flashing blank.
    var rangeGroup = document.getElementById('ggSalesTrendRange');
    if (rangeGroup) {
      rangeGroup.querySelectorAll('[data-range]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (btn.classList.contains('btn-dark')) return;

          rangeGroup.querySelectorAll('[data-range]').forEach(function (b) {
            b.classList.remove('btn-dark');
            b.classList.add('btn-outline-dark');
          });
          btn.classList.remove('btn-outline-dark');
          btn.classList.add('btn-dark');

          fetch('<?= site_url('owner/dashboard/sales-trend') ?>?range=' + btn.getAttribute('data-range'), { credentials: 'same-origin' })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (data) {
              if (! data) return;
              trendChart.data.labels = data.labels;
              trendChart.data.datasets[0].data = data.values;
              trendChart.update();
            })
            .catch(function () { /* leave the chart showing whatever it last had */ });
        });
      });
    }
  }

  var statusEl = document.getElementById('ggReservationStatusChart');
  if (statusEl) {
    var sLabels = JSON.parse(statusEl.getAttribute('data-labels'));
    var sValues = JSON.parse(statusEl.getAttribute('data-values'));

    new Chart(statusEl, {
      type: 'doughnut',
      data: {
        labels: sLabels,
        datasets: [{
          data: sValues,
          backgroundColor: [v('--gg-warning'), v('--gg-info'), v('--gg-primary'), v('--gg-success'), v('--gg-danger')],
          borderColor: '#fff',
          borderWidth: 2,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12, font: { size: 11 } } } },
      },
    });
  }
});
</script>
<?= view('layouts/owner_footer') ?>
