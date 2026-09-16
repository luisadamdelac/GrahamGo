<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\ProductModel;
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

    public function reservations()
    {
        $from = $this->request->getGet('from') ?: date('Y-m-01');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');

        return view('owner/reports/reservations', [
            'title'        => 'Reservation Report',
            'reservations' => $this->reservationRows($from, $to),
            'from'         => $from,
            'to'           => $to,
        ]);
    }

    public function reservationsPdf()
    {
        $from = $this->request->getGet('from') ?: date('Y-m-01');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');

        return $this->renderPdf('owner/reports/pdf/reservations', [
            'reservations' => $this->reservationRows($from, $to),
            'from'         => $from,
            'to'           => $to,
        ], 'reservation-report_' . $from . '_to_' . $to . '.pdf');
    }

    public function reservationsExcel()
    {
        $from = $this->request->getGet('from') ?: date('Y-m-01');
        $to   = $this->request->getGet('to') ?: date('Y-m-d');
        $rows = $this->reservationRows($from, $to);

        return $this->streamExcel(
            'reservation-report_' . $from . '_to_' . $to . '.xlsx',
            'Reservations',
            ['Reservation #', 'Claim Date', 'Customer', 'Customer Type', 'Product', 'Qty', 'Fulfillment', 'Total Amount', 'Payment Status', 'Status'],
            array_map(static fn ($r) => [
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
            ], $rows)
        );
    }

    /**
     * Shared by every reservations action so the screen view, PDF, and
     * Excel export always agree on exactly what "this report" means —
     * one row per product line item (a reservation with several
     * products appears as several rows).
     */
    private function reservationRows(string $from, string $to): array
    {
        $builder = db_connect()->table('reservations r')
            ->select('r.reservation_id, r.claim_date, r.fulfillment_type, r.total_amount, r.payment_status, r.status, u.name AS customer_name, u.customer_type, p.product_name, rd.quantity')
            ->join('users u', 'u.user_id = r.user_id')
            ->join('reservation_details rd', 'rd.reservation_id = r.reservation_id')
            ->join('products p', 'p.product_id = rd.product_id')
            ->orderBy('r.claim_date', 'DESC');

        if ($from) {
            $builder->where('r.claim_date >=', $from);
        }
        if ($to) {
            $builder->where('r.claim_date <=', $to);
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
        $sales      = $this->salesRows($from, $to, $walkInOnly);

        return view('owner/reports/sales', [
            'title'      => 'Sales Report',
            'sales'      => $sales,
            'total'      => array_sum(array_column($sales, 'total_amount')),
            'from'       => $from,
            'to'         => $to,
            'walkInOnly' => $walkInOnly,
        ]);
    }

    public function salesPdf()
    {
        $from       = $this->request->getGet('from') ?: date('Y-m-01');
        $to         = $this->request->getGet('to') ?: date('Y-m-d');
        $walkInOnly = (bool) $this->request->getGet('walkin_only');
        $sales      = $this->salesRows($from, $to, $walkInOnly);

        return $this->renderPdf('owner/reports/pdf/sales', [
            'reportTitle' => $walkInOnly ? 'Sales Report (Walk-ins Only)' : 'Sales Report',
            'sales'       => $sales,
            'total'       => array_sum(array_column($sales, 'total_amount')),
            'from'        => $from,
            'to'          => $to,
        ], 'sales-report_' . $from . '_to_' . $to . '.pdf');
    }

    public function salesExcel()
    {
        $from       = $this->request->getGet('from') ?: date('Y-m-01');
        $to         = $this->request->getGet('to') ?: date('Y-m-d');
        $walkInOnly = (bool) $this->request->getGet('walkin_only');
        $sales      = $this->salesRows($from, $to, $walkInOnly);

        return $this->streamExcel(
            'sales-report_' . $from . '_to_' . $to . '.xlsx',
            'Sales',
            ['Date', 'Reservation #', 'Customer', 'Product(s)', 'Qty', 'Amount', 'Payment Method', 'Status'],
            array_map(static fn ($s) => [
                date('M d, Y g:i A', strtotime($s['sale_date'])),
                '#' . $s['reservation_id'],
                $s['customer_name'],
                $s['product_names'],
                (int) $s['total_quantity'],
                (float) $s['total_amount'],
                $s['payment_method'],
                $s['reservation_status'],
            ], $sales)
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
        return view('owner/reports/inventory', [
            'title'   => 'Inventory Report',
            'summary' => $this->inventorySummary(),
        ]);
    }

    public function inventoryPdf()
    {
        return $this->renderPdf('owner/reports/pdf/inventory', [
            'summary' => $this->inventorySummary(),
        ], 'inventory-report_' . date('Y-m-d') . '.pdf');
    }

    public function inventoryExcel()
    {
        $summary = $this->inventorySummary();

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

    private function inventorySummary(): array
    {
        $productModel = new ProductModel();
        $products     = $productModel->orderBy('product_name', 'ASC')->findAll();

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
     * one per line, in the same column order as $headers.
     */
    private function streamExcel(string $filename, string $sheetTitle, array $headers, array $rows)
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
