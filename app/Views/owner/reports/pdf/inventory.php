<?= view('owner/reports/pdf/_layout_top', ['reportTitle' => 'Inventory Report']) ?>

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

<?= view('owner/reports/pdf/_layout_bottom') ?>
