<?php
/**
 * ajax_ai_autofill.php
 * Handles AI parsing of raw text (LinkedIn/Old CV) into structured CV JSON.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';

$data = json_decode(file_get_contents('php://input'), true);
$rawText = $data['text'] ?? '';

if (empty($rawText)) {
    echo json_encode(['success' => false, 'error' => 'Texte vide']);
    exit;
}

$cvc = new CVC();

// Custom prompt for structuring data
$prompt = "Tu es un expert en recrutement. Analyse le texte brut suivant (qui peut être un profil LinkedIn ou un vieux CV) et extrait les informations pour remplir un formulaire de CV.
Renvoie UNIQUEMENT un objet JSON strictement formaté comme suit :
{
  \"nomComplet\": \"string\",
  \"titrePoste\": \"string\",
  \"email\": \"string\",
  \"telephone\": \"string\",
  \"adresse\": \"string\",
  \"resume\": \"string (un résumé professionnel accrocheur basé sur les infos)\",
  \"experience\": [
    {
      \"role\": \"string\",
      \"company\": \"string\",
      \"dates\": \"string\",
      \"achievements\": [{\"text\": \"string\"}]
    }
  ],
  \"education\": [
    {
      \"degree\": \"string\",
      \"school\": \"string\",
      \"dates\": \"string\"
    }
  ],
  \"skills\": [\"string\"],
  \"languages\": [{\"lang\": \"string\", \"level\": \"A1|A2|B1|B2|C1|C2\"}]
}

Si une information est manquante, laisse une chaîne vide ou un tableau vide.
Texte à analyser :
$rawText";

try {
    $jsonResponse = $cvc->generateJSON($prompt);
    echo json_encode(['success' => true, 'data' => $jsonResponse]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
