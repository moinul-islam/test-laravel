<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * NOTE: This file is in a public folder. Do NOT hardcode secrets here.
 * Priority order:
 * 1) WIHIMA_DB_* env vars (recommended)
 * 2) Laravel project's .env DB_* values (fallback, convenient for local dev)
 */
function wihima_env(string $key): ?string {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    return null;
}

function wihima_read_project_env(): array {
    // public/hub -> project root .env
    $envPath = realpath(__DIR__ . '/../../.env');
    if (!$envPath || !is_file($envPath) || !is_readable($envPath)) return [];

    $out = [];
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // Strip optional quotes
        if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
            $v = substr($v, 1, -1);
        }
        $out[$k] = $v;
    }
    return $out;
}

$projectEnv = wihima_read_project_env();

define('DB_HOST', wihima_env('WIHIMA_DB_HOST') ?: ($projectEnv['WIHIMA_DB_HOST'] ?? ($projectEnv['DB_HOST'] ?? '127.0.0.1')));
define('DB_PORT', wihima_env('WIHIMA_DB_PORT') ?: ($projectEnv['WIHIMA_DB_PORT'] ?? ($projectEnv['DB_PORT'] ?? '3306')));
define('DB_NAME', wihima_env('WIHIMA_DB_NAME') ?: ($projectEnv['WIHIMA_DB_NAME'] ?? ($projectEnv['DB_DATABASE'] ?? '')));
define('DB_USER', wihima_env('WIHIMA_DB_USER') ?: ($projectEnv['WIHIMA_DB_USER'] ?? ($projectEnv['DB_USERNAME'] ?? '')));
define('DB_PASS', wihima_env('WIHIMA_DB_PASS') ?: ($projectEnv['WIHIMA_DB_PASS'] ?? ($projectEnv['DB_PASSWORD'] ?? '')));

define('OTP_EMAIL', wihima_env('WIHIMA_OTP_EMAIL') ?: ($projectEnv['MAIL_FROM_ADDRESS'] ?? 'otp@example.com'));
define('SITE_NAME', wihima_env('WIHIMA_SITE_NAME') ?: 'Wihima');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');

function getDB() {
    static $pdo = null;
    if (!$pdo) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function authRequired() {
    if (!isLoggedIn()) {
        header('Location: auth.php');
        exit;
    }
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sanitize($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function sendOTP($email, $otp) {
    $headers = "From: " . OTP_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $subject = "Your Wihima OTP Code";
    $body = "<h2>Your OTP is: <strong>{$otp}</strong></h2><p>Valid for 10 minutes.</p>";
    return mail($email, $subject, $body, $headers);
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('M j, Y', $time);
}
