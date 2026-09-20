<?= view('owner/reports/pdf/_layout_top', ['reportTitle' => $reportTitle ?? 'Inventory Report', 'from' => $from ?? null, 'to' => $to ?? null]) ?>

<?php if ($byDay ?? false): ?>
  <table class="gg-pdf-table" style="margin-bottom: 14px;">
    <thead>
      <tr>
        <th style="width:13%;">Time</th>
        <th style="width:30%;">Product</th>
        <th style="width:17%;">Type</th>
        <th style="width:10%;">Qty</th>
        <th style="width:30%;">Notes</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dayGroups as $day => $group): ?>
        <tr class="gg-pdf-daygroup">
          <td colspan="5">
            <?= esc(date('l, F j, Y', strtotime($day))) ?>
            <span style="float:right;"><?= $group['count'] ?> transaction<?= $group['count'] === 1 ? '' : 's' ?> &middot; <?= esc(stock_change_label($group['total'])) ?></span>
          </td>
        </tr>
        <?php foreach ($group['rows'] as $t): ?>
          <tr>
            <td><?= esc(date('g:i A', strtotime($t['transaction_date']))) ?></td>
            <td><?= esc($t['product_name']) ?></td>
            <td><?= esc($t['transaction_type']) ?></td>
            <td><?= (int) $t['quantity'] ?></td>
            <td><?= esc($t['notes'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <?php if (empty($dayGroups)): ?>
        <tr><td colspan="5" style="text-align:center; color:#7A6858;">No stock activity for this period.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <table class="gg-pdf-table">
    <thead>
      <tr>
        <th style="width:60%;">Current Stock</th>
        <th style="width:20%;">Available</th>
        <th style="width:20%;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($summary as $row): ?>
        <?php $ggLowStock = $row['available'] <= $row['product']['reorder_level']; ?>
        <tr>
          <td><?= esc($row['product']['product_name']) ?></td>
          <td><?= (int) $row['available'] ?></td>
          <td class="gg-status-<?= $ggLowStock ? 'low' : 'ok' ?>"><?= $ggLowStock ? 'Low Stock' : 'OK' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($summary)): ?>
        <tr><td colspan="3" style="text-align:center; color:#7A6858;">No products yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
<?php else: ?>
  <table class="gg-pdf-table">
    <thead>
      <tr>
        <th style="width:35%;">Product</th>
        <th style="width:18%;">Reserved (to date)</th>
        <th style="width:15%;">Sold (to date)</th>
        <th style="width:17%;">Currently Available</th>
        <th style="width:15%;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($summary as $row): ?>
        <?php $ggLowStock = $row['available'] <= $row['product']['reorder_level']; ?>
        <tr>
          <td class="gg-pdf-link"><?= esc($row['product']['product_name']) ?></td>
          <td><?= (int) $row['reserved'] ?></td>
          <td><?= (int) $row['sold'] ?></td>
          <td><?= (int) $row['available'] ?></td>
          <td class="gg-status-<?= $ggLowStock ? 'low' : 'ok' ?>"><?= $ggLowStock ? 'Low Stock' : 'OK' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($summary)): ?>
        <tr><td colspan="5" style="text-align:center; color:#7A6858;">No products yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
<?php endif; ?>

<?= view('owner/reports/pdf/_layout_bottom') ?>
