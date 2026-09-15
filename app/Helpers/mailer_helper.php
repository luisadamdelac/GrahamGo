<?php

use App\Libraries\BrevoMailer;
use App\Models\SettingModel;

if (! function_exists('mailer')) {
    /**
     * Every outgoing email goes through Brevo's HTTPS API. Gmail SMTP
     * was removed entirely: outbound SMTP (587 and 465 both) is blocked
     * on Railway — every send just hung until timeout — and the Gmail
     * App Password it needed was being stored in the settings table in
     * plain text, an unnecessary credential-at-rest risk once Brevo
     * covered the same job more securely (an API key, held only in this
     * environment's env vars, never touching the database).
     *
     * Requires BREVO_API_KEY to be set in the environment — including
     * locally, in .env — or this throws instead of silently trying a
     * transport that no longer exists.
     */
    function mailer(): BrevoMailer
    {
        $brevoKey = env('BREVO_API_KEY');
        if (! $brevoKey) {
            throw new \RuntimeException('BREVO_API_KEY is not set — see app/Helpers/mailer_helper.php.');
        }

        $settingModel = new SettingModel();
        $values       = $settingModel->getMany(['smtp_email', 'smtp_from_name']);

        return new BrevoMailer($brevoKey, $values['smtp_email'] ?? '', $values['smtp_from_name'] ?: 'GrahamGo');
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
    function email_template(BrevoMailer $emailService, string $bodyHtml): string
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
