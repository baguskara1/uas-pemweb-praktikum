<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Laporan Penghasilan';

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$tgl_awal = "$tahun-$bulan-01";
$tgl_akhir = date('Y-m-t', strtotime($tgl_awal));

// Stats
$pendapatan = $conn->query("SELECT IFNULL(SUM(total), 0) FROM transaksi WHERE DATE(tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir' AND status = 'lunas'")->fetch_row()[0];
$total_transaksi = $conn->query("SELECT COUNT(*) FROM transaksi WHERE DATE(tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir'")->fetch_row()[0];
$pelanggan_masuk = $conn->query("SELECT COUNT(DISTINCT id_pelanggan) FROM transaksi WHERE DATE(tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir'")->fetch_row()[0];
$sparepart_terjual = $conn->query("SELECT IFNULL(SUM(qty), 0) FROM detail_sparepart ds JOIN transaksi t ON ds.id_transaksi = t.id WHERE DATE(t.tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir'")->fetch_row()[0];

// Chart data (per hari dalam bulan)
$hari = date('t', strtotime($tgl_awal));
$chart_labels = [];
$chart_data = [];
for ($d = 1; $d <= $hari; $d++) {
    $date = "$tahun-$bulan-" . str_pad($d, 2, '0', STR_PAD_LEFT);
    $total = $conn->query("SELECT IFNULL(SUM(total), 0) FROM transaksi WHERE DATE(tgl) = '$date' AND status = 'lunas'")->fetch_row()[0];
    $chart_labels[] = $d;
    $chart_data[] = (int)$total;
}

// Transaksi
$transaksi = $conn->query("SELECT t.*, p.nama AS nama_pelanggan, k.plat_no FROM transaksi t LEFT JOIN pelanggan p ON t.id_pelanggan = p.id LEFT JOIN kendaraan k ON t.id_kendaraan = k.id WHERE DATE(t.tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY t.tgl DESC");

// Export
if (isset($_GET['export']) && $_GET['export'] === 'xlsx') {
    require_once '../../functions/export_laporan.php';
    exportLaporanXLSX($bulan, $tahun, $conn);
    exit;
}

$nama_bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-white">
                <i class="fas fa-chart-simple text-[#e60000] mr-2"></i>Laporan Penghasilan
            </h2>
            <form action="" method="GET" class="flex flex-wrap items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                <select name="bulan" class="flex-1 min-w-[100px] px-3 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#e60000]">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $bulan == $m ? 'selected' : '' ?>><?= $nama_bulan[$m] ?></option>
                    <?php endfor; ?>
                </select>
                <select name="tahun" class="flex-1 min-w-[80px] px-3 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#e60000]">
                    <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="flex-1 min-w-[100px] px-4 py-2 bg-[#e60000] hover:bg-[#ffd700] text-white rounded-xl text-sm transition-colors">
                    <i class="fas fa-filter mr-1"></i>Tampilkan
                </button>
                <a href="?export=xlsx&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
                    class="flex-1 min-w-[120px] text-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm transition-colors whitespace-nowrap">
                    <i class="fas fa-file-excel mr-1"></i>Export XLSX
                </a>
            </form>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <h3 class="text-gray-400 text-sm mb-4">
            <i class="far fa-calendar mr-2"></i><?= $nama_bulan[(int)$bulan] ?> <?= $tahun ?>
            <span class="text-gray-600 mx-2">—</span>
            <?= $tgl_awal ?> s/d <?= $tgl_akhir ?>
        </h3>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Pendapatan</p>
                        <p class="text-2xl font-bold text-white mt-1">Rp <?= number_format($pendapatan, 0, ',', '.') ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-500/10 flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Transaksi</p>
                        <p class="text-2xl font-bold text-white mt-1"><?= $total_transaksi ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fas fa-receipt text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Pelanggan</p>
                        <p class="text-2xl font-bold text-white mt-1"><?= $pelanggan_masuk ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center">
                        <i class="fas fa-users text-purple-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider">Sparepart Terjual</p>
                        <p class="text-2xl font-bold text-white mt-1"><?= $sparepart_terjual ?> pcs</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[#ff6600]/10 flex items-center justify-center">
                        <i class="fas fa-oil-can text-[#ff6600] text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-[#161622] rounded-xl p-6 border border-[#2a2a3a] mb-8">
            <h3 class="text-white font-semibold mb-4">Pendapatan Harian <?= $nama_bulan[(int)$bulan] ?> <?= $tahun ?></h3>
            <canvas id="chartLaporan" height="80"></canvas>
        </div>

        <!-- Table Transaksi -->
        <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#2a2a3a] flex justify-between items-center">
                <h3 class="text-white font-semibold">Daftar Transaksi</h3>
                <a href="?export=xlsx&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm transition-colors">
                    <i class="fas fa-file-excel mr-1"></i>Export XLSX
                </a>
            </div>
            <div class="table-responsive">
                <table class="table-custom w-full">
                    <thead>
                        <tr class="bg-[#0a0a0f]">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nota</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Pelanggan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Plat No</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2a2a3a]">
                        <?php if ($transaksi->num_rows > 0): ?>
                        <?php while ($t = $transaksi->fetch_assoc()): ?>
                        <tr class="hover:bg-[#0a0a0f] transition-colors">
                            <td class="px-4 py-3 text-sm font-mono text-white">
                                <a href="../transaksi/detail.php?id=<?= $t['id'] ?>" class="hover:text-[#e60000]">
                                    #TRX-<?= str_pad($t['id'], 4, '0', STR_PAD_LEFT) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-300"><?= date('d/m/Y H:i', strtotime($t['tgl'])) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($t['nama_pelanggan'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-sm text-gray-300 font-mono"><?= htmlspecialchars($t['plat_no'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-sm text-center">
                                <?php $bc = ['antrian'=>'text-[#ff6600]','dikerjakan'=>'text-yellow-400','selesai'=>'text-blue-400','lunas'=>'text-green-400']; ?>
                                <span class="<?= $bc[$t['status']] ?? 'text-gray-400' ?> font-medium uppercase"><?= $t['status'] ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-green-400 font-semibold">Rp <?= number_format($t['total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                <i class="fas fa-receipt text-3xl mb-3 block text-gray-600"></i>
                                Belum ada transaksi di periode ini.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
const ctx = document.getElementById('chartLaporan').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Pendapatan',
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: 'rgba(255, 215, 0, 0.6)',
            borderColor: '#ffd700',
            borderWidth: 1,
            borderRadius: 4,
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

<?php include '../../layout/footer.php'; ?>
