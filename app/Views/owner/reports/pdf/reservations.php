<?= view('owner/reports/pdf/_layout_top', ['reportTitle' => 'Reservation Report', 'from' => $from, 'to' => $to]) ?>

<table class="gg-pdf-table">
  <thead>
    <tr>
      <th style="width:8%;">Reservation</th>
      <th style="width:10%;">Claim Date</th>
      <th style="width:19%;">Customer</th>
      <th style="width:8%;">Type</th>
      <th style="width:19%;">Product</th>
      <th style="width:6%;">Qty</th>
      <th style="width:10%;">Total</th>
      <th style="width:10%;">Payment</th>
      <th style="width:10%;">Status</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($reservations as $r): ?>
      <tr>
        <td>#<?= (int) $r['reservation_id'] ?></td>
        <td><?= esc(date('M d, Y', strtotime($r['claim_date']))) ?></td>
        <td><?= esc($r['customer_name']) ?></td>
        <td><?= esc($r['customer_type']) ?></td>
        <td><?= esc($r['product_name']) ?></td>
        <td><?= (int) $r['quantity'] ?></td>
        <td>&#8369;<?= number_format($r['total_amount'], 2) ?></td>
        <td><?= esc($r['payment_status']) ?></td>
        <td><?= esc($r['status']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($reservations)): ?>
      <tr><td colspan="9" style="text-align:center; color:#7A6858;">No records for this period.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?= view('owner/reports/pdf/_layout_bottom') ?>
