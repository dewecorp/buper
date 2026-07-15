<?php
require_once __DIR__ . '/config/koneksi.php';

// Fetch profil data
$q = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
$profil = mysqli_fetch_assoc($q);

// Fetch pengaturan untuk tentangSingkat
$q_set = mysqli_query($conn, "SELECT nilai FROM pengaturan WHERE nama_pengaturan='tentangSingkat'");
$r_set = mysqli_fetch_assoc($q_set);
$tentangSingkat = $r_set['nilai'] ?? 'Bumi Perkemahan Kwartir Cabang Jepara menyediakan fasilitas perkemahan terbaik.';
$namaWebsiteLanding = getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';

// Fetch fasilitas
$q_fasilitas = mysqli_query($conn, "SELECT * FROM fasilitas WHERE status='tersedia' ORDER BY nama_fasilitas ASC");
$fasilitas_list = [];
while ($row = mysqli_fetch_assoc($q_fasilitas)) $fasilitas_list[] = $row;

// Fetch pengelola
$q_pengelola = mysqli_query($conn, "SELECT * FROM pengelola WHERE status='aktif' ORDER BY urutan ASC LIMIT 6");
$pengelola_list = [];
while ($row = mysqli_fetch_assoc($q_pengelola)) $pengelola_list[] = $row;

// Fetch biaya
$q_biaya = mysqli_query($conn, "SELECT * FROM biaya LIMIT 4");
$biaya_list = [];
while ($row = mysqli_fetch_assoc($q_biaya)) $biaya_list[] = $row;

$base = '/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($namaWebsiteLanding) ?></title>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <?php $logoLanding = getPengaturan($conn, 'logo'); ?>
    <?php if (!empty($logoLanding)): ?>
    <link rel="icon" href="<?= e($logoLanding) ?>">
    <?php endif; ?>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

<!-- Navbar -->
<nav class="sticky top-0 z-50 bg-purple-50/80 backdrop-blur-md shadow-sm border border-purple-200/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <a href="index.php" class="flex items-center gap-2">
                    <?php if (!empty($logoLanding)): ?>
                        <img src="<?= e($logoLanding) ?>" alt="Logo" class="w-10 h-10 object-contain">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-purple-700 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-md">BU</div>
                    <?php endif; ?>
                    <span class="text-lg font-bold text-purple-800"><?= e($namaWebsiteLanding) ?></span>
                </a>
            </div>
            <div class="hidden md:flex items-center space-x-1 text-sm font-medium">
                <a href="#profil" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-brown-100 transition">Profil</a>
                <a href="landing/pengelola.php" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-brown-100 transition">Pengelola</a>
                <a href="#fasilitas" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-brown-100 transition">Fasilitas</a>
                <div class="relative group">
                    <a href="#penggunaan" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-purple-100 transition">Penggunaan</a>
                    <div class="absolute left-0 top-full hidden group-hover:block pt-2" style="min-width:200px">
                        <div class="bg-white/80 backdrop-blur-md border border-purple-200/50 rounded-lg shadow-lg py-1">
                            <a href="landing/biaya.php" class="block px-4 py-2 text-sm text-brown-700 hover:bg-purple-100">Biaya Penggunaan</a>
                            <a href="landing/izin.php" class="block px-4 py-2 text-sm text-brown-700 hover:bg-purple-100">Izin Penggunaan</a>
                            <a href="landing/data_ajuan.php" class="block px-4 py-2 text-sm text-brown-700 hover:bg-purple-100">Data Ajuan</a>
                        </div>
                    </div>
                </div>
                <a href="#peta" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-brown-100 transition">Peta Lokasi</a>
                <a href="auth/login.php" target="_blank" class="px-4 py-2 bg-purple-700 text-white rounded-lg hover:bg-purple-600 transition shadow-md ml-2">Login</a>
            </div>
            <button class="md:hidden p-2" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden border-t border-purple-200 bg-purple-50/80 pb-4">
        <a href="#profil" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Profil</a>
        <a href="landing/pengelola.php" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Pengelola</a>
        <a href="#fasilitas" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Fasilitas</a>
        <a href="#penggunaan" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Penggunaan</a>
        <a href="landing/data_ajuan.php" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Data Ajuan</a>
        <a href="#peta" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Peta Lokasi</a>
        <a href="auth/login.php" target="_blank" class="block mx-4 mt-2 py-2 bg-purple-700 text-white text-center rounded-lg">Login</a>
    </div>
</nav>

<!-- Hero Section -->
<section class="relative bg-brown-800 text-white overflow-hidden">
    <?php if (!empty($profil['foto'])): ?>
    <div class="absolute inset-0">
        <img src="<?= e($profil['foto']) ?>" alt="Hero" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-brown-900/80 to-brown-800/70"></div>
    </div>
    <?php endif; ?>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-screen flex flex-col justify-center text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight drop-shadow-lg">
            <?= e($profil['nama_buper'] ?? 'Bumi Perkemahan Kwartir Cabang Jepara') ?>
        </h1>
        <p class="text-lg text-emerald-300 max-w-3xl mx-auto mb-8 leading-relaxed drop-shadow">
            <?= e($profil['deskripsi'] ?? 'Tempat perkemahan terbaik di Jepara untuk kegiatan Pramuka dan rekreasi alam.') ?>
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="#fasilitas" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg shadow-lg transition-colors">Lihat Fasilitas</a>
            <a href="landing/izin.php" class="px-8 py-3 bg-purple-700 hover:bg-purple-600 text-white font-semibold rounded-lg shadow-lg transition-colors">Ajukan Izin</a>
        </div>
    </div>
</section>

