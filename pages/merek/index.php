<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Data Merek Motor';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

if (!empty($search)) {
    $search_param = "%$search%";
    // Count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM merek WHERE nama LIKE ?");
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_row()[0];
    // Data
    $stmt = $conn->prepare("SELECT * FROM merek WHERE nama LIKE ? ORDER BY nama ASC LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $total = $conn->query("SELECT COUNT(*) FROM merek")->fetch_row()[0];
    $result = $conn->query("SELECT * FROM merek ORDER BY nama ASC LIMIT $limit OFFSET $offset");
}
$total_pages = ceil($total / $limit);

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
            <h2 class="text-xl font-bold text-white">Data Merek Motor</h2>
        </div>
    </header>
    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-6">
            <form method="GET" class="flex-1 max-w-md">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari merek..."
                        class="w-full px-4 py-2.5 pl-10 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                </div>
            </form>
            <a href="tambah.php"
                class="w-full sm:w-auto text-center px-4 py-2.5 bg-[#e60000] hover:bg-[#ffd700] text-white text-sm font-semibold rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Merek
            </a>
        </div>

        <div class="bg-[#161622] rounded-xl border border-[#2a2a3a] overflow-hidden">
            <div class="table-responsive">
            <table class="table-custom w-full">
                <thead>
                    <tr class="bg-[#0a0a0f]">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Merek</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a3a]">
                    <?php
                    $no = $offset + 1;
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400"><?= $no++ ?></td>
                        <td class="px-4 py-3 text-sm text-white font-medium"><?= htmlspecialchars($row['nama']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-400 hover:text-blue-300 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="hapus.php?id=<?= $row['id'] ?>"
                                class="text-red-400 hover:text-red-300"
                                onclick="return confirm('Yakin ingin menghapus merek ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-tag text-3xl mb-2 block"></i>
                            Belum ada data merek.
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
