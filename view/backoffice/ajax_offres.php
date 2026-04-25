<?php
/**
 * ═══ ROUTEUR AJAX - BACKOFFICE (Offres Admin) ═══
 */
require_once __DIR__ . '/../../controller/offreC.php';

$offreC = new offreC();
$offreC->handleAjax();
