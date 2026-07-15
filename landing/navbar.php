<?php
if (!isset($logoNavbar)) {
    $logoNavbar = \getPengaturan($conn, 'logo');
}
$namaWebsiteNavbar = \getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';
?>
<!-- Navbar landing (shared) -->
<nav class="sticky top-0 z-50 bg-purple-50/80 backdrop-blur-md shadow-sm border border-purple-200/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <a href="../index.php" class="flex items-center gap-2">
                    <?php if (!empty($logoNavbar)): ?>
                        <img src="../<?= e($logoNavbar) ?>" alt="Logo" class="w-10 h-10 object-contain">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-purple-700 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-md">BU</div>
                    <?php endif; ?>
                    <span class="text-lg font-bold text-purple-800"><?= e($namaWebsiteNavbar) ?></span>
                </a>
            </div>
            <div class="hidden md:flex items-center space-x-1 text-sm font-medium">
                <a href="../index.php#profil" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-purple-100 transition">Profil</a>
                <a href="pengelola.php" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-purple-100 transition">Pengelola</a>
                <a href="../index.php#fasilitas" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-purple-100 transition">Fasilitas</a>
                <div class="relative group">
                    <a href="../index.php#penggunaan" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-purple-100 transition">Penggunaan</a>
                    <div class="absolute left-0 top-full hidden group-hover:block pt-2" style="min-width:200px">
                        <div class="bg-white/80 backdrop-blur-md border border-purple-200/50 rounded-lg shadow-lg py-1">
                            <a href="biaya.php" class="block px-4 py-2 text-sm text-brown-700 hover:bg-purple-100">Biaya Penggunaan</a>
                            <a href="izin.php" class="block px-4 py-2 text-sm text-brown-700 hover:bg-purple-100">Izin Penggunaan</a>
                            <a href="data_ajuan.php" class="block px-4 py-2 text-sm text-brown-700 hover:bg-purple-100">Data Ajuan</a>
                        </div>
                    </div>
                </div>
                <a href="../index.php#peta" class="px-3 py-2 rounded-lg text-brown-700 hover:bg-purple-100 transition">Peta Lokasi</a>
                <a href="../auth/login.php" target="_blank" class="px-4 py-2 bg-purple-700 text-white rounded-lg hover:bg-purple-600 transition shadow-md ml-2">Login</a>
            </div>
            <button class="md:hidden p-2" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden border-t border-purple-200 bg-purple-50/80 pb-4">
        <a href="../index.php#profil" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Profil</a>
        <a href="pengelola.php" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Pengelola</a>
        <a href="../index.php#fasilitas" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Fasilitas</a>
        <a href="../index.php#penggunaan" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Penggunaan</a>
        <a href="data_ajuan.php" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Data Ajuan</a>
        <a href="../index.php#peta" class="block px-4 py-2 text-brown-700 hover:bg-purple-100">Peta Lokasi</a>
        <a href="../auth/login.php" target="_blank" class="block mx-4 mt-2 py-2 bg-purple-700 text-white text-center rounded-lg">Login</a>
    </div>
</nav>

