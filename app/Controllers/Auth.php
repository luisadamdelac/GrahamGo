<?php

namespace App\Controllers;

use App\Models\PasswordResetModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // -------------------------------------------------------------
    // Unified login (role is detected from the account itself)
    //
    // Note: this intentionally does NOT redirect away just because
    // some role is already logged in elsewhere in the same browser.
    // With one shared /login page for both roles, auto-redirecting
    // would bounce a tab that's simply sitting on /login toward
    // whichever role happens to be authenticated in another tab
    // (e.g. the owner), which is confusing. Only an actual login
    // submission (attemptLogin) should change where you land.
    // -------------------------------------------------------------
    public function login()
    {
        return view('auth/login', ['title' => 'Login']);
    }

    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid email and password.');
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Brute-force guard: cap attempts per IP and, separately, per the
        // email being targeted — so an attacker can't get around the IP
        // limit by trying many accounts, or get around the email limit by
        // spreading attempts across many IPs.
        if (! $this->withinLoginAttemptLimit($email)) {
            return redirect()->back()->withInput()->with('error', 'Too many login attempts. Please wait a minute and try again.');
        }

        $user = $this->userModel->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if ($user['role'] === 'owner') {
            $this->startOwnerSession($user);

            return redirect()->to('owner/dashboard')->with('success', 'Welcome back, ' . $user['name'] . '!');
        }

        $this->startCustomerSession($user);

        return redirect()->to('home')->with('success', 'Welcome back, ' . $user['name'] . '!');
    }

    /**
     * Maps a field name to which step of the register wizard it lives
     * on, so a server-side validation failure can reopen the form on
     * the step that actually needs fixing instead of always bouncing
     * back to step 1 and making the visitor re-click through steps
     * they'd already filled in correctly (their input itself is never
     * lost either way — old() below restores every field regardless).
     */
    private const REGISTER_STEP_FIELDS = [
        1 => ['last_name', 'first_name', 'middle_name', 'street', 'sitio', 'barangay', 'barangay_other'],
        2 => ['email', 'contact_number', 'customer_type', 'customer_type_other'],
        3 => ['password', 'confirm_password', 'terms_accepted'],
    ];

    public function register()
    {
        if (current_customer()) {
            return redirect()->to('home');
        }

        return view('auth/register', ['title' => 'Register']);
    }

    public function attemptRegister()
    {
        $rules = [
            'last_name'         => 'required|min_length[2]|max_length[100]',
            'first_name'        => 'required|min_length[2]|max_length[100]',
            'middle_name'       => 'permit_empty|max_length[100]',
            'street'            => 'required|max_length[150]',
            'barangay'          => 'required|max_length[100]',
            'barangay_other'    => 'permit_empty|max_length[100]',
            'email'             => 'required|valid_email|is_unique[users.email]',
            'password'          => 'required|' . UserModel::PASSWORD_RULE,
            'confirm_password'  => 'required|matches[password]',
            'contact_number'    => 'required|max_length[20]',
            'customer_type'     => 'required|in_list[Student,Faculty,Staff,Other]',
            'terms_accepted'    => 'required',
        ];

        $messages = [
            'password'       => ['regex_match' => 'Password must be ' . lcfirst(UserModel::PASSWORD_HINT)],
            'terms_accepted' => ['required' => 'You must agree to the Terms and Conditions to create an account.'],
        ];

        $customerType      = $this->request->getPost('customer_type');
        $customerTypeOther = $this->request->getPost('customer_type_other');
        $barangay           = $this->request->getPost('barangay');
        $barangayOther       = $this->request->getPost('barangay_other');

        if (! $this->validate($rules, $messages)) {
            return $this->backToRegisterStep()->with('error', implode(' ', $this->validator->getErrors()));
        }

        // "Other" needs a free-text description of what that actually
        // means (e.g. "Carpenter" for customer type, or an actual
        // barangay name outside Calapan) — not covered by a simple
        // in_list rule, so both are checked separately here.
        if ($customerType === 'Other' && empty(trim((string) $customerTypeOther))) {
            return $this->backToRegisterStep(2)->with('error', 'Please specify what "Other" means for you.');
        }
        if ($barangay === 'Other' && empty(trim((string) $barangayOther))) {
            return $this->backToRegisterStep(1)->with('error', 'Please specify your barangay.');
        }

        $userId = $this->userModel->insert([
            'last_name'           => $this->request->getPost('last_name'),
            'first_name'          => $this->request->getPost('first_name'),
            'middle_name'         => $this->request->getPost('middle_name'),
            'street'              => $this->request->getPost('street'),
            'sitio'               => $this->request->getPost('sitio'),
            'barangay'            => $barangay === 'Other' ? $barangayOther : $barangay,
            // Calapan City / Oriental Mindoro are fixed — never trust
            // whatever a client sent for these, even though the fields
            // are locked in the UI.
            'city_municipality'   => 'Calapan City',
            'province'            => 'Oriental Mindoro',
            'email'               => $this->request->getPost('email'),
            'password'            => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'contact_number'      => $this->request->getPost('contact_number'),
            'customer_type'       => $customerType,
            'customer_type_other' => $customerType === 'Other' ? $customerTypeOther : null,
            'terms_accepted_at'   => date('Y-m-d H:i:s'),
            'role'                => 'customer',
        ]);

        $user = $this->userModel->find($userId);
        $this->startCustomerSession($user);

        return redirect()->to('home')->with('success', 'Account created. Welcome to GrahamGo, ' . $user['name'] . '!');
    }

    /**
     * Redirects back to the register form, flashing which step it
     * should reopen on. Defaults to the earliest step with an invalid
     * field per REGISTER_STEP_FIELDS; pass an explicit step for checks
     * that happen outside the normal validator (customer_type_other,
     * barangay_other).
     */
    private function backToRegisterStep(?int $forceStep = null)
    {
        $step = $forceStep;

        if ($step === null) {
            $errorFields = array_keys($this->validator->getErrors());
            foreach (self::REGISTER_STEP_FIELDS as $stepNumber => $fields) {
                if (array_intersect($errorFields, $fields)) {
                    $step = $stepNumber;
                    break;
                }
            }
        }

        return redirect()->back()->withInput()->with('step', $step ?? 1);
    }

    public function logout()
    {
        session()->remove('customer');
        session()->regenerate(true);

        return redirect()->to('login');
    }

    public function ownerLogout()
    {
        session()->remove('owner');
        session()->regenerate(true);

        return redirect()->to('login');
    }

    /**
     * Ends BOTH the customer and admin sessions in this browser at once.
     * Exists mainly for shared/public devices: with two roles able to
     * stay signed in side by side, a single "logout" only clears one of
     * them — anyone who picks up the device afterward could still reach
     * the other account's dashboard with no password prompt. This is the
     * one-click way to make sure nothing is left signed in.
     */
    public function logoutAll()
    {
        session()->remove('customer');
        session()->remove('owner');
        session()->regenerate(true);

        return redirect()->to('/')->with('success', 'Logged out of all accounts on this browser.');
    }

    // -------------------------------------------------------------
    // Forgot / reset password via a 6-digit OTP emailed to the account
    // (works for both customer and owner accounts — role is determined
    // by the account itself). Flow: email -> emailed OTP -> verify-otp
    // -> reset-password. The OTP row (password_resets table) is the
    // same one the old link-based flow used; only the token contents
    // (a short numeric code instead of a long random string) and how
    // it's delivered to the browser (typed in, not clicked) changed.
    // -------------------------------------------------------------
    public function forgotPassword()
    {
        return view('auth/forgot_password', ['title' => 'Forgot Password']);
    }

    public function attemptForgotPassword()
    {
        if (! $this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid email address.');
        }

        // Without a limit here, this form could be used to spam an
        // inbox with OTP emails, or to hammer the Gmail SMTP account
        // into a send-rate ban.
        if (! service('throttler')->check(md5('forgot-password-' . $this->request->getIPAddress()), 3, 300)) {
            return redirect()->back()->with('error', 'Too many reset requests. Please wait a few minutes and try again.');
        }

        $email = $this->request->getPost('email');
        $user  = $this->userModel->findByEmail($email);

        // Always show the same message, whether or not the account exists,
        // so the form can't be used to find out which emails are registered.
        $genericMessage = 'If an account exists for that email, a 6-digit code has been sent to it.';

        if ($user) {
            $resetModel = new PasswordResetModel();
            $otp        = (string) random_int(100000, 999999);

            $resetModel->insert([
                'user_id'    => $user['user_id'],
                'token'      => hash('sha256', $otp),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            ]);

            $email_service = mailer();
            $email_service->setTo($user['email']);
            $email_service->setSubject('GrahamGo Password Reset Code');
            $email_service->setMessage(email_template($email_service,
                "<p style=\"margin:0 0 16px;\">Hi " . esc($user['name']) . ",</p>" .
                "<p style=\"margin:0 0 8px;\">Your GrahamGo password reset code is:</p>" .
                "<div style=\"margin:18px 0; text-align:center; background:#FCEEDD; border-radius:12px; padding:16px;\">" .
                "<span style=\"font-size:32px; font-weight:700; letter-spacing:8px; color:#C46F26;\">{$otp}</span>" .
                "</div>" .
                "<p style=\"margin:0; color:#8A7A6A; font-size:13px;\">This code expires in 10 minutes. If you did not request this, you can safely ignore this email.</p>"
            ));
            // The visitor always sees the same generic message either way
            // (see above — this is intentional, not a bug), so a send
            // failure would otherwise be invisible. Log it server-side so
            // it can still be diagnosed from Owner > Settings or the log
            // files, without leaking anything to the visitor.
            if (! $email_service->send()) {
                log_message('error', 'Password reset OTP failed to send to {email}: {debug}', [
                    'email' => $user['email'],
                    'debug' => strip_tags($email_service->printDebugger(['headers'])),
                ]);
            }
        }

        // Only carries the email the visitor themselves just typed (not
        // the OTP), purely to prefill the next form — doesn't leak
        // whether the account actually exists.
        session()->setFlashdata('reset_email', $email);

        return redirect()->to('verify-otp')->with('success', $genericMessage);
    }

    public function verifyOtp()
    {
        return view('auth/verify_otp', [
            'title' => 'Verify Code',
            'email' => session()->getFlashdata('reset_email'),
        ]);
    }

    public function attemptVerifyOtp()
    {
        $rules = [
            'email' => 'required|valid_email',
            'otp'   => 'required|exact_length[6]|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please enter your email and the 6-digit code.');
        }

        $email = $this->request->getPost('email');
        $otp   = $this->request->getPost('otp');

        // A 6-digit code is only ~1M combinations, so this endpoint needs
        // the same dual-bucket brute-force guard as login (per IP and
        // per email), otherwise it could be guessed by brute force.
        $throttler = service('throttler');
        $ipOk      = $throttler->check(md5('verify-otp-ip-' . $this->request->getIPAddress()), 5, 300);
        $emailOk   = $throttler->check(md5('verify-otp-email-' . strtolower($email)), 5, 300);

        if (! $ipOk || ! $emailOk) {
            return redirect()->back()->withInput()->with('error', 'Too many attempts. Please wait a few minutes and try again.');
        }

        $user       = $this->userModel->findByEmail($email);
        $resetModel = new PasswordResetModel();
        $record     = $user ? $resetModel
            ->where('user_id', $user['user_id'])
            ->where('token', hash('sha256', $otp))
            ->where('used', 0)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->orderBy('reset_id', 'DESC')
            ->first() : null;

        if (! $record) {
            return redirect()->back()->withInput()->with('error', 'Invalid or expired code.');
        }

        // The code itself is single-use info now stored server-side
        // (in the session) instead of a token in the URL — the visitor
        // gets a short window to actually set the new password.
        session()->set('otp_verified', [
            'reset_id' => $record['reset_id'],
            'user_id'  => $user['user_id'],
            'expires'  => time() + 600,
        ]);

        return redirect()->to('reset-password');
    }

    public function resetPassword()
    {
        $verified = session('otp_verified');

        if (! $verified || $verified['expires'] < time()) {
            return redirect()->to('forgot-password')->with('error', 'Please verify your code again.');
        }

        return view('auth/reset_password', ['title' => 'Reset Password']);
    }

    public function attemptResetPassword()
    {
        $verified = session('otp_verified');

        if (! $verified || $verified['expires'] < time()) {
            return redirect()->to('forgot-password')->with('error', 'Please verify your code again.');
        }

        $rules = [
            'password'         => 'required|' . UserModel::PASSWORD_RULE,
            'confirm_password' => 'required|matches[password]',
        ];

        $messages = [
            'password' => ['regex_match' => 'Password must be ' . lcfirst(UserModel::PASSWORD_HINT)],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->userModel->update($verified['user_id'], [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        $resetModel = new PasswordResetModel();
        $resetModel->update($verified['reset_id'], ['used' => 1]);

        session()->remove('otp_verified');

        return redirect()->to('login')->with('success', 'Your password has been reset. You may now log in.');
    }

    /**
     * Token-bucket check via CI4's built-in Throttler, keyed two ways at
     * once: per IP (5/min — stops one attacker from brute-forcing any
     * account) and per email (5/min — stops distributed attempts against
     * one specific account). Both buckets must have room left.
     */
    private function withinLoginAttemptLimit(string $email): bool
    {
        $throttler = service('throttler');

        $ipOk    = $throttler->check(md5('login-ip-' . $this->request->getIPAddress()), 5, 60);
        $emailOk = $throttler->check(md5('login-email-' . strtolower($email)), 5, 60);

        return $ipOk && $emailOk;
    }

    private function startCustomerSession(array $user): void
    {
        session()->regenerate();
        session()->set('customer', [
            'user_id' => $user['user_id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'avatar'  => $user['avatar'] ?? null,
        ]);
    }

    private function startOwnerSession(array $user): void
    {
        session()->regenerate();
        session()->set('owner', [
            'user_id' => $user['user_id'],
            'name'    => $user['name'],
            'email'   => $user['email'],
            'avatar'  => $user['avatar'] ?? null,
        ]);
    }
}