<!-- Profil Section -->
<section id="profil" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-brown-800 mb-2">Tentang Kami</h2>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
        </div>
        <div class="space-y-6">
            <div class="bg-gray-100 rounded-2xl p-8">
                <h3 class="text-xl font-bold text-brown-700 mb-4">Sejarah</h3>
                <p class="text-gray-600 leading-relaxed text-justify"><?= e($profil['sejarah'] ?? '') ?></p>
            </div>
            <div class="bg-gray-100 rounded-2xl p-8">
                <h3 class="text-xl font-bold text-emerald-700 mb-3">Visi</h3>
                <p class="text-gray-600 leading-relaxed text-justify"><?= e($profil['visi'] ?? '') ?></p>
            </div>
            <div class="bg-gray-100 rounded-2xl p-8">
                <h3 class="text-xl font-bold text-purple-700 mb-3">Misi</h3>
                <p class="text-gray-600 leading-relaxed text-justify"><?= e($profil['misi'] ?? '') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Pengelola Section -->
<section id="pengelola" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-brown-800 mb-2">Susunan Pengelola</h2>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($pengelola_list as $p): ?>
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow">
                <div class="w-20 h-20 rounded-full mx-auto mb-4 bg-brown-700 flex items-center justify-center text-white text-xl font-bold overflow-hidden">
                    <?php if (!empty($p['foto'])): ?>
                        <img src="<?= e($p['foto']) ?>" alt="Foto" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="bi bi-person-fill text-3xl"></i>
                    <?php endif; ?>
                </div>
                <h3 class="text-lg font-bold text-brown-800"><?= e($p['nama']) ?></h3>
                <p class="text-emerald-600 font-medium text-sm"><?= e($p['jabatan']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Fasilitas Section -->
<section id="fasilitas" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-brown-800 mb-2">Fasilitas Kami</h2>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($fasilitas_list as $f): ?>
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                <?php if (!empty($f['gambar'])): ?>
                <div class="h-48 overflow-hidden bg-emerald-50">
                    <img src="<?= e($f['gambar']) ?>" alt="Gambar" class="w-full h-full object-cover">
                </div>
                <?php endif; ?>
                <div class="p-6">
                <h3 class="text-lg font-bold text-brown-800 mb-2"><?= e($f['nama_fasilitas']) ?></h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-3"><?= e($f['deskripsi']) ?></p>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Tersedia</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Biaya Ringkas -->
<section class="bg-brown-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-2">Biaya Penggunaan</h2>
            <div class="w-20 h-1 bg-white/50 mx-auto rounded"></div>
        </div>
        <div class="grid md:grid-cols-4 gap-6">
            <?php foreach ($biaya_list as $b): ?>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition">
                <h3 class="text-lg font-bold mb-2"><?= e($b['nama_biaya']) ?></h3>
                <p class="text-2xl font-bold text-emerald-300 mb-2"><?= e(formatRupiah($b['harga'])) ?></p>
                <p class="text-sm text-white/80 mb-1"><?= e($b['satuan']) ?></p>
                <p class="text-xs text-white/60"><?= e($b['deskripsi']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <a href="landing/biaya.php" class="px-6 py-3 bg-white text-brown-800 font-semibold rounded-lg hover:bg-gray-100 transition shadow-lg">Lihat Semua Biaya</a>
        </div>
    </div>
</section>

<!-- Peta Lokasi -->
<section id="peta" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-brown-800 mb-2">Peta Lokasi</h2>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
            <p class="text-gray-600 mt-4"><?= e($profil['alamat'] ?? '') ?></p>
        </div>
        <div class="rounded-2xl overflow-hidden shadow-lg border border-gray-200 h-96">
            <?php
            $lat = $profil['latitude'] ?? '-6.5453';
            $lng = $profil['longitude'] ?? '110.9543';
            ?>
            <iframe
                width="100%"
                height="100%"
                style="border:0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.123456789!2d<?= e($lng) ?>!3d<?= e($lat) ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMzInNDMuMSJTIDExMMKwNTcnMTUuNSJF!5e0!3m2!1sid!2sid!4v1234567890!5m2!1sid!2sid">
            </iframe>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-brown-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-xl font-bold mb-4"><?= e($profil['nama_buper'] ?? 'Buper Jepara') ?></h3>
                <p class="text-brown-200 text-sm leading-relaxed">
                    <?= e($tentangSingkat) ?>
                </p>
            </div>
            <div>
                <h4 class="font-bold mb-4">Menu Cepat</h4>
                <ul class="space-y-2 text-sm text-brown-200">
                    <li><a href="#profil" class="hover:text-white transition">Profil</a></li>
                    <li><a href="landing/pengelola.php" class="hover:text-white transition">Pengelola</a></li>
                    <li><a href="#fasilitas" class="hover:text-white transition">Fasilitas</a></li>
                    <li><a href="landing/biaya.php" class="hover:text-white transition">Biaya Penggunaan</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Kontak</h4>
                <ul class="space-y-2 text-sm text-brown-200">
                    <li><i class="bi bi-telephone mr-2"></i><?= e($profil['telepon'] ?? '') ?></li>
                    <li><i class="bi bi-envelope mr-2"></i><?= e($profil['email'] ?? '') ?></li>
                    <li><i class="bi bi-geo-alt mr-2"></i><?= e($profil['alamat'] ?? '') ?></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-brown-600 mt-8 pt-8 text-center text-sm text-brown-300">
            &copy; <?= date('Y') ?> <?= e($profil['nama_buper'] ?? 'Buper Jepara') ?>. All rights reserved.
        </div>
    </div>
</footer>

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
