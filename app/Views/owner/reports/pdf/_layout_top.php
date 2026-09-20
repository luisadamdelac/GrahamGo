<?php
/**
 * Shared top of every report PDF — dompdf's CSS support is much more
 * limited than a real browser (no CSS custom properties/var(), no
 * flexbox), so this is plain HTML/table layout with literal colors
 * instead of reusing app.css's --gg-* variables or Bootstrap classes.
 *
 * This header uses position:fixed, offset entirely above y=0 (into
 * @page's own top margin) rather than sitting at top:0 — the latter
 * rendered cleanly on some pages but interleaved/overlapped with the
 * table's own rows on others, a dompdf quirk that couldn't be fully
 * resolved. The tradeoff: it renders cleanly, but (another dompdf
 * limitation) only reliably shows on page 1, not every page of a
 * multi-page report. The footer doesn't have this problem — it's
 * drawn per-page via dompdf's canvas page_script() API instead (see
 * ReportController::renderPdf()), which repeats correctly and is also
 * how the "Page X of Y" count is done (impossible from pure CSS).
 *
 * Params: $reportTitle (string), $from/$to (optional date strings —
 * omit both for a report with no date-range filter, e.g. Inventory).
 */
$from     = $from ?? null;
$to       = $to ?? null;
$logoPath = FCPATH . 'assets/img/logo.png';
// Embedded as a base64 data URI rather than a plain file path — dompdf
// restricts local filesystem reads to its own chroot, which a bare
// <img src="/var/www/html/public/..."> path falls outside of on
// Railway, rendering as a broken-image icon instead of the logo.
// A data: URI sidesteps that (and the isRemoteEnabled=false remote-URL
// restriction) entirely, since dompdf just decodes the bytes inline.
$logoDataUri = is_file($logoPath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
    : null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  /* Bottom margin just needs to clear the canvas-drawn footer bar
     (~26px tall, see ReportController::renderPdf) with a bit of air. */
  @page { margin: 160px 32px 45px 32px; }
  /* DejaVu Sans, not Helvetica — dompdf's built-in DejaVu Sans is a
     full-Unicode font it bundles by default; Helvetica (a base PDF
     font) only covers Latin-1, so the peso sign (?) was rendering as a
     missing glyph. */
  body { font-family: 'DejaVu Sans', sans-serif; color: #2C2116; font-size: 11px; }
  table { border-collapse: collapse; width: 100%; }
  .gg-pdf-header { position: fixed; top: -150px; left: 0; right: 0; }
  .gg-pdf-header-row td { border: none; padding: 0; vertical-align: top; }
  .gg-pdf-brand-cell table { width: auto; }
  .gg-pdf-brand-cell table td { border: none; padding: 0; vertical-align: middle; }
  .gg-pdf-logo { width: 38px; height: 38px; border-radius: 50%; }
  .gg-pdf-brand { font-size: 18px; font-weight: bold; color: #2C2116; }
  .gg-pdf-brand-sub { font-size: 10px; color: #C46F26; margin-top: 1px; }
  .gg-pdf-title-cell { text-align: right; }
  .gg-pdf-title { font-size: 19px; font-weight: bold; color: #2C2116; text-transform: uppercase; letter-spacing: 1px; }
  .gg-pdf-title-sub { font-size: 10px; color: #8A7A6A; margin-top: 2px; }
  .gg-pdf-hr { border-top: 1px solid #F0E4D6; margin: 9px 0; font-size: 0; line-height: 0; }
  .gg-pdf-hr-thick { border-top: 2px solid #E08A3E; margin: 9px 0 0; }
  .gg-pdf-meta-row td { border: none; padding: 0; font-size: 10px; white-space: nowrap; }
  .gg-pdf-meta-label { color: #C46F26; font-weight: bold; letter-spacing: .5px; padding-right: 6px !important; }
  .gg-pdf-meta-value { color: #4A3324; padding-right: 22px !important; }
  .gg-pdf-meta-sep { border-left: 1px solid #E5D6C5 !important; width: 1px; padding: 0 !important; }
  /* table-layout:fixed + explicit per-column widths (set once, on each
     <th>) — without it, dompdf sizes each row's columns from that
     row's own content independently instead of one consistent width
     for the whole column, so the header's column boundaries didn't
     line up with the data rows underneath it. Widths are set per
     report in each pdf/*.php view's <th style="width:...">. */
  .gg-pdf-table { table-layout: fixed; }
  .gg-pdf-table th { background: #C46F26; color: #FFFFFF; text-align: left; vertical-align: middle; line-height: 1.5; padding: 7px 8px; font-size: 10px; overflow: hidden; }
  .gg-pdf-table td { vertical-align: middle; line-height: 1.5; padding: 6px 8px; border-bottom: 1px solid #F0E4D6; font-size: 10px; overflow: hidden; word-wrap: break-word; }
  .gg-pdf-table tbody tr:nth-child(even) td { background: #FBF3EA; }
  /* "Daily Breakdown" date row — a full-width divider between each
     day's group of rows, overriding the zebra-stripe rule above since
     it needs to read clearly as a heading, not just another data row. */
  .gg-pdf-table tbody tr.gg-pdf-daygroup td { background: #F0E4D6; color: #4A3324; font-weight: bold; border-bottom: 1.5px solid #E08A3E; padding: 6px 8px; }
  .gg-pdf-link { color: #3B6FB5; }
  .gg-status-claimed, .gg-status-ok { color: #2E7D32; font-weight: bold; }
  .gg-status-cancelled, .gg-status-low { color: #C0392B; font-weight: bold; }
  .gg-status-pending, .gg-status-confirmed, .gg-status-ready { color: #C46F26; font-weight: bold; }
</style>
</head>
<body>

<div class="gg-pdf-header">
  <table class="gg-pdf-header-row">
    <tr>
      <td class="gg-pdf-brand-cell">
        <table><tr>
          <?php if ($logoDataUri): ?>
            <td style="width:44px;"><img src="<?= $logoDataUri ?>" class="gg-pdf-logo"></td>
          <?php endif; ?>
          <td>
            <div class="gg-pdf-brand">GrahamGo</div>
            <div class="gg-pdf-brand-sub">Graham Mango &amp; Oreo Graham</div>
          </td>
        </tr></table>
      </td>
      <td class="gg-pdf-title-cell">
        <div class="gg-pdf-title"><?= esc($reportTitle) ?></div>
        <div class="gg-pdf-title-sub">Reservation &amp; Sales System</div>
      </td>
    </tr>
  </table>
  <div class="gg-pdf-hr"></div>
  <table class="gg-pdf-meta-row">
    <tr>
      <?php if ($from || $to): ?>
        <td class="gg-pdf-meta-label">PERIOD</td>
        <td class="gg-pdf-meta-value"><?= esc($from ? date('M j, Y', strtotime($from)) : 'the beginning') ?> &ndash; <?= esc($to ? date('M j, Y', strtotime($to)) : 'today') ?></td>
        <td class="gg-pdf-meta-sep">&nbsp;</td>
        <td style="width:22px;">&nbsp;</td>
      <?php endif; ?>
      <td class="gg-pdf-meta-label">GENERATED</td>
      <td class="gg-pdf-meta-value"><?= date('M j, Y') ?> &middot; <?= date('g:i A') ?></td>
      <td></td>
    </tr>
  </table>
  <div class="gg-pdf-hr gg-pdf-hr-thick"></div>
</div>

