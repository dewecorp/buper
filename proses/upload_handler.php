<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) jsonResponse(false, 'Sesi habis.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    jsonResponse(false, 'Tidak ada file.');
}

$err = validateImage($_FILES['file']);
if ($err) jsonResponse(false, $err);

$folder = $_POST['folder'] ?? 'general';
$path = saveUploadedImage($_FILES['file'], $folder);
if ($path) {
    jsonResponse(true, 'Upload berhasil.', ['path' => $path, 'url' => '../' . $path]);
} else {
    jsonResponse(false, 'Gagal menyimpan file.');
}
