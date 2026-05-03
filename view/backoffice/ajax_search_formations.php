<?php
/**
 * ajax_search_formations.php — Routeur AJAX pour la recherche de formations (Back-office)
 * MVC Compliance : Délègue toute la logique au FormationController
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../controller/FormationController.php';

$formationC = new FormationController();
// On force l'action à 'search_formations' car ce fichier est dédié à la recherche
$_REQUEST['action'] = 'search_formations';
$formationC->handleAjax();
