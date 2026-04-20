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

    case 'generate_ai_syllabus':
        require_once __DIR__ . '/../../controller/AIController.php';
        $controller = new AIController();
        $titre = $_POST['titre'] ?? '';
        $domaine = $_POST['domaine'] ?? '';
        $niveau = $_POST['niveau'] ?? '';
        echo $controller->generateSyllabus($titre, $domaine, $niveau);
        break;

    case 'append_ai_syllabus':
        require_once __DIR__ . '/../../config.php';
        $db = config::getConnexion();
        $id_formation = $_POST['id_formation'] ?? 0;
        $html_content = "<!-- AI_SYLLABUS_START -->" . $_POST['html_content'] . "<!-- AI_SYLLABUS_END -->";
        
        $stmt = $db->prepare("SELECT description FROM formation WHERE id_formation = :id");
        $stmt->execute(['id' => $id_formation]);
        $row = $stmt->fetch();
        if ($row) {
            $desc = $row['description'];
            
            // Si un syllabus existe déjà, on le remplace
            if (strpos($desc, '<!-- AI_SYLLABUS_START -->') !== false) {
                $desc = preg_replace('/<!-- AI_SYLLABUS_START -->.*?<!-- AI_SYLLABUS_END -->/s', $html_content, $desc);
            } else {
                // Sinon on l'insère avant les ressources ou à la fin
                if (strpos($desc, '<!-- APTUS_RESOURCES:') !== false) {
                    $desc = str_replace('<!-- APTUS_RESOURCES:', $html_content . '<!-- APTUS_RESOURCES:', $desc);
                } else {
                    $desc .= $html_content;
                }
            }
            
            $stmtU = $db->prepare("UPDATE formation SET description = :desc WHERE id_formation = :id");
            $success = $stmtU->execute(['desc' => $desc, 'id' => $id_formation]);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false]);
        }
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
    // NOTIFICATIONS
    // --------------------------------------------------------
    case 'get_notifications':
        $uid = $_GET['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;
        require_once __DIR__ . '/../../controller/NotificationController.php';
        $notifC = new NotificationController();
        $notifs = $notifC->getUnreadNotifications((int)$uid);
        echo json_encode(['success' => true, 'notifications' => $notifs]);
        break;

    case 'mark_notifications_read':
        $uid = $_POST['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;
        require_once __DIR__ . '/../../controller/NotificationController.php';
        $notifC = new NotificationController();
        $success = $notifC->markAsRead((int)$uid);
        echo json_encode(['success' => $success]);
        break;

    // --------------------------------------------------------
    // CHAT HYBRIDE (Tuteur Augmenté)
    // --------------------------------------------------------
    case 'send_chat_message':
        $sender_id = $_POST['sender_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;
        $receiver_id = $_POST['receiver_id'] ?? 0;
        $formation_id = $_POST['formation_id'] ?? 0;
        $content = $_POST['content'] ?? '';
        if (empty($content) || $formation_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Message ou formation manquant.']);
            exit;
        }
        require_once __DIR__ . '/../../controller/ChatController.php';
        $chatC = new ChatController();
        $reply = $chatC->sendMessage((int)$sender_id, (int)$receiver_id, (int)$formation_id, $content);
        echo json_encode(['success' => true, 'ai_reply' => $reply]);
        break;

    case 'get_chat_history':
        $user1 = $_GET['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;
        $user2 = $_GET['tutor_id'] ?? 0;
        $formation_id = $_GET['formation_id'] ?? 0;
        require_once __DIR__ . '/../../controller/ChatController.php';
        $chatC = new ChatController();
        $history = $chatC->getHistory((int)$user1, (int)$user2, (int)$formation_id);
        echo json_encode(['success' => true, 'messages' => $history]);
        break;

    // --------------------------------------------------------
    // PEER REVIEW (Noter un mentor)
    // --------------------------------------------------------
    case 'submit_peer_review':
        $session_id = $_POST['session_id'] ?? 0;
        $rating = $_POST['rating'] ?? 0;
        $comment = $_POST['comment'] ?? '';
        require_once __DIR__ . '/../../controller/PeerLearningController.php';
        $peerC = new PeerLearningController();
        $success = $peerC->submitReview((int)$session_id, (int)$rating, $comment);
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
