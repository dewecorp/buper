<?php
// Sidebar content
?>
<div class="flex flex-col w-56 flex-shrink-0 self-stretch border-r border-purple-800 bg-purple-900 overflow-y-auto scroll-thin">
    <div class="flex flex-col p-3 flex-1">
        <div class="flex items-center gap-2 mb-4 p-2.5 rounded-lg bg-gradient-to-r from-brown-700 to-emerald-600 text-white shadow-sm">
            <div class="relative h-8 w-8 overflow-hidden rounded-full bg-brown-800 flex items-center justify-center text-white text-[11px] font-semibold ring-2 ring-emerald-300">
                <?= e($initials) ?>
                <?php if (!empty($foto)): ?>
                    <img src="../<?= e($foto) ?>" alt="Foto Profil" class="absolute inset-0 w-full h-full object-cover">
                <?php endif; ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium truncate"><?= e($nama) ?></p>
                <p class="text-[11px] text-emerald-200 capitalize"><?= e($role) ?></p>
            </div>
        </div>

        <nav class="flex-1 space-y-0.5">
            <p class="text-[11px] font-semibold text-purple-300 uppercase tracking-wider mb-1.5 px-1">Menu</p>
            <a href="index.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
                Dashboard
            </a>

            <?php if (isAdmin()): ?>
                <a href="profil_buper.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m7-12a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    Data Profil
                </a>
                <a href="pengelola.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-2v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2H3M7 11v3m10-3v3m4-10V4a2 2 0 00-2-2H5a2 2 0 00-2 2v2M10 20h4V4h-4v16z"/></svg>
                    Data Pengelola
                </a>
                <a href="fasilitas.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5v10m-9-5h-1m1 0h-1"/></svg>
                    Data Fasilitas
                </a>
                <a href="biaya.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Data Biaya
                </a>
                <a href="users.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Data Users
                </a>
                <a href="izin_penggunaan.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    Data Izin
                </a>
                <a href="pengaturan.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pengaturan
                </a>
            <?php elseif (isPengelola()): ?>
                <a href="izin_penggunaan.php" class="flex items-center px-2.5 py-2 rounded-lg text-purple-200 hover:bg-emerald-600 hover:text-white transition-colors duration-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    Data Izin Penggunaan
                </a>
            <?php endif; ?>

            <div class="border-t border-purple-700 my-3"></div>

            <a href="../auth/logout.php" class="flex items-center px-2.5 py-2 rounded-lg text-red-300 hover:bg-red-600 hover:text-white transition-colors duration-200 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </a>
        </nav>
    </div>
</div>
