<?php
$url = "http://localhost/aptus_first_official_version/controller/AgentController.php";
$data = ["text" => "Bonjour ! Réponds moi avec une action JSON pour aller sur la page d'accueil public"];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
curl_close($ch);

echo "Response from AgentController (simulated rate limit with Groq fallback):\n";
echo $res;
