<?php
/**
 * Shared top/bottom of every report PDF — dompdf's CSS support is much
 * more limited than a real browser (no CSS custom properties/var(),
 * no flexbox), so this is plain HTML/table layout with literal colors
 * instead of reusing app.css's --gg-* variables or Bootstrap classes.
 *
 * Params: $reportTitle (string), $from/$to (optional date strings —
 * omit both for a report with no date-range filter, e.g. Inventory).
 */
$from = $from ?? null;
$to   = $to ?? null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 28px 32px; }
  body { font-family: 'Helvetica', 'Arial', sans-serif; color: #2C2116; font-size: 11px; }
  table { border-collapse: collapse; width: 100%; }
  .gg-pdf-header { border-bottom: 2px solid #E08A3E; padding-bottom: 10px; margin-bottom: 16px; }
  .gg-pdf-brand { font-size: 18px; font-weight: bold; color: #4A3324; }
  .gg-pdf-sub { font-size: 10px; color: #7A6858; margin-top: 2px; }
  .gg-pdf-title { font-size: 15px; color: #C46F26; margin-top: 10px; font-weight: bold; }
  .gg-pdf-meta { font-size: 10px; color: #7A6858; margin-top: 4px; }
  .gg-pdf-table th { background: #FBF3EA; color: #4A3324; text-align: left; padding: 6px 8px; border-bottom: 1.5px solid #E08A3E; font-size: 10px; }
  .gg-pdf-table td { padding: 5px 8px; border-bottom: 1px solid #F0E4D6; font-size: 10px; }
  .gg-pdf-footer { margin-top: 18px; padding-top: 8px; border-top: 1px solid #F0E4D6; font-size: 9px; color: #7A6858; text-align: center; }
</style>
</head>
<body>

<div class="gg-pdf-header">
  <div class="gg-pdf-brand">GrahamGo</div>
  <div class="gg-pdf-sub">Graham Mango &amp; Oreo Graham &mdash; Reservation &amp; Sales System</div>
  <div class="gg-pdf-title"><?= esc($reportTitle) ?></div>
  <?php if ($from || $to): ?>
    <div class="gg-pdf-meta">Period: <?= esc($from ? date('M j, Y', strtotime($from)) : 'the beginning') ?> to <?= esc($to ? date('M j, Y', strtotime($to)) : 'today') ?></div>
  <?php endif; ?>
  <div class="gg-pdf-meta">Generated: <?= date('M j, Y g:i A') ?></div>
</div>
