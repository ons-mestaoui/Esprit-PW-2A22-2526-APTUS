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
            // Configuration prioritizes .env.php constants if defined, then Admin Settings
            $mail->isSMTP();
            $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : (!empty($s['smtp_server']) ? $s['smtp_server'] : '');
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('SMTP_USER') ? SMTP_USER : (!empty($s['smtp_user']) ? $s['smtp_user'] : '');
            $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : (!empty($s['smtp_pass']) ? $s['smtp_pass'] : '');
            $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : (!empty($s['smtp_port']) ? $s['smtp_port'] : 587);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            // Fix for local SSL issues (common on XAMPP)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Sender
            $fromEmail = defined('SMTP_FROM') ? SMTP_FROM : (!empty($s['smtp_email']) ? $s['smtp_email'] : 'noreply@aptus.tn');
            $fromName  = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (!empty($s['smtp_name']) ? $s['smtp_name'] : 'Aptus');
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
