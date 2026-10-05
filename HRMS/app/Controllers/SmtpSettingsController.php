<?php

namespace App\Controllers;

use Config\Email as EmailConfig;

/**
 * SMTP credentials live in .env (ops-managed secrets, not a per-tenant DB
 * setting — see the note in .env next to email.SMTPHost) — this is a
 * diagnostic page over that config, not a credential editor. Storing an
 * SMTP password in the tenant database would need its own encryption-at-rest
 * handling this app doesn't have; .env is the correct place for it.
 */
class SmtpSettingsController extends BaseController
{
    public function index()
    {
        $config = config(EmailConfig::class);

        return view('settings/smtp', [
            'title'  => 'Email (SMTP) Settings',
            'config' => [
                'protocol'   => $config->protocol,
                'host'       => $config->SMTPHost,
                'port'       => $config->SMTPPort,
                'crypto'     => $config->SMTPCrypto,
                'user'       => $config->SMTPUser ? substr($config->SMTPUser, 0, 3) . str_repeat('•', max(0, strlen($config->SMTPUser) - 3)) : '',
                'mailType'   => $config->mailType,
                'fromEmail'  => $config->fromEmail,
                'fromName'   => $config->fromName,
                'configured' => $config->protocol === 'smtp' && $config->SMTPHost !== '',
            ],
        ]);
    }

    public function sendTest()
    {
        if ($this->rateLimited('smtp_test', 5, MINUTE)) {
            return redirect()->back()->with('error', 'Too many test emails sent. Please wait a minute.');
        }

        $to = trim((string) $this->request->getPost('test_email'));
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Enter a valid email address to send the test to.');
        }

        $email = service('email');
        $email->setTo($to);
        $email->setSubject('HRMS test email — ' . company_name());
        $email->setMessage('<p>This is a test email from ' . esc(company_name()) . '\'s HRMS instance. If you received this, outbound SMTP is working.</p>');

        if ($email->send()) {
            return redirect()->back()->with('success', "Test email sent to {$to}.");
        }

        return redirect()->back()->with('error', 'Could not send the test email: ' . strip_tags($email->printDebugger(['headers'])));
    }
}
