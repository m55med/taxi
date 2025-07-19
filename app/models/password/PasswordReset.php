<?php

namespace App\Models\Password;

use App\Core\Database;
use PDO;
use PDOException;

class PasswordReset
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new password reset token.
     *
     * @param string $email
     * @param string $token
     * @param string|null $otp
     * @param string $expiresAt
     * @return bool
     */
    public function createResetToken(string $email, string $token, ?string $otp, string $expiresAt): bool
    {
        try {
            // Delete any existing tokens for this email to ensure only one is active
            $this->deleteResetToken($email);

            $sql = "INSERT INTO password_resets (email, token, otp, expires_at) VALUES (:email, :token, :otp, :expires_at)";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':otp' => $otp ?? '',
                ':expires_at' => $expiresAt,
            ]);
        } catch (PDOException $e) {
            error_log("Error creating password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds a password reset record by its token.
     *
     * @param string $token
     * @return array|false
     */
    public function findResetToken(string $token)
    {
        try {
            $sql = "SELECT * FROM password_resets WHERE token = :token";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a password reset token for a given email.
     *
     * @param string $email
     * @return bool
     */
    public function deleteResetToken(string $email): bool
    {
        try {
            $sql = "DELETE FROM password_resets WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':email' => $email]);
        } catch (PDOException $e) {
            error_log("Error deleting password reset token: " . $e->getMessage());
            return false;
        }
    }
} 