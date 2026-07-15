<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin() && !isPengelola()) jsonResponse(false, 'Akses ditolak.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $nama_biaya  = mysqli_real_escape_string($conn, trim($_POST['nama_biaya'] ?? ''));
    $deskripsi   = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $harga       = str_replace(['.', ','], ['', '.'], $_POST['harga'] ?? 0);
    $harga       = (float) $harga;
    $satuan      = mysqli_real_escape_string($conn, trim($_POST['satuan'] ?? ''));
    $keterangan  = mysqli_real_escape_string($conn, trim($_POST['keterangan'] ?? ''));

    if (empty($nama_biaya) || $harga <= 0 || empty($satuan)) jsonResponse(false, 'Nama, harga, dan satuan harus diisi.');

    $stmt = mysqli_prepare($conn, "INSERT INTO biaya (nama_biaya, deskripsi, harga, satuan, keterangan) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'ssdss', $nama_biaya, $deskripsi, $harga, $satuan, $keterangan);

    if (mysqli_stmt_execute($stmt)) {
        $insertedId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menambahkan biaya: {$nama_biaya}", "tambah");
        jsonResponse(true, 'Biaya berhasil ditambahkan.', ['insert_id' => $insertedId]);
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'edit') {
    $id         = (int) ($_POST['id'] ?? 0);
    $nama_biaya = mysqli_real_escape_string($conn, trim($_POST['nama_biaya'] ?? ''));
    $deskripsi  = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $harga      = str_replace(['.', ','], ['', '.'], $_POST['harga'] ?? 0);
    $harga      = (float) $harga;
    $satuan     = mysqli_real_escape_string($conn, trim($_POST['satuan'] ?? ''));
    $keterangan = mysqli_real_escape_string($conn, trim($_POST['keterangan'] ?? ''));

    if ($id < 1 || empty($nama_biaya) || $harga <= 0 || empty($satuan)) jsonResponse(false, 'Data tidak lengkap.');

    $stmt = mysqli_prepare($conn, "UPDATE biaya SET nama_biaya=?, deskripsi=?, harga=?, satuan=?, keterangan=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'ssdssi', $nama_biaya, $deskripsi, $harga, $satuan, $keterangan, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Mengedit biaya: {$nama_biaya}", "edit");
        jsonResponse(true, 'Biaya berhasil diperbarui.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $stmt = mysqli_prepare($conn, "DELETE FROM biaya WHERE id = ?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menghapus biaya #{$id}", "hapus");
        jsonResponse(true, 'Biaya berhasil dihapus.');
    } else {
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . mysqli_error($conn));
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}
