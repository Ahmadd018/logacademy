<?php
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'logacademy');
define('DB_USER', getenv('DB_USER') ?: 'loguser');
define('DB_PASS', getenv('DB_PASS') ?: 'logpass123');

define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USER', getenv('MAIL_USER') ?: '');
define('MAIL_PASS', getenv('MAIL_PASS') ?: '');
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'noreply@logacademy.local');

define('SECRET_KEY', 'edu_secret_2024'); // Chain 3 - intentionally hardcoded

function db() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('DB connection failed: ' . $conn->connect_error);
        }
    }
    return $conn;
}

function sendOTPEmail($to, $otp) {
    $subject = "LogAcademy - OTP";
    $message = "Kodunuz: " . $otp;

    $smtp = fsockopen("tls://smtp.gmail.com", 465, $errno, $errstr, 10);
    if (!$smtp) return;

    // Köməkçi funksiya: Gmail-in çoxsətirli cavablarını tam oxumaq üçün
    function wait_for_gmail($socket) {
        $data = "";
        while($str = fgets($socket, 1024)) {
            $data .= $str;
            // Əgər sətir "250 " (boşluqla) başlayırsa, deməli siyahı bitdi
            if (preg_match("/^\d{3} /", $str)) break;
        }
        return $data;
    }

    fgets($smtp, 1024); // 220 salamlaması

    fputs($smtp, "EHLO logacademy.local\r\n");
    wait_for_gmail($smtp); // Bütün 250- siyahısını gözləyirik

    fputs($smtp, "AUTH LOGIN\r\n");
    fgets($smtp, 1024); // 334 Username tələbi

    fputs($smtp, base64_encode(MAIL_USER) . "\r\n");
    fgets($smtp, 1024); // 334 Password tələbi

    fputs($smtp, base64_encode(MAIL_PASS) . "\r\n");
    $auth_res = fgets($smtp, 1024); // 235 Authentication successful

    if (strpos($auth_res, '235') === false) return; // Login uğursuzsa dayan

    fputs($smtp, "MAIL FROM:<" . MAIL_FROM . ">\r\n");
    fgets($smtp, 1024);

    fputs($smtp, "RCPT TO:<$to>\r\n");
    fgets($smtp, 1024);

    fputs($smtp, "DATA\r\n");
    fgets($smtp, 1024);

    fputs($smtp, "Subject: $subject\r\nTo: $to\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$message\r\n.\r\n");
    fgets($smtp, 1024);

    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
}
