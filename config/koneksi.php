<?php
session_start();
require_once __DIR__ . '/functions.php';

$host = "localhost";
$user = "root";
$pass = "";
$db   = "buper_jepara";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']));
}
mysqli_set_charset($conn, "utf8mb4");
?>
