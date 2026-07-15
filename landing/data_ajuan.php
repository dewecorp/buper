<?php
require_once __DIR__ . '/../config/koneksi.php';
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM profil WHERE id = 1"));
$logoAjuan = getPengaturan($conn, 'logo');
$namaWebAjuan = getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';

$search = trim($_GET['cari'] ?? '');
$s = mysqli_real_escape_string($conn, $search);
if (!empty($search)) {
    $q = mysqli_query($conn, "SELECT * FROM izin_penggunaan WHERE nama_peminjam LIKE '%$s%' OR organisasi LIKE '%$s%' OR telepon LIKE '%$s%' OR email LIKE '%$s%' ORDER BY created_at DESC");
} else {
    $q = mysqli_query($conn, "SELECT * FROM izin_penggunaan ORDER BY created_at DESC");
}
$izin_list = [];
while ($row = mysqli_fetch_assoc($q)) $izin_list[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($namaWebAjuan) ?> | Data Ajuan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brown: { 50:'#efebe9',100:'#d7ccc8',200:'#bcaaa4',300:'#a1887f',400:'#8d6e63',500:'#795548',600:'#5d4037',700:'#4e342e',800:'#3e2723',900:'#2c1a12' },
                        emerald: { 50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b' },
                        purple: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' }
                    }
                }
            }
        }
    </script>
    <?php if (!empty($logoAjuan)): ?>
    <link rel="icon" href="../<?= e($logoAjuan) ?>">
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .modal-dashboard { position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:1050; padding:1rem; overflow:hidden; }
        .modal-dashboard.hidden { display:none; }
        .modal-dialog { width:100%; }
        .modal-dashboard-lg .modal-dialog { max-width:640px; }
        .modal-content { background-color:#fff; border-radius:1rem; box-shadow:0 0 0 1px rgba(124,58,237,0.1), 0 8px 40px rgba(124,58,237,0.18), 0 2px 10px rgba(0,0,0,0.08); padding:1.5rem; max-height:85vh; overflow-y:auto; border-top:3px solid #7c3aed; }
        .modal-content::-webkit-scrollbar { width:5px; }
        .modal-content::-webkit-scrollbar-track { background:transparent; }
        .modal-content::-webkit-scrollbar-thumb { background:rgba(124,58,237,0.3); border-radius:99px; }
    </style>
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/navbar.php'; ?>

<section class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-brown-800 mb-2">Data Ajuan Penggunaan</h1>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
            <p class="text-gray-600 mt-4">Cari status pengajuan izin penggunaan Buper Anda.</p>
        </div>

        <!-- Search Form -->
        <div class="max-w-xl mx-auto mb-10">
            <form method="GET" class="flex gap-2">
                <input type="text" name="cari" value="<?= e($search) ?>" placeholder="Cari berdasarkan nama, organisasi, telepon, atau email..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none text-sm">
                <button type="submit" class="px-5 py-2.5 bg-purple-700 text-white rounded-lg hover:bg-purple-600 transition shadow-md font-medium text-sm"><i class="bi bi-search mr-1"></i>Cari</button>
            </form>
        </div>

        <?php if (empty($izin_list)): ?>
            <div class="text-center py-12 text-gray-500">
                <i class="bi bi-inbox text-5xl block mb-3"></i>
                <p><?= $search ? 'Tidak ditemukan data untuk pencarian "<strong>' . e($search) . '</strong>".' : 'Belum ada data ajuan.' ?></p>
            </div>
        <?php else: ?>
            <div class="mb-2 text-sm text-gray-500">Menampilkan <?= count($izin_list) ?> data</div>
            <div class="bg-white rounded-2xl shadow-md border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm leading-normal">
                                <th class="py-3 px-4 text-left">No</th>
                                <th class="py-3 px-4 text-left">Nama</th>
                                <th class="py-3 px-4 text-left">No. WA</th>
                                <th class="py-3 px-4 text-left">Nama Kegiatan</th>
                                <th class="py-3 px-4 text-left">Bentuk Kegiatan</th>
                                <th class="py-3 px-4 text-left">Organisasi</th>
                                <th class="py-3 px-4 text-left">Tanggal</th>
                                <th class="py-3 px-4 text-center">Peserta</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php foreach ($izin_list as $i => $row): ?>
                            <?php
                                $sc = 'bg-gray-100 text-gray-700';
                                if ($row['status'] === 'pending') $sc = 'bg-yellow-100 text-yellow-700';
                                elseif ($row['status'] === 'disetujui') $sc = 'bg-emerald-100 text-emerald-700';
                                elseif ($row['status'] === 'ditolak') $sc = 'bg-red-100 text-red-700';
                                elseif ($row['status'] === 'selesai') $sc = 'bg-blue-100 text-blue-700';
                                $bk = $row['bentuk_kegiatan'] ?? '';
                                if ($bk === 'perkemahan') $bk = 'Perkemahan';
                                elseif ($bk === 'outbond') $bk = 'Out Bond';
                                elseif ($bk === 'outdoor_project') $bk = 'Outdoor Project';
                                elseif ($bk === 'lainnya') $bk = 'Lainnya';
                            ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium"><?= $i + 1 ?></td>
                                <td class="py-3 px-4 font-medium"><?= e($row['nama_peminjam']) ?></td>
                                <td class="py-3 px-4"><?= e($row['nowa'] ?? '-') ?></td>
                                <td class="py-3 px-4"><?= e($row['nama_kegiatan'] ?? '-') ?></td>
                                <td class="py-3 px-4"><?= e($bk) ?></td>
                                <td class="py-3 px-4"><?= e($row['organisasi'] ?? '-') ?></td>
                                <td class="py-3 px-4 text-sm whitespace-nowrap"><?= e(formatTanggal($row['tanggal_mulai'])) ?> - <?= e(formatTanggal($row['tanggal_selesai'])) ?></td>
                                <td class="py-3 px-4 text-center"><?= e($row['jumlah_peserta']) ?></td>
                                <td class="py-3 px-4 text-center"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= $sc ?>"><?= e(ucfirst($row['status'])) ?></span></td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <?php if ($row['status'] === 'pending'): ?>
                                        <button onclick='openEditAjuan(<?= json_encode($row) ?>)' class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!empty($row['file_surat'])): ?>
                                        <button onclick="previewSurat(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Lihat Surat">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </button>
                                        <?php endif; ?>
                                        <a href="cetak_izin.php?id=<?= e($row['id']) ?>" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition" title="Cetak Bukti">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Edit Ajuan -->
