<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';
require_once __DIR__ . '/SettingsAdminC.php';

class EmailService {
    public static function send($to, $subject, $body, $altBody = '') {
        $settingsC = new SettingsAdminC();
        $s = $settingsC->getSettings();

        // Check if we have .env.php fallback constants
        if (file_exists(__DIR__ . '/../.env.php')) {
            require_once __DIR__ . '/../.env.php';
        }

        $mail = new PHPMailer(true);

        try {
            // Configuration prioritizes Admin Settings, then fallbacks to constants
            $mail->isSMTP();
            $mail->Host       = !empty($s['smtp_server']) ? $s['smtp_server'] : (defined('SMTP_HOST') ? SMTP_HOST : '');
            $mail->SMTPAuth   = true;
            $mail->Username   = !empty($s['smtp_user']) ? $s['smtp_user'] : (defined('SMTP_USER') ? SMTP_USER : '');
            $mail->Password   = !empty($s['smtp_pass']) ? $s['smtp_pass'] : (defined('SMTP_PASS') ? SMTP_PASS : '');
            $mail->Port       = !empty($s['smtp_port']) ? $s['smtp_port'] : (defined('SMTP_PORT') ? SMTP_PORT : 587);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            // Sender
            $fromEmail = !empty($s['smtp_email']) ? $s['smtp_email'] : (defined('SMTP_FROM') ? SMTP_FROM : 'noreply@aptus.tn');
            $fromName  = !empty($s['smtp_name']) ? $s['smtp_name'] : (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Aptus');
            $mail->setFrom($fromEmail, $fromName);

            // Recipient
            if (is_array($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            } else {
                $mail->addAddress($to);
            }

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $body;
            if ($altBody) {
                $mail->AltBody = $altBody;
            }

            return $mail->send();
        } catch (Exception $e) {
            error_log("EmailService Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
