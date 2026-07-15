<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin()) jsonResponse(false, 'Hanya admin yang dapat mengelola data user.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');
requireCSRF();

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $username     = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $nama_lengkap = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap'] ?? ''));
    $password     = $_POST['password'] ?? '';
    $role         = mysqli_real_escape_string($conn, trim($_POST['role'] ?? 'pengelola'));

    if (empty($username) || empty($nama_lengkap) || empty($password)) {
        jsonResponse(false, 'Username, nama lengkap, dan password harus diisi.');
    }
    if (!in_array($role, ['admin', 'pengelola'])) jsonResponse(false, 'Role tidak valid.');

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' LIMIT 1");
    if (mysqli_num_rows($check) > 0) jsonResponse(false, 'Username sudah digunakan.');

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'ssss', $username, $hash, $nama_lengkap, $role);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        jsonResponse(true, 'User berhasil ditambahkan.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'edit') {
    $id           = (int) ($_POST['id'] ?? 0);
    $username     = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $nama_lengkap = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap'] ?? ''));
    $password     = $_POST['password'] ?? '';
    $role         = mysqli_real_escape_string($conn, trim($_POST['role'] ?? 'pengelola'));

    if ($id < 1 || empty($username) || empty($nama_lengkap)) {
        jsonResponse(false, 'Data tidak lengkap.');
    }
    if (!in_array($role, ['admin', 'pengelola'])) jsonResponse(false, 'Role tidak valid.');

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' AND id != $id LIMIT 1");
    if (mysqli_num_rows($check) > 0) jsonResponse(false, 'Username sudah digunakan.');

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, password=?, nama_lengkap=?, role=? WHERE id=?");
        if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
        mysqli_stmt_bind_param($stmt, 'ssssi', $username, $hash, $nama_lengkap, $role, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, nama_lengkap=?, role=? WHERE id=?");
        if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
        mysqli_stmt_bind_param($stmt, 'sssi', $username, $nama_lengkap, $role, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        jsonResponse(true, 'User berhasil diperbarui.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        jsonResponse(true, 'User berhasil dihapus.');
    } else {
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . mysqli_error($conn));
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}