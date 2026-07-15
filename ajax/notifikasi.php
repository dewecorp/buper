<?php
require_once __DIR__ . '/../config/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
    mysqli_query($conn, "DELETE FROM notifikasi WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $q = mysqli_query($conn, "SELECT * FROM notifikasi ORDER BY created_at DESC LIMIT 20");
    $list = [];
    $unread = 0;
    while ($r = mysqli_fetch_assoc($q)) {
        $list[] = $r;
        if (!$r['dibaca']) $unread++;
    }
    echo json_encode(['success' => true, 'unread' => $unread, 'data' => $list], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'read') {
    $id = (int) ($_POST['id'] ?? 0);
    mysqli_query($conn, "UPDATE notifikasi SET dibaca=1 WHERE id=$id AND dibaca=0");
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'read_all') {
    mysqli_query($conn, "UPDATE notifikasi SET dibaca=1 WHERE dibaca=0");
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
