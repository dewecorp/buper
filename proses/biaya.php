<?php
require_once __DIR__ . '/../config/koneksi.php';
cekSessionTimeout();
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin() && !isPengelola()) jsonResponse(false, 'Akses ditolak.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $isEdit = $action === 'edit';
    $id      = $isEdit ? (int) ($_POST['id'] ?? 0) : 0;
    $nama_biaya = mysqli_real_escape_string($conn, trim($_POST['nama_biaya'] ?? ''));
    $kategori   = mysqli_real_escape_string($conn, trim($_POST['kategori'] ?? ''));
    $tipe_durasi = mysqli_real_escape_string($conn, trim($_POST['tipe_durasi'] ?? ''));
    $satuan     = mysqli_real_escape_string($conn, trim($_POST['satuan'] ?? ''));
    $deskripsi  = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $keterangan = mysqli_real_escape_string($conn, trim($_POST['keterangan'] ?? ''));
    $min_peserta = isset($_POST['min_peserta']) && $_POST['min_peserta'] !== '' ? (int) $_POST['min_peserta'] : null;
    $max_peserta = isset($_POST['max_peserta']) && $_POST['max_peserta'] !== '' ? (int) $_POST['max_peserta'] : null;
    $harga_dasar = str_replace(['.', ','], ['', '.'], $_POST['harga_dasar'] ?? 0);
    $harga_dasar = (float) $harga_dasar;
    $harga_tambahan = isset($_POST['harga_per_hari_tambahan']) && $_POST['harga_per_hari_tambahan'] !== ''
        ? (float) str_replace(['.', ','], ['', '.'], $_POST['harga_per_hari_tambahan'])
        : null;

    $validKategori = ['fasilitas_umum', 'fasilitas_khusus', 'kegiatan_pramuka', 'kegiatan_umum', 'event_khusus'];
    $validDurasi = ['hari', 'paket', 'event'];
    if (empty($nama_biaya) || empty($kategori) || empty($tipe_durasi) || empty($satuan) || $harga_dasar < 0)
        jsonResponse(false, 'Nama, kategori, tipe durasi, satuan, dan harga dasar harus diisi.');
    if (!in_array($kategori, $validKategori) || !in_array($tipe_durasi, $validDurasi))
        jsonResponse(false, 'Kategori atau tipe durasi tidak valid.');

    if ($isEdit) {
        if ($id < 1) jsonResponse(false, 'ID tidak valid.');
        $stmt = mysqli_prepare($conn, "UPDATE biaya SET nama_biaya=?, kategori=?, tipe_durasi=?, min_peserta=?, max_peserta=?, deskripsi=?, harga_dasar=?, harga_per_hari_tambahan=?, satuan=?, keterangan=?, harga=? WHERE id=?");
        if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
        mysqli_stmt_bind_param($stmt, 'sssiisddssdi', $nama_biaya, $kategori, $tipe_durasi, $min_peserta, $max_peserta, $deskripsi, $harga_dasar, $harga_tambahan, $satuan, $keterangan, $harga_dasar, $id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO biaya (nama_biaya, kategori, tipe_durasi, min_peserta, max_peserta, deskripsi, harga_dasar, harga_per_hari_tambahan, satuan, keterangan, harga) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
        mysqli_stmt_bind_param($stmt, 'sssiisddssd', $nama_biaya, $kategori, $tipe_durasi, $min_peserta, $max_peserta, $deskripsi, $harga_dasar, $harga_tambahan, $satuan, $keterangan, $harga_dasar);
    }

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        $msg = $isEdit ? 'Biaya berhasil diperbarui.' : 'Biaya berhasil ditambahkan.';
        catatAktivitas($conn, ($isEdit ? 'Mengedit' : 'Menambahkan') . " biaya: {$nama_biaya}", $isEdit ? 'edit' : 'tambah');
        jsonResponse(true, $msg);
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
