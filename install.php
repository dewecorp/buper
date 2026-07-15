<?php
/* Installer otomatis - hapus file ini setelah selesai */
$host = "localhost";
$user = "root";
$pass = "";
$db   = "buper_jepara";

$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

$sql = file_get_contents(__DIR__ . '/sql/buper.sql');

// Eksekusi per statement (pisah dengan ;)
$statements = array_filter(array_map('trim', explode(';', $sql)));
$ok = 0; $err = [];
foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    if (mysqli_query($conn, $stmt)) {
        $ok++;
    } else {
        $err[] = mysqli_error($conn) . " | " . substr($stmt, 0, 60);
    }
}

echo "<h2>Install Selesai</h2>";
echo "<p>Statement sukses: $ok</p>";
if ($err) {
    echo "<p style='color:red'>Error:</p><ul>";
    foreach ($err as $e) echo "<li>$e</li>";
    echo "</ul>";
} else {
    echo "<p style='color:green'>Semua tabel & data berhasil dibuat.</p>";
    echo "<p><a href='index.php'>Buka Landing Page</a> | <a href='auth/login.php'>Login Admin</a></p>";
}
?>

