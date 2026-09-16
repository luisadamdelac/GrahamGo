<?= view('layouts/owner_header', ['title' => 'Walk-in Sale']) ?>

<style>
  /* Standard "hidden but still a real, focusable/validatable form
     field" pattern — position:absolute + a 1x1 box instead of
     display:none, so the browser's own required-field validation still
     works on it (a display:none required field can silently block
     submit in some browsers with no visible error to explain why). */
  .gg-visually-hidden {
    position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
  }
  .gg-custom-select { position: relative; }
  .gg-custom-select-list {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 20;
    background: #fff; border: 1.5px solid var(--gg-border); border-radius: var(--gg-radius-sm);
    box-shadow: var(--gg-shadow); max-height: 260px; overflow-y: auto;
  }
  .gg-custom-select-option { padding: .6rem .9rem; font-size: .9rem; cursor: pointer; }
  .gg-custom-select-option:hover, .gg-custom-select-option.is-active { background: var(--gg-primary-light); color: var(--gg-cocoa); }
</style>

<h4 class="mb-1 enter"><i class="bi bi-cash-coin" style="color:var(--gg-primary-dark);"></i> Walk-in Sale</h4>
<p class="text-muted mb-4">For a customer buying and paying on the spot. Deducts stock and records the sale immediately, no online reservation needed.</p>

<div class="row justify-content-center">
  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-body p-4">
        <?= form_open('owner/walk-in-sale') ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Product</label>
              <!-- Hand-rolled dropdown, no library — a native <select>'s
                   OS-rendered option list can't be sized via CSS at all
                   (especially iOS), but a third-party JS enhancer
                   (Choices.js) broke the real functionality here three
                   times over before being reverted. This is plain
                   markup/CSS/JS instead: the real <select> stays the
                   actual form field and event source (recalc() below is
                   untouched), just visually hidden — gg-custom-select is
                   a separate div built purely for compact display. -->
              <div class="gg-custom-select" id="wsProductCustom">
                <button type="button" class="form-select text-start" id="wsProductTrigger">Select product</button>
                <div class="gg-custom-select-list d-none" id="wsProductList">
                  <?php foreach ($products as $p): ?>
                    <div class="gg-custom-select-option" data-value="<?= $p['product_id'] ?>">
                      <?= esc($p['product_name']) ?> (₱<?= number_format($p['price'], 2) ?>, <?= (int) $p['stock'] ?> in stock)
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <select name="product_id" id="wsProduct" class="gg-visually-hidden" required>
                <option value="">Select product</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p['product_id'] ?>" data-price="<?= esc($p['price'], 'attr') ?>" data-stock="<?= (int) $p['stock'] ?>">
                    <?= esc($p['product_name']) ?> (₱<?= number_format($p['price'], 2) ?>, <?= (int) $p['stock'] ?> in stock)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
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
              <div class="gg-custom-select" id="wsPaymentMethodCustom">
                <button type="button" class="form-select text-start" id="wsPaymentMethodTrigger">Cash</button>
                <div class="gg-custom-select-list d-none" id="wsPaymentMethodList">
                  <div class="gg-custom-select-option is-active" data-value="Cash">Cash</div>
                  <div class="gg-custom-select-option" data-value="GCash">GCash</div>
                </div>
              </div>
              <select name="payment_method" id="wsPaymentMethod" class="gg-visually-hidden" required>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>
            <!-- Only Cash has a real "handed over more, give change back"
                 scenario — GCash is a digital transfer of the exact
                 amount, so there's nothing to enter or make change for;
                 the field stays in the DOM (JS keeps it synced to the
                 total) so it still submits, just hidden — Total Amount
                 above already says everything GCash needs to. -->
            <div class="col-md-6" id="wsAmountPaidWrap">
              <label class="form-label">Amount Paid</label>
              <input type="number" step="0.01" min="0" name="amount_paid" id="wsAmountPaid" class="form-control" required>
              <div class="form-text">Defaults to the total. Raise it if the customer hands over more, to work out change.</div>
            </div>
            <div class="col-md-6" id="wsChangeWrap">
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
  var paymentMethod  = document.getElementById('wsPaymentMethod');
  var amountPaidWrap = document.getElementById('wsAmountPaidWrap');
  var amountPaid     = document.getElementById('wsAmountPaid');
  var changeWrap     = document.getElementById('wsChangeWrap');
  var changeDisplay  = document.getElementById('wsChangeDisplay');
  var currentTotal   = 0;

  // Hand-rolled dropdown, no library — a native <select>'s OS-rendered
  // option list can't be sized via CSS at all (especially iOS), but a
  // third-party JS enhancer (Choices.js) broke real functionality here
  // three times over before being reverted. Picking a custom option sets
  // the real (visually-hidden) <select>'s value and fires a real
  // 'change' event on it, so everything downstream (recalc,
  // applyPaymentMethod, form submission) behaves exactly as if it had
  // been picked from a native <select> — the custom div/list is only
  // ever a display layer on top of that real one.
  function initCustomSelect(wrapId, selectEl, triggerId, listId) {
    var wrap    = document.getElementById(wrapId);
    var trigger = document.getElementById(triggerId);
    var list    = document.getElementById(listId);

    trigger.addEventListener('click', function () {
      list.classList.toggle('d-none');
    });

    list.querySelectorAll('.gg-custom-select-option').forEach(function (optionEl) {
      optionEl.addEventListener('click', function () {
        selectEl.value = optionEl.getAttribute('data-value');
        trigger.textContent = optionEl.textContent.trim();
        list.querySelectorAll('.gg-custom-select-option').forEach(function (o) { o.classList.remove('is-active'); });
        optionEl.classList.add('is-active');
        list.classList.add('d-none');
        selectEl.dispatchEvent(new Event('change'));
      });
    });

    document.addEventListener('click', function (e) {
      if (! wrap.contains(e.target)) list.classList.add('d-none');
    });
  }

  initCustomSelect('wsProductCustom', productSelect, 'wsProductTrigger', 'wsProductList');
  initCustomSelect('wsPaymentMethodCustom', paymentMethod, 'wsPaymentMethodTrigger', 'wsPaymentMethodList');

  function recalc() {
    var opt = productSelect.options[productSelect.selectedIndex];
    var price = opt ? parseFloat(opt.getAttribute('data-price')) || 0 : 0;
    var stock = opt ? parseInt(opt.getAttribute('data-stock'), 10) || 0 : 0;
    var qty   = parseInt(quantityInput.value, 10) || 0;

    quantityInput.max = stock || '';
    stockHint.textContent = opt && opt.value ? stock + ' available' : '';

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

  // GCash is a digital transfer of the exact amount owed — there's no
  // "handed over more, give change back" scenario for it the way there
  // is for physical Cash, so neither Amount Paid nor Change adds
  // anything beyond what Total Amount already says; both hide, while
  // the (still-required) amount_paid input stays synced to the total
  // in the background so the form still submits it correctly.
  function applyPaymentMethod() {
    var isGcash = paymentMethod.value === 'GCash';
    amountPaidWrap.classList.toggle('d-none', isGcash);
    changeWrap.classList.toggle('d-none', isGcash);
    if (isGcash) {
      amountPaid.value = currentTotal > 0 ? currentTotal.toFixed(2) : '';
      recalcChange();
    }
  }

  productSelect.addEventListener('change', recalc);
  quantityInput.addEventListener('input', recalc);
  amountPaid.addEventListener('input', recalcChange);
  paymentMethod.addEventListener('change', applyPaymentMethod);
  applyPaymentMethod();
});
</script>

<?= view('layouts/owner_footer') ?>
