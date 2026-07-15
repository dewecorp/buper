<?php
require_once __DIR__ . '/../config/koneksi.php';
$csrf = generateCSRFToken();

$q = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
$profil = mysqli_fetch_assoc($q);
$logoIzin = getPengaturan($conn, 'logo');
$namaWebsiteIzin = getPengaturan($conn, 'nama_website') ?: 'Buper';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($namaWebsiteIzin) ?> | Izin Penggunaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <?php if (!empty($logoIzin)): ?>
    <link rel="icon" href="../<?= e($logoIzin) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/navbar.php'; ?>

<section class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-brown-800 mb-2">Ajukan Izin Penggunaan</h1>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
        </div>

        <!-- Info Section -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 mb-8">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-sm text-gray-700 space-y-2">
                    <p class="font-semibold text-emerald-800">Salam Pramuka,</p>
                    <p>Apabila kakak-kakak berminat menggunakan <strong><?= e($namaWebsiteIzin) ?> Kwarcab Jepara</strong> untuk kegiatan kepramukaan atau kegiatan lainnya, silahkan mengajukan permohonan penggunaan Buper kepada Ketua Kwarcab Jepara. Dengan ketentuan sebagai berikut:</p>
                    <ol class="list-decimal list-inside space-y-1 text-gray-600">
                        <li>Isilah dengan lengkap form pengajuan di bawah.</li>
                        <li>Unduh template <a href="../Form-Ijin-Pemakaian-Buper.docx" class="text-emerald-700 font-semibold underline hover:text-emerald-800" download>Surat Permohonan Penggunaan Buper</a>.</li>
                        <li>Buat surat permohonan sesuai template.</li>
                        <li>Kirimkan surat permohonan dalam form ini.</li>
                        <li>Format surat dalam bentuk <strong>PDF</strong>. Ukuran maksimal <strong>2 MB</strong>.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            <form id="izinForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <?= csrfInput() ?>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_peminjam" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organisasi/Instansi</label>
                        <input type="text" name="organisasi" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="telepon" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. WA <span class="text-red-500">*</span></label>
                        <input type="text" name="nowa" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kegiatan" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab <span class="text-red-500">*</span></label>
                        <input type="text" name="penanggung_jawab" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bentuk Kegiatan <span class="text-red-500">*</span></label>
                        <select name="bentuk_kegiatan" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                            <option value="">-- Pilih --</option>
                            <option value="perkemahan">Perkemahan</option>
                            <option value="outbond">Out Bond</option>
                            <option value="outdoor_project">Outdoor Project</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_selesai" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Peserta <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah_peserta" min="1" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pendamping</label>
                        <input type="number" name="jumlah_pendamping" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan/Rencana Kegiatan</label>
                    <textarea name="keperluan" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="Jelaskan rencana kegiatan Anda..."></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Surat Permohonan <span class="text-red-500">*</span> <span class="text-xs text-gray-400 font-normal">(format PDF, maks 2 MB)</span></label>
                    <input type="file" name="file_surat" accept=".pdf,application/pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                </div>

                <button type="submit" class="w-full py-3 bg-purple-700 text-white font-semibold rounded-lg shadow-lg hover:bg-purple-600 transition">
                    <i class="bi bi-send mr-2"></i>Ajukan Izin
                </button>
            </form>
        </div>
    </div>
</section>

<footer class="bg-brown-800 text-white py-8 text-center text-sm">
    &copy; <?= date('Y') ?> <?= e($profil['nama_buper'] ?? 'Buper Jepara') ?>. All rights reserved.
</footer>

<script>
document.getElementById('izinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('../proses/izin.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message + ' Menunggu persetujuan pengelola.',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = 'data_ajuan.php';
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#047857' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.', confirmButtonColor: '#047857' }));
});
</script>
<!-- Back to Top -->
<button onclick="window.scrollTo({top:0,behavior:'smooth'})" id="backToTop" class="fixed bottom-6 right-6 z-50 w-10 h-10 rounded-full bg-purple-700 text-white shadow-lg hover:bg-purple-600 transition-opacity opacity-0 invisible flex items-center justify-center" style="transition: opacity 0.3s, visibility 0.3s">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
</button>
<script>
window.addEventListener('scroll', function() {
    var btn = document.getElementById('backToTop');
    if (window.scrollY > 300) {
        btn.classList.remove('opacity-0', 'invisible');
    } else {
        btn.classList.add('opacity-0', 'invisible');
    }
});
</script>
</body>
</html>
