<?php
// Full end-to-end test of save_cv.php
$payload = json_encode([
    'id_cv'        => null,
    'id_template'  => 1,
    'nomComplet'   => 'Jean Dupont',
    'titrePoste'   => 'Dev',
    'email'        => 'jean@test.com',
    'telephone'    => '123',
    'adresse'      => 'Tunis',
    'resume'       => 'Mon profil',
    'experience'   => 'Exp test',
    'competences'  => 'PHP',
    'formation'    => 'ESPRIT',
    'langues'      => 'FR',
    'urlPhoto'     => '',
    'couleurTheme' => '#2563eb'
]);

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
        'content' => $payload
    ]
];

$context = stream_context_create($opts);
$result = file_get_contents('http://localhost/Backup/view/frontoffice/save_cv.php', false, $context);
echo "Server response:\n" . $result . "\n";
