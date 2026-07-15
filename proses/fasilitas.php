<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin() && !isPengelola()) jsonResponse(false, 'Akses ditolak.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $nama_fasilitas = mysqli_real_escape_string($conn, trim($_POST['nama_fasilitas'] ?? ''));
    $deskripsi      = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $icon           = mysqli_real_escape_string($conn, trim($_POST['icon'] ?? ''));
    $status_f       = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'tersedia'));
    $gambar         = '';

    if (empty($nama_fasilitas)) jsonResponse(false, 'Nama fasilitas harus diisi.');

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $err = validateImage($_FILES['gambar']);
        if ($err) jsonResponse(false, $err);
        $gambar = saveUploadedImage($_FILES['gambar'], 'fasilitas');
        if (!$gambar) jsonResponse(false, 'Gagal menyimpan gambar.');
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO fasilitas (nama_fasilitas, deskripsi, icon, gambar, status) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'sssss', $nama_fasilitas, $deskripsi, $icon, $gambar, $status_f);

    if (mysqli_stmt_execute($stmt)) {
        $insertedId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menambahkan fasilitas: {$nama_fasilitas}", "tambah");
        jsonResponse(true, 'Fasilitas berhasil ditambahkan.', ['insert_id' => $insertedId]);
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'edit') {
    $id             = (int) ($_POST['id'] ?? 0);
    $nama_fasilitas = mysqli_real_escape_string($conn, trim($_POST['nama_fasilitas'] ?? ''));
    $deskripsi      = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $icon           = mysqli_real_escape_string($conn, trim($_POST['icon'] ?? ''));
    $status_f       = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'tersedia'));
    $gambar_lama    = mysqli_real_escape_string($conn, trim($_POST['gambar_lama'] ?? ''));

    if ($id < 1 || empty($nama_fasilitas)) jsonResponse(false, 'Data tidak lengkap.');

    $gambar = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $err = validateImage($_FILES['gambar']);
        if ($err) jsonResponse(false, $err);
        $gambar = saveUploadedImage($_FILES['gambar'], 'fasilitas');
        if (!$gambar) jsonResponse(false, 'Gagal menyimpan gambar.');
        if (!empty($gambar_lama) && file_exists(__DIR__ . '/../' . $gambar_lama)) {
            @unlink(__DIR__ . '/../' . $gambar_lama);
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE fasilitas SET nama_fasilitas=?, deskripsi=?, icon=?, gambar=?, status=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'sssssi', $nama_fasilitas, $deskripsi, $icon, $gambar, $status_f, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Mengedit fasilitas: {$nama_fasilitas}", "edit");
        jsonResponse(true, 'Fasilitas berhasil diperbarui.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $q = mysqli_prepare($conn, "SELECT gambar FROM fasilitas WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($q);

    $stmt = mysqli_prepare($conn, "DELETE FROM fasilitas WHERE id = ?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        if ($row && !empty($row['gambar']) && file_exists(__DIR__ . '/../' . $row['gambar'])) {
            @unlink(__DIR__ . '/../' . $row['gambar']);
        }
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menghapus fasilitas #{$id}", "hapus");
        jsonResponse(true, 'Fasilitas berhasil dihapus.');
    } else {
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . mysqli_error($conn));
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}
