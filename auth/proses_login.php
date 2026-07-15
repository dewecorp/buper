<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode request tidak diizinkan.');
}

$csrfToken = $_POST['csrf_token'] ?? '';
$username  = $_POST['username'] ?? '';
$password  = $_POST['password'] ?? '';

if (!verifyCSRFToken($csrfToken)) {
    jsonResponse(false, 'Token tidak valid. Refresh halaman.');
}
if (empty(trim($username)) || empty(trim($password))) {
    jsonResponse(false, 'Username dan password harus diisi.');
}

$maxAttempts = 5;
$lockoutTime = 30 * 60;
$attemptKey  = 'login_attempts';

if (!isset($_SESSION[$attemptKey])) {
    $_SESSION[$attemptKey] = ['count' => 0, 'time' => 0];
}
if ($_SESSION[$attemptKey]['count'] >= $maxAttempts) {
    $elapsed = time() - $_SESSION[$attemptKey]['time'];
    $remaining = $lockoutTime - $elapsed;
    if ($remaining > 0) {
        $menit = ceil($remaining / 60);
        jsonResponse(false, "Terlalu banyak percobaan. Coba lagi dalam $menit menit.");
    }
    $_SESSION[$attemptKey] = ['count' => 0, 'time' => 0];
}

$username = mysqli_real_escape_string($conn, trim($username));
$query  = "SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ? LIMIT 1";
$stmt   = mysqli_prepare($conn, $query);
if (!$stmt) jsonResponse(false, 'Terjadi kesalahan sistem.');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION[$attemptKey]['count']++;
    if ($_SESSION[$attemptKey]['count'] === 1) {
        $_SESSION[$attemptKey]['time'] = time();
    }
    $sisa = $maxAttempts - $_SESSION[$attemptKey]['count'];
    jsonResponse(false, "Username atau password salah. Sisa percobaan: $sisa.");
}

unset($_SESSION[$attemptKey]);

$_SESSION['user_id']       = $user['id'];
$_SESSION['username']      = $user['username'];
$_SESSION['nama_lengkap']  = $user['nama_lengkap'];
$_SESSION['role']          = $user['role'];
$_SESSION['foto']          = $user['foto'] ?? '';

jsonResponse(true, 'Login berhasil.', ['redirect' => '../dashboard/']);
