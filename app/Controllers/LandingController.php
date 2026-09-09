<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\UserModel;

/**
 * Public marketing page (the "/" route).
 */
class LandingController extends BaseController
{
    public function index()
    {
        // The landing page is always reachable, logged in or not — it no
        // longer force-redirects a logged-in visitor away. Being bounced
        // straight to a dashboard just for opening "/" is what made it
        // look like a session had "moved" or gotten lost when it was
        // still there the whole time. Instead, the view itself adapts:
        // it swaps the Login/Register buttons for "Go to Dashboard"
        // links matching whichever role(s) are currently active.
        $productModel = new ProductModel();

        return view('landing/index', [
            'title'      => 'GrahamGo, Graham Mango & Oreo Graham',
            'products'   => $productModel->activeProducts(),
            'isCustomer' => (bool) current_customer(),
            'isOwner'    => (bool) current_owner(),
        ]);
    }

    /**
     * Handles the landing page's Contact form. There's no separate inbox
     * or "messages" table for this — a submission is simply emailed
     * straight to whoever holds the owner account, using the same Gmail
     * SMTP settings configured under Owner > Settings. The visitor's own
     * email is set as Reply-To, so the owner can just hit reply.
     */
    public function submitContact()
    {
        $rules = [
            'contact_name'    => 'required|min_length[2]|max_length[150]',
            'contact_email'   => 'required|valid_email',
            'contact_message' => 'required|min_length[5]|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return $this->backToContactForm()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // Guards against the form being used to spam the owner's inbox
        // (or to burn through the Gmail account's daily send quota).
        if (! service('throttler')->check(md5('contact-' . $this->request->getIPAddress()), 3, 600)) {
            return $this->backToContactForm()->with('error', 'Too many messages sent. Please wait a while and try again.');
        }

        $name    = $this->request->getPost('contact_name');
        $email   = $this->request->getPost('contact_email');
        $message = $this->request->getPost('contact_message');

        // Only one owner account exists in this system, so we email that
        // account directly rather than maintaining a separate "recipient"
        // setting.
        $userModel = new UserModel();
        $owner     = $userModel->where('role', 'owner')->first();

        if (! $owner) {
            return $this->backToContactForm()->with('error', 'Unable to send your message right now. Please try again later.');
        }

        if ($this->sendContactEmail($owner['email'], $name, $email, $message)) {
            return $this->backToContactForm()->with('success', "Thanks, {$name}! Your message has been sent.");
        }

        return $this->backToContactForm()->with('error', 'Failed to send your message. Please try again later.');
    }

    private function sendContactEmail(string $ownerEmail, string $name, string $replyEmail, string $message): bool
    {
        $emailService = mailer();
        $emailService->setTo($ownerEmail);
        $emailService->setReplyTo($replyEmail, $name);
        $emailService->setSubject('GrahamGo Inquiry from ' . $name);
        $emailService->setMessage(email_template($emailService,
            "<p style=\"margin:0 0 16px;\">You received a new message from the GrahamGo contact form.</p>" .
            "<p style=\"margin:0 0 4px;\"><strong>Name:</strong> " . esc($name) . "</p>" .
            "<p style=\"margin:0 0 16px;\"><strong>Email:</strong> " . esc($replyEmail) . "</p>" .
            "<div style=\"background:#FBF3EA; border-radius:12px; padding:14px 16px; color:#4A3324;\">" . nl2br(esc($message)) . "</div>" .
            "<p style=\"margin:16px 0 0; color:#8A7A6A; font-size:13px;\">Reply directly to this email to respond to " . esc($name) . ".</p>"
        ));

        return $emailService->send();
    }

    /**
     * Every outcome of the contact form (success, validation error, send
     * failure) lands back on "/" with a flash message. The 'scroll' flag
     * tells the landing view to jump straight to the #contact section
     * instead of leaving the visitor back at the top of the page.
     */
    private function backToContactForm()
    {
        return redirect()->to('/')->with('scroll', 'contact');
    }
}
