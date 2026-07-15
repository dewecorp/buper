<?php
require_once __DIR__ . '/config/koneksi.php';
cekSessionTimeout();
if (!isLogin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}
requireCSRF();

header('Content-Type: application/json');

// Rate limit: hanya 1 kali per jam per user
$lastUpdate = $_SESSION['last_update_sistem'] ?? 0;
if (time() - $lastUpdate < 3600) {
    $sisa = 3600 - (time() - $lastUpdate);
    $menit = ceil($sisa / 60);
    echo json_encode(['success' => false, 'message' => "Tunggu {$menit} menit lagi sebelum update berikutnya."]);
    exit;
}

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
curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);
fclose($fp);

if ($httpCode !== 200) {
    rrmdir($tmpDir);
    echo json_encode(['success' => false, 'message' => 'Gagal mengunduh repository. HTTP code: ' . $httpCode]);
    exit;
}

// Verifikasi URL final berasal dari GitHub
if (strpos($finalUrl, 'github.com') === false && strpos($finalUrl, 'githubusercontent.com') === false) {
    rrmdir($tmpDir);
    echo json_encode(['success' => false, 'message' => 'Sumber file tidak valid.']);
    exit;
}

// Verifikasi content-type adalah ZIP
if (strpos($contentType, 'zip') === false && strpos($contentType, 'octet-stream') === false) {
    rrmdir($tmpDir);
    echo json_encode(['success' => false, 'message' => 'Format file tidak valid.']);
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

// Verifikasi struktur dasar repo: harus ada index.php, config/, dashboard/
$requiredFiles = ['index.php', 'config/koneksi.php', 'config/functions.php', 'dashboard/index.php'];
foreach ($requiredFiles as $rf) {
    if (!file_exists($extractDir . '/' . $rf)) {
        rrmdir($tmpDir);
        echo json_encode(['success' => false, 'message' => 'Repository tidak valid: ' . $rf . ' tidak ditemukan.']);
        exit;
    }
}

// Scan file PHP untuk fungsi berbahaya
$dangerous = scanForDangerousFunctions($extractDir);
if (!empty($dangerous)) {
    rrmdir($tmpDir);
    echo json_encode(['success' => false, 'message' => 'Update ditolak: ditemukan kode mencurigakan di file: ' . implode(', ', array_slice($dangerous, 0, 5))]);
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

// Catat aktivitas + IP
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
catatAktivitas($conn, "Memperbarui sistem dari GitHub (IP: {$ip})", "update");

// Simpan timestamp rate limit
$_SESSION['last_update_sistem'] = time();

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

function scanForDangerousFunctions($dir) {
    $danger = ['eval', 'exec', 'system', 'passthru', 'shell_exec', 'popen', 'proc_open', 'assert', 'create_function', 'phpinfo'];
    $found = [];
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($items as $item) {
        if ($item->isFile() && $item->getExtension() === 'php') {
            $content = file_get_contents($item->getPathname());
            foreach ($danger as $func) {
                if (preg_match('/\b' . $func . '\s*\(/i', $content)) {
                    $relPath = str_replace($dir . '/', '', $item->getPathname());
                    if (!in_array($relPath, $found)) $found[] = $relPath;
                }
            }
        }
    }
    return $found;
}
