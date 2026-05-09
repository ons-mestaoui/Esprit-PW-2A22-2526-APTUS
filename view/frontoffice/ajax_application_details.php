<?php
require_once '../../controller/candidatureC.php';
require_once '../../controller/offreC.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $candidatureC = new candidatureC();
    $offreC = new offreC();
    
    $cand = $candidatureC->getCandidatureById($id);
    
    if ($cand) {
        $offre = $offreC->getOffreById($cand['id_offre']);
        
        $statusClass = 'status-pending';
        if ($cand['statut'] === 'Accepté') $statusClass = 'status-accepted';
        if ($cand['statut'] === 'Refusé') $statusClass = 'status-rejected';

        echo json_encode([
            'success' => true,
            'titre' => $offre['titre'],
            'entreprise' => $offre['nom_entreprise'],
            'question' => $offre['question'] ?? 'Parlez-nous de vous...',
            'reponse' => $cand['reponses_ques'],
            'date' => date('d M Y', strtotime($cand['date_candidature'])),
            'statut' => $cand['statut'],
            'statusClass' => $statusClass,
            'cv' => $cand['cv__cand'] // Base64 avec double underscore
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Candidature introuvable']);
    }
}
?>
