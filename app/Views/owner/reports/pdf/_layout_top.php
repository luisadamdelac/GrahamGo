<?php
/**
 * Shared top/bottom of every report PDF — dompdf's CSS support is much
 * more limited than a real browser (no CSS custom properties/var(),
 * no flexbox), so this is plain HTML/table layout with literal colors
 * instead of reusing app.css's --gg-* variables or Bootstrap classes.
 *
 * The header/footer use position:fixed, offset above/below the visible
 * page (into @page's own top/bottom margin) rather than sitting at
 * top:0/bottom:0 — the latter rendered cleanly on some pages but
 * interleaved/overlapped with the table's own rows on others, a dompdf
 * quirk that couldn't be fully resolved. The tradeoff: header/footer
 * are guaranteed to render cleanly, but (another dompdf limitation)
 * don't reliably repeat on every page of a multi-page report — most
 * reliably present on page 1 and the footer, less so mid-document.
 *
 * Params: $reportTitle (string), $from/$to (optional date strings —
 * omit both for a report with no date-range filter, e.g. Inventory).
 */
$from     = $from ?? null;
$to       = $to ?? null;
$logoPath = FCPATH . 'assets/img/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 118px 32px 50px 32px; }
  /* DejaVu Sans, not Helvetica — dompdf's built-in DejaVu Sans is a
     full-Unicode font it bundles by default; Helvetica (a base PDF
     font) only covers Latin-1, so the peso sign (?) was rendering as a
     missing glyph. */
  body { font-family: 'DejaVu Sans', sans-serif; color: #2C2116; font-size: 11px; }
  table { border-collapse: collapse; width: 100%; }
  /* dompdf only ever reliably repeats a position:fixed element that's
     offset entirely above y=0 (into the page's own top margin) — top:0
     (or top+width:100%) rendered it overlapping/interleaved with the
     table's own first rows instead of sitting cleanly above them. The
     -110px offset trades "repeats on every page" (a dompdf quirk this
     couldn't get fully working — see ReportController) for "always
     renders cleanly", which matters more for a document meant to be
     read. */
  .gg-pdf-header {
    position: fixed; top: -110px; left: 0; right: 0;
    border-bottom: 2px solid #E08A3E; padding-bottom: 10px;
  }
  .gg-pdf-logo { width: 34px; height: 34px; }
  .gg-pdf-brand { font-size: 18px; font-weight: bold; color: #4A3324; }
  .gg-pdf-sub { font-size: 10px; color: #7A6858; margin-top: 2px; }
  .gg-pdf-title { font-size: 15px; color: #C46F26; margin-top: 8px; font-weight: bold; }
  .gg-pdf-meta { font-size: 10px; color: #7A6858; margin-top: 3px; }
  .gg-pdf-table th { background: #FBF3EA; color: #4A3324; text-align: left; padding: 6px 8px; border-bottom: 1.5px solid #E08A3E; font-size: 10px; }
  .gg-pdf-table td { padding: 5px 8px; border-bottom: 1px solid #F0E4D6; font-size: 10px; }
  .gg-pdf-footer {
    position: fixed; bottom: -35px; left: 0; right: 0;
    padding-top: 6px; border-top: 1px solid #F0E4D6;
    font-size: 9px; color: #7A6858; text-align: center;
  }
</style>
</head>
<body>

<div class="gg-pdf-header">
  <table style="border:none;">
    <tr>
      <?php if (is_file($logoPath)): ?>
        <td style="width:40px; border:none; padding:0; vertical-align:top;"><img src="<?= $logoPath ?>" class="gg-pdf-logo"></td>
      <?php endif; ?>
      <td style="border:none; padding:0; vertical-align:top;">
        <div class="gg-pdf-brand">GrahamGo</div>
        <div class="gg-pdf-sub">Graham Mango &amp; Oreo Graham &mdash; Reservation &amp; Sales System</div>
      </td>
    </tr>
  </table>
  <div class="gg-pdf-title"><?= esc($reportTitle) ?></div>
  <?php if ($from || $to): ?>
    <div class="gg-pdf-meta">Period: <?= esc($from ? date('M j, Y', strtotime($from)) : 'the beginning') ?> to <?= esc($to ? date('M j, Y', strtotime($to)) : 'today') ?></div>
  <?php endif; ?>
  <div class="gg-pdf-meta">Generated: <?= date('M j, Y g:i A') ?></div>
</div>
