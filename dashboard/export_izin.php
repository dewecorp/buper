<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');

$type = $_GET['type'] ?? '';
if (!in_array($type, ['excel', 'pdf'])) die('Tipe tidak valid.');

$q = mysqli_query($conn, "SELECT * FROM izin_penggunaan ORDER BY created_at DESC");
$daftar = [];
while ($row = mysqli_fetch_assoc($q)) $daftar[] = $row;

$title = "Data Izin Penggunaan BUPER Pakis Adhi Kwarcab Jepara";
$ketua = getPengaturan($conn, 'ketua_kwarcab');

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_izin.xls"');
    header('Cache-Control: max-age=0');
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '<style>td,th{font-size:11pt;padding:4px 6px;border:1px solid #999}th{background:#e0e0e0;font-weight:bold}</style>';
    echo '</head><body>';
    echo "<h2>{$title}</h2>";
    echo '<table>';
    echo '<tr><th>No</th><th>No. Registrasi</th><th>Nama Peminjam</th><th>No. WA</th><th>Nama Kegiatan</th><th>Penanggung Jawab</th><th>Organisasi</th><th>Bentuk Kegiatan</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Peserta</th><th>Pendamping</th><th>Status</th><th>Catatan</th></tr>';
    foreach ($daftar as $i => $row) {
        $bk = $row['bentuk_kegiatan'] ?? '';
        if ($bk === 'perkemahan') $bk = 'Perkemahan';
        elseif ($bk === 'outbond') $bk = 'Out Bond';
        elseif ($bk === 'outdoor_project') $bk = 'Outdoor Project';
        elseif ($bk === 'lainnya') $bk = 'Lainnya';
        echo '<tr>';
        echo '<td>' . ($i + 1) . '</td>';
        echo '<td>' . e($row['nomor_registrasi'] ?? '-') . '</td>';
        echo '<td>' . e($row['nama_peminjam']) . '</td>';
        echo '<td>' . e($row['nowa'] ?? '-') . '</td>';
        echo '<td>' . e($row['nama_kegiatan'] ?? '-') . '</td>';
        echo '<td>' . e($row['penanggung_jawab'] ?? '-') . '</td>';
        echo '<td>' . e($row['organisasi'] ?? '-') . '</td>';
        echo '<td>' . e($bk) . '</td>';
        echo '<td>' . ($row['tanggal_mulai'] ? formatTanggal($row['tanggal_mulai']) : '-') . '</td>';
        echo '<td>' . ($row['tanggal_selesai'] ? formatTanggal($row['tanggal_selesai']) : '-') . '</td>';
        echo '<td>' . e($row['jumlah_peserta']) . '</td>';
        echo '<td>' . e($row['jumlah_pendamping'] ?? '0') . '</td>';
        echo '<td>' . e(ucfirst($row['status'])) . '</td>';
        echo '<td>' . e($row['catatan_admin'] ?? '-') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</body></html>';
    exit;
}

if ($type === 'pdf') {
    $profil = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
    $profil = mysqli_fetch_assoc($profil);
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= e($title) ?></title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #333; margin: 10px 5px; padding:0; }
    .kop { text-align: center; margin-bottom: 12px; }
    .kop-title { font-size: 13pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin: 2px 0; }
    .kop-sub { font-size: 12pt; font-weight: 600; text-transform: uppercase; margin: 2px 0; }
    .kop-tahun { font-size: 11pt; margin: 2px 0; }
    .kop-divider { border: none; border-top: 2px solid #333; margin: 6px 0 10px; }
    table { width: 100%; border-collapse: collapse; font-size: 8pt; }
    th, td { border: 1px solid #666; padding: 3px 4px; text-align: left; }
    th { background: #d9d9d9; font-weight: bold; }
    .ttd { margin-top: 30px; display: flex; justify-content: flex-end; font-size: 10pt; }
    .ttd div { text-align: center; }
    .no-print { text-align: center; margin-bottom: 10px; }
    .no-print button { padding: 8px 18px; background: #059669; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin: 0 4px; }
    .no-print button:hover { background: #047857; }
    @media print {
        body { margin: 10px 5px; padding:0; }
        .no-print { display: none; }
        th { background: #d9d9d9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()">Cetak / Simpan PDF</button>
    <button onclick="window.close()">Tutup</button>
</div>

<div class="kop">
    <div class="kop-title">DATA IZIN PENGGUNAAN</div>
    <div class="kop-sub">BUPER PAKIS ADHI JEPARA</div>
    <div class="kop-tahun">Tahun <?= date('Y') ?></div>
    <hr class="kop-divider">
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No. Registrasi</th>
            <th>Nama Peminjam</th>
            <th>No. WA</th>
            <th>Nama Kegiatan</th>
            <th>Penanggung Jawab</th>
            <th>Organisasi</th>
            <th>Bentuk Kegiatan</th>
            <th>Tanggal</th>
            <th>Peserta</th>
            <th>Pendamping</th>
            <th>Status</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($daftar as $i => $row):
            $bk = $row['bentuk_kegiatan'] ?? '';
            if ($bk === 'perkemahan') $bk = 'Perkemahan';
            elseif ($bk === 'outbond') $bk = 'Out Bond';
            elseif ($bk === 'outdoor_project') $bk = 'Outdoor Project';
            elseif ($bk === 'lainnya') $bk = 'Lainnya';
        ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($row['nomor_registrasi'] ?? '-') ?></td>
            <td><?= e($row['nama_peminjam']) ?></td>
            <td><?= e($row['nowa'] ?? '-') ?></td>
            <td><?= e($row['nama_kegiatan'] ?? '-') ?></td>
            <td><?= e($row['penanggung_jawab'] ?? '-') ?></td>
            <td><?= e($row['organisasi'] ?? '-') ?></td>
            <td><?= e($bk) ?></td>
            <td><?= formatTanggal($row['tanggal_mulai']) ?> - <?= formatTanggal($row['tanggal_selesai']) ?></td>
            <td style="text-align:center"><?= e($row['jumlah_peserta']) ?></td>
            <td style="text-align:center"><?= e($row['jumlah_pendamping'] ?? '0') ?></td>
            <td><?= e(ucfirst($row['status'])) ?></td>
            <td><?= e($row['catatan_admin'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="ttd">
    <div>
        <div>Jepara, <?= formatTanggal(date('Y-m-d')) ?></div>
        <div style="margin-top:16px;color:#888;font-size:10pt">Ketua Kwarcab Jepara</div>
        <?php if (!empty($ketua)): ?>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data=<?= urlencode(e($ketua)) ?>" alt="QR" style="width:70px;height:70px;display:block;margin:6px auto 4px">
        <?php endif; ?>
        <div style="margin-top:4px;font-weight:bold;font-size:11pt"><?= e($ketua ?: 'Ketua Kwarcab Jepara') ?></div>
    </div>
</div>
<script>window.onload = function() { setTimeout(function() { window.print(); }, 500); };</script>
</body>
</html>
<?php
    exit;
}
