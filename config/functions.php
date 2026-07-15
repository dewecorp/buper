<?php
// ============================================================
// FUNGSI UTILITAS - Buper Jepara
// ============================================================

/**
 * Sanitize input: mysqli_real_escape_string + htmlspecialchars
 */
function sanitize($conn, $data) {
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke URL tertentu
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Cek apakah user sedang login
 */
function isLogin() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Cek apakah user adalah pengelola
 */
function isPengelola() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'pengelola';
}

/**
 * Format angka ke Rupiah
 * Contoh: 1500000 => "Rp 1.500.000"
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Format tanggal ke Bahasa Indonesia
 * Contoh: 2026-07-14 => "14 Juli 2026"
 */
function formatTanggal($tanggal) {
    $bulan = [
        1  => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $t = new DateTime($tanggal);
    $d = $t->format('d');
    $m = (int) $t->format('m');
    $y = $t->format('Y');
    return "$d $bulan[$m] $y";
}

/**
 * Upload gambar dengan validasi
 * Hanya menerima: jpg, jpeg, png, gif, webp
 * Maksimal ukuran: 2MB
 * Mengembalikan nama file baru yang sudah di-rename aman
 */
function uploadImage($file, $folder) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Gagal mengupload file.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Hanya jpg, jpeg, png, gif, webp.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 10MB.'];
    }

    $uploadDir = __DIR__ . '/../uploads/' . $folder;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newName = uniqid('img_', true) . '.' . $ext;
    $destination = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Gagal memindahkan file ke server.'];
    }

    return ['success' => true, 'filename' => $newName];
}

/**
 * Set flash message ke session
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Get flash message dari session lalu hapus
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Escape output untuk XSS prevention
 */
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get single pengaturan value by key
 */
function getPengaturan($conn, $key) {
    $key = mysqli_real_escape_string($conn, $key);
    $q = mysqli_query($conn, "SELECT nilai FROM pengaturan WHERE nama_pengaturan='$key' LIMIT 1");
    $r = mysqli_fetch_assoc($q);
    return $r['nilai'] ?? '';
}

/**
 * Generate CSRF token untuk session
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken(?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Hidden input HTML untuk CSRF token
 */
function csrfInput(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(generateCSRFToken()) . '">';
}

/**
 * Cek dan hentikan jika CSRF token tidak valid (untuk aksi terautentikasi)
 */
function requireCSRF(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($token)) {
        jsonResponse(false, 'Token keamanan tidak valid. Muat ulang halaman.');
    }
}

/**
 * Validasi ekstensi & ukuran file gambar
 */
function validateImage(array $file, int $maxSize = 10485760): ?string
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'Terjadi kesalahan saat mengunggah file.';
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return 'Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WEBP.';
    }
    if ($file['size'] > $maxSize) {
        return 'Ukuran file melebihi batas maksimal ' . ($maxSize / 1048576) . ' MB.';
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
        return 'File bukan gambar yang valid.';
    }
    return null;
}

/**
 * Simpan file gambar ke direktori uploads
 */
function saveUploadedImage(array $file, string $prefix = 'img'): ?string
{
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $name;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/' . $name;
    }
    return null;
}

/**
 * Catat aktivitas pengguna
 */
function catatAktivitas($conn, $aktivitas, $jenis = 'umum') {
    $id_user = $_SESSION['user_id'] ?? 0;
    $nama_user = $_SESSION['nama_lengkap'] ?? 'Sistem';
    $role_user = $_SESSION['role'] ?? 'sistem';
    $stmt = mysqli_prepare($conn, "INSERT INTO aktivitas (id_user, nama_user, role_user, aktivitas, jenis, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'issss', $id_user, $nama_user, $role_user, $aktivitas, $jenis);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/**
 * Kirim notifikasi WhatsApp via Fonnte API
 */
function sendWhatsAppNotification($conn, $target, $message) {
    $apiKey = getPengaturan($conn, 'wa_api_key');
    if (empty($apiKey) || empty($target)) return false;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'target' => $target,
            'message' => $message,
        ]),
        CURLOPT_HTTPHEADER => ['Authorization: ' . $apiKey],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

/**
 * Kirim respon JSON lalu hentikan eksekusi
 */
function jsonResponse(bool $success, string $message, array $extra = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

?>

