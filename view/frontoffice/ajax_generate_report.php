<?php
require_once __DIR__ . '/../../controller/candidatureC.php';
require_once __DIR__ . '/../../controller/scoreAIC.php';

if (isset($_POST['id_candidature'])) {
    $id = $_POST['id_candidature'];
    $candC = new candidatureC();
    $scoreAI = new scoreAIC();

    // 1. Récupérer les données de la candidature et de l'offre
    $cand = $candC->getCandidatureById($id);
    
    // Récupérer l'offre associée
    $db = config::getConnexion();
    $sqlOffre = "SELECT titre, description, question FROM offreemploi WHERE id_offre = :id";
    $reqOffre = $db->prepare($sqlOffre);
    $reqOffre->execute(['id' => $cand['id_offre']]);
    $offre = $reqOffre->fetch();

    if ($cand && $offre) {
        // 1.5 Vérifier si un rapport existe déjà
        if (!empty($cand['ai_report'])) {
            echo json_encode(['status' => 'success', 'report' => $cand['ai_report']]);
            exit;
        }

        // 2. Construire le prompt détaillé
        $prompt = "Détails de l'Offre : " . $offre['titre'] . "\nDescription : " . $offre['description'] . "\nQuestion posée : " . $offre['question'] . "\n\n";
        $prompt .= "Profil Candidat : " . $cand['prenom'] . " " . $cand['nom'] . "\nRéponse du candidat : " . $cand['reponses_ques'] . "\n\n";
        $prompt .= "Rédige un rapport complet avec Points Forts, Points de Vigilance et Impression Générale.";

        // 3. Générer le rapport via l'IA puissante
        $report = $scoreAI->genererRapportDetailed($prompt);

        // 4. Sauvegarder dans la DB
        $candC->saveAiReport($id, $report);

        echo json_encode(['status' => 'success', 'report' => $report]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Candidature ou offre introuvable.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant.']);
}
?>
