<?php
// Test complet du save_cv_v2.php
$payload = json_encode([
    'cv_id'       => null,
    'template_id' => 1,
    'name'        => 'Jean Dupont Test',
    'title'       => 'Dev Full-Stack',
    'email'       => 'jean@test.com',
    'phone'       => '+21600000000',
    'location'    => 'Tunis',
    'summary'     => 'Mon résumé pro',
    'experience'  => 'Dev chez X 2022-2024',
    'skills'      => 'PHP,JS,MySQL',
    'education'   => 'ESPRIT 2024',
    'languages'   => 'Arabe,Français,Anglais',
    'photo'       => '',
    'color_theme' => '#2563eb'
]);

$opts    = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $payload]];
$context = stream_context_create($opts);
$result  = file_get_contents('http://localhost/Backup/view/frontoffice/save_cv_v2.php', false, $context);
echo "Réponse: " . $result . "\n";

if ($result) {
    $r = json_decode($result, true);
    if ($r && $r['success']) {
        echo "✅ Insertion réussie! id=" . $r['id'] . "\n";
        
        // Test Update immédiat
        $payload2 = json_encode(['cv_id' => $r['id'], 'template_id' => 1, 'name' => 'Jean Modifié', 'title' => 'Lead Dev', 'email' => 'jean@test.com', 'phone' => '', 'location' => '', 'summary' => '', 'experience' => '', 'skills' => '', 'education' => '', 'languages' => '', 'photo' => '', 'color_theme' => '#8b5cf6']);
        $opts2     = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $payload2]];
        $result2   = file_get_contents('http://localhost/Backup/view/frontoffice/save_cv_v2.php', false, stream_context_create($opts2));
        echo "Update réponse: " . $result2 . "\n";
        
        require 'config.php';
        $db = config::getConnexion();
        $db->exec('SET FOREIGN_KEY_CHECKS=0');
        $db->exec("DELETE FROM cv WHERE id_cv=" . (int)$r['id']);
        $db->exec('SET FOREIGN_KEY_CHECKS=1');
        echo "🧹 Nettoyé.\n";
    } else {
        echo "❌ Erreur: " . ($r['message'] ?? 'inconnue') . "\n";
    }
}
