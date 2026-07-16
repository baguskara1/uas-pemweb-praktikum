<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Data Kendaraan';

$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-white">Data Kendaraan</h2>
            <div class="flex items-center gap-4">
                <span class="text-gray-400 text-sm">
                    <i class="far fa-calendar mr-2"></i><?= date('d F Y') ?>
                </span>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <?php if (isset($_SESSION['flash'])): ?>
            <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '<?= $flash['type'] ?>',
                        title: '<?= $flash['type'] === 'success' ? 'Berhasil' : 'Gagal' ?>',
                        text: '<?= $flash['message'] ?>',
                        timer: 3000,
                        showConfirmButton: false,
                        background: '#161622',
                        color: '#fff',
                        iconColor: '<?= $flash['type'] === 'success' ? '#22c55e' : '#e60000' ?>',
                        toast: true,
                        position: 'top-end'
                    });
                });
            </script>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 mb-6">
            <form method="GET" class="flex-1 max-w-md">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari plat no. atau nama pelanggan..."
                        class="w-full px-4 py-2.5 pl-10 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                </div>
            </form>
            <a href="tambah.php"
                class="w-full sm:w-auto text-center px-4 py-2.5 bg-[#e60000] hover:bg-[#ffd700] text-white text-sm font-semibold rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Kendaraan
            </a>
        </div>

        <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] overflow-hidden">
            <div class="table-responsive">
            <table class="w-full">
                <thead>
                    <tr class="bg-[#0a0a0f]/50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Pemilik</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Merek</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Plat No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Model</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">CC</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tahun</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a3a]">
                    <?php
                    $count_sql = "SELECT COUNT(*)
                            FROM kendaraan k
                            JOIN pelanggan p ON k.id_pelanggan = p.id
                            JOIN merek m ON k.id_merek = m.id";

                    $sql = "SELECT k.*, p.nama AS nama_pelanggan, m.nama AS nama_merek
                            FROM kendaraan k
                            JOIN pelanggan p ON k.id_pelanggan = p.id
                            JOIN merek m ON k.id_merek = m.id";

                    if (!empty($search)) {
                        $search_param = "%$search%";
                        $count_sql .= " WHERE k.plat_no LIKE ? OR p.nama LIKE ?";
                        $sql .= " WHERE k.plat_no LIKE ? OR p.nama LIKE ?";

                        $stmt = $conn->prepare($count_sql);
                        $stmt->bind_param('ss', $search_param, $search_param);
                        $stmt->execute();
                        $total = $stmt->get_result()->fetch_row()[0];

                        $sql .= " ORDER BY k.id DESC LIMIT ? OFFSET ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('ssii', $search_param, $search_param, $limit, $offset);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        $total = $conn->query($count_sql)->fetch_row()[0];
                        $sql .= " ORDER BY k.id DESC LIMIT $limit OFFSET $offset";
                        $result = $conn->query($sql);
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

                    $no = $offset + 1;
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-400"><?= $no++ ?></td>
                                <td class="px-4 py-3 text-sm text-white font-medium"><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($row['nama_merek']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300 font-mono"><?= htmlspecialchars($row['plat_no']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($row['model'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?= $row['cc'] ?> cc</td>
                                <td class="px-4 py-3 text-sm text-gray-300"><?= $row['tahun'] ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-400 hover:text-blue-300 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id'] ?>"
                                        onclick="return confirm('Yakin ingin menghapus kendaraan ini?')"
                                        class="text-red-400 hover:text-red-300">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-motorcycle text-3xl mb-2 block"></i>
                                <?= empty($search) ? 'Belum ada data kendaraan.' : 'Kendaraan tidak ditemukan.' ?>
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
