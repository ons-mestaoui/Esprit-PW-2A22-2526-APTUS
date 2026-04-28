<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

$type    = 'none';
$message = '';

if (!empty($_SESSION['flash_success'])) {
    $type    = 'success';
    $message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
} elseif (!empty($_SESSION['flash_error'])) {
    $type    = 'error';
    $message = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

echo json_encode(['type' => $type, 'message' => $message]);
