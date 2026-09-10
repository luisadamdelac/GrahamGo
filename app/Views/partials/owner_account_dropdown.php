<?php
/**
 * The avatar + name account dropdown, factored out so it can be included
 * in both the mobile topbar and the desktop topbar — same reasoning as
 * partials/owner_notification_bell.php (this partial can legally appear
 * twice on the same page since Bootstrap dropdowns don't need unique
 * IDs to function, and nothing here needs live-polling updates).
 */
?>
<div class="dropdown">
  <button class="topbar-account-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    <?php if (! empty(current_owner()['avatar'])): ?>
      <img src="<?= cloudinary_resized(avatar_url(current_owner()['avatar']), 68) ?>" class="rounded-circle" style="width:34px;height:34px;object-fit:cover;" alt="">
    <?php else: ?>
      <div class="topbar-avatar-fallback"><i class="bi bi-person-fill"></i></div>
    <?php endif; ?>
    <span class="d-none d-xl-inline"><?= esc(current_owner()['name']) ?></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="<?= site_url('owner/profile') ?>"><i class="bi bi-person-circle"></i> Profile</a></li>
    <li><a class="dropdown-item" href="<?= site_url('owner/settings') ?>"><i class="bi bi-gear-fill"></i> Settings</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item text-danger" href="<?= site_url('owner/logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
  </ul>
</div>
