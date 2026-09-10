<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= esc($title ?? 'GrahamGo') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/app.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/app.css') ?>" rel="stylesheet">
<link rel="icon" href="<?= base_url('assets/img/logo.png') ?>">
</head>
<body class="has-bottomnav">

<?php
$navItems = [
    ['url' => 'home', 'icon' => 'grid-fill', 'label' => 'Products', 'match' => 'home'],
    ['url' => 'my-reservations', 'icon' => 'journal-bookmark-fill', 'label' => 'Reservations', 'match' => 'my-reservations'],
    ['url' => 'profile', 'icon' => 'person-circle', 'label' => 'Profile', 'match' => 'profile'],
];
?>

<nav class="navbar navbar-expand-lg gg-topbar">
  <div class="container">
    <a class="navbar-brand" href="<?= site_url('home') ?>">
      <img src="<?= base_url('assets/img/logo.png') ?>" alt="GrahamGo" style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;">
      GrahamGo
    </a>

    <a href="<?= site_url('logout') ?>" class="d-lg-none text-danger fs-5 ms-auto"><i class="bi bi-box-arrow-right"></i></a>

    <div class="collapse navbar-collapse d-none d-lg-flex">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <?php foreach ($navItems as $item): ?>
          <li class="nav-item">
            <a class="nav-link <?= str_starts_with(uri_string(), $item['match']) ? 'active' : '' ?>" href="<?= site_url($item['url']) ?>">
              <?php if ($item['url'] === 'profile' && ! empty(current_customer()['avatar'])): ?>
                <img src="<?= cloudinary_resized(avatar_url(current_customer()['avatar']), 40) ?>" class="rounded-circle" style="width:20px;height:20px;object-fit:cover;" alt="">
              <?php else: ?>
                <i class="bi bi-<?= $item['icon'] ?>"></i>
              <?php endif; ?>
              <?= $item['label'] ?>
            </a>
          </li>
        <?php endforeach; ?>
        <li class="nav-item"><a class="nav-link text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mb-4 mt-4">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success fade show d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill"></i> <?= esc(session()->getFlashdata('success')) ?>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger fade show d-flex align-items-center gap-2">
      <i class="bi bi-exclamation-triangle-fill"></i> <?= esc(session()->getFlashdata('error')) ?>
    </div>
  <?php endif; ?>
