<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');
if (!isAdmin()) { redirect('../dashboard/'); }

$title = "Data Pengaturan";
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';

$q = mysqli_query($conn, "SELECT * FROM pengaturan ORDER BY id ASC");
$settings = [];
while ($row = mysqli_fetch_assoc($q)) $settings[] = $row;

// Cari nilai pengaturan
$logoVal = $logoPramukaVal = $logoWosmVal = '';
foreach ($settings as $s) {
    if ($s['nama_pengaturan'] === 'logo') $logoVal = $s['nilai'];
    if ($s['nama_pengaturan'] === 'logo_pramuka') $logoPramukaVal = $s['nilai'];
    if ($s['nama_pengaturan'] === 'logo_wosm') $logoWosmVal = $s['nilai'];
}
?>

<main class="flex-1 p-6 bg-gray-100 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Data Pengaturan</h1>
            <p class="text-sm text-gray-500">Kelola pengaturan umum Buper.</p>
        </div>
        <button onclick="saveSettings()" class="bg-emerald-600 text-white px-4 py-2 rounded-lg shadow hover:bg-emerald-700 transition-colors">
            Simpan Semua
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
        <form id="pengaturanForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">

            <!-- Upload Logo Website -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo Website</label>
                <div class="flex items-center gap-6">
                    <div id="logoPreview" class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50">
                        <?php if (!empty($logoVal) && file_exists(__DIR__ . '/../' . $logoVal)): ?>
                            <img src="../<?= e($logoVal) ?>" alt="Logo" class="w-full h-full object-contain">
                        <?php else: ?>
                            <span class="text-xs text-gray-400">No Logo</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <input type="file" name="logo_file" accept="image/png,image/jpeg,image/gif,image/webp" id="logoInput" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Format: PNG, JPG, GIF, WEBP. Maks 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Upload Logo Pramuka -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo Pramuka (Gerakan Pramuka)</label>
                <div class="flex items-center gap-6">
                    <div id="logoPramukaPreview" class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50">
                        <?php if (!empty($logoPramukaVal) && file_exists(__DIR__ . '/../' . $logoPramukaVal)): ?>
                            <img src="../<?= e($logoPramukaVal) ?>" alt="Logo Pramuka" class="w-full h-full object-contain">
                        <?php else: ?>
                            <span class="text-xs text-gray-400">No Logo</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <input type="file" name="logo_pramuka_file" accept="image/png,image/jpeg,image/gif,image/webp" id="logoPramukaInput" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Format: PNG, JPG, GIF, WEBP. Maks 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Upload Logo WOSM -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo WOSM</label>
                <div class="flex items-center gap-6">
                    <div id="logoWosmPreview" class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50">
                        <?php if (!empty($logoWosmVal) && file_exists(__DIR__ . '/../' . $logoWosmVal)): ?>
                            <img src="../<?= e($logoWosmVal) ?>" alt="Logo WOSM" class="w-full h-full object-contain">
                        <?php else: ?>
                            <span class="text-xs text-gray-400">No Logo</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <input type="file" name="logo_wosm_file" accept="image/png,image/jpeg,image/gif,image/webp" id="logoWosmInput" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Format: PNG, JPG, GIF, WEBP. Maks 2MB.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($settings as $s): ?>
                    <?php if (in_array($s['nama_pengaturan'], ['logo','logo_pramuka','logo_wosm'])) continue; ?>
                    <div class="<?= $s['nama_pengaturan'] === 'lokasi_map' ? 'md:col-span-2' : '' ?>">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= e($s['keterangan'] ?? $s['nama_pengaturan']) ?></label>
                        <?php if ($s['nama_pengaturan'] === 'lokasi_map'): ?>
                            <textarea name="settings[<?= e($s['nama_pengaturan']) ?>]" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none font-mono text-xs"><?= e($s['nilai']) ?></textarea>
                        <?php else: ?>
                            <input type="text" name="settings[<?= e($s['nama_pengaturan']) ?>]" value="<?= e($s['nilai']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</main>

<script>
function previewLogo(inputId, previewId) {
    document.getElementById(inputId)?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '<img src="' + ev.target.result + '" alt="Logo" class="w-full h-full object-contain">';
        };
        reader.readAsDataURL(file);
    });
}
previewLogo('logoInput', 'logoPreview');
previewLogo('logoPramukaInput', 'logoPramukaPreview');
previewLogo('logoWosmInput', 'logoWosmPreview');

function saveSettings() {
    const form = document.getElementById('pengaturanForm');
    const formData = new FormData(form);

    fetch('../proses/pengaturan.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#047857' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.', confirmButtonColor: '#047857' }));
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
