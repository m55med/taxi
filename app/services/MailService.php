<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mailer;

    public function __construct()
    {
        $required_vars = ['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_PORT', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'];
        foreach ($required_vars as $var) {
            if (empty($_ENV[$var])) {
                throw new Exception("Mail configuration error: Environment variable {$var} is not set. Please check your .env file.");
            }
        }

        $this->mailer = new PHPMailer(true); // Enable exceptions

        try {
            // Server settings from .env
            $this->mailer->isSMTP();
            $this->mailer->Host       = $_ENV['MAIL_HOST'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $_ENV['MAIL_USERNAME'];
            $this->mailer->Password   = $_ENV['MAIL_PASSWORD'];
            $this->mailer->SMTPSecure = $_ENV['MAIL_SMTP_SECURE'] ?? 'tls'; // SMTPSecure can be optional
            $this->mailer->Port       = $_ENV['MAIL_PORT'];
            $this->mailer->CharSet    = 'UTF-8';

            // Recipients
            $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);

        } catch (Exception $e) {
            // Log error but don't expose details
            error_log("Mailer configuration error: {$this->mailer->ErrorInfo}");
            // Optionally, throw a custom, more generic exception
            throw new \Exception("Could not configure the mail service. Please check the logs.");
        }
    }

    /**
     * Send an email.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The email subject.
     * @param string $body The email body (HTML).
     * @param string $altBody The plain text alternative for non-HTML mail clients.
     * @return bool True on success, false on failure.
     */
    public function send(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);

            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
} 