<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Prevent browser caching of the logout action
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Best Practice: Tell modern browsers to clear all site data upon logout
header('Clear-Site-Data: "cache", "cookies", "storage"');

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Explicitly clear the session cookie from the browser
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

header("Location: login.php");
exit();
?>
