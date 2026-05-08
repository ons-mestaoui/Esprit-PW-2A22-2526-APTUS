<?php
/**
 * Face Recognition API Endpoint
 * Handles face descriptor enrollment and verification.
 * 
 * Actions:
 *   - enroll:  Save face descriptor for the logged-in user (requires session)
 *   - verify:  Match a face descriptor against a stored one for login (by email)
 *   - status:  Check if the logged-in user has face ID enrolled
 *   - remove:  Remove face ID enrollment for the logged-in user
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$db = config::getConnexion();

switch ($action) {

    // ── Enroll: Save face descriptor for logged-in user ──
    case 'enroll':
        if (!isset($_SESSION['id_utilisateur'])) {
            echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
            exit;
        }

        $descriptor = $input['descriptor'] ?? null;
        if (!$descriptor || !is_array($descriptor)) {
            echo json_encode(['success' => false, 'message' => 'Descripteur facial invalide.']);
            exit;
        }

        try {
            $json = json_encode($descriptor);
            $query = $db->prepare("UPDATE utilisateur SET face_descriptor = :fd WHERE id_utilisateur = :id");
            $query->execute([
                'fd' => $json,
                'id' => $_SESSION['id_utilisateur']
            ]);
            echo json_encode(['success' => true, 'message' => 'Face ID enregistré avec succès.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }
        break;

    // ── Verify: Compare face descriptor against a stored one (login) ──
    case 'verify':
        $email = trim($input['email'] ?? '');
        $descriptor = $input['descriptor'] ?? null;

        if (empty($email) || !$descriptor || !is_array($descriptor)) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
            exit;
        }

        try {
            $query = $db->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
                exit;
            }

            if (empty($user['face_descriptor'])) {
                echo json_encode(['success' => false, 'message' => 'Face ID non configuré pour ce compte.']);
                exit;
            }

            // Check account verification status
            if (isset($user['est_verifie']) && $user['est_verifie'] == 0) {
                echo json_encode(['success' => false, 'message' => 'Votre compte n\'est pas encore vérifié. Veuillez vérifier votre email.']);
                exit;
            }

            $storedDescriptor = json_decode($user['face_descriptor'], true);

            // Compute Euclidean distance between descriptors
            $distance = 0;
            for ($i = 0; $i < count($storedDescriptor); $i++) {
                $diff = ($descriptor[$i] ?? 0) - ($storedDescriptor[$i] ?? 0);
                $distance += $diff * $diff;
            }
            $distance = sqrt($distance);

            // Threshold: face-api.js recommends 0.6 for a match
            if ($distance < 0.6) {
                // Login success — create session
                $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'] ?? '';
                $_SESSION['role'] = $user['role'];

                $roleRoutes = [
                    'admin' => '../backoffice/dashboard.php',
                    'candidat' => 'jobs_feed.php',
                    'entreprise' => 'hr_posts.php'
                ];
                $roleKey = strtolower($user['role']);
                $redirect = $roleRoutes[$roleKey] ?? 'landing.php';

                echo json_encode([
                    'success' => true,
                    'message' => 'Authentification réussie !',
                    'redirect' => $redirect,
                    'distance' => round($distance, 4)
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Visage non reconnu. Veuillez réessayer.',
                    'distance' => round($distance, 4)
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }
        break;

    // ── Status: Check if logged-in user has face ID ──
    case 'status':
        if (!isset($_SESSION['id_utilisateur'])) {
            echo json_encode(['success' => false, 'enrolled' => false]);
            exit;
        }

        try {
            $query = $db->prepare("SELECT face_descriptor FROM utilisateur WHERE id_utilisateur = :id");
            $query->execute(['id' => $_SESSION['id_utilisateur']]);
            $row = $query->fetch();
            $enrolled = !empty($row['face_descriptor']);
            echo json_encode(['success' => true, 'enrolled' => $enrolled]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'enrolled' => false]);
        }
        break;

    // ── Remove: Delete face descriptor ──
    case 'remove':
        if (!isset($_SESSION['id_utilisateur'])) {
            echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
            exit;
        }

        try {
            $query = $db->prepare("UPDATE utilisateur SET face_descriptor = NULL WHERE id_utilisateur = :id");
            $query->execute(['id' => $_SESSION['id_utilisateur']]);
            echo json_encode(['success' => true, 'message' => 'Face ID supprimé.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
        break;
}
?>
