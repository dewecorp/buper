<?php
require_once __DIR__ . '/config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) { header('HTTP/1.0 404 Not Found'); exit; }

$q = mysqli_query($conn, "SELECT file_surat FROM izin_penggunaan WHERE id = $id LIMIT 1");
$row = mysqli_fetch_assoc($q);
if (!$row || empty($row['file_surat'])) { header('HTTP/1.0 404 Not Found'); exit; }

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