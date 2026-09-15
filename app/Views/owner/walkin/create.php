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
  /* Choices' own default item size reads as oversized next to the rest
     of the page's compact form controls — trimmed down to a normal
     dropdown-menu-ish row height instead. */
  .choices__list--dropdown .choices__item { padding: .55rem .9rem; font-size: .9rem; }
  .choices__list--dropdown { max-height: 260px; }
  .choices__list--dropdown .choices__list { max-height: 260px; }
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
              <div class="form-text">Defaults to the total — raise it if the customer hands over more, to work out change.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Change</label>
              <input type="text" id="wsChangeDisplay" class="form-control" value="₱0.00" disabled>
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
  var productSelect  = document.getElementById('wsProduct');
  var quantityInput  = document.getElementById('wsQuantity');
  var totalDisplay   = document.getElementById('wsTotalDisplay');
  var stockHint      = document.getElementById('wsStockHint');
  var amountPaid     = document.getElementById('wsAmountPaid');
  var changeDisplay  = document.getElementById('wsChangeDisplay');
  var currentTotal   = 0;

  // Source of truth for price/stock — read from this plain object
  // rather than each <option>'s data-price/data-stock attributes,
  // since Choices.js rebuilds its own internal list from the select
  // and doesn't reliably carry custom data-* attributes through to the
  // DOM node recalc() ends up looking at afterward.
  var productData = <?= json_encode(array_reduce($products, static function ($acc, $p) {
      $acc[(string) $p['product_id']] = ['price' => (float) $p['price'], 'stock' => (int) $p['stock']];
      return $acc;
  }, [])) ?>;

  if (typeof Choices !== 'undefined') {
    new Choices(productSelect, {
      searchEnabled: true,
      searchPlaceholderValue: 'Search products…',
      itemSelectText: '',
      shouldSort: false,
    });
  }

  function recalc(productId) {
    // Choices.js's own 'change'/'choice' events carry the picked value
    // straight in event.detail (see onProductPicked below) — used when
    // given, since relying on productSelect.value/selectedIndex after
    // the fact isn't reliably in sync with what Choices just rendered.
    if (productId === undefined) productId = productSelect.value;
    var data  = productId ? productData[productId] : null;
    var price = data ? data.price : 0;
    var stock = data ? data.stock : 0;
    var qty   = parseInt(quantityInput.value, 10) || 0;

    quantityInput.max = stock || '';
    stockHint.textContent = data ? stock + ' available' : '';

    currentTotal = price * qty;
    totalDisplay.value = '₱' + currentTotal.toFixed(2);
    amountPaid.value = currentTotal > 0 ? currentTotal.toFixed(2) : '';
    recalcChange();
  }

  // Change is whatever the customer handed over beyond the total —
  // amount_paid defaults to match the total exactly (see recalc()
  // above), so this reads ₱0.00 until the cashier raises it for an
  // overpayment that needs change given back.
  function recalcChange() {
    var paid   = parseFloat(amountPaid.value) || 0;
    var change = paid - currentTotal;
    changeDisplay.value = '₱' + (change > 0 ? change : 0).toFixed(2);
  }

  // Choices.js dispatches its own 'change' (detail.value) and 'choice'
  // (detail.choice.value) custom events on the underlying <select> when
  // a product is picked — read whichever detail is present rather than
  // trusting productSelect.value/selectedIndex to already be in sync at
  // that point. Falls back to productSelect.value for a plain <select>
  // (e.g. if the Choices.js CDN script failed to load).
  function onProductPicked(event) {
    var productId = productSelect.value;
    if (event && event.detail) {
      if (event.detail.value !== undefined) productId = event.detail.value;
      else if (event.detail.choice && event.detail.choice.value !== undefined) productId = event.detail.choice.value;
    }
    recalc(productId);
  }

  productSelect.addEventListener('change', onProductPicked);
  productSelect.addEventListener('choice', onProductPicked);
  quantityInput.addEventListener('input', function () { recalc(); });
  amountPaid.addEventListener('input', recalcChange);
});
</script>

<?= view('layouts/owner_footer') ?>
