<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFFAF4; padding:32px 16px; font-family: Arial, Helvetica, sans-serif;">
  <tr>
    <td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 4px 20px rgba(74,51,36,.10);">
        <tr>
          <td align="center" style="background:linear-gradient(135deg,#E08A3E,#C46F26); background-color:#E08A3E; padding:28px 24px;">
            <img src="<?= esc($logoSrc, 'attr') ?>" width="56" height="56" alt="GrahamGo" style="width:56px; height:56px; border-radius:50%; object-fit:cover; border:3px solid rgba(255,255,255,.55); display:block; margin:0 auto 10px;">
            <div style="color:#ffffff; font-size:20px; font-weight:700; font-family: Arial, Helvetica, sans-serif;">GrahamGo</div>
            <div style="color:rgba(255,255,255,.85); font-size:12px; margin-top:2px;">Graham Mango &amp; Oreo Graham</div>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 26px; color:#2C2116; font-size:14px; line-height:1.65;">
            <?= $body ?>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:16px 24px; background:#FBF3EA; color:#8A7A6A; font-size:11px;">
            &copy; <?= date('Y') ?> GrahamGo &mdash; this is an automated email, please do not reply directly.
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
