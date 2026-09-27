<?php
$namaWebsiteFooter = getPengaturan($conn, 'nama_website') ?: 'Buper Jepara';
$versiSistem = getPengaturan($conn, 'versi') ?: '1.0';
?>
</div> <!-- Close inner flex row -->
</div> <!-- Close content wrapper -->

<!-- Dashboard Footer -->
<footer class="mt-auto flex-shrink-0 bg-purple-900 text-purple-300 text-[11px] px-6 py-3 flex items-center justify-between">
    <span>&copy; <?= date('Y') ?> <span class="text-white font-medium"><?= e($namaWebsiteFooter) ?></span>. All rights reserved.</span>
    <span class="text-purple-400">v<?= e($versiSistem) ?></span>
</footer>

<script>
document.querySelectorAll('.modal-dashboard').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.add('hidden');
    });
});
</script>
</body>
</html>
