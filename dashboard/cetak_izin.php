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
$namaWebDCetak = getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';
$alamatKwarcab = getPengaturan($conn, 'alamat_kwarcab') ?: '-';
$emailKwarcab = getPengaturan($conn, 'email_kwarcab') ?: '-';
$websiteKwarcab = getPengaturan($conn, 'website_kwarcab') ?: '-';
$logoPramuka = getPengaturan($conn, 'logo_pramuka');
$logoWosm = getPengaturan($conn, 'logo_wosm');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pengajuan Izin | <?= e($namaWebDCetak) ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12pt; color: #333; margin: 0; padding: 40px; }
        .kop { text-align: center; margin-bottom: 20px; padding-bottom: 12px; }
        .kop-logos { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
        .kop-logos img { width: 70px; height: 70px; object-fit: contain; }
        .kop-title { font-size: 14pt; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin: 4px 0; }
        .kop-subtitle { font-size: 14pt; font-weight: 600; text-transform: uppercase; margin: 2px 0; }
        .kop-info { font-size: 11px; margin: 2px 0; color: #555; }
        .kop-divider { border: none; border-top: 3px solid #333; margin: 8px 0 0; }
        .judul { text-align: center; font-size: 16px; font-weight: bold; margin: 20px 0 80px; }
        table.data { width: 100%; border-collapse: collapse; font-size: 12pt; }
        table.data td { padding: 5px 8px; vertical-align: top; }
        table.data td.label { width: 200px; color: #555; }
        table.data td.sep { width: 10px; text-align: center; }
        table.data td.value { font-weight: 600; }
        .tanda-tangan { margin-top: 60px; display: flex; justify-content: space-between; align-items: flex-end; }
        .footer { font-size: 11px; color: #888; margin-top: 20px; padding-top: 10px; }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 8px 20px; background: #7c3aed; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; margin: 0 4px; }
        .no-print button:hover { background: #6d28d9; }
        .no-print .close-btn { background: #e5e7eb; color: #374151; }
        .no-print .close-btn:hover { background: #d1d5db; }
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="kop">
    <div class="kop-logos">
        <div><?php if (!empty($logoPramuka) && file_exists(__DIR__ . '/../' . $logoPramuka)): ?><img src="../<?= e($logoPramuka) ?>" alt="Pramuka"><?php endif; ?></div>
        <div>
            <div class="kop-title">Gerakan Pramuka</div>
            <div class="kop-subtitle">Kwartir Cabang Jepara</div>
            <div class="kop-info">Sekretariat: <?= e($alamatKwarcab) ?></div>
            <div class="kop-info">Email: <?= e($emailKwarcab) ?> | Website: <?= e($websiteKwarcab) ?></div>
        </div>
        <div><?php if (!empty($logoWosm) && file_exists(__DIR__ . '/../' . $logoWosm)): ?><img src="../<?= e($logoWosm) ?>" alt="WOSM"><?php endif; ?></div>
    </div>
    <hr class="kop-divider">
</div>

<div class="judul" style="margin-bottom:8px">BUKTI PENGAJUAN IZIN PENGGUNAAN</div>
<div style="text-align:center;font-size:12pt;font-weight:600;margin-bottom:80px">BUPER PAKIS ADHI KWARCAB JEPARA</div>

<table class="data">
    <tr><td class="label">1. No. Registrasi</td><td class="sep">:</td><td class="value">2011<?= date('y') . date('n') . str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td></tr>
    <tr><td class="label">2. Nama Pemohon</td><td class="sep">:</td><td class="value"><?= e($row['nama_peminjam']) ?></td></tr>
    <tr><td class="label">3. Penanggung Jawab</td><td class="sep">:</td><td class="value"><?= e($row['penanggung_jawab'] ?? '-') ?></td></tr>
    <tr><td class="label">4. Organisasi</td><td class="sep">:</td><td class="value"><?= e($row['organisasi'] ?? '-') ?></td></tr>
    <tr><td class="label">5. No. HP/WA</td><td class="sep">:</td><td class="value"><?= e($row['telepon'] ?? '-') ?></td></tr>
    <tr><td class="label">6. Email</td><td class="sep">:</td><td class="value"><?= e($row['email'] ?? '-') ?></td></tr>
    <tr><td class="label">7. Nama Kegiatan</td><td class="sep">:</td><td class="value"><?= e($row['nama_kegiatan'] ?? '-') ?></td></tr>
    <tr><td class="label">8. Bentuk Kegiatan</td><td class="sep">:</td><td class="value"><?= e($row['bentuk_kegiatan'] ?? '-') ?></td></tr>
    <tr><td class="label">9. Tanggal Mulai</td><td class="sep">:</td><td class="value"><?= e(formatTanggal($row['tanggal_mulai'])) ?></td></tr>
    <tr><td class="label">10. Tanggal Selesai</td><td class="sep">:</td><td class="value"><?= e(formatTanggal($row['tanggal_selesai'])) ?></td></tr>
    <tr><td class="label">11. Jumlah Peserta</td><td class="sep">:</td><td class="value"><?= e($row['jumlah_peserta']) ?> orang</td></tr>
    <tr><td class="label">12. Status</td><td class="sep">:</td><td class="value" style="color:<?= $statusColor ?>"><?= $statusLabel ?></td></tr>
    <?php if (!empty($row['catatan_admin'])): ?>
    <tr><td class="label">13. Catatan Admin</td><td class="sep">:</td><td class="value"><?= nl2br(e($row['catatan_admin'])) ?></td></tr>
    <?php endif; ?>
</table>

<?php if (!empty($ketua)): ?>
<div class="tanda-tangan">
    <div></div>
    <div style="text-align:center">
        <div style="font-size:12pt;margin-bottom:24px">Jepara, <?= formatTanggal(date('Y-m-d')) ?></div>
        <div style="font-size:12pt;color:#888;margin-bottom:4px">Ketua Kwarcab Jepara</div>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= urlencode(e($ketua)) ?>" alt="QR" style="width:80px;height:80px;display:block;margin:0 auto 4px">
        <div style="font-size:12pt;font-weight:600;margin-top:4px"><?= e($ketua) ?></div>
    </div>
</div>
<?php endif; ?>
<div class="footer">
    <p>Dokumen ini adalah bukti pengajuan izin penggunaan Bumi Perkemahan Kwartir Cabang Jepara.</p>
</div>

<div class="no-print">
    <button onclick="window.print()">Cetak / Print</button>
    <button class="close-btn" onclick="window.close()">Tutup</button>
</div>

</body>
</html>
