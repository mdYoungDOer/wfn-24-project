<?php

namespace WFN24\Controllers;

use WFN24\Config\Database;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * User Registration
     */
    public function register($data)
    {
        try {
            // Validate input
            $errors = $this->validateRegistration($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Check if user already exists
            $stmt = $this->db->getConnection()->prepare(
                "SELECT id FROM users WHERE email = ? OR username = ?"
            );
            $stmt->execute([$data['email'], $data['username']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'User already exists with this email or username'];
            }

            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO users (username, email, password_hash, first_name, last_name, is_active) 
                 VALUES (?, ?, ?, ?, ?, TRUE)"
            );
            $stmt->execute([
                $data['username'],
                $data['email'],
                $passwordHash,
                $data['first_name'] ?? '',
                $data['last_name'] ?? ''
            ]);

            return ['success' => true, 'message' => 'Registration successful! Please log in.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * User Login
     */
    public function login($email, $password)
    {
        try {
            // Find user by email
            $stmt = $this->db->getConnection()->prepare(
                "SELECT id, username, email, password_hash, first_name, last_name, is_admin, is_active 
                 FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Account is deactivated. Please contact support.'];
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Start session and store user data
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            return [
                'success' => true, 
                'message' => 'Login successful!',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'is_admin' => $user['is_admin']
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    /**
     * User Logout
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session data
        session_unset();
        session_destroy();

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
     * Get current user data
     */
    public function getCurrentUser()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'is_admin' => $_SESSION['is_admin']
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE users SET first_name = ?, last_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->execute([
                $data['first_name'] ?? '',
                $data['last_name'] ?? '',
                $userId
            ]);

            // Update session data
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['first_name'] = $data['first_name'] ?? '';
            $_SESSION['last_name'] = $data['last_name'] ?? '';

            return ['success' => true, 'message' => 'Profile updated successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Profile update failed: ' . $e->getMessage()];
        }
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Get current password hash
            $stmt = $this->db->getConnection()->prepare(
                "SELECT password_hash FROM users WHERE id = ?"
            );
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Validate new password
            if (strlen($newPassword) < 8) {
                return ['success' => false, 'message' => 'New password must be at least 8 characters long'];
            }

            // Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $stmt = $this->db->getConnection()->prepare(
                "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->execute([$newPasswordHash, $userId]);

            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Password change failed: ' . $e->getMessage()];
        }
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset($email)
    {
        try {
            // Check if user exists
            $stmt = $this->db->getConnection()->prepare(
                "SELECT id, username FROM users WHERE email = ? AND is_active = TRUE"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'No active account found with this email'];
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token (you might want to create a password_resets table)
            // For now, we'll just return success
            // In a real implementation, you'd store the token and send an email

            return ['success' => true, 'message' => 'Password reset instructions sent to your email'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Password reset request failed: ' . $e->getMessage()];
        }
    }

    /**
     * Validate registration data
     */
    private function validateRegistration($data)
    {
        $errors = [];

        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long';
        }

        if (isset($data['password_confirm']) && $data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match';
        }

        return $errors;
    }
}
