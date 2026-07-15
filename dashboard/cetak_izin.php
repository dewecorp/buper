<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) die('ID tidak valid.');

$q = mysqli_query($conn, "SELECT * FROM izin_penggunaan WHERE id = $id");
$row = mysqli_fetch_assoc($q);
if (!$row) die('Data tidak ditemukan.');

$profil = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
$profil = mysqli_fetch_assoc($profil);

$statusLabel = ucfirst($row['status']);
$statusColor = $row['status'] === 'disetujui' ? '#059669' : ($row['status'] === 'ditolak' ? '#dc2626' : ($row['status'] === 'selesai' ? '#2563eb' : '#d97706'));
$ketua = getPengaturan($conn, 'ketua_kwarcab');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pengajuan Izin - Buper Jepara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Courier New', monospace; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg border border-gray-200 p-8 print:shadow-none print:border-0">
        <!-- Header -->
        <div class="text-center border-b-2 border-gray-300 pb-4 mb-6">
            <h1 class="text-xl font-bold text-gray-900 uppercase tracking-wide">Bumi Perkemahan</h1>
            <h2 class="text-lg font-semibold text-gray-700">Kwartir Cabang Jepara</h2>
            <p class="text-sm text-gray-500"><?= e($profil['alamat'] ?? '') ?></p>
            <p class="text-sm text-gray-500">Telp: <?= e($profil['telepon'] ?? '') ?> | Email: <?= e($profil['email'] ?? '') ?></p>
        </div>

        <h3 class="text-center font-bold text-lg mb-6 text-gray-800">BUKTI PENGAJUAN IZIN PENGGUNAAN</h3>

        <table class="w-full text-sm">
            <tr>
                <td class="py-1.5 pr-4 text-gray-500 w-40">No. Registrasi</td>
                <td class="py-1.5 font-semibold">#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Nama Pemohon</td>
                <td class="py-1.5 font-semibold"><?= e($row['nama_peminjam']) ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Organisasi</td>
                <td class="py-1.5 font-semibold"><?= e($row['organisasi'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Telepon</td>
                <td class="py-1.5 font-semibold"><?= e($row['telepon'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Email</td>
                <td class="py-1.5 font-semibold"><?= e($row['email'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Tanggal Mulai</td>
                <td class="py-1.5 font-semibold"><?= e(formatTanggal($row['tanggal_mulai'])) ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Tanggal Selesai</td>
                <td class="py-1.5 font-semibold"><?= e(formatTanggal($row['tanggal_selesai'])) ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Jumlah Peserta</td>
                <td class="py-1.5 font-semibold"><?= e($row['jumlah_peserta']) ?> orang</td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Keperluan</td>
                <td class="py-1.5 font-semibold"><?= nl2br(e($row['keperluan'] ?? '-')) ?></td>
            </tr>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500">Status</td>
                <td class="py-1.5">
                    <span style="color:<?= $statusColor ?>; font-weight:700"><?= $statusLabel ?></span>
                </td>
            </tr>
            <?php if (!empty($row['catatan_admin'])): ?>
            <tr>
                <td class="py-1.5 pr-4 text-gray-500 align-top">Catatan Admin</td>
                <td class="py-1.5 font-semibold"><?= nl2br(e($row['catatan_admin'])) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <?php if (!empty($ketua)): ?>
        <div class="border-t border-gray-200 mt-8 pt-6 flex items-end justify-between">
            <div class="text-sm text-gray-500">
                <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
                <p class="mt-1">Dokumen ini adalah bukti pengajuan izin penggunaan Bumi Perkemahan Kwartir Cabang Jepara.</p>
            </div>
            <div class="text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= urlencode(e($ketua)) ?>" alt="QR Ketua" class="w-20 h-20 mx-auto mb-1">
                <p class="text-[10px] text-gray-500 font-semibold"><?= e($ketua) ?></p>
                <p class="text-[9px] text-gray-400">Ketua Kwarcab Jepara</p>
            </div>
        </div>
        <?php else: ?>
        <div class="border-t border-gray-200 mt-8 pt-6 text-sm text-gray-500 text-center">
            <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
            <p class="mt-1">Dokumen ini adalah bukti pengajuan izin penggunaan Bumi Perkemahan Kwartir Cabang Jepara.</p>
        </div>
        <?php endif; ?>

        <div class="text-center mt-8 no-print">
            <button onclick="window.print()" class="px-6 py-2.5 bg-purple-700 text-white rounded-lg hover:bg-purple-600 transition shadow-md font-medium">Cetak / Print</button>
            <button onclick="window.close()" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition ml-2 font-medium">Tutup</button>
        </div>
    </div>
</body>
</html>
