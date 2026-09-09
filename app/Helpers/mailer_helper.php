<?php

use App\Models\SettingModel;
use Config\Email as EmailConfig;

if (! function_exists('mailer')) {
    /**
     * Builds a CodeIgniter Email service configured with the Gmail SMTP
     * credentials stored in the settings table (set via Owner > Settings).
     */
    function mailer(): \CodeIgniter\Email\Email
    {
        $settingModel = new SettingModel();
        $values       = $settingModel->getMany(['smtp_email', 'smtp_app_password', 'smtp_from_name']);

        $config             = new EmailConfig();
        $config->protocol   = 'smtp';
        $config->SMTPHost   = 'smtp.gmail.com';
        $config->SMTPPort   = 587;
        $config->SMTPCrypto = 'tls';
        $config->SMTPUser   = $values['smtp_email'] ?? '';
        $config->SMTPPass   = $values['smtp_app_password'] ?? '';
        $config->fromEmail  = $values['smtp_email'] ?? '';
        $config->fromName   = $values['smtp_from_name'] ?: 'GrahamGo';
        $config->mailType   = 'html';
        // Default is 5s — too tight once the branded template's logo is
        // attached as a base64 CID image; the extra bytes can push a
        // normal send past that and cause it to fail mid-transfer.
        $config->SMTPTimeout = 30;

        return \Config\Services::email($config);
    }
}

if (! function_exists('email_template')) {
    /**
     * Wraps a snippet of message HTML in the shared branded email shell
     * (app/Views/emails/layout.php) so every outgoing email — OTP codes,
     * contact form messages, test emails — looks like it came from the
     * same system instead of a plain unstyled text block.
     *
     * The logo is embedded as a CID attachment on the given $emailService
     * rather than linked via base_url() — an <img src="http://localhost/...">
     * only ever renders for whoever is running the app locally; Gmail (or
     * any other real inbox) has no way to reach "localhost" to fetch it,
     * so the logo would show as broken for every actual recipient. A CID
     * attachment travels inside the email itself, so it renders correctly
     * regardless of whether the app is deployed anywhere yet.
     */
    function email_template(\CodeIgniter\Email\Email $emailService, string $bodyHtml): string
    {
        $logoPath = FCPATH . 'assets/img/logo.png';
        $logoSrc  = base_url('assets/img/logo.png');

        if (is_file($logoPath)) {
            $emailService->attach($logoPath);
            $cid = $emailService->setAttachmentCID($logoPath);

            if ($cid) {
                $logoSrc = 'cid:' . $cid;
            }
        }

        return view('emails/layout', ['body' => $bodyHtml, 'logoSrc' => $logoSrc]);
    }
}
