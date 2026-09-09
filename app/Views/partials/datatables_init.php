<!--
  Adds search, column sorting, and pagination to every desktop table
  marked with class="dg-table" (mobile keeps its own separate card-list
  layout — see partials/mobile_card_search.php for that). Columns whose
  header carries class="no-sort" are action/button columns (View,
  Manage, Adjust, ...) with nothing meaningful to sort by. `order: []`
  keeps each table in the same order the server already sent it in
  until the admin/customer actually clicks a header.

  class="dg-table-compact" is the same thing but for small, bounded
  "glance" widgets (e.g. the dashboard's Needs Attention list) — sorting
  stays, search/length/pagination chrome is dropped.

  Everything below runs on DOMContentLoaded rather than immediately —
  confirmed necessary (not just cautious) by the same class of bug this
  script itself once had, and that partials/mobile_card_search.php later
  turned out to have too: code that queries for an element the instant
  the script tag is parsed can run before that element exists yet.
  Deferring to DOMContentLoaded removes any doubt about it.
-->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // A "back"/"forward" navigation can restore this exact page from the
  // browser's cache (bfcache) with the DOM already transformed by
  // DataTables from the last time it loaded, but without the JS-side
  // memory of having done that — so the init below would run again on a
  // table that no longer looks like the plain one it expects. A full
  // reload on a restored page guarantees a clean DOM + script state.
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) location.reload();
  });

  if (typeof jQuery === 'undefined' || ! jQuery.fn.DataTable) {
    console.error('[dg-search] jQuery or DataTables did not load — search/sort/pagination unavailable on this page.');
    return;
  }

  // DataTables' default error handling is a blocking window.alert() —
  // for a warning as harmless as this one (worst case: that one table
  // just doesn't get search/sort/pagination), freezing the entire page
  // behind a dialog is worse than the warning itself.
  jQuery.fn.dataTable.ext.errMode = 'none';

  var baseOptions = {
    order: [],
    columnDefs: [{ orderable: false, targets: 'no-sort' }],
    language: {
      search: '',
      searchPlaceholder: 'Search...',
      lengthMenu: 'Show _MENU_',
      info: 'Showing _START_-_END_ of _TOTAL_',
      infoEmpty: 'No records',
      infoFiltered: '(filtered from _MAX_)',
      paginate: { previous: '‹', next: '›' },
    },
  };

  document.querySelectorAll('.dg-table').forEach(function (tableEl) {
    var $table = jQuery(tableEl);
    if (jQuery.fn.DataTable.isDataTable($table)) return;

    var dt = $table.DataTable(jQuery.extend({}, baseOptions, {
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
    }));

    // .DataTable() above builds the search box synchronously — by this
    // line it already exists as a real element in the DOM, so a plain,
    // direct (non-delegated) query + bind here is reliable.
    var searchInput = tableEl.closest('.dataTables_wrapper').querySelector('.dataTables_filter input');
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        dt.search(searchInput.value).draw();
      });
    } else {
      console.warn('[dg-search] search box not found for table', tableEl.id);
    }
  });

  document.querySelectorAll('.dg-table-compact').forEach(function (tableEl) {
    var $table = jQuery(tableEl);
    if (jQuery.fn.DataTable.isDataTable($table)) return;

    $table.DataTable(jQuery.extend({}, baseOptions, {
      paging: false,
      searching: false,
      lengthChange: false,
      info: false,
    }));
  });
});
</script>
