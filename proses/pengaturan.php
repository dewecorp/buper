<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin()) jsonResponse(false, 'Hanya admin yang dapat mengubah pengaturan.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'edit') {
    // Handle logo upload
    if (!empty($_FILES['logo_file']['name']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $error = validateImage($_FILES['logo_file'], 2 * 1024 * 1024);
        if ($error) {
            jsonResponse(false, $error);
        }
        $path = saveUploadedImage($_FILES['logo_file'], 'logo');
        if ($path) {
            $stmt = mysqli_prepare($conn, "UPDATE pengaturan SET nilai=? WHERE nama_pengaturan='logo'");
            mysqli_stmt_bind_param($stmt, 's', $path);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $settings = $_POST['settings'] ?? [];
    if (empty($settings)) {
        jsonResponse(true, 'Logo berhasil diperbarui.');
    }

    $success = 0;
    foreach ($settings as $key => $value) {
        $nama = mysqli_real_escape_string($conn, trim($key));
        $nilai = mysqli_real_escape_string($conn, trim($value));
        $stmt = mysqli_prepare($conn, "UPDATE pengaturan SET nilai=? WHERE nama_pengaturan=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $nilai, $nama);
            if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($conn) > 0) {
                $success++;
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($success > 0) {
        jsonResponse(true, "$success pengaturan berhasil diperbarui.");
    } else {
        jsonResponse(true, 'Pengaturan tersimpan.');
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}
