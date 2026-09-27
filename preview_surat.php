<?php
require_once __DIR__ . '/config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) { header('HTTP/1.0 404 Not Found'); exit; }

$stmt = mysqli_prepare($conn, "SELECT file_surat, nowa FROM izin_penggunaan WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);
if (!$row || empty($row['file_surat'])) { header('HTTP/1.0 404 Not Found'); exit; }

// Akses: user login bebas; publik wajib cocokkan nomor WA pemilik ajuan
if (!isLogin()) {
    $nowa = trim($_GET['nowa'] ?? '');
    if ($nowa === '' || !hash_equals((string)($row['nowa'] ?? ''), $nowa)) {
        header('HTTP/1.0 403 Forbidden');
        exit('Akses ditolak.');
    }
}

$file = __DIR__ . '/' . $row['file_surat'];
if (!file_exists($file)) { header('HTTP/1.0 404 Not Found'); exit; }

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if ($ext !== 'pdf') { header('HTTP/1.0 403 Forbidden'); exit; }

header('Content-Type: application/x-binary');
header('Content-Length: ' . filesize($file));
header('Cache-Control: private, no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
ob_clean();
flush();
readfile($file);
exit;