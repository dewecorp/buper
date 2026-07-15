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

// Monthly chart data (last 12 months)
$bulan_indonesia = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$chart_months = [];
$chart_pending = [];
$chart_disetujui = [];
$chart_ditolak = [];
$chart_selesai = [];
for ($m = 11; $m >= 0; $m--) {
    $month = date('Y-m', strtotime("-${m} months"));
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
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Izin Pending</p>
                <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.3 2.807-1.3 3.572 0L15.682 9H4.318L8.257 3.099zM5.571 16H14.43c.963 0 1.75-.787 1.75-1.75v-1.5a1.75 1.75 0 00-1.75-1.75h-8.86a1.75 1.75 0 00-1.75 1.75v1.5c0 .963.787 1.75 1.75 1.75z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-amber-600 mt-4"><?= e($total_izin_pending) ?></p>
            <p class="text-xs text-gray-400 mt-1">Perlu persetujuan</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Izin Disetujui</p>
                <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-emerald-600 mt-4"><?= e($total_izin_disetujui) ?></p>
            <p class="text-xs text-gray-400 mt-1">Telah disetujui</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Ajuan Ditolak</p>
                <div class="p-2.5 rounded-xl bg-red-50 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-red-600 mt-4"><?= e($total_izin_ditolak ?? 0) ?></p>
            <p class="text-xs text-gray-400 mt-1">Ajuan ditolak</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Total Ajuan Izin</p>
                <div class="p-2.5 rounded-xl bg-purple-50 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-purple-600 mt-4"><?= e($total_izin) ?></p>
            <p class="text-xs text-gray-400 mt-1">Total seluruh ajuan</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">Total Fasilitas</p>
                <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM11 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z" /></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-emerald-600 mt-4"><?= e($total_fasilitas) ?></p>
            <p class="text-xs text-gray-400 mt-1">Tersedia</p>
        </div>
    </div>

    <!-- Grafik Peminjam -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Grafik Peminjam (12 Bulan)</h2>
            <div class="flex gap-4 text-xs">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>Pending</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>Disetujui</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>Ditolak</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span>Selesai</span>
            </div>
        </div>
        <canvas id="peminjamChart" height="100"></canvas>
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
