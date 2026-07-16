<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/transaksi.php';
cek_login();

$title = 'Data Transaksi';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Count total
$count_sql = "SELECT COUNT(*)
        FROM transaksi t
        LEFT JOIN pelanggan p ON t.id_pelanggan = p.id
        LEFT JOIN kendaraan k ON t.id_kendaraan = k.id
        LEFT JOIN merek m ON k.id_merek = m.id";

// Data query
$sql = "SELECT t.*, p.nama AS nama_pelanggan, k.plat_no, m.nama AS merek, k.model
        FROM transaksi t
        LEFT JOIN pelanggan p ON t.id_pelanggan = p.id
        LEFT JOIN kendaraan k ON t.id_kendaraan = k.id
        LEFT JOIN merek m ON k.id_merek = m.id";

$where = [];
if ($status_filter) {
    $where[] = "t.status = '$status_filter'";
}
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where[] = "(p.nama LIKE '%$search_esc%' OR k.plat_no LIKE '%$search_esc%')";
}
if ($where) {
    $where_clause = " WHERE " . implode(" AND ", $where);
    $count_sql .= $where_clause;
    $sql .= $where_clause;
}
$total = $conn->query($count_sql)->fetch_row()[0];
$total_pages = ceil($total / $limit);

$sql .= " ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$preserve_params = '';
if (!empty($_GET)) {
    $get_params = $_GET;
    unset($get_params['page']);
    if (!empty($get_params)) {
        $preserve_params = '&' . http_build_query($get_params);
    }
}

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-white">Data Transaksi</h2>
        </div>
    </header>
    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <!-- Filter -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="?status=" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors <?= !$status_filter ? 'bg-[#e60000] text-white' : 'bg-[#161622] text-gray-400 hover:text-white border border-[#2a2a3a]' ?>">Semua</a>
                <a href="?status=antrian" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors <?= $status_filter === 'antrian' ? 'bg-[#e60000] text-white' : 'bg-[#161622] text-gray-400 hover:text-white border border-[#2a2a3a]' ?>">Antrian</a>
                <a href="?status=dikerjakan" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors <?= $status_filter === 'dikerjakan' ? 'bg-[#e60000] text-white' : 'bg-[#161622] text-gray-400 hover:text-white border border-[#2a2a3a]' ?>">Dikerjakan</a>
                <a href="?status=selesai" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors <?= $status_filter === 'selesai' ? 'bg-[#e60000] text-white' : 'bg-[#161622] text-gray-400 hover:text-white border border-[#2a2a3a]' ?>">Selesai</a>
                <a href="?status=lunas" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors <?= $status_filter === 'lunas' ? 'bg-[#e60000] text-white' : 'bg-[#161622] text-gray-400 hover:text-white border border-[#2a2a3a]' ?>">Lunas</a>
            </div>
            <a href="baru.php"
                class="w-full sm:w-auto text-center px-4 py-2.5 bg-[#e60000] hover:bg-[#ffd700] text-white text-sm font-semibold rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>POS Baru
            </a>
        </div>

        <div class="bg-[#161622] rounded-xl border border-[#2a2a3a] overflow-hidden">
            <div class="table-responsive">
            <table class="table-custom w-full">
                <thead>
                    <tr class="bg-[#0a0a0f]">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kendaraan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a3a]">
                    <?php
                    $no = $offset + 1;
                    while ($row = $result->fetch_assoc()):
                        $status_colors = [
                            'antrian' => 'bg-[#ff6600]/10 text-[#ff6600]',
                            'dikerjakan' => 'bg-yellow-500/10 text-yellow-400',
                            'selesai' => 'bg-blue-500/10 text-blue-400',
                            'lunas' => 'bg-green-500/10 text-green-400',
                        ];
                        $status_color = $status_colors[$row['status']] ?? 'bg-gray-500/10 text-gray-400';
                    ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400"><?= $no++ ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300"><?= date('d/m/Y H:i', strtotime($row['tgl'])) ?></td>
                        <td class="px-4 py-3 text-sm text-white"><?= htmlspecialchars($row['nama_pelanggan'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($row['merek'] ?? '') ?> <?= htmlspecialchars($row['plat_no'] ?? '') ?></td>
                        <td class="px-4 py-3 text-sm text-green-400 font-medium"><?= format_rupiah($row['total']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <form method="POST" action="update_status.php" class="inline">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <select name="status" onchange="this.form.submit()"
                                    class="px-2 py-1 rounded-lg text-xs font-medium border-0 cursor-pointer <?= $status_color ?>">
                                    <?php $s = $row['status']; ?>
                                    <option value="antrian" <?= $s === 'antrian' ? 'selected' : '' ?>>Antrian</option>
                                    <option value="dikerjakan" <?= $s === 'dikerjakan' ? 'selected' : '' ?>>Dikerjakan</option>
                                    <option value="selesai" <?= $s === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    <option value="lunas" <?= $s === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a href="detail.php?id=<?= $row['id'] ?>" class="text-blue-400 hover:text-blue-300">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-receipt text-3xl mb-2 block"></i>
                            Belum ada transaksi.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-6">
            <p class="text-sm text-gray-400">Menampilkan <?= $offset + 1 ?>-<?= min($offset + $limit, $total) ?> dari <?= $total ?></p>
            <div class="flex flex-wrap gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $preserve_params ?>" class="px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm text-gray-300 hover:text-white hover:border-[#e60000] transition-colors">« Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $preserve_params ?>" class="px-4 py-2 rounded-xl text-sm transition-colors <?= $i == $page ? 'bg-[#e60000] text-white' : 'bg-[#0a0a0f] border border-[#2a2a3a] text-gray-300 hover:text-white hover:border-[#e60000]' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $preserve_params ?>" class="px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm text-gray-300 hover:text-white hover:border-[#e60000] transition-colors">Next »</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
<?php include '../../layout/footer.php'; ?>
