<?php
require_once __DIR__ . '/config/koneksi.php';
if (!isLogin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

header('Content-Type: application/json');

$repoZip = 'https://github.com/dewecorp/buper/archive/refs/heads/main.zip';
$tmpDir = __DIR__ . '/tmp_update';
$zipFile = $tmpDir . '/repo.zip';
$extractDir = $tmpDir . '/buper-main';

// Bersihkan tmp sebelumnya jika ada
if (is_dir($tmpDir)) {
    rrmdir($tmpDir);
}

if (!mkdir($tmpDir, 0755, true)) {
    echo json_encode(['success' => false, 'message' => 'Gagal membuat direktori temporary.']);
    exit;
}

// Download ZIP
$ch = curl_init($repoZip);
$fp = fopen($zipFile, 'wb');
if (!$ch || !$fp) {
    echo json_encode(['success' => false, 'message' => 'Gagal menginisialisasi download.']);
    exit;
}
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
fclose($fp);

if ($httpCode !== 200) {
    rrmdir($tmpDir);
    echo json_encode(['success' => false, 'message' => 'Gagal mengunduh repository. HTTP code: ' . $httpCode]);
    exit;
}

// Extract ZIP
$zip = new ZipArchive;
if ($zip->open($zipFile) !== true) {
    rrmdir($tmpDir);
    echo json_encode(['success' => false, 'message' => 'Gagal membuka file ZIP.']);
    exit;
}
$zip->extractTo($tmpDir);
$zip->close();

if (!is_dir($extractDir)) {
    rrmdir($tmpDir);
    echo json_encode(['success' => false, 'message' => 'Direktori ekstraksi tidak ditemukan.']);
    exit;
}

// Daftar file/direktori yang tidak boleh ditimpa
$exclude = [
    'config/koneksi.php',
    'config/functions.php',
    '.htaccess',
    'deploy.bat',
    'buper_backup.zip',
    'tmp_update',
];

// Copy file dari extractDir ke root project
$copied = copyDir($extractDir, __DIR__, $exclude);

// Bersihkan temporary
rrmdir($tmpDir);

catatAktivitas($conn, "Memperbarui sistem dari GitHub", "update");

$versiLama = getPengaturan($conn, 'versi');
$parts = explode('.', $versiLama ?: '1.0');
$parts[count($parts)-1] = (int) ($parts[count($parts)-1]) + 1;
$versiBaru = implode('.', $parts);
mysqli_query($conn, "UPDATE pengaturan SET nilai='$versiBaru' WHERE nama_pengaturan='versi'");

echo json_encode(['success' => true, 'message' => "Sistem berhasil diperbarui! ($copied file diupdate)"]);

// Helper functions
function copyDir($src, $dst, $exclude = []) {
    $count = 0;
    $dir = opendir($src);
    if (!$dir) return $count;

    // Buat direktori di dst jika belum ada
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }

    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..') continue;

        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;

        // Relative path untuk pengecekan exclude
        $relPath = str_replace(__DIR__ . '/', '', $dstPath);

        // Cek exclude
        $excluded = false;
        foreach ($exclude as $e) {
            if ($relPath === $e || strpos($relPath, $e . '/') === 0) {
                $excluded = true;
                break;
            }
        }
        if ($excluded) continue;

        if (is_dir($srcPath)) {
            $count += copyDir($srcPath, $dstPath, $exclude);
        } else {
            copy($srcPath, $dstPath);
            $count++;
        }
    }
    closedir($dir);
    return $count;
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}
