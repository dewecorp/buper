<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../config/koneksi.php';
}
if (!isLogin()) {
    redirect('../auth/login.php');
}
$timeout = 7200;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php?expired=1');
    exit;
}
$_SESSION['last_activity'] = time();
$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama_lengkap'] ?? '';
$foto = $_SESSION['foto'] ?? '';
$initials = '';
if (!empty($nama)) {
    $parts = explode(' ', $nama);
    foreach ($parts as $p) $initials .= strtoupper($p[0] ?? '');
    $initials = substr($initials, 0, 2);
}
$logoDashboard = getPengaturan($conn, 'logo');
$namaWebsiteHeader = getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= e($namaWebsiteHeader) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <?php if (!empty($logoDashboard)): ?>
    <link rel="icon" href="../<?= e($logoDashboard) ?>">
    <?php endif; ?>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        * { box-sizing: border-box; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brown: { 50:'#efebe9',100:'#d7ccc8',200:'#bcaaa4',300:'#a1887f',400:'#8d6e63',500:'#795548',600:'#5d4037',700:'#4e342e',800:'#3e2723',900:'#2c1a12' },
                        emerald: { 50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b' },
                        purple: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-screen bg-gray-50 flex flex-col overflow-hidden">
<!-- Top Navbar -->
<nav class="sticky top-0 z-50 bg-purple-900 text-white shadow-md px-5 py-2.5 flex items-center justify-between flex-shrink-0">
    <div class="flex items-center gap-2">
        <a href="index.php" class="flex items-center gap-2">
            <?php if (!empty($logoDashboard)): ?>
                <img src="../<?= e($logoDashboard) ?>" alt="Logo" class="w-7 h-7 object-contain">
            <?php else: ?>
                <div class="w-7 h-7 bg-emerald-600 rounded-lg flex items-center justify-center text-white font-bold text-[11px] shadow-md">BU</div>
            <?php endif; ?>
            <span class="text-sm font-bold text-white"><?= e($namaWebsiteHeader) ?></span>
        </a>
        <span class="text-emerald-300 mx-1.5 text-xs">|</span>
        <span class="text-xs text-purple-200">Dashboard</span>
    </div>
    <div class="flex items-center gap-3">
        <span id="clockDisplay" class="text-xs text-purple-200 hidden md:inline"></span>
        <a href="../index.php" target="_blank" class="text-xs text-purple-200 hover:text-emerald-300 transition flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Lihat Website
        </a>
        <!-- Notifikasi -->
        <div class="relative" id="notifDropdown">
            <button onclick="toggleNotif()" class="relative text-purple-200 hover:text-white transition p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 leading-none hidden">0</span>
            </button>
            <div id="notifMenu" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-50">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Notifikasi</h3>
                    <button onclick="markAllRead()" class="text-xs text-purple-600 hover:text-purple-800 font-medium transition">Tandai Semua Dibaca</button>
                </div>
                <div id="notifList" class="max-h-80 overflow-y-auto">
                    <div class="px-4 py-6 text-center text-sm text-gray-400">Memuat...</div>
                </div>
            </div>
        </div>
        <!-- User Dropdown -->
        <div class="relative" id="userDropdown">
            <button onclick="toggleDropdown()" class="flex items-center gap-2 text-xs text-purple-200 hover:text-white transition cursor-pointer">
                <span class="hidden sm:inline"><?= e($nama) ?></span>
                <div class="w-6 h-6 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-semibold ring-2 ring-emerald-400 flex-shrink-0"><?= e($initials) ?></div>
            </button>
            <div id="dropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 hidden z-50">
                <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-800 truncate"><?= e($nama) ?></p>
                    <p class="text-xs text-gray-500 capitalize"><?= e($role) ?></p>
                </div>
                <?php if ($role === 'admin'): ?>
                <button onclick="updateSistem()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition text-left">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Update Sistem
                </button>
                <hr class="border-gray-100">
                <?php endif; ?>
                <a href="../auth/logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="flex flex-1 overflow-hidden">

<script>
function updateClock() {
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const now = new Date();
    const d = days[now.getDay()];
    const dd = String(now.getDate()).padStart(2,'0');
    const mm = months[now.getMonth()];
    const yyyy = now.getFullYear();
    const hh = String(now.getHours()).padStart(2,'0');
    const mn = String(now.getMinutes()).padStart(2,'0');
    const ss = String(now.getSeconds()).padStart(2,'0');
    document.getElementById('clockDisplay').textContent = `${d}, ${dd} ${mm} ${yyyy} ${hh}:${mn}:${ss}`;
}
updateClock();
setInterval(updateClock, 1000);

function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const dd = document.getElementById('userDropdown');
    const menu = document.getElementById('dropdownMenu');
    if (!dd.contains(e.target)) {
        menu.classList.add('hidden');
    }
    const nd = document.getElementById('notifDropdown');
    const nm = document.getElementById('notifMenu');
    if (!nd.contains(e.target)) {
        nm.classList.add('hidden');
    }
});

