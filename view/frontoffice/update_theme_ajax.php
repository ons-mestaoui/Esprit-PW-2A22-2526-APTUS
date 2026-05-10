<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_utilisateur'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['theme'])) {
        include_once __DIR__ . '/../../controller/UtilisateurC.php';
        $utC = new UtilisateurC();
        $id = $_SESSION['id_utilisateur'];
        $theme = $data['theme'] === 'light' ? 'light' : 'dark';
        
        // Update only the theme, keep other preferences
        $utC->updatePreferences($id, ['theme' => $theme]);
        
        echo json_encode(['success' => true]);
        exit();
    }
}

http_response_code(400);
echo json_encode(['error' => 'Bad Request']);
