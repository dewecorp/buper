<?php
require_once __DIR__ . '/../config/koneksi.php';
if (!isLogin()) redirect('../auth/login.php');

$title = "Dashboard";
$nama_user = $_SESSION['nama_lengkap'] ?? '';
$role_user = $_SESSION['role'] ?? '';

$total_izin_pending = 0;
$total_izin_disetujui = 0;
$total_izin_selesai = 0;
$total_izin_ditolak = 0;
$total_izin = 0;
$total_fasilitas = 0;

$q1 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM izin_penggunaan WHERE status = 'pending'");
$r1 = mysqli_fetch_assoc($q1);
$total_izin_pending = $r1['total'] ?? 0;

$q2 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM izin_penggunaan WHERE status = 'disetujui'");
$r2 = mysqli_fetch_assoc($q2);
$total_izin_disetujui = $r2['total'] ?? 0;

$q3 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM izin_penggunaan");
$r3 = mysqli_fetch_assoc($q3);
$total_izin = $r3['total'] ?? 0;

$q4 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM fasilitas");
$r4 = mysqli_fetch_assoc($q4);
$total_fasilitas = $r4['total'] ?? 0;

$q5 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM izin_penggunaan WHERE status = 'selesai'");
$r5 = mysqli_fetch_assoc($q5);
$total_izin_selesai = $r5['total'] ?? 0;

$q6 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM izin_penggunaan WHERE status = 'ditolak'");
$r6 = mysqli_fetch_assoc($q6);
$total_izin_ditolak = $r6['total'] ?? 0;

// Aktivitas terbaru
mysqli_query($conn, "UPDATE aktivitas SET aktivitas = REPLACE(REPLACE(aktivitas, 'GitHub', 'server pusat'), 'github', 'server pusat') WHERE aktivitas LIKE '%github%' OR aktivitas LIKE '%GitHub%'");
mysqli_query($conn, "DELETE FROM aktivitas WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$q_total_aktivitas = mysqli_query($conn, "SELECT COUNT(*) AS total FROM aktivitas");
$r_total_aktivitas = mysqli_fetch_assoc($q_total_aktivitas);
$total_aktivitas = $r_total_aktivitas['total'] ?? 0;
$q_aktivitas = mysqli_query($conn, "SELECT * FROM aktivitas ORDER BY created_at DESC LIMIT 20");
$aktivitas_list = [];
while ($row = mysqli_fetch_assoc($q_aktivitas)) $aktivitas_list[] = $row;

function timeAgo($datetime) {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->y > 0) return $diff->y . ' thn lalu';
    if ($diff->m > 0) return $diff->m . ' bln lalu';
    if ($diff->d > 0) return $diff->d . ' hr lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' mnt lalu';
    return 'baru saja';
}

function iconAktivitas($jenis) {
    switch ($jenis) {
        case 'tambah': return 'plus-circle';
        case 'edit': return 'pencil-square';
        case 'hapus': return 'trash';
        case 'login': return 'arrow-right-on-rectangle';
        case 'logout': return 'arrow-left-on-rectangle';
        case 'update': return 'arrow-repeat';
        default: return 'circle';
    }
}

function colorAktivitas($jenis) {
    switch ($jenis) {
        case 'tambah': return 'emerald';
        case 'edit': return 'amber';
        case 'hapus': return 'red';
        case 'login': return 'blue';
        case 'logout': return 'orange';
        case 'update': return 'purple';
        default: return 'gray';
    }
}

// Monthly chart data (last 12 months)
$bulan_indonesia = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$chart_months = [];
$chart_pending = [];
$chart_disetujui = [];
$chart_ditolak = [];
$chart_selesai = [];
for ($m = 11; $m >= 0; $m--) {
    $month = date('Y-m', strtotime("-{$m} months"));
    $m_num = (int)date('m', strtotime($month . '-01')) - 1;
    $chart_months[] = $bulan_indonesia[$m_num] . ' ' . date('Y', strtotime($month . '-01'));
    $cq = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM izin_penggunaan WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month' GROUP BY status");
    $cp = 0; $cd = 0; $ct = 0; $cs = 0;
    while ($cr = mysqli_fetch_assoc($cq)) {
        if ($cr['status'] === 'pending') $cp = $cr['total'];
        elseif ($cr['status'] === 'disetujui') $cd = $cr['total'];
        elseif ($cr['status'] === 'ditolak') $ct = $cr['total'];
        elseif ($cr['status'] === 'selesai') $cs = $cr['total'];
    }
    $chart_pending[] = $cp;
    $chart_disetujui[] = $cd;
    $chart_ditolak[] = $ct;
    $chart_selesai[] = $cs;
}

include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>

