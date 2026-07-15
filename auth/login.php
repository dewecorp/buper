<?php
require_once __DIR__ . '/../config/koneksi.php';
if (isLogin()) {
    redirect('../dashboard/');
}

$csrfToken = generateCSRFToken();
$namaWebsiteLogin = getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(getPengaturan($conn, 'nama_website') ?: 'Buper Jepara') ?> | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .login-bg {
            background-color: #3e2723;
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center login-bg p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white tracking-wide"><?= e($namaWebsiteLogin) ?></h1>
            <p class="text-emerald-200 text-sm mt-1">Sistem Reservasi Bumi Perkemahan</p>
        </div>
        <div class="card-glass rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Masuk</h2>
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
        <p class="text-center text-gray-300 text-xs mt-6">&copy; <?= date('Y') ?> Buper Jepara</p>
    </div>
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

