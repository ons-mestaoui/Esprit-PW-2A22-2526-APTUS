<?php
/**
 * ajax_tuteur.php — Routeur AJAX pour la gestion des tuteurs
 * Reçoit les actions POST depuis tuteurs_admin.php
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

require_once __DIR__ . '/TuteurController.php';

$tuteurC = new TuteurController();
$tuteurC->handleAjax();
