<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\ReservationModel;
use App\Models\SaleModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends BaseController
{
    public function index()
    {
        return view('owner/reports/index', ['title' => 'Reports']);
    }

    // -----------------------------------------------------------------
    // Reservations
    // -----------------------------------------------------------------

    /**
     * "Claimed" by default rather than "All" — a report is normally
     * pulled to look at completed business (what was actually sold and
     * picked up/delivered), not the still-in-progress Pending/Confirmed/
     * Ready reservations the owner already tracks live on the
     * Reservations list page. An explicit ?status=All (or any other
     * status) overrides it.
     */
    private function reservationStatusFilter(): string
    {
        $status = $this->request->getGet('status');

        return $status === null ? 'Claimed' : $status;
    }

    /**
     * Defaults to the Daily Breakdown view until the owner has actually
     * touched the filter form (the 'filtered' marker), since an unchecked
     * checkbox is simply absent from the query string, indistinguishable
     * from a fresh page load without this marker.
     */
    private function inventoryByDayFilter(): bool
    {
        if ($this->request->getGet('filtered') === null) {
            return true;
        }

        return (bool) $this->request->getGet('daily_breakdown');
    }

    private function inventoryProductFilter(): ?int
    {
        $productId = $this->request->getGet('product');

        return $productId ? (int) $productId : null;
    }

    public function reservations()
    {
        $from     = $this->request->getGet('from') ?: date('Y-m-01');
        $to       = $this->request->getGet('to') ?: date('Y-m-d');
        $byDay    = (bool) $this->request->getGet('daily_breakdown');
        $status   = $this->reservationStatusFilter();
        $customer = trim((string) $this->request->getGet('customer'));
        $rows     = $this->reservationRows($from, $to, $status, $customer);

        return view('owner/reports/reservations', [
            'title'        => 'Reservation Report',
            'reservations' => $rows,
            'byDay'        => $byDay,
            'dayGroups'    => $byDay ? $this->groupRowsByDate($rows, 'claim_date', 'total_amount', 'reservation_id') : [],
            'from'         => $from,
            'to'           => $to,
            'status'       => $status,
            'customer'     => $customer,
        ]);
    }

    public function reservationsPdf()
    {
        $from     = $this->request->getGet('from') ?: date('Y-m-01');
        $to       = $this->request->getGet('to') ?: date('Y-m-d');
        $byDay    = (bool) $this->request->getGet('daily_breakdown');
        $status   = $this->reservationStatusFilter();
        $customer = trim((string) $this->request->getGet('customer'));
        $rows     = $this->reservationRows($from, $to, $status, $customer);

        return $this->renderPdf('owner/reports/pdf/reservations', [
            'reportTitle'  => $status !== 'All' ? 'Reservation Report (' . $status . ')' : 'Reservation Report',
            'reservations' => $rows,
            'byDay'        => $byDay,
            'dayGroups'    => $byDay ? $this->groupRowsByDate($rows, 'claim_date', 'total_amount', 'reservation_id') : [],
            'from'         => $from,
            'to'           => $to,
        ], 'reservation-report_' . $from . '_to_' . $to . '.pdf');
    }

    public function reservationsExcel()
    {
        $from     = $this->request->getGet('from') ?: date('Y-m-01');
        $to       = $this->request->getGet('to') ?: date('Y-m-d');
        $byDay    = (bool) $this->request->getGet('daily_breakdown');
        $status   = $this->reservationStatusFilter();
        $customer = trim((string) $this->request->getGet('customer'));
        $rows     = $this->reservationRows($from, $to, $status, $customer);

        $headers = ['Reservation #', 'Claim Date', 'Customer', 'Customer Type', 'Product', 'Qty', 'Fulfillment', 'Total Amount', 'Payment Status', 'Status'];
        $toRow   = static fn ($r) => [
            '#' . $r['reservation_id'],
            date('M d, Y', strtotime($r['claim_date'])),
            $r['customer_name'],
            $r['customer_type'],
            $r['product_name'],
            (int) $r['quantity'],
            $r['fulfillment_type'],
            (float) $r['total_amount'],
            $r['payment_status'],
            $r['status'],
        ];

        if ($byDay) {
            [$excelRows, $boldRows] = $this->buildDailyBreakdownExcelRows(
                $this->groupRowsByDate($rows, 'claim_date', 'total_amount', 'reservation_id'),
                $toRow,
                count($headers),
                static fn ($day, $group) => date('l, F j, Y', strtotime($day)) . ' · ' . $group['count'] . ' reservation(s), ₱' . number_format($group['total'], 2)
            );
        } else {
            $excelRows = array_map($toRow, $rows);
            $boldRows  = [];
        }

        return $this->streamExcel(
            'reservation-report_' . $from . '_to_' . $to . '.xlsx',
            'Reservations',
            $headers,
            $excelRows,
            $boldRows
        );
    }

    /**
     * Shared by every reservations action so the screen view, PDF, and
     * Excel export always agree on exactly what "this report" means —
     * one row per product line item (a reservation with several
     * products appears as several rows).
     */
    private function reservationRows(string $from, string $to, string $status = 'All', string $customer = ''): array
    {
        $builder = db_connect()->table('reservations r')
            ->select('r.reservation_id, r.claim_date, r.fulfillment_type, r.total_amount, r.payment_status, r.status, u.name AS customer_name, u.customer_type, p.product_name, rd.quantity')
            ->join('users u', 'u.user_id = r.user_id')
            ->join('reservation_details rd', 'rd.reservation_id = r.reservation_id')
            ->join('products p', 'p.product_id = rd.product_id')
            ->orderBy('r.claim_date', 'ASC');

        if ($from) {
            $builder->where('r.claim_date >=', $from);
        }
        if ($to) {
            $builder->where('r.claim_date <=', $to);
        }
        if ($status !== 'All' && in_array($status, ReservationModel::STATUSES, true)) {
            $builder->where('r.status', $status);
        }
        if ($customer !== '') {
            $builder->like('u.name', $customer);
        }

        return $builder->get()->getResultArray();
    }

    // -----------------------------------------------------------------
    // Sales
    // -----------------------------------------------------------------

    public function sales()
    {
        $from       = $this->request->getGet('from') ?: date('Y-m-01');
        $to         = $this->request->getGet('to') ?: date('Y-m-d');
        $walkInOnly = (bool) $this->request->getGet('walkin_only');
        $byDay      = (bool) $this->request->getGet('daily_breakdown');
        $sales      = $this->salesRows($from, $to, $walkInOnly);

        return view('owner/reports/sales', [
            'title'      => 'Sales Report',
            'sales'      => $sales,
            'total'      => array_sum(array_column($sales, 'total_amount')),
            'from'       => $from,
            'to'         => $to,
            'walkInOnly' => $walkInOnly,
            'byDay'      => $byDay,
            'dayGroups'  => $byDay ? $this->groupRowsByDate($sales, 'sale_date', 'total_amount') : [],
        ]);
    }

    public function salesPdf()
    {
        $from       = $this->request->getGet('from') ?: date('Y-m-01');
        $to         = $this->request->getGet('to') ?: date('Y-m-d');
        $walkInOnly = (bool) $this->request->getGet('walkin_only');
        $byDay      = (bool) $this->request->getGet('daily_breakdown');
        $sales      = $this->salesRows($from, $to, $walkInOnly);

        return $this->renderPdf('owner/reports/pdf/sales', [
            'reportTitle' => $walkInOnly ? 'Sales Report (Walk-ins Only)' : 'Sales Report',
            'sales'       => $sales,
            'total'       => array_sum(array_column($sales, 'total_amount')),
            'from'        => $from,
            'to'          => $to,
            'byDay'       => $byDay,
            'dayGroups'   => $byDay ? $this->groupRowsByDate($sales, 'sale_date', 'total_amount') : [],
        ], 'sales-report_' . $from . '_to_' . $to . '.pdf');
    }

    public function salesExcel()
    {
        $from       = $this->request->getGet('from') ?: date('Y-m-01');
        $to         = $this->request->getGet('to') ?: date('Y-m-d');
        $walkInOnly = (bool) $this->request->getGet('walkin_only');
        $byDay      = (bool) $this->request->getGet('daily_breakdown');
        $sales      = $this->salesRows($from, $to, $walkInOnly);

        $headers = ['Date', 'Reservation #', 'Customer', 'Product(s)', 'Qty', 'Amount', 'Payment Method', 'Status'];
        $toRow   = static fn ($s) => [
            date('M d, Y g:i A', strtotime($s['sale_date'])),
            '#' . $s['reservation_id'],
            $s['customer_name'],
            $s['product_names'],
            (int) $s['total_quantity'],
            (float) $s['total_amount'],
            $s['payment_method'],
            $s['reservation_status'],
        ];

        if ($byDay) {
            [$excelRows, $boldRows] = $this->buildDailyBreakdownExcelRows(
                $this->groupRowsByDate($sales, 'sale_date', 'total_amount'),
                $toRow,
                count($headers),
                static fn ($day, $group) => date('l, F j, Y', strtotime($day)) . ' · ' . $group['count'] . ' sale(s), ₱' . number_format($group['total'], 2)
            );
        } else {
            $excelRows = array_map($toRow, $sales);
            $boldRows  = [];
        }

        return $this->streamExcel(
            'sales-report_' . $from . '_to_' . $to . '.xlsx',
            'Sales',
            $headers,
            $excelRows,
            $boldRows
        );
    }

    private function salesRows(string $from, string $to, bool $walkInOnly = false): array
    {
        return (new SaleModel())->withDetails($from ?: null, $to ?: null, $walkInOnly);
    }

    // -----------------------------------------------------------------
    // Inventory
    // -----------------------------------------------------------------

    public function inventory()
    {
        $byDay     = $this->inventoryByDayFilter();
        $from      = $this->request->getGet('from') ?: date('Y-m-01');
        $to        = $this->request->getGet('to') ?: date('Y-m-d');
        $productId = $this->inventoryProductFilter();

        return view('owner/reports/inventory', [
            'title'           => 'Inventory Report',
            'summary'         => $this->inventorySummary($productId),
            'byDay'           => $byDay,
            'from'            => $from,
            'to'              => $to,
            'dayGroups'       => $byDay ? $this->groupRowsByDate($this->inventoryTransactionRows($from, $to, $productId), 'transaction_date', 'quantity') : [],
            'products'        => (new ProductModel())->orderBy('product_name', 'ASC')->findAll(),
            'selectedProduct' => $productId,
        ]);
    }

    public function inventoryPdf()
    {
        $byDay     = $this->inventoryByDayFilter();
        $from      = $this->request->getGet('from') ?: date('Y-m-01');
        $to        = $this->request->getGet('to') ?: date('Y-m-d');
        $productId = $this->inventoryProductFilter();
        $product   = $productId ? (new ProductModel())->find($productId) : null;

        $titleParts = [];
        if ($byDay) {
            $titleParts[] = 'Daily Breakdown';
        }
        if ($product) {
            $titleParts[] = $product['product_name'];
        }

        return $this->renderPdf('owner/reports/pdf/inventory', [
            'reportTitle' => 'Inventory Report' . ($titleParts ? ' (' . implode(' · ', $titleParts) . ')' : ''),
            'summary'     => $this->inventorySummary($productId),
            'byDay'       => $byDay,
            'from'        => $byDay ? $from : null,
            'to'          => $byDay ? $to : null,
            'dayGroups'   => $byDay ? $this->groupRowsByDate($this->inventoryTransactionRows($from, $to, $productId), 'transaction_date', 'quantity') : [],
        ], 'inventory-report_' . date('Y-m-d') . '.pdf');
    }

    public function inventoryExcel()
    {
        $byDay     = $this->inventoryByDayFilter();
        $from      = $this->request->getGet('from') ?: date('Y-m-01');
        $to        = $this->request->getGet('to') ?: date('Y-m-d');
        $productId = $this->inventoryProductFilter();

        if ($byDay) {
            $headers = ['Date', 'Product', 'Type', 'Qty', 'Notes'];
            $toRow   = static fn ($t) => [
                date('M d, Y g:i A', strtotime($t['transaction_date'])),
                $t['product_name'],
                $t['transaction_type'],
                $t['quantity'],
                $t['notes'],
            ];

            [$excelRows, $boldRows] = $this->buildDailyBreakdownExcelRows(
                $this->groupRowsByDate($this->inventoryTransactionRows($from, $to, $productId), 'transaction_date', 'quantity'),
                $toRow,
                count($headers),
                static fn ($day, $group) => date('l, F j, Y', strtotime($day)) . ' · ' . $group['count'] . ' transaction(s) · Stock Change: ' . sprintf('%+d', (int) round($group['total']))
            );

            return $this->streamExcel(
                'inventory-report_' . $from . '_to_' . $to . '.xlsx',
                'Inventory',
                $headers,
                $excelRows,
                $boldRows
            );
        }

        $summary = $this->inventorySummary($productId);

        return $this->streamExcel(
            'inventory-report_' . date('Y-m-d') . '.xlsx',
            'Inventory',
            ['Product', 'Reserved (to date)', 'Sold (to date)', 'Currently Available', 'Status'],
            array_map(static fn ($row) => [
                $row['product']['product_name'],
                $row['reserved'],
                $row['sold'],
                $row['available'],
                $row['available'] <= $row['product']['reorder_level'] ? 'Low Stock' : 'OK',
            ], $summary)
        );
    }

    /**
     * The raw transaction log behind inventorySummary()'s lifetime
     * totals — feeds the "Daily Breakdown" view (see groupRowsByDate())
     * so the owner can see which day stock actually moved, not just the
     * all-time Reserved/Sold/Available numbers.
     */
    private function inventoryTransactionRows(string $from, string $to, ?int $productId = null): array
    {
        $builder = db_connect()->table('inventory_transactions it')
            ->select('it.transaction_id, it.transaction_date, it.transaction_type, it.quantity, it.notes, p.product_name')
            ->join('products p', 'p.product_id = it.product_id')
            ->orderBy('it.transaction_date', 'ASC');

        if ($from) {
            $builder->where('it.transaction_date >=', $from . ' 00:00:00');
        }
        if ($to) {
            $builder->where('it.transaction_date <=', $to . ' 23:59:59');
        }
        if ($productId) {
            $builder->where('it.product_id', $productId);
        }

        return $builder->get()->getResultArray();
    }

    private function inventorySummary(?int $productId = null): array
    {
        $productModel = new ProductModel();
        $query        = $productModel->orderBy('product_name', 'ASC');
        if ($productId) {
            $query->where('product_id', $productId);
        }
        $products = $query->findAll();

        $summary = [];
        foreach ($products as $p) {
            $reserved = (int) abs((float) (db_connect()->table('inventory_transactions')
                ->selectSum('quantity', 'total')
                ->where('product_id', $p['product_id'])
                ->where('transaction_type', 'Reserved')
                ->get()->getRowArray()['total'] ?? 0));

            $sold = (int) abs((float) (db_connect()->table('inventory_transactions')
                ->selectSum('quantity', 'total')
                ->where('product_id', $p['product_id'])
                ->where('transaction_type', 'Sold')
                ->get()->getRowArray()['total'] ?? 0));

            $summary[] = [
                'product'   => $p,
                'reserved'  => $reserved,
                'sold'      => $sold,
                'available' => $p['stock'],
            ];
        }

        return $summary;
    }

    // -----------------------------------------------------------------
    // Daily breakdown (Reservation + Sales reports)
    // -----------------------------------------------------------------

    /**
     * Groups $rows by calendar day (using $dateField), with a running
     * count and amount total per day — feeds the screen, PDF, and Excel
     * outputs so all three agree on exactly how days are split and
     * totaled. $uniqueKey de-duplicates the count/total by that field
     * before adding it in: reservation rows are one row per product
     * line item (see reservationRows()), so a reservation with three
     * products would otherwise have its total_amount counted three
     * times over for the same reservation. Sales rows are already one
     * row per sale (see SaleModel::withDetails()), so $uniqueKey is
     * left null there — every row counts once.
     */
    private function groupRowsByDate(array $rows, string $dateField, string $amountField, ?string $uniqueKey = null): array
    {
        $grouped     = [];
        $seenPerDay  = [];

        foreach ($rows as $row) {
            $day = date('Y-m-d', strtotime($row[$dateField]));
            $grouped[$day]['rows'][] = $row;
            $grouped[$day]['count'] ??= 0;
            $grouped[$day]['total'] ??= 0.0;

            $isNewUnique = true;
            if ($uniqueKey !== null) {
                $seenPerDay[$day] ??= [];
                $isNewUnique = ! in_array($row[$uniqueKey], $seenPerDay[$day], true);
                if ($isNewUnique) {
                    $seenPerDay[$day][] = $row[$uniqueKey];
                }
            }

            if ($isNewUnique) {
                $grouped[$day]['count']++;
                $grouped[$day]['total'] += (float) $row[$amountField];
            }
        }

        return $grouped;
    }

    /**
     * Turns a groupRowsByDate() result into a flat row list for
     * streamExcel(), inserting a single-cell date-header row ahead of
     * each day's data rows. $labelFn builds that header's text from
     * (string $day, array $group) — callers decide what to show after
     * the date (a ₱ total makes sense for Sales/Reservations, but not
     * Inventory, whose "quantity" can be positive or negative depending
     * on transaction_type, so a plain sum wouldn't read as meaningful).
     * Returns [rows, boldRowIndexes] so the caller can bold just those
     * day-header rows.
     */
    private function buildDailyBreakdownExcelRows(array $dayGroups, callable $toRow, int $columnCount, callable $labelFn): array
    {
        $excelRows = [];
        $boldRows  = [];

        foreach ($dayGroups as $day => $group) {
            $boldRows[]  = count($excelRows);
            $excelRows[] = array_pad([$labelFn($day, $group)], $columnCount, '');

            foreach ($group['rows'] as $row) {
                $excelRows[] = $toRow($row);
            }
        }

        return [$excelRows, $boldRows];
    }

    // -----------------------------------------------------------------
    // Shared PDF / Excel rendering
    // -----------------------------------------------------------------

    /**
     * Renders $viewName to a PDF and streams it inline (opens in the
     * browser as a preview instead of forcing a download — the visitor
     * can still save it from there, e.g. via the browser's own
     * print-to-PDF/save icon, same as any other PDF a browser can open).
     */
    private function renderPdf(string $viewName, array $data, string $filename)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($viewName, $data));
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $this->drawPdfFooter($dompdf);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Draws the branded footer bar (dark background, orange top border,
     * "GrahamGo · Reservation & Sales System" on the left, "Page X of Y"
     * on the right) on every single page, via dompdf's low-level canvas
     * API rather than an HTML position:fixed element. This must run
     * after render() (the canvas has no pages yet before that) — unlike
     * CSS position:fixed, which this codebase found only reliably shows
     * on page 1 of a multi-page document, page_script()'s callback runs
     * once per already-rendered page, so this genuinely repeats on all
     * of them and is also the only way to get a real "Page X of Y"
     * count (dompdf has no CSS counter(page) support).
     */
    private function drawPdfFooter(Dompdf $dompdf): void
    {
        $canvas      = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font        = $fontMetrics->getFont('DejaVu Sans', 'normal');

        $pageWidth  = $canvas->get_width();
        $pageHeight = $canvas->get_height();
        $barHeight  = 26;
        $barTop     = $pageHeight - $barHeight;
        $margin     = 32;

        $darkBrown = [0x2C / 255, 0x21 / 255, 0x16 / 255];
        $orange    = [0xE0 / 255, 0x8A / 255, 0x3E / 255];
        $lightText = [0xEA / 255, 0xDD / 255, 0xCF / 255];

        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($font, $pageWidth, $barHeight, $barTop, $margin, $darkBrown, $orange, $lightText) {
            $canvas->filled_rectangle(0, $barTop, $pageWidth, $barHeight, $darkBrown);
            $canvas->line(0, $barTop, $pageWidth, $barTop, $orange, 2);

            $leftText = 'GrahamGo · Reservation & Sales System';
            $canvas->text($margin, $barTop + 8, $leftText, $font, 9, $lightText);

            $rightText      = 'Page ' . $pageNumber . ' of ' . $pageCount;
            $rightTextWidth = $fontMetrics->getTextWidth($rightText, $font, 9);
            $canvas->text($pageWidth - $margin - $rightTextWidth, $barTop + 8, $rightText, $font, 9, $lightText);
        });
    }

    /**
     * Streams a $headers + $rows table as a real .xlsx workbook — bold
     * header row, auto-sized columns. $rows is a list of plain arrays,
     * one per line, in the same column order as $headers. $boldRows is
     * a list of 0-indexed positions within $rows (not counting the
     * header) to bold — used for the "Daily Breakdown" date rows.
     */
    private function streamExcel(string $filename, string $sheetTitle, array $headers, array $rows, array $boldRows = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($sheetTitle, 0, 31));

        $sheet->fromArray($headers, null, 'A1');
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);

        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }

        foreach ($boldRows as $rowIndex) {
            $sheet->getStyle('A' . ($rowIndex + 2) . ':' . $lastCol . ($rowIndex + 2))->getFont()->setBold(true);
        }

        foreach (range(1, count($headers)) as $colIndex) {
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }
}
