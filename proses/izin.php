<?php
require_once __DIR__ . '/../config/koneksi.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Metode tidak diizinkan.');

$action = $_POST['action'] ?? '';
$role   = $_SESSION['role'] ?? '';

// Public actions — no login/CSRF required (even if user is logged in)
$publicActions = ['add', 'add_public', 'edit_public'];
$isPublic = in_array($action, $publicActions);

// Protected actions require login + CSRF
if (!$isPublic) {
    if (!isLogin()) jsonResponse(false, 'Silakan login terlebih dahulu.');
    requireCSRF();
}

// Role checks for protected actions
if ($action === 'delete') {
    if (!in_array($role, ['pengelola', 'admin'])) jsonResponse(false, 'Akses ditolak.');
}
if (($action === 'edit') && !$isPublic) {
    if ($role !== 'pengelola') jsonResponse(false, 'Hanya pengelola yang dapat mengubah data izin.');
}
if ($action === 'approve' || $action === 'reject' || $action === 'selesai') {
    if (!in_array($role, ['pengelola', 'admin'])) jsonResponse(false, 'Akses ditolak.');
}

if ($action === 'add' || $action === 'add_public') {
    $nama_peminjam     = mysqli_real_escape_string($conn, trim($_POST['nama_peminjam'] ?? ''));
    $organisasi        = mysqli_real_escape_string($conn, trim($_POST['organisasi'] ?? ''));
    $telepon           = mysqli_real_escape_string($conn, trim($_POST['telepon'] ?? ''));
    $nowa              = mysqli_real_escape_string($conn, trim($_POST['nowa'] ?? ''));
    $email             = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $nama_kegiatan     = mysqli_real_escape_string($conn, trim($_POST['nama_kegiatan'] ?? ''));
    $penanggung_jawab  = mysqli_real_escape_string($conn, trim($_POST['penanggung_jawab'] ?? ''));
    $bentuk_kegiatan   = mysqli_real_escape_string($conn, trim($_POST['bentuk_kegiatan'] ?? ''));
    $tanggal_mulai     = mysqli_real_escape_string($conn, trim($_POST['tanggal_mulai'] ?? ''));
    $tanggal_selesai   = mysqli_real_escape_string($conn, trim($_POST['tanggal_selesai'] ?? ''));
    $jumlah_peserta    = (int) ($_POST['jumlah_peserta'] ?? 0);
    $jumlah_pendamping = (int) ($_POST['jumlah_pendamping'] ?? 0);
    $keperluan         = mysqli_real_escape_string($conn, trim($_POST['keperluan'] ?? ''));
    $file_surat        = '';

    if (empty($nama_peminjam) || empty($nowa) || empty($nama_kegiatan) || empty($penanggung_jawab) || empty($bentuk_kegiatan) || empty($tanggal_mulai) || empty($tanggal_selesai) || $jumlah_peserta < 1) {
        jsonResponse(false, 'Data tidak lengkap.');
    }
    if (!in_array($bentuk_kegiatan, ['perkemahan','outbond','outdoor_project','lainnya'])) {
        jsonResponse(false, 'Bentuk kegiatan tidak valid.');
    }

    if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['file_surat']['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            jsonResponse(false, 'File harus berupa PDF.');
        }
        if ($_FILES['file_surat']['size'] > 2097152) {
            jsonResponse(false, 'Ukuran file maksimal 2 MB.');
        }
        $ext = strtolower(pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION));
        $name = 'surat_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = __DIR__ . '/../uploads/' . $name;
        if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $dest)) {
            $file_surat = 'uploads/' . $name;
        } else {
            jsonResponse(false, 'Gagal menyimpan file.');
        }
    } else {
        jsonResponse(false, 'File surat permohonan wajib diunggah.');
    }

    $status = 'pending';
    $stmt = mysqli_prepare($conn, "INSERT INTO izin_penggunaan (nama_peminjam, organisasi, telepon, nowa, email, nama_kegiatan, penanggung_jawab, bentuk_kegiatan, tanggal_mulai, tanggal_selesai, jumlah_peserta, jumlah_pendamping, keperluan, file_surat, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'ssssssssssiisss', $nama_peminjam, $organisasi, $telepon, $nowa, $email, $nama_kegiatan, $penanggung_jawab, $bentuk_kegiatan, $tanggal_mulai, $tanggal_selesai, $jumlah_peserta, $jumlah_pendamping, $keperluan, $file_surat, $status);

    if (mysqli_stmt_execute($stmt)) {
        $insertedId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menambahkan izin penggunaan: {$nama_peminjam}", "tambah");

        $notifPesan = "Nama: {$nama_peminjam}\nOrganisasi: {$organisasi}\nKegiatan: {$nama_kegiatan}\nTanggal: " . formatTanggal($tanggal_mulai) . " - " . formatTanggal($tanggal_selesai) . "\nPeserta: {$jumlah_peserta} orang";
        buatNotifikasi($conn, $insertedId, "Ajuan Baru dari {$nama_peminjam}", $notifPesan);

        // Notifikasi WhatsApp ke pengelola (silent, jangan blok)
        $extra = ['insert_id' => $insertedId];
        $waTarget = getPengaturan($conn, 'wa_pengelola');
        if (!empty($waTarget)) {
            $waMsg = "📋 *Notifikasi Pengajuan Izin Penggunaan Buper*\n\n"
                . "Nama: {$nama_peminjam}\n"
                . "Organisasi: {$organisasi}\n"
                . "Kegiatan: {$nama_kegiatan}\n"
                . "Tanggal: " . formatTanggal($tanggal_mulai) . " - " . formatTanggal($tanggal_selesai) . "\n"
                . "Peserta: {$jumlah_peserta} orang\n\n"
                . "Mohon peninjauan dan pemberian izin. Terima kasih.";
            @sendWhatsAppNotification($conn, $waTarget, $waMsg);
            $phone = preg_replace('/[^0-9]/', '', $waTarget);
            if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
            if (substr($phone, 0, 2) !== '62') $phone = '62' . $phone;
            $extra['wa_url'] = 'https://wa.me/' . $phone . '?text=' . urlencode($waMsg);
        }

        jsonResponse(true, 'Izin berhasil diajukan.', $extra);
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'edit') {
    $id              = (int) ($_POST['id'] ?? 0);
    $nama_peminjam   = mysqli_real_escape_string($conn, trim($_POST['nama_peminjam'] ?? ''));
    $organisasi      = mysqli_real_escape_string($conn, trim($_POST['organisasi'] ?? ''));
    $telepon         = mysqli_real_escape_string($conn, trim($_POST['telepon'] ?? ''));
    $email           = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $tanggal_mulai   = mysqli_real_escape_string($conn, trim($_POST['tanggal_mulai'] ?? ''));
    $tanggal_selesai = mysqli_real_escape_string($conn, trim($_POST['tanggal_selesai'] ?? ''));
    $jumlah_peserta  = (int) ($_POST['jumlah_peserta'] ?? 0);
    $keperluan       = mysqli_real_escape_string($conn, trim($_POST['keperluan'] ?? ''));

    if ($id < 1 || empty($nama_peminjam)) jsonResponse(false, 'Data tidak lengkap.');

    $stmt = mysqli_prepare($conn, "UPDATE izin_penggunaan SET nama_peminjam=?, organisasi=?, telepon=?, email=?, tanggal_mulai=?, tanggal_selesai=?, jumlah_peserta=?, keperluan=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'ssssssisi', $nama_peminjam, $organisasi, $telepon, $email, $tanggal_mulai, $tanggal_selesai, $jumlah_peserta, $keperluan, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Mengedit izin penggunaan #{$id}", "edit");
        jsonResponse(true, 'Izin berhasil diperbarui.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'edit_public') {
    $id                = (int) ($_POST['id'] ?? 0);
    $nama_peminjam     = mysqli_real_escape_string($conn, trim($_POST['nama_peminjam'] ?? ''));
    $organisasi        = mysqli_real_escape_string($conn, trim($_POST['organisasi'] ?? ''));
    $telepon           = mysqli_real_escape_string($conn, trim($_POST['telepon'] ?? ''));
    $nowa              = mysqli_real_escape_string($conn, trim($_POST['nowa'] ?? ''));
    $email             = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $nama_kegiatan     = mysqli_real_escape_string($conn, trim($_POST['nama_kegiatan'] ?? ''));
    $penanggung_jawab  = mysqli_real_escape_string($conn, trim($_POST['penanggung_jawab'] ?? ''));
    $bentuk_kegiatan   = mysqli_real_escape_string($conn, trim($_POST['bentuk_kegiatan'] ?? ''));
    $tanggal_mulai     = mysqli_real_escape_string($conn, trim($_POST['tanggal_mulai'] ?? ''));
    $tanggal_selesai   = mysqli_real_escape_string($conn, trim($_POST['tanggal_selesai'] ?? ''));
    $jumlah_peserta    = (int) ($_POST['jumlah_peserta'] ?? 0);
    $jumlah_pendamping = (int) ($_POST['jumlah_pendamping'] ?? 0);
    $keperluan         = mysqli_real_escape_string($conn, trim($_POST['keperluan'] ?? ''));

    if ($id < 1 || empty($nama_peminjam) || empty($nowa) || empty($nama_kegiatan) || empty($penanggung_jawab) || empty($bentuk_kegiatan) || empty($tanggal_mulai) || empty($tanggal_selesai) || $jumlah_peserta < 1) {
        jsonResponse(false, 'Data tidak lengkap.');
    }
    if (!in_array($bentuk_kegiatan, ['perkemahan','outbond','outdoor_project','lainnya'])) {
        jsonResponse(false, 'Bentuk kegiatan tidak valid.');
    }

    // Handle optional file replacement
    $q = mysqli_query($conn, "SELECT file_surat FROM izin_penggunaan WHERE id = $id LIMIT 1");
    $old = mysqli_fetch_assoc($q);
    $file_surat = $old['file_surat'] ?? '';

    if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['file_surat']['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') jsonResponse(false, 'File harus berupa PDF.');
        if ($_FILES['file_surat']['size'] > 2097152) jsonResponse(false, 'Ukuran file maksimal 2 MB.');
        $ext = strtolower(pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION));
        $name = 'surat_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = __DIR__ . '/../uploads/' . $name;
        if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $dest)) {
            if (!empty($file_surat) && file_exists(__DIR__ . '/../' . $file_surat)) {
                @unlink(__DIR__ . '/../' . $file_surat);
            }
            $file_surat = 'uploads/' . $name;
        } else {
            jsonResponse(false, 'Gagal menyimpan file.');
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE izin_penggunaan SET nama_peminjam=?, organisasi=?, telepon=?, nowa=?, email=?, nama_kegiatan=?, penanggung_jawab=?, bentuk_kegiatan=?, tanggal_mulai=?, tanggal_selesai=?, jumlah_peserta=?, jumlah_pendamping=?, keperluan=?, file_surat=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'ssssssssssiissi', $nama_peminjam, $organisasi, $telepon, $nowa, $email, $nama_kegiatan, $penanggung_jawab, $bentuk_kegiatan, $tanggal_mulai, $tanggal_selesai, $jumlah_peserta, $jumlah_pendamping, $keperluan, $file_surat, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Mengedit ajuan publik #{$id}", "edit");
        jsonResponse(true, 'Ajuan berhasil diperbarui.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $stmt = mysqli_prepare($conn, "DELETE FROM izin_penggunaan WHERE id = ?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        catatAktivitas($conn, "Menghapus izin penggunaan #{$id}", "hapus");
        jsonResponse(true, 'Izin berhasil dihapus.');
    } else {
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . mysqli_error($conn));
    }
} elseif ($action === 'approve') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $catatan = mysqli_real_escape_string($conn, trim($_POST['catatan_admin'] ?? ''));
    $stmt = mysqli_prepare($conn, "UPDATE izin_penggunaan SET status='disetujui', catatan_admin=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'si', $catatan, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        $extra = [];
        $q = mysqli_query($conn, "SELECT nama_peminjam, nowa FROM izin_penggunaan WHERE id=$id");
        $r = mysqli_fetch_assoc($q);
        if ($r && !empty($r['nowa'])) {
            $msg = "✅ *Izin Penggunaan Buper Disetujui*\n\n"
                . "Assalamu'alaikum {$r['nama_peminjam']},\n\n"
                . "Izin penggunaan Bumi Perkemahan Kwarcab Jepara telah *DISETUJUI*.\n"
                . ($catatan ? "Catatan: {$catatan}\n\n" : "\n")
                . "Silakan hubungi pengelola untuk informasi lebih lanjut.\n"
                . "Terima kasih.";
            @sendWhatsAppNotification($conn, $r['nowa'], $msg);
            $phone = preg_replace('/[^0-9]/', '', $r['nowa']);
            if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
            if (substr($phone, 0, 2) !== '62') $phone = '62' . $phone;
            $extra['wa_url'] = 'https://wa.me/' . $phone . '?text=' . urlencode($msg);
        }

        jsonResponse(true, 'Izin disetujui.', $extra);
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'reject') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $catatan = mysqli_real_escape_string($conn, trim($_POST['catatan_admin'] ?? ''));
    $stmt = mysqli_prepare($conn, "UPDATE izin_penggunaan SET status='ditolak', catatan_admin=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'si', $catatan, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        $extra = [];
        $q = mysqli_query($conn, "SELECT nama_peminjam, nowa FROM izin_penggunaan WHERE id=$id");
        $r = mysqli_fetch_assoc($q);
        if ($r && !empty($r['nowa'])) {
            $msg = "❌ *Izin Penggunaan Buper Ditolak*\n\n"
                . "Assalamu'alaikum {$r['nama_peminjam']},\n\n"
                . "Mohon maaf, izin penggunaan Bumi Perkemahan Kwarcab Jepara *DITOLAK*.\n"
                . ($catatan ? "Alasan: {$catatan}\n\n" : "\n")
                . "Silakan ajukan ulang atau hubungi pengelola untuk keterangan lebih lanjut.\n"
                . "Terima kasih.";
            @sendWhatsAppNotification($conn, $r['nowa'], $msg);
            $phone = preg_replace('/[^0-9]/', '', $r['nowa']);
            if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
            if (substr($phone, 0, 2) !== '62') $phone = '62' . $phone;
            $extra['wa_url'] = 'https://wa.me/' . $phone . '?text=' . urlencode($msg);
        }

        jsonResponse(true, 'Izin ditolak.', $extra);
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} elseif ($action === 'selesai') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) jsonResponse(false, 'ID tidak valid.');

    $catatan = mysqli_real_escape_string($conn, trim($_POST['catatan_admin'] ?? ''));
    $stmt = mysqli_prepare($conn, "UPDATE izin_penggunaan SET status='selesai', catatan_admin=? WHERE id=?");
    if (!$stmt) jsonResponse(false, 'Gagal menyiapkan query.');
    mysqli_stmt_bind_param($stmt, 'si', $catatan, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        jsonResponse(true, 'Status diubah selesai.');
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        jsonResponse(false, 'Gagal: ' . $err);
    }
} else {
    jsonResponse(false, 'Aksi tidak dikenal.');
}
