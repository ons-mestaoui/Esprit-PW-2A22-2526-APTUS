<?php
/**
 * 🚀 ajax_handler_back.php — Point d'entrée AJAX unique pour le Back-office (MVC Compliance)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/TuteurController.php';

$action = $_REQUEST['action'] ?? '';

// Dispatcher selon le domaine
if (strpos($action, 'tuteur') !== false || strpos($action, 'creneau') !== false) {
    $tuteurC = new TuteurController();
    $tuteurC->handleAjax();
} else {
    $formationC = new FormationController();
    $formationC->handleAjax();
}
