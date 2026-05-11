<?php
/**
 * SessionManager.php
 * Aptus AI — Centralized Authentication & Session Management
 * 
 * This class ensures a secure and consistent way to handle user sessions
 * across the platform, using the project standard 'id_utilisateur'.
 */

class SessionManager
{
    /**
     * Start the session safely if not already started.
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get the currently logged-in User ID.
     * 
     * @param bool $allowMock Whether to allow falling back to URL params (for dev only).
     * @return int|null The user ID or null if not authenticated.
     */
    public static function getUserId($allowMock = false)
    {
        self::start();

        // 1. Priority: Real session key matching DB column name
        if (isset($_SESSION['id_utilisateur'])) {
            return (int) $_SESSION['id_utilisateur'];
        }

        // 2. Legacy Support: id_user or user_id (Migration phase)
        if (isset($_SESSION['id_user'])) {
            return (int) $_SESSION['id_user'];
        }
        if (isset($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        // 3. Secondary: URL override for testing (Only if allowMock is explicitly true)
        if ($allowMock) {
            if (isset($_GET['id_utilisateur']))
                return (int) $_GET['id_utilisateur'];
            if (isset($_GET['user_id']))
                return (int) $_GET['user_id'];
        }

        return null;
    }

    /**
     * Check if a user is currently logged in.
     */
    public static function isLoggedIn()
    {
        self::start();
        return isset($_SESSION['id_utilisateur']) || isset($_SESSION['id_user']) || isset($_SESSION['user_id']);
    }

    /**
     * Set the session for a specific user (login).
     * @param int $userId
     * @param array $userData Optional extra data to store
     */
    public static function login($userId, $userData = [])
    {
        self::start();
        $_SESSION['id_utilisateur'] = $userId;
        foreach ($userData as $key => $value) {
            $_SESSION[$key] = $value;
        }
        session_regenerate_id(true);
    }

    /**
     * Clear the session (logout).
     */
    public static function logout()
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Restrict access to a page. Redirects to login if not authenticated.
     */
    public static function requireLogin($redirect = "login.php")
    {
        if (!self::isLoggedIn()) {
            header("Location: $redirect");
            exit();
        }
    }
}
