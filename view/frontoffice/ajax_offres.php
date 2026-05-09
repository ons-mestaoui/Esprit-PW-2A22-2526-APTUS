<?php
/**
 * ═══ ROUTEUR AJAX - FRONTOFFICE (Offres) ═══
 */
require_once __DIR__ . '/../../controller/offreC.php';

$offreC = new offreC();
$offreC->handleAjax();
