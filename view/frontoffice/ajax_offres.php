<?php
/**
 * ═══ ROUTEUR AJAX - FRONTOFFICE (Offres) ═══
 */
session_start();
require_once __DIR__ . '/../../controller/offreC.php';

$offreC = new offreC();
$offreC->handleAjax();
