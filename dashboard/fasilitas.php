<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');

$title = "Data Fasilitas";
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';

$q = mysqli_query($conn, "SELECT * FROM fasilitas ORDER BY nama_fasilitas ASC");
$daftar = [];
while ($row = mysqli_fetch_assoc($q)) $daftar[] = $row;
?>

<main class="flex-1 p-6 bg-gray-100 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Data Fasilitas</h1>
            <p class="text-sm text-gray-500">Kelola fasilitas Buper.</p>
        </div>
        <button onclick="openAddModal()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg shadow hover:bg-emerald-700 transition-colors">
            + Tambah Fasilitas
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm leading-normal">
                        <th class="py-3 px-4 text-left">No</th>
                        <th class="py-3 px-4 text-left">Gambar</th>
                        <th class="py-3 px-4 text-left">Nama Fasilitas</th>
                        <th class="py-3 px-4 text-left">Deskripsi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($daftar as $i => $row): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium"><?= e($i + 1) ?></td>
                        <td class="py-3 px-4">
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center">
                                <?php if (!empty($row['gambar'])): ?>
                                    <img src="../<?= e($row['gambar']) ?>" alt="" class="w-full h-full object-cover" loading="lazy">
                                <?php else: ?>
                                    <span class="text-xs font-bold text-gray-500">No Img</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-medium"><?= e($row['nama_fasilitas']) ?></td>
                        <td class="py-3 px-4 max-w-xs truncate"><?= e($row['deskripsi']) ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($row['status'] === 'tersedia'): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Tersedia</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Tidak Tersedia</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick='openEditModal(<?= json_encode($row) ?>)' class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button onclick="deleteFasilitas(<?= e($row['id']) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($daftar)): ?>
                    <tr><td colspan="6" class="py-8 text-center text-gray-500">Belum ada data fasilitas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah/Edit Fasilitas -->
    <div id="fasilitasModal" class="modal-dashboard modal-dashboard-lg hidden">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Tambah Fasilitas</h3>
                    <button onclick="closeModal('fasilitasModal')" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                </div>
                <form id="fasilitasForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
                    <input type="hidden" name="id" id="fasilitas_id">
                    <input type="hidden" name="gambar_lama" id="fasilitas_gambar_lama" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Fasilitas <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_fasilitas" id="fasilitas_nama" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="fasilitas_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                                <option value="tersedia">Tersedia</option>
                                <option value="tidak_tersedia">Tidak Tersedia</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" id="fasilitas_deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                        <input type="file" name="gambar" accept="image/*" id="fasilitas_gambar_file" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon (CSS class)</label>
                        <input type="text" name="icon" id="fasilitas_icon" placeholder="Contoh: bi-building, bi-tent" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal('fasilitasModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
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
    document.getElementById('modalTitle').textContent = 'Tambah Fasilitas';
    document.getElementById('fasilitasForm').reset();
    document.querySelector('input[name="action"]').value = 'add';
    openModal('fasilitasModal');
}

function openEditModal(data) {
    document.getElementById('modalTitle').textContent = 'Edit Fasilitas';
    document.querySelector('input[name="action"]').value = 'edit';
    document.getElementById('fasilitas_id').value = data.id;
    document.getElementById('fasilitas_gambar_lama').value = data.gambar || '';
    document.getElementById('fasilitas_nama').value = data.nama_fasilitas;
    document.getElementById('fasilitas_deskripsi').value = data.deskripsi || '';
    document.getElementById('fasilitas_icon').value = data.icon || '';
    document.getElementById('fasilitas_status').value = data.status;
    openModal('fasilitasModal');
}

function deleteFasilitas(id) {
    Swal.fire({
        title: 'Hapus Fasilitas?',
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

            fetch('../proses/fasilitas.php', { method: 'POST', body: formData })
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

document.getElementById('fasilitasForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('../proses/fasilitas.php', { method: 'POST', body: formData })
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
