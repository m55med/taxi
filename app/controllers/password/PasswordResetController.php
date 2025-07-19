<?php

namespace App\Controllers\Password;

use App\Core\Controller;
use App\Models\User\User;
use App\Models\Password\PasswordReset;
use App\Services\MailService;
use Exception;

class PasswordResetController extends Controller
{
    private $userModel;
    private $passwordResetModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->passwordResetModel = new PasswordReset();
    }

    /**
     * Show the form to request a password reset link.
     */
    public function showRequestForm()
    {
        $this->view('password/request');
    }

    /**
     * Handle the submission of the password reset request form.
     */
    public function handleRequestForm()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('password/request', ['error' => 'Please enter a valid email address.']);
            return;
        }

        if (!$this->userModel->isEmailExists($email)) {
            $this->view('password/request', ['error' => 'No account found with that email address.']);
            return;
        }

        try {
            $token = bin2hex(random_bytes(32));
            // $otp = random_int(100000, 999999); // OTP is no longer needed
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // Token expires in 1 hour

            $this->passwordResetModel->createResetToken($email, $token, null, $expiresAt); // Store null for OTP

            // Send email
            $mailService = new MailService();
            $resetLink = BASE_URL . "/reset-password/{$token}";
            $subject = 'Your Password Reset Request';
            $body = $this->createEmailBody($resetLink);
            
            if (!$mailService->send($email, $subject, $body)) {
                $this->view('password/request', ['error' => 'We could not send the password reset email. Please try again later.']);
                return;
            }

            $this->view('password/success', ['message' => 'A password reset link has been sent to your email.']);

        } catch (Exception $e) {
            error_log($e);
            $this->view('password/request', ['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Show the form to reset the password.
     * @param string $token The reset token from the URL.
     */
    public function showResetForm($token)
    {
        $tokenData = $this->passwordResetModel->findResetToken($token);

        if (!$tokenData || strtotime($tokenData['expires_at']) < time()) {
            // Token is invalid or expired, show an error message within the view
            $this->view('password/reset', [
                'error' => 'This password reset token is invalid or has expired.',
                'token' => null // Ensure token is not passed to the form
            ]);
            return;
        }

        $this->view('password/reset', ['token' => $token]);
    }
    
    /**
     * Handle the password reset form submission.
     */
    public function handleReset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $token = $_POST['token'] ?? '';
        // $otp = $_POST['otp'] ?? ''; // OTP is no longer needed
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $tokenData = $this->passwordResetModel->findResetToken($token);

        if (!$tokenData || strtotime($tokenData['expires_at']) < time()) {
            $this->view('password/reset', ['error' => 'This password reset token is invalid or has expired.', 'token' => $token]);
            return;
        }

        /* OTP check is removed
        if ($tokenData['otp'] !== $otp) {
            $this->view('password/reset', ['error' => 'The OTP you entered is incorrect.', 'token' => $token]);
            return;
        }
        */

        if (empty($password) || strlen($password) < 6) {
            $this->view('password/reset', ['error' => 'Password must be at least 6 characters long.', 'token' => $token]);
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->view('password/reset', ['error' => 'Passwords do not match.', 'token' => $token]);
            return;
        }
        
        // Update user's password
        $this->userModel->updatePasswordByEmail($tokenData['email'], $password);

        // Delete the token
        $this->passwordResetModel->deleteResetToken($tokenData['email']);

        $_SESSION['success'] = 'Your password has been reset successfully. You can now log in.';
        header('Location: ' . BASE_URL . '/login');
        exit();
    }
    
    /**
     * Creates the HTML body for the password reset email.
     */
    private function createEmailBody($resetLink): string
    {
        $year = date('Y');
        $appName = $_ENV['APP_NAME'] ?? 'Your App';

        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
                .content { padding: 20px 0; }
                .button { display: block; width: 200px; margin: 20px auto; padding: 15px 10px; background-color: #007bff; color: #ffffff; text-align: center; text-decoration: none; border-radius: 5px; font-size: 16px; }
                .link-text { text-align: center; margin-top: 15px; font-size: 12px; color: #777; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>Hello,</p>
                    <p>We received a request to reset the password for your account. Please click the button below to set a new password.</p>
                    <a href='{$resetLink}' class='button'>Reset Password</a>
                    <p>This link is valid for 1 hour.</p>
                    <p>If you did not request a password reset, please ignore this email. No changes will be made to your account.</p>
                    <div class='link-text'>
                        <p>If the button doesn't work, copy and paste this link into your browser:</p>
                        <p>{$resetLink}</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; {$year} {$appName}. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
} 