// Notifikasi
function toggleNotif() {
    const m = document.getElementById('notifMenu');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) loadNotifikasi();
}
function loadNotifikasi() {
    fetch('../ajax/notifikasi.php?action=list')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const list = document.getElementById('notifList');
            if (!res.data.length) {
                list.innerHTML = '<div class="px-4 py-6 text-center text-sm text-gray-400">Belum ada notifikasi</div>';
                return;
            }
            list.innerHTML = res.data.map(n => {
                const tgl = formatWaktu(n.created_at);
                const bold = !n.dibaca ? 'font-bold' : 'font-normal';
                const bg = !n.dibaca ? 'bg-purple-50' : '';
                return '<div onclick="bukaNotif(' + n.id + ',' + n.id_izin + ')" class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer ' + bg + '">' +
                    '<p class="text-xs text-gray-800 ' + bold + '">' + escHtml(n.judul) + '</p>' +
                    '<p class="text-xs text-gray-500 mt-0.5 whitespace-pre-line">' + escHtml(n.pesan) + '</p>' +
                    '<p class="text-xs text-gray-400 mt-1">' + tgl + '</p>' +
                '</div>';
            }).join('');
            if (res.unread > 0) {
                document.getElementById('notifBadge').textContent = res.unread;
                document.getElementById('notifBadge').classList.remove('hidden');
            } else {
                document.getElementById('notifBadge').classList.add('hidden');
            }
        });
}
function bukaNotif(id, idIzin) {
    fetch('../ajax/notifikasi.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: 'action=read&id=' + id })
        .then(() => { window.location.href = 'izin_penggunaan.php'; });
}
function markAllRead() {
    fetch('../ajax/notifikasi.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: 'action=read_all' })
        .then(() => {
            document.getElementById('notifBadge').classList.add('hidden');
            document.querySelectorAll('#notifList > div').forEach(el => { el.classList.remove('bg-purple-50'); el.querySelector('p:first-child')?.classList.remove('font-bold'); el.querySelector('p:first-child')?.classList.add('font-normal'); });
            loadNotifikasi();
        });
}
function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
function formatWaktu(dt) {
    const d = new Date(dt.replace(' ', 'T'));
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const dd = String(d.getDate()).padStart(2,'0');
    const mm = months[d.getMonth()];
    const yyyy = d.getFullYear();
    const hh = String(d.getHours()).padStart(2,'0');
    const mn = String(d.getMinutes()).padStart(2,'0');
    return dd + ' ' + mm + ' ' + yyyy + ' ' + hh + ':' + mn;
}
// Auto refresh notif setiap 30 detik
setInterval(() => {
    fetch('../ajax/notifikasi.php?action=list')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            if (res.unread > 0) {
                document.getElementById('notifBadge').textContent = res.unread;
                document.getElementById('notifBadge').classList.remove('hidden');
            } else {
                document.getElementById('notifBadge').classList.add('hidden');
            }
        });
}, 30000);
</script>
    <!-- ====== SIDEBAR ====== -->
