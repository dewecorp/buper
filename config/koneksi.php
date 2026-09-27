<?php
// Hardening cookie session: HttpOnly + SameSite=Lax (+ Secure bila HTTPS)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/functions.php';

$host = "localhost";
$user = "root";
$pass = "";
$db   = "buper_jepara";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']));
}
mysqli_set_charset($conn, "utf8mb4");
date_default_timezone_set('Asia/Jakarta');
?>
