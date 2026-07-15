<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');

$title = "Data Biaya Penggunaan";
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';

$q = mysqli_query($conn, "SELECT * FROM biaya ORDER BY id ASC");
$daftar = [];
while ($row = mysqli_fetch_assoc($q)) $daftar[] = $row;
?>

<main class="flex-1 p-6 bg-gray-100 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Data Biaya Penggunaan</h1>
            <p class="text-sm text-gray-500">Kelola biaya penggunaan Buper.</p>
        </div>
        <button onclick="openAddModal()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg shadow hover:bg-emerald-700 transition-colors">
            + Tambah Biaya
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm leading-normal">
                        <th class="py-3 px-4 text-left">No</th>
                        <th class="py-3 px-4 text-left">Nama Biaya</th>
                        <th class="py-3 px-4 text-left">Deskripsi</th>
                        <th class="py-3 px-4 text-right">Harga</th>
                        <th class="py-3 px-4 text-left">Satuan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($daftar as $i => $row): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium"><?= e($i + 1) ?></td>
                        <td class="py-3 px-4 font-medium"><?= e($row['nama_biaya']) ?></td>
                        <td class="py-3 px-4 max-w-xs truncate"><?= e($row['deskripsi']) ?></td>
                        <td class="py-3 px-4 text-right font-semibold text-emerald-700"><?= formatRupiah($row['harga']) ?></td>
                        <td class="py-3 px-4"><?= e($row['satuan']) ?></td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick='openEditModal(<?= json_encode($row) ?>)' class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button onclick="deleteBiaya(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($daftar)): ?>
                    <tr><td colspan="6" class="py-8 text-center text-gray-500">Belum ada data biaya.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah/Edit Biaya -->
    <div id="biayaModal" class="modal-dashboard modal-dashboard-lg hidden">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Tambah Biaya</h3>
                    <button onclick="closeModal('biayaModal')" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                </div>
                <form id="biayaForm">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
                    <input type="hidden" name="id" id="biaya_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Biaya <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_biaya" id="biaya_nama" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga <span class="text-red-500">*</span></label>
                            <input type="number" name="harga" id="biaya_harga" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                            <input type="text" name="satuan" id="biaya_satuan" placeholder="Contoh: per malam, per orang" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <input type="text" name="keterangan" id="biaya_keterangan" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" id="biaya_deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal('biayaModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
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
    document.getElementById('modalTitle').textContent = 'Tambah Biaya';
    document.getElementById('biayaForm').reset();
    document.querySelector('input[name="action"]').value = 'add';
    openModal('biayaModal');
}

function openEditModal(data) {
    document.getElementById('modalTitle').textContent = 'Edit Biaya';
    document.querySelector('input[name="action"]').value = 'edit';
    document.getElementById('biaya_id').value = data.id;
    document.getElementById('biaya_nama').value = data.nama_biaya;
    document.getElementById('biaya_harga').value = data.harga;
    document.getElementById('biaya_satuan').value = data.satuan;
    document.getElementById('biaya_keterangan').value = data.keterangan || '';
    document.getElementById('biaya_deskripsi').value = data.deskripsi || '';
    openModal('biayaModal');
}

function deleteBiaya(id) {
    Swal.fire({
        title: 'Hapus Biaya?',
        text: 'Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('../proses/biaya.php', { method: 'POST', body: formData })
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
        }
    });
}

document.getElementById('biayaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('../proses/biaya.php', { method: 'POST', body: formData })
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

<?php include __DIR__ . '/footer.php'; ?>
