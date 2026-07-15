<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');

$title = "Data Izin Penggunaan";
$role = $_SESSION['role'] ?? '';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';

$q = mysqli_query($conn, "SELECT * FROM izin_penggunaan ORDER BY created_at DESC");
$daftar = [];
while ($row = mysqli_fetch_assoc($q)) $daftar[] = $row;
?>

<main class="flex-1 p-6 bg-gray-100 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Data Izin Penggunaan</h1>
            <p class="text-sm text-gray-500">Kelola izin penggunaan Buper.</p>
        </div>
        <?php if ($role === 'pengelola'): ?>
        <button onclick="openAddModal()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg shadow hover:bg-emerald-700 transition-colors">
            + Tambah Izin
        </button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm leading-normal">
                        <th class="py-3 px-4 text-left w-10">No</th>
                        <th class="py-3 px-4 text-left w-48">Nama Peminjam</th>
                        <th class="py-3 px-4 text-left w-28">No. WA</th>
                        <th class="py-3 px-4 text-left w-44">Nama Kegiatan</th>
                        <th class="py-3 px-4 text-left w-36">Penanggung Jawab</th>
                        <th class="py-3 px-4 text-left w-36">Organisasi</th>
                        <th class="py-3 px-4 text-left w-28">Bentuk Kegiatan</th>
                        <th class="py-3 px-4 text-left w-52">Tanggal</th>
                        <th class="py-3 px-4 text-center w-16">Peserta</th>
                        <th class="py-3 px-4 text-center w-20">Pendamping</th>
                        <th class="py-3 px-4 text-center w-12">File</th>
                        <th class="py-3 px-4 text-center w-24">Status</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($daftar as $i => $row): ?>
                    <?php
                        $bk = $row['bentuk_kegiatan'] ?? '';
                        if ($bk === 'perkemahan') $bk = 'Perkemahan';
                        elseif ($bk === 'outbond') $bk = 'Out Bond';
                        elseif ($bk === 'outdoor_project') $bk = 'Outdoor Project';
                        elseif ($bk === 'lainnya') $bk = 'Lainnya';
                    ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium"><?= e($i + 1) ?></td>
                        <td class="py-3 px-4 font-medium whitespace-nowrap"><?= e($row['nama_peminjam']) ?></td>
                        <td class="py-3 px-4 whitespace-nowrap"><?= e($row['nowa'] ?? '-') ?></td>
                        <td class="py-3 px-4"><?= e($row['nama_kegiatan'] ?? '-') ?></td>
                        <td class="py-3 px-4"><?= e($row['penanggung_jawab'] ?? '-') ?></td>
                        <td class="py-3 px-4"><?= e($row['organisasi'] ?? '-') ?></td>
                        <td class="py-3 px-4"><?= e($bk) ?></td>
                        <td class="py-3 px-4 text-sm whitespace-nowrap">
                            <?= e(formatTanggal($row['tanggal_mulai'])) ?> - <?= e(formatTanggal($row['tanggal_selesai'])) ?>
                        </td>
                        <td class="py-3 px-4 text-center"><?= e($row['jumlah_peserta']) ?></td>
                        <td class="py-3 px-4 text-center"><?= e($row['jumlah_pendamping'] ?? '0') ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if (!empty($row['file_surat'])): ?>
                                <button onclick="previewSurat(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition" title="Preview & Cetak Surat">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </button>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <?php
                            $statusClass = 'bg-gray-100 text-gray-700';
                            if ($row['status'] === 'pending') $statusClass = 'bg-yellow-100 text-yellow-700';
                            elseif ($row['status'] === 'disetujui') $statusClass = 'bg-emerald-100 text-emerald-700';
                            elseif ($row['status'] === 'ditolak') $statusClass = 'bg-red-100 text-red-700';
                            elseif ($row['status'] === 'selesai') $statusClass = 'bg-blue-100 text-blue-700';
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>"><?= e(ucfirst($row['status'])) ?></span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <button onclick="approveIzin(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition" title="Setujui">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <button onclick="rejectIzin(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Tolak">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                <?php endif; ?>
                                <?php if ($row['status'] === 'disetujui'): ?>
                                    <button onclick="selesaiIzin(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Selesai">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                <?php endif; ?>
                                <a href="cetak_izin.php?id=<?= e($row['id']) ?>" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition" title="Cetak">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <button onclick="deleteIzin(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($daftar)): ?>
                    <tr><td colspan="13" class="py-8 text-center text-gray-500">Belum ada data izin.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah/Edit Izin -->
    <div id="izinModal" class="modal-dashboard modal-dashboard-lg hidden">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Tambah Izin</h3>
                    <button onclick="closeModal('izinModal')" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                </div>
                <form id="izinForm">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
                    <input type="hidden" name="id" id="izin_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Peminjam <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_peminjam" id="izin_nama" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organisasi</label>
                            <input type="text" name="organisasi" id="izin_organisasi" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                            <input type="text" name="telepon" id="izin_telepon" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="izin_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="izin_tgl_mulai" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="izin_tgl_selesai" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Peserta <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_peserta" id="izin_jumlah" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan</label>
                        <textarea name="keperluan" id="izin_keperluan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal('izinModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow hover:bg-emerald-700 transition-colors">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Izin';
    document.getElementById('izinForm').reset();
    document.querySelector('input[name="action"]').value = 'add';
    openModal('izinModal');
}

function openEditModal(data) {
    document.getElementById('modalTitle').textContent = 'Edit Izin';
    document.querySelector('input[name="action"]').value = 'edit';
    document.getElementById('izin_id').value = data.id;
    document.getElementById('izin_nama').value = data.nama_peminjam;
    document.getElementById('izin_organisasi').value = data.organisasi || '';
    document.getElementById('izin_telepon').value = data.telepon || '';
    document.getElementById('izin_email').value = data.email || '';
    document.getElementById('izin_tgl_mulai').value = data.tanggal_mulai;
    document.getElementById('izin_tgl_selesai').value = data.tanggal_selesai;
    document.getElementById('izin_jumlah').value = data.jumlah_peserta;
    document.getElementById('izin_keperluan').value = data.keperluan || '';
    openModal('izinModal');
}

function approveIzin(id) {
    Swal.fire({
        title: 'Setujui Izin?',
        input: 'text',
        inputLabel: 'Catatan (opsional)',
        inputPlaceholder: 'Masukkan catatan...',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        confirmButtonText: 'Ya, Setujui!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'approve');
            formData.append('id', id);
            formData.append('catatan_admin', result.value || '');
            formData.append('csrf_token', '<?= e(generateCSRFToken()) ?>');

            fetch('../proses/izin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.wa_url) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            showCancelButton: true,
                            confirmButtonColor: '#25D366',
                            confirmButtonText: 'Notifikasi Pemohon',
                            cancelButtonText: 'Tutup'
                        }).then((r) => {
                            if (r.isConfirmed) window.open(data.wa_url, '_blank');
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Kesalahan koneksi.' }));
        }
    });
}

function rejectIzin(id) {
    Swal.fire({
        title: 'Tolak Izin?',
        input: 'text',
        inputLabel: 'Alasan penolakan',
        inputPlaceholder: 'Masukkan alasan...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Tolak!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'reject');
            formData.append('id', id);
            formData.append('catatan_admin', result.value || '');
            formData.append('csrf_token', '<?= e(generateCSRFToken()) ?>');

            fetch('../proses/izin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.wa_url) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            showCancelButton: true,
                            confirmButtonColor: '#25D366',
                            confirmButtonText: 'Notifikasi Pemohon',
                            cancelButtonText: 'Tutup'
                        }).then((r) => {
                            if (r.isConfirmed) window.open(data.wa_url, '_blank');
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Kesalahan koneksi.' }));
        }
    });
}

function selesaiIzin(id) {
    Swal.fire({
        title: 'Tandai Selesai?',
        text: 'Izin akan ditandai selesai.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        confirmButtonText: 'Ya, Selesai!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'selesai');
            formData.append('id', id);
            formData.append('csrf_token', '<?= e(generateCSRFToken()) ?>');

            fetch('../proses/izin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Kesalahan koneksi.' }));
        }
    });
}

function deleteIzin(id) {
    Swal.fire({
        title: 'Hapus Izin?',
        text: 'Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            formData.append('csrf_token', '<?= e(generateCSRFToken()) ?>');

            fetch('../proses/izin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Kesalahan koneksi.' }));
        }
    });
}

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

document.getElementById('izinForm').addEventListener('submit', function(e) {
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

<?php include __DIR__ . '/footer.php'; ?>
