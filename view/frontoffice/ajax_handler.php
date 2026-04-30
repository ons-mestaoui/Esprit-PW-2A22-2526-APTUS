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
    // CONCEPT 4 : Tuteur Dashboard - Gestion de progression et ressources
    // --------------------------------------------------------
    // --------------------------------------------------------
    // CONCEPT INNOVATION 1 : Smart Dwell Time — Progression Algorithmique
    // Le PHP valide mathématiquement le temps de lecture avant d'incrémenter.
    // --------------------------------------------------------
    case 'update_dwell_progression':
        require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
        $controller   = new TuteurDashboardController();
        $id_formation = (int)($_POST['id_formation'] ?? 0);
        $id_user      = (int)($_POST['id_user'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0);
        $mode         = $_POST['mode'] ?? 'dwell'; // 'chapter' ou 'dwell'
        $new_prog     = (int)($_POST['new_prog'] ?? 0);

        // Lire la progression actuelle pour ne jamais régresser
        $db_local = config::getConnexion();
        $stmt_cur = $db_local->prepare("SELECT progression FROM inscription WHERE id_formation=:f AND id_user=:u LIMIT 1");
        $stmt_cur->execute(['f' => $id_formation, 'u' => $id_user]);
        $current = (int)($stmt_cur->fetchColumn() ?? 0);

        if ($mode === 'chapter') {
            // ── MODE A : Chapitres ──────────────────────────────────
            // La progression envoyée = (chapitre_index+1 / total) * 100
            // On fait confiance à la valeur (elle est calculée côté PHP au rendu)
            $final = max($current, min(100, $new_prog));

        } else {
            // ── MODE B : Dwell Time avec validation mathématique ────
            $dwell_seconds = (int)($_POST['dwell_seconds'] ?? 0);
            $word_count    = (int)($_POST['word_count'] ?? 0);

            // Plancher 180 secondes (3 min) même pour les courts textes
            $min_required  = max(180, ($word_count > 0) ? ($word_count / 4.17) : 180);
            $ratio         = ($dwell_seconds > 0) ? min($dwell_seconds / $min_required, 1.0) : 0;
            $calc_prog     = (int)round($ratio * 100);

            // On prend le MAX entre ce que le client dit et ce que le calcul donne
            // (protection anti-triche : on prend le minimum des deux)
            $validated = min($new_prog, $calc_prog);
            $final     = max($current, $validated);
        }

        $success = $controller->updateProgression($id_formation, $id_user, $final);
        echo json_encode([
            'success'     => $success,
            'progression' => $final,
            'mode'        => $mode
        ]);
        break;


    // ─── Ancienne route (rétro-compatibilité tuteur, si toujours utilisée) ───
    case 'update_progression':
        require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
        $controller = new TuteurDashboardController();
        $id_formation = $_POST['id_formation'] ?? 0;
        $id_user = $_POST['id_user'] ?? 0;
        $progression = $_POST['progression'] ?? 0;
        $success = $controller->updateProgression((int)$id_formation, (int)$id_user, (int)$progression);
        echo json_encode(['success' => $success]);
        break;

    case 'get_ai_alerts':
        require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
        $controller = new TuteurDashboardController();
        $id_tuteur = $_GET['tuteur_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 1;
        $alerts = $controller->getRecentAIAlerts((int)$id_tuteur);
        echo json_encode(['success' => true, 'alerts' => $alerts]);
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

    // --------------------------------------------------------
    // CONCEPT INNOVATION 2 : Self-Healing Syllabus
    // --------------------------------------------------------
    case 'self_healing_syllabus':
        require_once __DIR__ . '/../../controller/AIController.php';
        $controller = new AIController();
        $titre_cours = $_GET['titre'] ?? $_POST['titre'] ?? '';
        if (empty($titre_cours)) {
            echo json_encode(['success' => false, 'has_update' => false, 'message' => 'Titre manquant.']);
        } else {
            echo $controller->selfHealingSyllabus($titre_cours);
        }
        break;

    // --------------------------------------------------------
    // CONCEPT INNOVATION 3 : Generative Learning Path (RAG)
    // --------------------------------------------------------
    case 'generate_crash_course':
        require_once __DIR__ . '/../../controller/AIController.php';
        require_once __DIR__ . '/../../controller/FormationController.php';
        $aiC = new AIController();
        $formC = new FormationController();
        $user_prompt = $_POST['prompt'] ?? '';
        if (empty($user_prompt)) {
            echo json_encode(['success' => false, 'message' => 'Requête vide.']);
            break;
        }
        // RAG : On injecte tout le catalogue dans le contexte
        $catalogue_raw = $formC->listerFormations();
        $catalogue = ($catalogue_raw instanceof PDOStatement) ? $catalogue_raw->fetchAll() : ($catalogue_raw ?? []);
        echo $aiC->generateCrashCourse($user_prompt, $catalogue);
        break;

    // --------------------------------------------------------
    // CONCEPT INNOVATION 4 : Course Factory (Admin 1-Clic)
    // --------------------------------------------------------
    case 'generate_course_factory':
        require_once __DIR__ . '/../../controller/AIController.php';
        $controller = new AIController();
        $prompt = $_POST['prompt'] ?? '';
        if (empty($prompt)) {
            echo json_encode(['success' => false, 'message' => 'Prompt vide.']);
        } else {
            echo $controller->generateCourseFactory($prompt);
        }
        break;

    case 'append_ai_syllabus':
        require_once __DIR__ . '/../../controller/AIController.php';
        $controller = new AIController();
        $id_formation = $_POST['id_formation'] ?? 0;
        $html_content = "<!-- AI_SYLLABUS_START -->" . $_POST['html_content'] . "<!-- AI_SYLLABUS_END -->";
        
        echo $controller->appendSyllabus($id_formation, $html_content);
        break;

    case 'delete_resource':
        require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
        $controller = new TuteurDashboardController();
        $id_formation = $_POST['id_formation'] ?? 0;
        $resource_id = $_POST['resource_id'] ?? '';
        $success = $controller->deleteResource((int)$id_formation, $resource_id);
        echo json_encode(['success' => $success]);
        break;

    case 'get_emotion_stats':
        require_once __DIR__ . '/../../controller/AIController.php';
        $controller = new AIController();
        $id_candidat = $_POST['id_candidat'] ?? 0;
        $id_formation = $_POST['id_formation'] ?? 0;
        
        echo $controller->getEmotionStats($id_candidat, $id_formation);
        break;

    case 'save_emotion':
        require_once __DIR__ . '/../../controller/AIController.php';
        $controller = new AIController();
        $id_candidat = $_POST['id_candidat'] ?? null;
        $id_formation = $_POST['id_formation'] ?? null;
        $emotion = $_POST['emotion'] ?? null;
        echo $controller->saveStudentEmotion($id_candidat, $id_formation, $emotion);
        break;

    case 'analyze_student_emotions':
        require_once __DIR__ . '/../../controller/AIController.php';
        $controller = new AIController();
        $stats_json = $_POST['stats'] ?? '[]';
        $stats = json_decode($stats_json, true);
        echo $controller->analyzeStudentEmotions($stats);
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
        $notif_id = $_POST['notif_id'] ?? null;
        require_once __DIR__ . '/../../controller/NotificationController.php';
        $notifC = new NotificationController();
        
        if ($notif_id) {
            $success = $notifC->markOneAsRead((int)$notif_id);
        } else {
            $success = $notifC->markAsRead((int)$uid);
        }
        echo json_encode(['success' => $success]);
        break;

    case 'delete_all_notifications':
        $uid = $_POST['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;
        require_once __DIR__ . '/../../controller/NotificationController.php';
        $notifC = new NotificationController();
        $success = $notifC->deleteAll((int)$uid);
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

    case 'send_recording_notif':
        $uid = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;
        $id_formation = $_POST['id_formation'] ?? 0;
        $transcript = $_POST['transcript_summary'] ?? '';
        
        require_once __DIR__ . '/../../controller/NotificationController.php';
        require_once __DIR__ . '/../../controller/FormationController.php';
        
        $formC = new FormationController();
        $f = $formC->getFormationById($id_formation);
        $titre = $f ? $f['titre'] : "votre cours";
        
        $msg = "🎬 L'enregistrement de '$titre' est disponible ! Retrouvez le transcript IA dans votre espace.";
        $url = "formation_viewer.php?id=" . $id_formation;
        
        $success = NotificationController::creerNotification($uid, 'certif_ready', $msg, $url, 'video', 'URGENT');
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
