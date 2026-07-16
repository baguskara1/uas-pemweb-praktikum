<?php
require_once 'config/session.php';
require_once 'config/database.php';
cek_login();

$title = 'Dashboard';

$filter_mode = $_GET['filter'] ?? 'hari_ini';
$today = date('Y-m-d');

// Tentukan rentang tanggal
if ($filter_mode === 'hari_ini') {
    $tgl_awal = $today;
    $tgl_akhir = $today;
    $chart_hari = 7;
} elseif ($filter_mode === 'bulan_ini') {
    $tgl_awal = date('Y-m-01');
    $tgl_akhir = date('Y-m-t');
    $chart_hari = (int)date('t');
} elseif ($filter_mode === 'bulan_lalu') {
    $tgl_awal = date('Y-m-01', strtotime('-1 month'));
    $tgl_akhir = date('Y-m-t', strtotime('-1 month'));
    $chart_hari = (int)date('t', strtotime('-1 month'));
} else {
    $tgl_awal = $today;
    $tgl_akhir = $today;
    $chart_hari = 7;
}

// Stats
$pendapatan = $conn->query("SELECT IFNULL(SUM(total), 0) FROM transaksi WHERE DATE(tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir' AND status = 'lunas'")->fetch_row()[0];
$total_transaksi = $conn->query("SELECT COUNT(*) FROM transaksi WHERE DATE(tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir'")->fetch_row()[0];
$antrian = $conn->query("SELECT COUNT(*) FROM transaksi WHERE status = 'antrian'")->fetch_row()[0];
$stok_menipis = $conn->query("SELECT COUNT(*) FROM sparepart WHERE stok < 5")->fetch_row()[0];

// Chart data
$chart_labels = [];
$chart_data = [];
if ($filter_mode === 'hari_ini') {
    for ($i = $chart_hari - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $day = date('D', strtotime($date));
        $total = $conn->query("SELECT IFNULL(SUM(total), 0) FROM transaksi WHERE DATE(tgl) = '$date' AND status = 'lunas'")->fetch_row()[0];
        $chart_labels[] = $day;
        $chart_data[] = (int)$total;
    }
} else {
    $hari_bulan = (int)date('t', strtotime($tgl_awal));
    $bulan_num = date('m', strtotime($tgl_awal));
    $tahun_num = date('Y', strtotime($tgl_awal));
    for ($d = 1; $d <= $hari_bulan; $d++) {
        $date = "$tahun_num-$bulan_num-" . str_pad($d, 2, '0', STR_PAD_LEFT);
        $total = $conn->query("SELECT IFNULL(SUM(total), 0) FROM transaksi WHERE DATE(tgl) = '$date' AND status = 'lunas'")->fetch_row()[0];
        $chart_labels[] = $d;
        $chart_data[] = (int)$total;
    }
}

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="flex-1 flex flex-col w-full min-w-0">
    <!-- Topbar -->
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-white">
                <i class="fas fa-gauge-high text-[#e60000] mr-2"></i>Dashboard
            </h2>
            <div class="flex items-center gap-3">
                <form action="" method="GET" class="flex items-center gap-2">
                    <select name="filter" onchange="this.form.submit()"
                        class="px-3 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#e60000] cursor-pointer">
                        <option value="hari_ini" <?= $filter_mode === 'hari_ini' ? 'selected' : '' ?>>Hari Ini</option>
                        <option value="bulan_ini" <?= $filter_mode === 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                        <option value="bulan_lalu" <?= $filter_mode === 'bulan_lalu' ? 'selected' : '' ?>>Bulan Lalu</option>
                    </select>
                </form>
                <span class="text-gray-400 text-sm hidden sm:inline">
                    <i class="far fa-calendar mr-2"></i><?= date('d F Y') ?>
                </span>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a] card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Pendapatan <?= $filter_mode === 'hari_ini' ? 'Hari Ini' : ($filter_mode === 'bulan_ini' ? 'Bulan Ini' : 'Bulan Lalu') ?></p>
                        <p class="text-2xl font-bold text-white mt-1">Rp <?= number_format($pendapatan, 0, ',', '.') ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-500/10 flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a] card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Transaksi <?= $filter_mode === 'hari_ini' ? 'Hari Ini' : ($filter_mode === 'bulan_ini' ? 'Bulan Ini' : 'Bulan Lalu') ?></p>
                        <p class="text-2xl font-bold text-white mt-1"><?= $total_transaksi ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fas fa-receipt text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a] card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Antrian</p>
                        <p class="text-2xl font-bold text-white mt-1"><?= $antrian ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[#ff6600]/10 flex items-center justify-center">
                        <i class="fas fa-clock text-[#ff6600] text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a] card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Stok Menipis</p>
                        <p class="text-2xl font-bold text-white mt-1"><?= $stok_menipis ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[#ff6600]/10 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-[#ff6600] text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-[#161622] rounded-xl p-6 border border-[#2a2a3a]">
            <h3 class="text-white font-semibold mb-4">Pendapatan <?= $filter_mode === 'hari_ini' ? '7 Hari Terakhir' : ($filter_mode === 'bulan_ini' ? 'Bulan Ini' : 'Bulan Lalu') ?></h3>
            <canvas id="chartPenjualan" height="80"></canvas>
        </div>
    </main>
</div>

<script>
const ctx = document.getElementById('chartPenjualan').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Pendapatan',
            data: <?= json_encode($chart_data) ?>,
            borderColor: '#e60000',
            backgroundColor: 'rgba(230, 0, 0, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ffd700',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: '#9ca3af' }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: {
                    color: '#9ca3af',
                    callback: v => 'Rp' + v.toLocaleString('id-ID')
                }
            }
        }
    }
});
</script>

<?php include 'layout/footer.php'; ?>
