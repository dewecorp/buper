<?php
require_once __DIR__ . '/../config/koneksi.php';
if (isLogin()) {
    redirect('../dashboard/');
}

$csrfToken = generateCSRFToken();
$namaWebsiteLogin = getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';
$logoLogin = getPengaturan($conn, 'logo');
$q = mysqli_query($conn, "SELECT * FROM profil WHERE id = 1");
$profil = mysqli_fetch_assoc($q);
$bgFoto = !empty($profil['foto']) ? '../' . $profil['foto'] : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(getPengaturan($conn, 'nama_website') ?: 'Buper Jepara') ?> | Login</title>
    <?php if (!empty($logoLogin)): ?>
    <link rel="icon" href="../<?= e($logoLogin) ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="h-screen overflow-hidden flex flex-col bg-brown-800 relative">
    <?php if (!empty($bgFoto)): ?>
    <div class="fixed inset-0">
        <img src="<?= e($bgFoto) ?>" alt="" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-brown-900/80 to-brown-800/70"></div>
    </div>
    <?php endif; ?>
    <div class="relative z-10 flex-1 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8 bg-black/50 rounded-xl px-6 py-4 backdrop-blur-sm">
                <h1 class="text-3xl font-bold text-white tracking-wide"><?= e($namaWebsiteLogin) ?></h1>
                <p class="text-emerald-200 text-sm mt-1">Sistem Reservasi Bumi Perkemahan</p>
            </div>
            <div class="card-glass rounded-2xl shadow-2xl p-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-6 text-center">Masukkan Username dan Password</h2>
                <form id="loginForm" method="post" action="proses_login.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="mb-5">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" id="username" name="username" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               placeholder="Masukkan username">
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                               placeholder="Masukkan password">
                    </div>
                    <button type="submit"
                            class="w-full py-3 px-4 bg-purple-700 text-white font-semibold rounded-lg hover:bg-purple-600 transition duration-200 shadow-lg">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
    <footer class="relative z-10 bg-brown-800/80 backdrop-blur-sm text-white py-2 text-center text-sm text-brown-300">
        &copy; <?= date('Y') ?> <?= e($profil['nama_buper'] ?? 'Buper Jepara') ?>. All rights reserved.
    </footer>
    <script>
        <?php if (isset($_GET['expired'])): ?>
        Swal.fire({
            icon: 'info',
            title: 'Sesi Berakhir',
            text: 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 2 jam. Silakan login kembali.',
            confirmButtonColor: '#6B21A8'
        });
        <?php endif; ?>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = form.querySelector('button[type="submit"]');
            const origText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Memproses...';

            const formData = new FormData(form);

            fetch('proses_login.php', {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect || '../dashboard/';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message,
                        confirmButtonColor: '#6B21A8'
                    });
                    btn.disabled = false;
                    btn.textContent = origText;
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan koneksi.',
                    confirmButtonColor: '#6B21A8'
                });
                btn.disabled = false;
                btn.textContent = origText;
            });
        });
    </script>
</body>
</html>

