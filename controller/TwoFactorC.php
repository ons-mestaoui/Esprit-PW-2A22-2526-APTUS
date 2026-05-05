<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../libs/GoogleAuthenticator.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if (!isset($_SESSION['id_utilisateur']) && $action !== 'verify_login') {
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$db = config::getConnexion();
$ga = new GoogleAuthenticator();

switch ($action) {
    case 'setup':
        // Generate a new secret but don't enable yet
        $secret = $ga->createSecret();
        $_SESSION['temp_2fa_secret'] = $secret;
        
        $email = '';
        $q = $db->prepare("SELECT email FROM utilisateur WHERE id_utilisateur = :id");
        $q->execute(['id' => $_SESSION['id_utilisateur']]);
        $user = $q->fetch();
        $email = $user['email'] ?? 'user@aptus.tn';

        $qrCodeUrl = $ga->getQRCodeGoogleUrl($email, $secret, 'Aptus');
        
        echo json_encode([
            'success' => true, 
            'secret' => $secret, 
            'qrCodeUrl' => $qrCodeUrl
        ]);
        break;

    case 'confirm':
        $code = $input['code'] ?? '';
        $secret = $_SESSION['temp_2fa_secret'] ?? '';

        if (empty($code) || empty($secret)) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
            exit;
        }

        if ($ga->verifyCode($secret, $code)) {
            // Enable 2FA
            $q = $db->prepare("UPDATE utilisateur SET two_factor_secret = :secret, two_factor_enabled = 1 WHERE id_utilisateur = :id");
            $q->execute([
                'secret' => $secret,
                'id' => $_SESSION['id_utilisateur']
            ]);
            unset($_SESSION['temp_2fa_secret']);
            echo json_encode(['success' => true, 'message' => '2FA activée avec succès !']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Code incorrect. Veuillez réessayer.']);
        }
        break;

    case 'disable':
        $q = $db->prepare("UPDATE utilisateur SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id_utilisateur = :id");
        $q->execute(['id' => $_SESSION['id_utilisateur']]);
        echo json_encode(['success' => true, 'message' => '2FA désactivée.']);
        break;

    case 'status':
        $q = $db->prepare("SELECT two_factor_enabled FROM utilisateur WHERE id_utilisateur = :id");
        $q->execute(['id' => $_SESSION['id_utilisateur']]);
        $row = $q->fetch();
        echo json_encode(['success' => true, 'enabled' => (bool)($row['two_factor_enabled'] ?? false)]);
        break;

    case 'verify_login':
        $code = $input['code'] ?? '';
        $userId = $_SESSION['pending_2fa_user_id'] ?? null;

        if (!$userId || empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Session expirée ou code manquant.']);
            exit;
        }

        $q = $db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
        $q->execute(['id' => $userId]);
        $user = $q->fetch();

        if ($user && $ga->verifyCode($user['two_factor_secret'], $code)) {
            // Complete login
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'] ?? '';
            $_SESSION['role'] = $user['role'];
            unset($_SESSION['pending_2fa_user_id']);

            $roleRoutes = [
                'admin' => '../backoffice/dashboard.php',
                'candidat' => 'jobs_feed.php',
                'entreprise' => 'hr_posts.php',
                'tuteur' => 'dashboard_tuteur.php'
            ];
            $roleKey = strtolower($user['role']);
            $redirect = $roleRoutes[$roleKey] ?? 'landing.php';

            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Code incorrect.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
        break;
}
