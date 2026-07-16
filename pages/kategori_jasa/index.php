<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Kategori Jasa';
$search = $_GET['search'] ?? '';

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Kategori Jasa</h2>
        </div>
    </header>
    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-6">
            <form method="GET" class="flex-1 max-w-md">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari kategori..."
                        class="w-full px-4 py-2.5 pl-10 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                </div>
            </form>
            <a href="tambah.php"
                class="w-full sm:w-auto text-center px-4 py-2.5 bg-[#e60000] hover:bg-[#ffd700] text-white text-sm font-semibold rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Kategori
            </a>
        </div>

        <div class="bg-[#161622] rounded-xl border border-[#2a2a3a] overflow-hidden">
            <div class="table-responsive">
            <table class="table-custom w-full">
                <thead>
                    <tr class="bg-[#0a0a0f]">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Icon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Punya Breakdown</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a3a]">
                    <?php
                    $where = '';
                    if (!empty($search)) {
                        $search_param = "%$search%";
                        $stmt = $conn->prepare("SELECT * FROM kategori_jasa WHERE nama LIKE ? ORDER BY id ASC");
                        $stmt->bind_param("s", $search_param);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        $result = $conn->query("SELECT * FROM kategori_jasa ORDER BY id ASC");
                    }
                    $no = 1;
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400"><?= $no++ ?></td>
                        <td class="px-4 py-3 text-sm text-white font-medium"><?= htmlspecialchars($row['nama']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300">
                            <i class="fas <?= htmlspecialchars($row['icon'] ?? 'fa-wrench') ?> text-[#e60000]"></i>
                            <?= htmlspecialchars($row['icon'] ?? '-') ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <?php if ($row['punya_breakdown']): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-500/10 text-green-400">Ya</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-500/10 text-gray-400">Tidak</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-400 hover:text-blue-300 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="hapus.php?id=<?= $row['id'] ?>"
                                class="text-red-400 hover:text-red-300"
                                onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-folder-open text-3xl mb-2 block"></i>
                            Belum ada data kategori.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </main>
</div>
<?php include '../../layout/footer.php'; ?>
