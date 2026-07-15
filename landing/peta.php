<?php
require_once __DIR__ . '/../config/koneksi.php';
$q = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
$profil = mysqli_fetch_assoc($q);
$lat = $profil['latitude'] ?? '-6.5453';
$lng = $profil['longitude'] ?? '110.9543';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Lokasi - Buper Jepara</title>
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
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/navbar.php'; ?>

<section class="py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-brown-800 mb-2">Peta Lokasi</h1>
            <div class="w-20 h-1 bg-gradient-to-r from-brown-700 to-emerald-500 mx-auto rounded"></div>
            <p class="text-gray-600 mt-4"><?= e($profil['alamat'] ?? '') ?></p>
        </div>

        <div class="rounded-2xl overflow-hidden shadow-lg border border-gray-200 h-[500px]">
            <iframe
                width="100%"
                height="100%"
                style="border:0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.123456789!2d<?= e($lng) ?>!3d<?= e($lat) ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMzInNDMuMSJTIDExMMKwNTcnMTUuNSJF!5e0!3m2!1sid!2sid!4v1234567890!5m2!1sid!2sid">
            </iframe>
        </div>

        <div class="mt-8 grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-geo-alt text-xl"></i>
                </div>
                <h3 class="font-bold text-brown-800">Alamat</h3>
                <p class="text-gray-600 text-sm mt-1"><?= e($profil['alamat'] ?? '-') ?></p>
            </div>
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-telephone text-xl"></i>
                </div>
                <h3 class="font-bold text-brown-800">Telepon</h3>
                <p class="text-gray-600 text-sm mt-1"><?= e($profil['telepon'] ?? '-') ?></p>
            </div>
            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-envelope text-xl"></i>
                </div>
                <h3 class="font-bold text-brown-800">Email</h3>
                <p class="text-gray-600 text-sm mt-1"><?= e($profil['email'] ?? '-') ?></p>
            </div>
        </div>
    </div>
</section>

<footer class="bg-brown-800 text-white py-8 text-center text-sm">
    &copy; <?= date('Y') ?> <?= e($profil['nama_buper'] ?? 'Buper Jepara') ?>. All rights reserved.
</footer>
</body>
</html>
