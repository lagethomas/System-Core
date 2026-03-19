<?php
declare(strict_types=1);

/**
 * Global Mailer Helper using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    /**
     * Send an email using global SMTP settings
     */
    public static function send($to, $subject, $body, $altBody = '') {
        $pdo = DB::getInstance();
        $logFile = dirname(__DIR__, 2) . '/logs/email.log';
        $timestamp = date('Y-m-d H:i:s');

        // Fetch Global SMTP Settings
        $stmt_s = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings WHERE setting_key IN ('smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name', 'smtp_secure', 'enable_system_logs')");
        $stmt_s->execute();
        $settings = $stmt_s->fetchAll(PDO::FETCH_KEY_PAIR);

        $logsEnabled = (($settings['enable_system_logs'] ?? '0') === '1');

        if (empty($settings['smtp_host'])) {
            if ($logsEnabled) {
                file_put_contents($logFile, "[$timestamp] ERROR: SMTP host not configured.\n", FILE_APPEND);
            }
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->CharSet    = 'UTF-8';
            $mail->Host       = $settings['smtp_host'] ?? '';
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['smtp_user'] ?? '';
            $mail->Password   = $settings['smtp_pass'] ?? '';
            $mail->Port       = $settings['smtp_port'] ?? 587;
            
            // SMTP Debug (optional)
            $mail->SMTPDebug = 0; 

            // Encryption
            $secure = $settings['smtp_secure'] ?? '';
            if ($secure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($secure === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }

            // Recipients
            $mail->setFrom($settings['smtp_from_email'] ?? '', $settings['smtp_from_name'] ?? 'SaaSFlow');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);

            return $mail->send();

        } catch (Exception $e) {
            if ($logsEnabled) {
                $errorMsg = "[$timestamp] ERROR: " . $mail->ErrorInfo . " (To: $to, Subject: $subject)\n";
                file_put_contents($logFile, $errorMsg, FILE_APPEND);
            }
            error_log("MAILER ERROR: " . $mail->ErrorInfo);
            return false;
        }
    }
}
