<?php

class mailC {
    private $apiKey;
    private $senderEmail;

    public function __construct() {
        // Charger le fichier .env si ce n'est pas déjà fait
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if ($key === 'BREVO_API_KEY') {
                        $this->apiKey = $value;
                    } elseif ($key === 'MAIL_SENDER') {
                        $this->senderEmail = $value;
                    }
                }
            }
        }
    }

    public function envoyerMailRefus($emailCandidat, $nomCandidat, $nomEntreprise, $emailEntreprise) {
        $url = 'https://api.brevo.com/v3/smtp/email';

        // NOTE : Le 'sender' doit être un email validé sur votre compte Brevo
        // On utilise donc le MAIL_SENDER du .env pour l'envoi technique
        // Mais on affiche le nom de l'entreprise
        $data = [
            'sender' => [
                'name' => $nomEntreprise,
                'email' => $this->senderEmail ?? 'no-reply@aptus.tn'
            ],
            'to' => [
                [
                    'email' => $emailCandidat,
                    'name' => $nomCandidat
                ]
            ],
            'replyTo' => [
                'email' => $emailEntreprise,
                'name' => $nomEntreprise
            ],
            'subject' => "Mise à jour de votre candidature chez $nomEntreprise",
            'htmlContent' => "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h2 style='color: #9333ea;'>Bonjour $nomCandidat,</h2>
                    <p>Nous vous remercions vivement de l'intérêt que vous portez à <strong>$nomEntreprise</strong> et du temps que vous avez consacré à votre candidature.</p>
                    <p>Après une étude attentive de votre profil, nous avons le regret de vous informer que nous ne pouvons pas donner suite à votre demande pour le moment.</p>
                    <p>Cependant, votre profil a retenu notre attention et nous conservons vos coordonnées dans notre base de données pour d'éventuelles opportunités futures qui pourraient mieux correspondre à votre parcours.</p>
                    <p>Nous vous souhaitons beaucoup de succès dans vos recherches et vos projets futurs.</p>
                    <br>
                    <p>Cordialement,<br><strong>L'équipe de recrutement $nomEntreprise</strong></p>
                    <hr style='border: none; border-top: 1px solid #eee;'>
                    <small style='color: #777;'>Ceci est un message envoyé via la plateforme <strong>Aptus</strong>.</small>
                </div>
            "
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 201) {
            return true; // Mail envoyé avec succès
        } else {
            error_log("Erreur Brevo ($httpCode): " . $response);
            return false;
        }
    }
}
