<?= view('layouts/owner_header', ['title' => 'Customers']) ?>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3 enter">
  <h4 class="mb-0">Customers</h4>
  <?php if (! empty($q)): ?>
    <span class="badge bg-info">Search: "<?= esc($q) ?>"</span>
    <a href="<?= site_url('owner/customers') ?>" class="small text-muted">Clear</a>
  <?php endif; ?>
</div>

<!-- Mobile: card list -->
<div class="d-lg-none d-flex flex-column gap-2">
  <?php foreach ($customers as $c): ?>
    <a href="<?= site_url('owner/customers/' . $c['user_id']) ?>" class="card text-decoration-none text-reset">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <?= avatar_chip($c['name'], $c['avatar'], 42) ?>
        <div class="flex-grow-1" style="min-width:0;">
          <div class="fw-semibold"><?= esc($c['name']) ?></div>
          <div class="small text-muted"><?= esc($c['email']) ?></div>
          <div class="small text-muted"><?= esc($c['contact_number']) ?> &middot; <?= esc($c['customer_type']) ?></div>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
  <?php if (empty($customers)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4"><?= $q ? 'No customers match "' . esc($q) . '".' : 'No customers yet.' ?></div></div>
  <?php endif; ?>
</div>

<!-- Desktop: table -->
<div class="table-responsive d-none d-lg-block">
  <table class="table align-middle mb-0 dg-table">
    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Contact</th><th>Type</th><th>Joined</th><th class="no-sort"></th></tr></thead>
    <tbody>
      <?php foreach ($customers as $c): ?>
        <tr>
          <td><div class="d-flex align-items-center gap-2"><?= avatar_chip($c['name'], $c['avatar'], 32) ?> <?= esc($c['name']) ?></div></td>
          <td><?= esc($c['email']) ?></td>
          <td><?= esc($c['contact_number']) ?></td>
          <td><?= esc($c['customer_type']) ?></td>
          <td><?= $c['created_at'] ? date('M d, Y', strtotime($c['created_at'])) : '-' ?></td>
          <td><a href="<?= site_url('owner/customers/' . $c['user_id']) ?>" class="btn btn-sm btn-outline-dark">View</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($customers)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4"><?= $q ? 'No customers match "' . esc($q) . '".' : 'No customers yet.' ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= view('layouts/owner_footer') ?>
