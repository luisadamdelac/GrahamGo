<?= view('layouts/owner_header', ['title' => 'Walk-in Sale']) ?>

<style>
  /* Matches .form-control/.form-select's own look (see app.css) so the
     Choices.js-enhanced Product field reads as the same kind of input
     as everything around it, not a visibly different widget. */
  .choices__inner {
    border-radius: var(--gg-radius-sm) !important;
    border: 1.5px solid var(--gg-border) !important;
    padding: .43rem .9rem !important;
    background: #fff !important;
    min-height: 44px;
  }
  .choices.is-focused .choices__inner { border-color: var(--gg-primary) !important; box-shadow: 0 0 0 .22rem rgba(224, 138, 62, .16); }
  .choices__list--dropdown, .choices__list[aria-expanded] {
    border-radius: var(--gg-radius-sm) !important;
    border: 1.5px solid var(--gg-border) !important;
    box-shadow: var(--gg-shadow);
  }
  .choices__list--dropdown .choices__item--selectable.is-highlighted { background: var(--gg-primary-light) !important; color: var(--gg-cocoa); }
  .choices__list--dropdown .choices__input { margin: 0; }
</style>

<h4 class="mb-1 enter"><i class="bi bi-cash-coin" style="color:var(--gg-primary-dark);"></i> Walk-in Sale</h4>
<p class="text-muted mb-4">For a customer buying and paying on the spot — deducts stock and records the sale immediately, no online reservation needed.</p>

<div class="row justify-content-center">
  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-body p-4">
        <?= form_open('owner/walk-in-sale') ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Product</label>
              <select name="product_id" id="wsProduct" class="form-select" required>
                <option value="">Select product</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p['product_id'] ?>" data-price="<?= esc($p['price'], 'attr') ?>" data-stock="<?= (int) $p['stock'] ?>">
                    <?= esc($p['product_name']) ?> — ₱<?= number_format($p['price'], 2) ?> (<?= (int) $p['stock'] ?> in stock)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <!-- Choices.js enhances the plain <select> above into a
                 searchable, custom-styled dropdown instead of the OS's
                 own plain popup list — the underlying <select> stays the
                 real source of truth (Choices just wraps/hides it and
                 keeps it in sync), so nothing else about how this form
                 submits or how recalc() reads it needed to change. -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
            <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
            <div class="col-md-6">
              <label class="form-label">Quantity</label>
              <input type="number" name="quantity" id="wsQuantity" class="form-control" min="1" value="1" required>
              <div class="form-text" id="wsStockHint"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Total Amount</label>
              <input type="text" id="wsTotalDisplay" class="form-control" value="₱0.00" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Payment Method</label>
              <select name="payment_method" class="form-select" required>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Amount Paid</label>
              <input type="number" step="0.01" min="0" name="amount_paid" id="wsAmountPaid" class="form-control" required>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-gg-primary"><i class="bi bi-check2-circle"></i> Record Sale</button>
            <a href="<?= site_url('owner/reports/sales') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var productSelect = document.getElementById('wsProduct');
  var quantityInput  = document.getElementById('wsQuantity');
  var totalDisplay   = document.getElementById('wsTotalDisplay');
  var stockHint      = document.getElementById('wsStockHint');
  var amountPaid     = document.getElementById('wsAmountPaid');

  if (typeof Choices !== 'undefined') {
    // silent: false (default) — Choices still fires a real 'change'
    // event on the underlying <select> whenever a choice is picked, so
    // the existing productSelect.addEventListener('change', recalc)
    // below keeps working unmodified.
    new Choices(productSelect, {
      searchEnabled: true,
      searchPlaceholderValue: 'Search products…',
      itemSelectText: '',
      shouldSort: false,
    });
  }

  function recalc() {
    var opt = productSelect.options[productSelect.selectedIndex];
    var price = opt ? parseFloat(opt.getAttribute('data-price')) || 0 : 0;
    var stock = opt ? parseInt(opt.getAttribute('data-stock'), 10) || 0 : 0;
    var qty   = parseInt(quantityInput.value, 10) || 0;

    quantityInput.max = stock || '';
    stockHint.textContent = opt && opt.value ? stock + ' available' : '';

    var total = price * qty;
    totalDisplay.value = '₱' + total.toFixed(2);
    amountPaid.value = total > 0 ? total.toFixed(2) : '';
  }

  productSelect.addEventListener('change', recalc);
  quantityInput.addEventListener('input', recalc);
});
</script>

<?= view('layouts/owner_footer') ?>
