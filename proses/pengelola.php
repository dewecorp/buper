<?php
require_once __DIR__ . '/../config/koneksi.php';
cekSessionTimeout();
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin() && !isPengelola()) jsonResponse(false, 'Akses ditolak.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $nama    = mysqli_real_escape_string($conn, trim($_POST['nama'] ?? ''));
    $jabatan = mysqli_real_escape_string($conn, trim($_POST['jabatan'] ?? ''));
    $urutan  = (int) ($_POST['urutan'] ?? 0);
    $status  = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'aktif'));
    $foto    = '';

    if (empty($nama) || empty($jabatan)) jsonResponse(false, 'Nama dan jabatan harus diisi.');

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $err = validateImage($_FILES['foto']);
        if ($err) jsonResponse(false, $err);
        $foto = saveUploadedImage($_FILES['foto'], 'pengelola');
        if (!$foto) jsonResponse(false, 'Gagal menyimpan foto.');
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO pengelola (nama, jabatan, foto, urutan, status) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'sssis', $nama, $jabatan, $foto, $urutan, $status);

    if (mysqli_stmt_execute($stmt)) {
        $insertedId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menambahkan pengelola: {$nama}", "tambah");
        jsonResponse(true, 'Pengelola berhasil ditambahkan.', ['insert_id' => $insertedId]);
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'edit') {
    $id      = (int) ($_POST['id'] ?? 0);
    $nama    = mysqli_real_escape_string($conn, trim($_POST['nama'] ?? ''));
    $jabatan = mysqli_real_escape_string($conn, trim($_POST['jabatan'] ?? ''));
    $urutan  = (int) ($_POST['urutan'] ?? 0);
    $status  = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'aktif'));
    $foto_lama = mysqli_real_escape_string($conn, trim($_POST['foto_lama'] ?? ''));

    if ($id < 1 || empty($nama) || empty($jabatan)) jsonResponse(false, 'Data tidak lengkap.');

    $foto = $foto_lama;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $err = validateImage($_FILES['foto']);
        if ($err) jsonResponse(false, $err);
        $foto = saveUploadedImage($_FILES['foto'], 'pengelola');
        if (!$foto) jsonResponse(false, 'Gagal menyimpan foto.');
        if (!empty($foto_lama) && file_exists(__DIR__ . '/../' . $foto_lama)) {
            @unlink(__DIR__ . '/../' . $foto_lama);
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE pengelola SET nama=?, jabatan=?, foto=?, urutan=?, status=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'sssisi', $nama, $jabatan, $foto, $urutan, $status, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Mengedit pengelola: {$nama}", "edit");
        jsonResponse(true, 'Pengelola berhasil diperbarui.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $q = mysqli_prepare($conn, "SELECT foto FROM pengelola WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($q);

    $stmt = mysqli_prepare($conn, "DELETE FROM pengelola WHERE id = ?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        if ($row && !empty($row['foto']) && file_exists(__DIR__ . '/../' . $row['foto'])) {
            @unlink(__DIR__ . '/../' . $row['foto']);
        }
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menghapus pengelola #{$id}", "hapus");
        jsonResponse(true, 'Pengelola berhasil dihapus.');
    } else {
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . mysqli_error($conn));
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}
