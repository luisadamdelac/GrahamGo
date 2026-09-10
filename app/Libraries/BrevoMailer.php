<?php

namespace App\Libraries;

/**
 * Drop-in stand-in for CodeIgniter\Email\Email, used instead of it when
 * BREVO_API_KEY is configured. Same method names/signatures as the calls
 * every controller already makes (setTo/setReplyTo/setSubject/
 * setMessage/send/attach/setAttachmentCID/printDebugger), so no call site
 * needed to change — only mailer_helper.php decides which one to hand
 * back.
 *
 * Exists because Railway (and many PaaS hosts) block outbound SMTP
 * (ports 587 and 465 both hang until timeout) to curb spam abuse — this
 * sends over plain HTTPS to Brevo's API instead, which isn't blocked.
 */
class BrevoMailer
{
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;
    private array $to = [];
    private ?array $replyTo = null;
    private string $subject = '';
    private string $htmlBody = '';
    private string $lastError = '';

    public function __construct(string $apiKey, string $fromEmail, string $fromName)
    {
        $this->apiKey    = $apiKey;
        $this->fromEmail = $fromEmail;
        $this->fromName  = $fromName;
    }

    public function setTo($to): self
    {
        $this->to = is_array($to) ? $to : [$to];

        return $this;
    }

    public function setReplyTo($replyto, $name = ''): self
    {
        $this->replyTo = ['email' => $replyto, 'name' => $name ?: $replyto];

        return $this;
    }

    public function setSubject($subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function setMessage($body): self
    {
        $this->htmlBody = $body;

        return $this;
    }

    /**
     * No-op — the Brevo path embeds the logo via its real public URL
     * instead of a CID attachment (see email_template()'s check for
     * BrevoMailer), so there's nothing to attach.
     */
    public function attach($file, $disposition = '', $newname = null, $mime = ''): self
    {
        return $this;
    }

    public function setAttachmentCID($filename)
    {
        return false;
    }

    public function send(): bool
    {
        if ($this->to === [] || $this->subject === '' || $this->htmlBody === '') {
            $this->lastError = 'Brevo send skipped: to/subject/message not all set.';

            return false;
        }

        $payload = [
            'sender'      => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to'          => array_map(static fn ($email) => ['email' => $email], $this->to),
            'subject'     => $this->subject,
            'htmlContent' => $this->htmlBody,
        ];

        if ($this->replyTo) {
            $payload['replyTo'] = $this->replyTo;
        }

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $this->apiKey,
            'content-type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        $this->lastError = "Brevo API error (HTTP {$httpCode}): {$curlError} {$response}";
        log_message('error', 'Brevo send failed: {error}', ['error' => $this->lastError]);

        return false;
    }

    public function printDebugger(array $items = []): string
    {
        return $this->lastError !== '' ? $this->lastError : 'No error recorded.';
    }
}
