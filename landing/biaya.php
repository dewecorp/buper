<?php
require_once __DIR__ . '/../config/koneksi.php';

$q = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
$profil = mysqli_fetch_assoc($q);

$q_biaya = mysqli_query($conn, "SELECT * FROM biaya ORDER BY id ASC");
$biaya_list = [];
while ($row = mysqli_fetch_assoc($q_biaya)) $biaya_list[] = $row;
$logoBiaya = getPengaturan($conn, 'logo');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biaya Penggunaan - Buper Jepara</title>
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
    <?php if (!empty($logoBiaya)): ?>
    <link rel="icon" href="../<?= e($logoBiaya) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/navbar.php'; ?>

<section class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-brown-800 mb-2">Biaya Penggunaan Buper</h1>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
            <p class="text-gray-600 mt-4">Berikut adalah daftar biaya penggunaan Bumi Perkemahan.</p>
        </div>

        <div class="space-y-4">
            <?php foreach ($biaya_list as $i => $b): ?>
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-brown-800 mb-1"><?= e($b['nama_biaya']) ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?= e($b['deskripsi']) ?></p>
                        <p class="text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i><?= e($b['keterangan']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-emerald-700"><?= e(formatRupiah($b['harga'])) ?></p>
                        <p class="text-sm text-gray-500"><?= e($b['satuan']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 text-center">
            <a href="izin.php" class="inline-block px-8 py-3 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition shadow-lg">Ajukan Izin Sekarang</a>
        </div>
    </div>
</section>

<footer class="bg-brown-800 text-white py-8 text-center text-sm">
    &copy; <?= date('Y') ?> <?= e($profil['nama_buper'] ?? 'Buper Jepara') ?>. All rights reserved.
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
