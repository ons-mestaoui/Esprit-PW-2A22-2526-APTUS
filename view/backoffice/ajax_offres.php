<?php
/**
 * ═══ ROUTEUR AJAX - BACKOFFICE (Offres Admin) ═══
 */
session_start();
require_once __DIR__ . '/../../controller/offreC.php';

$offreC = new offreC();
$offreC->handleAjax();
