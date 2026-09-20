<?= view('owner/reports/pdf/_layout_top', ['reportTitle' => $reportTitle ?? 'Sales Report', 'from' => $from, 'to' => $to]) ?>

<table class="gg-pdf-table">
  <thead>
    <tr>
      <th style="width:16%;">Date</th>
      <th style="width:9%;">Reservation</th>
      <th style="width:17%;">Customer</th>
      <th style="width:23%;">Product(s)</th>
      <th style="width:6%;">Qty</th>
      <th style="width:10%;">Amount</th>
      <th style="width:9%;">Payment</th>
      <th style="width:10%;">Status</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($byDay ?? false): ?>
      <?php foreach ($dayGroups as $day => $group): ?>
        <tr class="gg-pdf-daygroup">
          <td colspan="8">
            <?= esc(date('l, F j, Y', strtotime($day))) ?>
            <span style="float:right;"><?= $group['count'] ?> sale<?= $group['count'] === 1 ? '' : 's' ?> &middot; &#8369;<?= number_format($group['total'], 2) ?></span>
          </td>
        </tr>
        <?php foreach ($group['rows'] as $s): ?>
          <tr>
            <td><?= esc(date('M d, Y g:i A', strtotime($s['sale_date']))) ?></td>
            <td class="gg-pdf-link">#<?= (int) $s['reservation_id'] ?></td>
            <td class="gg-pdf-link"><?= esc($s['customer_name']) ?></td>
            <td><?= esc($s['product_names']) ?></td>
            <td><?= (int) $s['total_quantity'] ?></td>
            <td>&#8369;<?= number_format($s['total_amount'], 2) ?></td>
            <td><?= esc($s['payment_method']) ?></td>
            <td class="gg-status-<?= strtolower($s['reservation_status']) ?>"><?= esc($s['reservation_status']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <?php if (empty($dayGroups)): ?>
        <tr><td colspan="8" style="text-align:center; color:#7A6858;">No records for this period.</td></tr>
      <?php endif; ?>
    <?php else: ?>
      <?php foreach ($sales as $s): ?>
        <tr>
          <td><?= esc(date('M d, Y g:i A', strtotime($s['sale_date']))) ?></td>
          <td class="gg-pdf-link">#<?= (int) $s['reservation_id'] ?></td>
          <td class="gg-pdf-link"><?= esc($s['customer_name']) ?></td>
          <td><?= esc($s['product_names']) ?></td>
          <td><?= (int) $s['total_quantity'] ?></td>
          <td>&#8369;<?= number_format($s['total_amount'], 2) ?></td>
          <td><?= esc($s['payment_method']) ?></td>
          <td class="gg-status-<?= strtolower($s['reservation_status']) ?>"><?= esc($s['reservation_status']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($sales)): ?>
        <tr><td colspan="8" style="text-align:center; color:#7A6858;">No records for this period.</td></tr>
      <?php endif; ?>
    <?php endif; ?>
  </tbody>
</table>

<p style="margin-top:14px; font-weight:bold; font-size:12px;">Total: &#8369;<?= number_format($total, 2) ?></p>

<?= view('owner/reports/pdf/_layout_bottom') ?>