<main class="flex-1 p-6 bg-gray-100 overflow-y-auto font-jakarta">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Dashboard</h1>
    <p class="text-sm text-gray-500 mb-6">Ringkasan data Buper Jepara.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-amber-50 border border-amber-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-amber-900">Izin Pending</p>
                <div class="p-2.5 rounded-xl bg-amber-100 text-amber-700 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.3 2.807-1.3 3.572 0L15.682 9H4.318L8.257 3.099zM5.571 16H14.43c.963 0 1.75-.787 1.75-1.75v-1.5a1.75 1.75 0 00-1.75-1.75h-8.86a1.75 1.75 0 00-1.75 1.75v1.5c0 .963.787 1.75 1.75 1.75z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-amber-700 mt-4"><?= e($total_izin_pending) ?></p>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-emerald-900">Izin Disetujui</p>
                <div class="p-2.5 rounded-xl bg-emerald-100 text-emerald-700 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-emerald-700 mt-4"><?= e($total_izin_disetujui) ?></p>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-red-900">Ajuan Ditolak</p>
                <div class="p-2.5 rounded-xl bg-red-100 text-red-700 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-red-700 mt-4"><?= e($total_izin_ditolak ?? 0) ?></p>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-purple-900">Total Ajuan Izin</p>
                <div class="p-2.5 rounded-xl bg-purple-100 text-purple-700 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-purple-700 mt-4"><?= e($total_izin) ?></p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-blue-900">Total Fasilitas</p>
                <div class="p-2.5 rounded-xl bg-blue-100 text-blue-700 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM11 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-blue-700 mt-4"><?= e($total_fasilitas) ?></p>
        </div>
    </div>

    <!-- Grid Grafik & Aktivitas (2 Kolom Sejajar) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 items-start">
        <!-- Grafik Peminjam -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 pb-2.5 border-b border-gray-100 gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-900 leading-tight">Grafik Peminjam</h2>
                    <p class="text-xs text-gray-400">Statistik 12 bulan terakhir</p>
                </div>
                <div class="flex flex-wrap gap-2.5 text-[11px]">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>Pending</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block"></span>Disetujui</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>Ditolak</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-400 inline-block"></span>Selesai</span>
                </div>
            </div>
            <div class="relative w-full h-[260px]">
                <canvas id="peminjamChart"></canvas>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
            <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-purple-50 text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 leading-tight">Aktivitas Terbaru</h2>
                        <p class="text-xs text-gray-400">Log kegiatan sistem terkini</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 text-[11px] font-semibold bg-purple-100 text-purple-700 rounded-full">
                    <?= e($total_aktivitas) ?> Aktivitas
                </span>
            </div>

            <?php if (empty($aktivitas_list)): ?>
                <div class="flex flex-col items-center justify-center h-[260px] text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-gray-300 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 00-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-xs font-medium">Belum ada aktivitas baru</p>
                </div>
            <?php else: ?>
                <div class="relative">
                    <div class="absolute left-[21px] top-3 bottom-3 w-0.5 bg-slate-200"></div>
                    <div class="space-y-1 h-[260px] overflow-y-auto pr-1 scroll-thin">
                        <?php foreach ($aktivitas_list as $a):
                            $c = colorAktivitas($a['jenis']);
                            $i = iconAktivitas($a['jenis']);
                            $roleName = !empty($a['role_user']) ? ucfirst($a['role_user']) : 'User';
                        ?>
                        <div class="group relative flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 transition-all duration-150 border border-transparent hover:border-gray-100">
                            <div class="relative z-10 flex-shrink-0 w-7 h-7 rounded-lg bg-<?= $c ?>-50 text-<?= $c ?>-600 flex items-center justify-center ring-2 ring-white shadow-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <?php if ($i === 'plus-circle'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    <?php elseif ($i === 'pencil-square'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                    <?php elseif ($i === 'trash'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    <?php elseif ($i === 'arrow-repeat'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                                    <?php elseif ($i === 'arrow-right-on-rectangle'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                                    <?php elseif ($i === 'arrow-left-on-rectangle'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15m-3 0l-3-3m0 0l3-3m-3 3H15"/>
                                    <?php else: ?>
                                    <circle cx="12" cy="12" r="10"/>
                                    <?php endif; ?>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="text-xs font-semibold text-gray-900 truncate"><?= e($a['nama_user']) ?></span>
                                        <span class="text-[9px] font-medium px-1 py-0.2 rounded bg-slate-100 text-slate-500 uppercase tracking-wide border border-slate-200"><?= e($roleName) ?></span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 flex-shrink-0 whitespace-nowrap"><?= timeAgo($a['created_at']) ?></span>
                                </div>
                                <p class="text-xs text-gray-600 truncate mt-0.5">
                                    <?= e(str_ireplace('GitHub', 'server pusat', $a['aktivitas'])) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('peminjamChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_months) ?>,
        datasets: [
            {
                label: 'Pending',
                data: <?= json_encode($chart_pending) ?>,
                backgroundColor: '#facc15',
                borderColor: '#eab308',
                borderWidth: 1
            },
            {
                label: 'Disetujui',
                data: <?= json_encode($chart_disetujui) ?>,
                backgroundColor: '#34d399',
                borderColor: '#10b981',
                borderWidth: 1
            },
            {
                label: 'Ditolak',
                data: <?= json_encode($chart_ditolak) ?>,
                backgroundColor: '#f87171',
                borderColor: '#ef4444',
                borderWidth: 1
            },
            {
                label: 'Selesai',
                data: <?= json_encode($chart_selesai) ?>,
                backgroundColor: '#60a5fa',
                borderColor: '#3b82f6',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
