<?php
/* Reset password admin & pengelola - buka sekali lalu hapus file ini */
require_once __DIR__ . '/config/koneksi.php';

$hashAdmin     = password_hash('admin123', PASSWORD_DEFAULT);
$hashPengelola = password_hash('pengelola123', PASSWORD_DEFAULT);

$s1 = mysqli_prepare($conn, "UPDATE users SET password=? WHERE username=?");
mysqli_stmt_bind_param($s1, 'ss', $hashAdmin, $admin);
$admin = 'admin';
mysqli_stmt_execute($s1);

$s2 = mysqli_prepare($conn, "UPDATE users SET password=? WHERE username=?");
mysqli_stmt_bind_param($s2, 'ss', $hashPengelola, $pengelola);
$pengelola = 'pengelola';
mysqli_stmt_execute($s2);

echo "<h2>Password berhasil di-reset</h2>";
echo "<p>Admin: <b>admin</b> / <b>admin123</b></p>";
echo "<p>Pengelola: <b>pengelola</b> / <b>pengelola123</b></p>";
echo "<p><a href='auth/login.php'>Login sekarang</a></p>";
echo "<p style='color:red;margin-top:20px'><b>Hapus file ini setelah dipakai!</b></p>";
?>