<?php
/**
 * ═══ ROUTEUR AJAX - FRONTOFFICE (Offres) ═══
 */
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();
require_once __DIR__ . '/../../controller/offreC.php';

$offreC = new offreC();
$offreC->handleAjax();
