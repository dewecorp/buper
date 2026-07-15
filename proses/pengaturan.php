<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin()) jsonResponse(false, 'Hanya admin yang dapat mengubah pengaturan.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'edit') {
    $logoFields = [
        'logo_file'       => 'logo',
        'logo_pramuka_file' => 'logo_pramuka',
        'logo_wosm_file'    => 'logo_wosm',
    ];
    foreach ($logoFields as $inputName => $dbName) {
        if (!empty($_FILES[$inputName]['name']) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $error = validateImage($_FILES[$inputName], 2 * 1024 * 1024);
            if ($error) jsonResponse(false, $error);
            $path = saveUploadedImage($_FILES[$inputName], $dbName);
            if ($path) {
                $stmt = mysqli_prepare($conn, "UPDATE pengaturan SET nilai=? WHERE nama_pengaturan=?");
                mysqli_stmt_bind_param($stmt, 'ss', $path, $dbName);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    $settings = $_POST['settings'] ?? [];
    if (empty($settings)) {
        catatAktivitas($conn, "Memperbarui pengaturan", "edit");
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
        catatAktivitas($conn, "Memperbarui pengaturan", "edit");
        jsonResponse(true, "$success pengaturan berhasil diperbarui.");
    } else {
        catatAktivitas($conn, "Memperbarui pengaturan", "edit");
        jsonResponse(true, 'Pengaturan tersimpan.');
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}
