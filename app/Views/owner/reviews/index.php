<?= view('layouts/owner_header', ['title' => 'Reviews']) ?>

<?php
$statusColors = ['Pending' => 'warning', 'Approved' => 'success', 'Rejected' => 'secondary'];
$statuses     = ['Pending', 'Approved', 'Rejected'];

$ggStars = static function (int $rating): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<i class="bi bi-star' . ($i <= $rating ? '-fill' : '') . '" style="color:var(--gg-primary-dark);"></i>';
    }
    return $out;
};
?>

<h4 class="mb-3 enter">Reviews</h4>

<div class="mb-3 d-flex flex-nowrap gap-2 overflow-auto pb-1">
  <?php foreach ($statuses as $s): ?>
    <a href="<?= site_url('owner/reviews') ?>?status=<?= $s ?>" class="btn btn-sm text-nowrap btn-<?= $status === $s ? 'dark' : 'outline-dark' ?>">
      <?= $s ?> <span class="badge bg-<?= $statusColors[$s] ?> ms-1"><?= $counts[$s] ?></span>
    </a>
  <?php endforeach; ?>
</div>

<div class="d-flex flex-column gap-2">
  <?php foreach ($reviews as $r): ?>
    <div class="card">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
          <div>
            <span class="fw-semibold"><?= esc($r['customer_name']) ?></span>
            <span class="text-muted small">on</span>
            <span class="fw-semibold"><?= esc($r['product_name']) ?></span>
          </div>
          <span class="badge bg-<?= $statusColors[$r['status']] ?? 'secondary' ?>"><?= esc($r['status']) ?></span>
        </div>
        <div class="mb-2"><?= $ggStars((int) $r['rating']) ?></div>
        <?php if ($r['comment']): ?>
          <p class="mb-2"><?= esc($r['comment']) ?></p>
        <?php endif; ?>
        <div class="small text-muted mb-2"><?= date('M d, Y g:i A', strtotime($r['created_at'])) ?></div>
        <?php if ($r['status'] === 'Pending'): ?>
          <div class="d-flex gap-2">
            <?= form_open('owner/reviews/' . $r['review_id'] . '/approve') ?>
              <button type="submit" class="btn btn-sm btn-gg-primary"><i class="bi bi-check2"></i> Approve</button>
            <?= form_close() ?>
            <?= form_open('owner/reviews/' . $r['review_id'] . '/reject') ?>
              <button type="submit" class="btn btn-sm btn-outline-dark" data-confirm="Reject this review? It won't show publicly." data-confirm-variant="danger">Reject</button>
            <?= form_close() ?>
          </div>
        <?php elseif ($r['status'] === 'Rejected'): ?>
          <?= form_open('owner/reviews/' . $r['review_id'] . '/approve') ?>
            <button type="submit" class="btn btn-sm btn-outline-dark">Approve instead</button>
          <?= form_close() ?>
        <?php else: ?>
          <?= form_open('owner/reviews/' . $r['review_id'] . '/reject') ?>
            <button type="submit" class="btn btn-sm btn-outline-dark" data-confirm="Reject this review? It'll stop showing publicly." data-confirm-variant="danger">Unpublish</button>
          <?= form_close() ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($reviews)): ?>
    <div class="card"><div class="card-body text-center text-muted py-4">No <?= strtolower($status) ?> reviews.</div></div>
  <?php endif; ?>
</div>

<?= view('layouts/owner_footer') ?>
