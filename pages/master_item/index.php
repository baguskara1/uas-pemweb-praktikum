<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Produk';

$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$where = '';
if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $where = "WHERE nama_item LIKE '%$search_safe%' OR satuan LIKE '%$search_safe%'";
}

$total = $conn->query("SELECT COUNT(*) FROM master_item $where")->fetch_row()[0];
$total_pages = ceil($total / $limit);

$query = "SELECT * FROM master_item $where ORDER BY nama_item ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

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
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Produk</h2>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-6">
            <form method="GET" class="w-full sm:max-w-md">
                <input type="text" name="search" placeholder="Cari item atau satuan..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00]">
            </form>
            <a href="tambah.php" class="w-full sm:w-auto text-center px-4 py-2.5 bg-[#ccff00] hover:bg-[#ff0066] text-white text-sm font-semibold rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Item
            </a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] overflow-hidden">
                <div class="table-responsive">
                    <table class="table-custom w-full">
                        <thead>
                            <tr class="bg-[#0a0a0f]">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Satuan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = $offset + 1; while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-t border-[#2a2a3a] hover:bg-white/[0.02]">
                                    <td class="px-4 py-3 text-sm text-gray-400"><?= $no++ ?></td>
                                    <td class="px-4 py-3 text-sm font-medium text-white"><?= htmlspecialchars($row['nama_item']) ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex px-2.5 py-1 bg-[#0a0a0f] rounded-lg text-gray-300 border border-[#2a2a3a] text-xs uppercase">
                                            <?= htmlspecialchars($row['satuan']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-400 hover:text-blue-300 mr-2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id'] ?>" 
                                           class="text-red-400 hover:text-red-300" 
                                           title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus item &quot;<?= htmlspecialchars($row['nama_item']) ?>&quot;?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] p-12 text-center">
                <i class="fas fa-cube text-4xl text-gray-600 mb-4"></i>
                <p class="text-gray-400 text-lg"><?= $search ? 'Item tidak ditemukan' : 'Belum ada data produk' ?></p>
                <?php if (!$search): ?>
                    <a href="tambah.php" class="inline-block mt-4 px-4 py-2.5 bg-[#ccff00] hover:bg-[#ff0066] text-white text-sm font-semibold rounded-xl transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Item Pertama
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-6">
            <p class="text-sm text-gray-400">Menampilkan <?= $offset + 1 ?>-<?= min($offset + $limit, $total) ?> dari <?= $total ?></p>
            <div class="flex flex-wrap gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $preserve_params ?>" class="px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm text-gray-300 hover:text-white hover:border-[#ccff00] transition-colors">« Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $preserve_params ?>" class="px-4 py-2 rounded-xl text-sm transition-colors <?= $i == $page ? 'bg-[#ccff00] text-white' : 'bg-[#0a0a0f] border border-[#2a2a3a] text-gray-300 hover:text-white hover:border-[#ccff00]' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $preserve_params ?>" class="px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm text-gray-300 hover:text-white hover:border-[#ccff00] transition-colors">Next »</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php include '../../layout/footer.php'; ?>
