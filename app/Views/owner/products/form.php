<?= view('layouts/owner_header', ['title' => $product ? 'Edit Product' : 'Add Product']) ?>

<h4 class="mb-4"><?= $product ? 'Edit Product' : 'Add Product' ?></h4>

<div class="card shadow-sm" style="max-width:600px;">
  <div class="card-body p-4">
    <?= form_open_multipart($product ? 'owner/products/' . $product['product_id'] : 'owner/products') ?>
      <div class="mb-3">
        <label class="form-label">Product Photo</label>
        <div class="d-flex align-items-center gap-3">
          <?php if (! empty($product['image'])): ?>
            <img id="productImagePreview" src="<?= product_image_url($product['image']) ?>" class="rounded" style="width:72px;height:72px;object-fit:cover;" alt="">
          <?php else: ?>
            <div id="productImagePreview" class="rounded d-flex align-items-center justify-content-center" style="width:72px;height:72px;background:var(--gg-primary-light);">
              <i class="bi bi-cake-fill" style="color:var(--gg-primary-dark);"></i>
            </div>
          <?php endif; ?>
          <div>
            <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm" onchange="ggPreviewProductImage(this)">
            <div class="form-text mb-0">JPG, PNG, or WEBP. Max 2MB. Shown on the landing page and product listing.</div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Product Name</label>
        <input type="text" name="product_name" class="form-control" value="<?= esc(old('product_name', $product['product_name'] ?? '')) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= esc(old('description', $product['description'] ?? '')) ?></textarea>
      </div>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Price (₱)</label>
          <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= esc(old('price', $product['price'] ?? '')) ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Stock</label>
          <input type="number" min="0" name="stock" class="form-control" value="<?= esc(old('stock', $product['stock'] ?? 0)) ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Low Stock Alert Level</label>
          <input type="number" min="0" name="reorder_level" class="form-control" value="<?= esc(old('reorder_level', $product['reorder_level'] ?? 5)) ?>">
          <div class="form-text mb-0">You'll be alerted when stock drops to this number or below.</div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="Active" <?= ($product['status'] ?? 'Active') === 'Active' ? 'selected' : '' ?>>Active</option>
          <option value="Inactive" <?= ($product['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <button type="submit" class="btn btn-gg-primary w-100 w-md-auto"><i class="bi bi-check2"></i> Save Product</button>
      <a href="<?= site_url('owner/products') ?>" class="btn btn-link">Cancel</a>
    <?= form_close() ?>
  </div>
</div>

<script>
function ggPreviewProductImage(input) {
  if (! input.files || ! input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function (e) {
    var img = document.createElement('img');
    img.id = 'productImagePreview';
    img.src = e.target.result;
    img.className = 'rounded';
    img.style.width = '72px';
    img.style.height = '72px';
    img.style.objectFit = 'cover';
    document.getElementById('productImagePreview').replaceWith(img);
  };
  reader.readAsDataURL(input.files[0]);
}
</script>

<?= view('layouts/owner_footer') ?>
