<?php

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class SettingController extends BaseController
{
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $settings = $this->settingModel->getMany(['smtp_email', 'smtp_from_name']);

        return view('owner/settings/index', [
            'title'    => 'Settings',
            'settings' => $settings,
            'hasAppPassword' => (bool) $this->settingModel->getValue('smtp_app_password'),
            'maxReservationDaysAhead' => (int) $this->settingModel->getValue('max_reservation_days_ahead', '7'),
        ]);
    }

    /**
     * How far ahead a customer may pick a claim date when reserving — the
     * business's perishable dessert shelf life makes an unbounded claim
     * date risky (product would spoil before pickup), so this caps it.
     * Read by Customer\ReservationController for both the date picker's
     * max and the server-side check.
     */
    public function updateReservationRules()
    {
        $rules = ['max_reservation_days_ahead' => 'required|integer|greater_than[0]|less_than_equal_to[90]'];

        if (! $this->validate($rules)) {
            return redirect()->to('owner/settings')->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->settingModel->setValue('max_reservation_days_ahead', $this->request->getPost('max_reservation_days_ahead'));

        return redirect()->to('owner/settings')->with('success', 'Reservation rules updated.');
    }

    public function update()
    {
        $rules = [
            'smtp_email'     => 'permit_empty|valid_email',
            'smtp_from_name' => 'permit_empty|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->settingModel->setValue('smtp_email', $this->request->getPost('smtp_email'));
        $this->settingModel->setValue('smtp_from_name', $this->request->getPost('smtp_from_name') ?: 'GrahamGo');

        $appPassword = $this->request->getPost('smtp_app_password');
        if (! empty($appPassword)) {
            // Gmail app passwords are shown with spaces; SMTP auth needs them removed.
            $this->settingModel->setValue('smtp_app_password', str_replace(' ', '', $appPassword));
        }

        return redirect()->to('owner/settings')->with('success', 'Email settings updated.');
    }

    public function sendTest()
    {
        $senderEmail = $this->settingModel->getValue('smtp_email');

        if (! $senderEmail) {
            return redirect()->to('owner/settings')->with('error', 'Set up and save the sender email first.');
        }

        $recipient = $this->request->getPost('test_email') ?: $senderEmail;

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('owner/settings')->with('error', 'Please enter a valid email to send the test to.');
        }

        $emailService = mailer();
        $emailService->setTo($recipient);
        $emailService->setSubject('GrahamGo Test Email');
        $emailService->setMessage(email_template($emailService,
            "<p style=\"margin:0 0 8px;\">This is a test email from your GrahamGo system.</p>" .
            "<p style=\"margin:0; color:#3FA772; font-weight:600;\">If you received this, your email settings are working correctly.</p>"
        ));

        if ($emailService->send()) {
            return redirect()->to('owner/settings')->with('success', 'Test email sent to ' . $recipient . '. Check the inbox (and spam folder).');
        }

        return redirect()->to('owner/settings')->with('error', 'Failed to send test email: ' . implode(' ', $emailService->printDebugger(['headers'])));
    }
}
