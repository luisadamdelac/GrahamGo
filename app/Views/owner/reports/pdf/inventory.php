<?= view('owner/reports/pdf/_layout_top', ['reportTitle' => 'Inventory Report']) ?>

<table class="gg-pdf-table">
  <thead>
    <tr><th>Product</th><th>Reserved (to date)</th><th>Sold (to date)</th><th>Currently Available</th><th>Status</th></tr>
  </thead>
  <tbody>
    <?php foreach ($summary as $row): ?>
      <tr>
        <td><?= esc($row['product']['product_name']) ?></td>
        <td><?= (int) $row['reserved'] ?></td>
        <td><?= (int) $row['sold'] ?></td>
        <td><?= (int) $row['available'] ?></td>
        <td><?= $row['available'] <= $row['product']['reorder_level'] ? 'Low Stock' : 'OK' ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($summary)): ?>
      <tr><td colspan="5" style="text-align:center; color:#7A6858;">No products yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?= view('owner/reports/pdf/_layout_bottom') ?>
