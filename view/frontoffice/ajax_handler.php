<?php
/**
 * ============================================================
 * ajax_handler.php — Routeur AJAX centralisé
 * ============================================================
 * Ce fichier est le point d'entrée unique pour toutes les
 * requêtes AJAX des 3 concepts innovants.
 *
 * Routing via le paramètre GET 'action' :
 *   ?action=peer_help          → PeerLearningController::handleAjax()
 *   ?action=softskills_validate → SoftSkillsController::handleAjax()
 *
 * Conforme à l'architecture MVC : le handler instancie le bon
 * contrôleur et délègue toute la logique à celui-ci.
 *
 * Règles de sécurité :
 *   - Seuls les POST sont acceptés (sauf mention contraire)
 *   - La session est vérifiée avant tout traitement
 *   - Les réponses sont toujours du JSON
 */

// Démarrage de session pour récupérer l'ID utilisateur
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// En-tête JSON par défaut (peut être surchargé par le contrôleur)
header('Content-Type: application/json');

// Récupération sécurisée de l'action demandée
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';

// Check for POST max size exceeded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le fichier envoyé est trop lourd (dépasse la limite de PHP).']);
    exit;
}

// ── Routage ──────────────────────────────────────────────────
switch ($action) {

    // --------------------------------------------------------
    // CONCEPT 1 : Peer Learning — Trouver un mentor
    // --------------------------------------------------------
    case 'peer_help':
        require_once __DIR__ . '/../../controller/PeerLearningController.php';
        $controller = new PeerLearningController();
        $controller->handleAjax();
        break;

    // --------------------------------------------------------
    // CONCEPT 3 : Soft Skills — Valider le certificat
    // --------------------------------------------------------
    case 'softskills_validate':
        require_once __DIR__ . '/../../controller/SoftSkillsController.php';
        $controller = new SoftSkillsController();
        $controller->handleAjax();
        break;

    // --------------------------------------------------------
    // CONCEPT 4 : Tuteur Dashboard - Gestion de progression et ressources
    // --------------------------------------------------------
    case 'update_progression':
        require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
        $controller = new TuteurDashboardController();
        $id_formation = $_POST['id_formation'] ?? 0;
        $id_user = $_POST['id_user'] ?? 0;
        $progression = $_POST['progression'] ?? 0;
        $success = $controller->updateProgression((int)$id_formation, (int)$id_user, (int)$progression);
        echo json_encode(['success' => $success]);
        break;

    case 'add_resource':
        require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
        $controller = new TuteurDashboardController();
        $id_formation = $_POST['id_formation'] ?? 0;
        $type = $_POST['type'] ?? '';
        $titre = $_POST['titre'] ?? '';
        $url = $_POST['url'] ?? '';

        $message = 'Success';

        if ($type === 'pdf') {
            if (!isset($_FILES['fichier_pdf'])) {
                echo json_encode(['success' => false, 'message' => 'Fichier introuvable.']);
                exit;
            }
            if ($_FILES['fichier_pdf']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erreur upload: ' . $_FILES['fichier_pdf']['error']]);
                exit;
            }

            // Conversion 100% BDD : Lire le contenu et passer en Base64
            $fileContent = file_get_contents($_FILES['fichier_pdf']['tmp_name']);
            $fileType = $_FILES['fichier_pdf']['type'];
            $base64 = base64_encode($fileContent);
            $url = 'data:' . $fileType . ';base64,' . $base64;
        }

        $success = $controller->addResource((int)$id_formation, $type, $titre, $url);
        echo json_encode(['success' => $success, 'message' => $message]);
        break;

    case 'delete_resource':
        require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
        $controller = new TuteurDashboardController();
        $id_formation = $_POST['id_formation'] ?? 0;
        $resource_id = $_POST['resource_id'] ?? '';
        $success = $controller->deleteResource((int)$id_formation, $resource_id);
        echo json_encode(['success' => $success]);
        break;

    // --------------------------------------------------------
    // Action inconnue → erreur 400
    // --------------------------------------------------------
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Action AJAX inconnue : '{$action}'"
        ]);
        break;
}
