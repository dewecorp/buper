<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin()) jsonResponse(false, 'Hanya admin yang dapat mengubah profil.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'edit') {
    $id = (int) ($_POST['id'] ?? 0);
    $nama_buper = mysqli_real_escape_string($conn, trim($_POST['nama_buper'] ?? ''));
    $deskripsi  = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $sejarah    = mysqli_real_escape_string($conn, trim($_POST['sejarah'] ?? ''));
    $visi       = mysqli_real_escape_string($conn, trim($_POST['visi'] ?? ''));
    $misi       = mysqli_real_escape_string($conn, trim($_POST['misi'] ?? ''));
    $sejarah    = mysqli_real_escape_string($conn, trim($_POST['sejarah'] ?? ''));
    $visi       = mysqli_real_escape_string($conn, trim($_POST['visi'] ?? ''));
    $misi       = mysqli_real_escape_string($conn, trim($_POST['misi'] ?? ''));
    $alamat     = mysqli_real_escape_string($conn, trim($_POST['alamat'] ?? ''));
    $telepon    = mysqli_real_escape_string($conn, trim($_POST['telepon'] ?? ''));
    $email      = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $latitude   = mysqli_real_escape_string($conn, trim($_POST['latitude'] ?? ''));
    $longitude  = mysqli_real_escape_string($conn, trim($_POST['longitude'] ?? ''));
    $foto_lama  = mysqli_real_escape_string($conn, trim($_POST['foto_lama'] ?? ''));

    if ($id < 1 || empty($nama_buper)) jsonResponse(false, 'Data tidak lengkap.');

    $foto = $foto_lama;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $err = validateImage($_FILES['foto']);
        if ($err) jsonResponse(false, $err);
        $foto = saveUploadedImage($_FILES['foto'], 'profil');
        if (!$foto) jsonResponse(false, 'Gagal menyimpan foto.');
        if (!empty($foto_lama) && file_exists(__DIR__ . '/../' . $foto_lama)) {
            @unlink(__DIR__ . '/../' . $foto_lama);
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE profil SET nama_buper=?, deskripsi=?, sejarah=?, visi=?, misi=?, alamat=?, telepon=?, email=?, foto=?, latitude=?, longitude=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'sssssssssssi', $nama_buper, $deskripsi, $sejarah, $visi, $misi, $alamat, $telepon, $email, $foto, $latitude, $longitude, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Memperbarui profil buper", "edit");
        jsonResponse(true, 'Profil berhasil diperbarui.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}
