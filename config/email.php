<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

/**
 * Send email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body
 * @param string $recipientName Recipient name (optional)
 * @return bool True if email sent successfully, false otherwise
 */
function sendEmail($to, $subject, $message, $recipientName = '')
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Change to your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME') ?: '';  // Set in environment
        $mail->Password   = getenv('SMTP_PASSWORD') ?: '';  // Set in environment
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // For local development/testing, you can use:
        // $mail->SMTPDebug = 2; // Enable verbose debug output

        // Recipients
        $mail->setFrom('noreply@spotlightlistings.com', 'Spotlight Listings');
        $mail->addAddress($to, $recipientName);
        $mail->addReplyTo('spotlightlisting1@gmail.com', 'Spotlight Listings Support');

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send HTML email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $htmlMessage HTML email body
 * @param string $plainMessage Plain text fallback
 * @param string $recipientName Recipient name (optional)
 * @return bool True if email sent successfully, false otherwise
 */
function sendHtmlEmail($to, $subject, $htmlMessage, $plainMessage = '', $recipientName = '')
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Change to your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME') ?: '';  // Set in environment
        $mail->Password   = getenv('SMTP_PASSWORD') ?: '';  // Set in environment
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@spotlightlistings.com', 'Spotlight Listings');
        $mail->addAddress($to, $recipientName);
        $mail->addReplyTo('info@spotlightlistings.com', 'Spotlight Listings Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;
        $mail->AltBody = $plainMessage ?: strip_tags($htmlMessage);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}
