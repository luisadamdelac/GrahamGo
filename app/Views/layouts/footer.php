  <footer class="text-center py-4 small d-none d-lg-block">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="" style="width:16px;height:16px;border-radius:50%;object-fit:cover;vertical-align:-2px;"> &copy; <?= date('Y') ?> GrahamGo &mdash; Graham Mango &amp; Oreo Graham
  </footer>
</div>

<?php
$navItems = [
    ['url' => 'home', 'icon' => 'grid-fill', 'label' => 'Products', 'match' => 'home'],
    ['url' => 'my-reservations', 'icon' => 'journal-bookmark-fill', 'label' => 'Reservations', 'match' => 'my-reservations'],
    ['url' => 'profile', 'icon' => 'person-circle', 'label' => 'Profile', 'match' => 'profile'],
];
?>
<nav class="gg-bottomnav d-lg-none">
  <?php foreach ($navItems as $item): ?>
    <a href="<?= site_url($item['url']) ?>" class="<?= str_starts_with(uri_string(), $item['match']) ? 'active' : '' ?>">
      <?php if ($item['url'] === 'profile' && ! empty(current_customer()['avatar'])): ?>
        <img src="<?= cloudinary_resized(avatar_url(current_customer()['avatar']), 40) ?>" class="rounded-circle" style="width:20px;height:20px;object-fit:cover;" alt="">
      <?php else: ?>
        <i class="bi bi-<?= $item['icon'] ?>"></i>
      <?php endif; ?>
      <span><?= $item['label'] ?></span>
    </a>
  <?php endforeach; ?>
  <a href="<?= site_url('logout') ?>">
    <i class="bi bi-box-arrow-right"></i>
    <span>Logout</span>
  </a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= view('partials/datatables_init') ?>
<?= view('partials/confirm_modal') ?>
<?= view('partials/auto_dismiss_alerts') ?>
<?= view('partials/scroll_reveal') ?>
<?= view('partials/password_toggle') ?>
</body>
</html>