<div id="editAjuanModal" class="modal-dashboard modal-dashboard-lg hidden">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Edit Ajuan</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>
            <form id="editAjuanForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit_public">
                <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_peminjam" id="edit_nama" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organisasi/Instansi</label>
                        <input type="text" name="organisasi" id="edit_organisasi" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="telepon" id="edit_telepon" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. WA <span class="text-red-500">*</span></label>
                        <input type="text" name="nowa" id="edit_nowa" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kegiatan" id="edit_nama_kegiatan" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab <span class="text-red-500">*</span></label>
                        <input type="text" name="penanggung_jawab" id="edit_penanggung_jawab" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bentuk Kegiatan <span class="text-red-500">*</span></label>
                        <select name="bentuk_kegiatan" id="edit_bentuk_kegiatan" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                            <option value="perkemahan">Perkemahan</option>
                            <option value="outbond">Out Bond</option>
                            <option value="outdoor_project">Outdoor Project</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" id="edit_tgl_mulai" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_selesai" id="edit_tgl_selesai" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Peserta <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah_peserta" id="edit_jumlah_peserta" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pendamping</label>
                        <input type="number" name="jumlah_pendamping" id="edit_jumlah_pendamping" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan/Rencana Kegiatan</label>
                    <textarea name="keperluan" id="edit_keperluan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ganti File Surat <span class="text-xs text-gray-400 font-normal">(PDF, maks 2 MB, kosongkan jika tidak diubah)</span></label>
                    <input type="file" name="file_surat" accept=".pdf,application/pdf" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow hover:bg-emerald-700 transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview Surat -->
<div id="previewModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Preview Surat</h3>
            <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600 transition-colors text-2xl leading-none">&times;</button>
        </div>
        <div class="flex-1 min-h-0 p-2">
            <iframe id="previewIframe" src="" class="w-full h-[80vh] rounded border-0"></iframe>
        </div>
    </div>
</div>
<script>
function previewSurat(id) {
    document.getElementById('previewIframe').src = '';
    document.getElementById('previewModal').classList.remove('hidden');
    document.getElementById('previewModal').querySelector('h3').textContent = 'Memuat...';
    const url = '../preview_surat.php?id=' + id + '&t=' + Date.now();
    fetch(url)
        .then(r => r.arrayBuffer())
        .then(buf => {
            const blob = new Blob([buf], { type: 'application/pdf' });
            document.getElementById('previewIframe').src = URL.createObjectURL(blob);
            document.getElementById('previewModal').querySelector('h3').textContent = 'Preview Surat';
        })
        .catch(() => window.open(url, '_blank'));
}
function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
    document.getElementById('previewIframe').src = '';
}
</script>

<footer class="bg-brown-800 text-white py-8 text-center text-sm">
    &copy; <?= date('Y') ?> <?= e($profil['nama_buper'] ?? 'Buper Jepara') ?>. All rights reserved.
</footer>

<script>
function openEditAjuan(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama_peminjam;
    document.getElementById('edit_organisasi').value = data.organisasi || '';
    document.getElementById('edit_telepon').value = data.telepon || '';
    document.getElementById('edit_nowa').value = data.nowa || '';
    document.getElementById('edit_email').value = data.email || '';
    document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan || '';
    document.getElementById('edit_penanggung_jawab').value = data.penanggung_jawab || '';
    document.getElementById('edit_bentuk_kegiatan').value = data.bentuk_kegiatan || 'perkemahan';
    document.getElementById('edit_tgl_mulai').value = data.tanggal_mulai;
    document.getElementById('edit_tgl_selesai').value = data.tanggal_selesai;
    document.getElementById('edit_jumlah_peserta').value = data.jumlah_peserta;
    document.getElementById('edit_jumlah_pendamping').value = data.jumlah_pendamping || 0;
    document.getElementById('edit_keperluan').value = data.keperluan || '';
    document.getElementById('editAjuanModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editAjuanModal').classList.add('hidden');
}

document.getElementById('editAjuanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('../proses/izin.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 })
            .then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#047857' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.', confirmButtonColor: '#047857' }));
});
</script>
</body>
</html>
