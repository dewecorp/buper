<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');

$title = "Data Profil";
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';

// Fetch profil data (assume single row with id=1)
$q = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
$profil = mysqli_fetch_assoc($q);
if (!$profil) {
    // Create default if not exists
    mysqli_query($conn, "INSERT INTO profil (nama_buper) VALUES ('Bumi Perkemahan Kwartir Cabang Jepara')");
    $q = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
    $profil = mysqli_fetch_assoc($q);
}
?>

<main class="flex-1 p-6 bg-gray-100 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Data Profil Buper</h1>
            <p class="text-sm text-gray-500"><?= isAdmin() ? 'Kelola informasi profil Bumi Perkemahan.' : 'Informasi profil Bumi Perkemahan.' ?></p>
        </div>
        <?php if (isAdmin()): ?>
        <button onclick="openModal('editModal')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg shadow hover:bg-emerald-700 transition-colors">
            Edit Profil
        </button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Nama Buper</p>
                <p class="text-lg font-semibold text-gray-900"><?= e($profil['nama_buper'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Email</p>
                <p class="text-lg font-semibold text-gray-900"><?= e($profil['email'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Telepon</p>
                <p class="text-lg font-semibold text-gray-900"><?= e($profil['telepon'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Alamat</p>
                <p class="text-lg font-semibold text-gray-900"><?= e($profil['alamat'] ?? '-') ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Deskripsi</h3>
        <p class="text-gray-700 leading-relaxed"><?= e($profil['deskripsi'] ?? '-') ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h4 class="text-sm font-semibold text-emerald-700 uppercase tracking-wide mb-2">Sejarah</h4>
            <p class="text-gray-700 text-sm leading-relaxed"><?= e($profil['sejarah'] ?? '-') ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h4 class="text-sm font-semibold text-purple-700 uppercase tracking-wide mb-2">Visi</h4>
            <p class="text-gray-700 text-sm leading-relaxed"><?= e($profil['visi'] ?? '-') ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h4 class="text-sm font-semibold text-brown-600 uppercase tracking-wide mb-2">Misi</h4>
            <p class="text-gray-700 text-sm leading-relaxed"><?= e($profil['misi'] ?? '-') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Foto</h4>
            <?php if (!empty($profil['foto'])): ?>
                <img src="../<?= e($profil['foto']) ?>" alt="Foto Buper" class="w-32 h-32 object-cover rounded-lg">
            <?php else: ?>
                <p class="text-gray-400 italic">Belum ada foto</p>
            <?php endif; ?>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Lokasi</h4>
            <p class="text-sm text-gray-700">Latitude: <span class="font-mono"><?= e($profil['latitude'] ?? '-') ?></span></p>
            <p class="text-sm text-gray-700">Longitude: <span class="font-mono"><?= e($profil['longitude'] ?? '-') ?></span></p>
        </div>
    </div>

    <!-- Modal Edit Profil -->
    <div id="editModal" class="modal-dashboard modal-dashboard-lg hidden">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Edit Profil</h3>
                    <button onclick="closeModal('editModal')" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                </div>
                <form id="editProfilForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
                    <input type="hidden" name="id" value="<?= e($profil['id'] ?? 1) ?>">
                    <input type="hidden" name="foto_lama" value="<?= e($profil['foto'] ?? '') ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Buper <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_buper" value="<?= e($profil['nama_buper'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="<?= e($profil['email'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                            <input type="text" name="telepon" value="<?= e($profil['telepon'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
                            <input type="file" name="foto" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"><?= e($profil['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sejarah</label>
                        <textarea name="sejarah" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"><?= e($profil['sejarah'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Visi</label>
                        <textarea name="visi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"><?= e($profil['visi'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Misi</label>
                        <textarea name="misi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"><?= e($profil['misi'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="alamat" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"><?= e($profil['alamat'] ?? '') ?></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                            <input type="text" name="latitude" value="<?= e($profil['latitude'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                            <input type="text" name="longitude" value="<?= e($profil['longitude'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
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

document.getElementById('editProfilForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('../proses/profil.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message,
                confirmButtonColor: '#047857'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan koneksi.',
            confirmButtonColor: '#047857'
        });
    });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
