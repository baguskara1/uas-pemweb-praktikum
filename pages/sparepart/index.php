<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Data Sparepart';

$search = $_GET['search'] ?? '';
$sort_stok = $_GET['sort_stok'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where = "WHERE s.nama LIKE ? OR s.kode LIKE ?";
    $params = ["%$search%", "%$search%"];
    $types = "ss";
}

$order_clause = "s.id DESC";
if ($sort_stok === 'asc') $order_clause = "s.stok ASC, s.nama ASC";
if ($sort_stok === 'desc') $order_clause = "s.stok DESC, s.nama ASC";

// Count total
$count_sql = "SELECT COUNT(*) 
        FROM sparepart s 
        JOIN kategori_sparepart k ON s.id_kategori = k.id 
        $where";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total / $limit);

// Data with pagination
$sql = "SELECT s.*, k.nama AS nama_kategori 
        FROM sparepart s 
        JOIN kategori_sparepart k ON s.id_kategori = k.id 
        $where 
        ORDER BY $order_clause 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $bind_types = $types . "ii";
    $bind_params = array_merge($params, [$limit, $offset]);
    $stmt->bind_param($bind_types, ...$bind_params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$sparepart = $stmt->get_result();

$preserve_params = '';
if (!empty($_GET)) {
    $get_params = $_GET;
    unset($get_params['page']);
    if (!empty($get_params)) {
        $preserve_params = '&' . http_build_query($get_params);
    }
}
?>
<?php include '../../layout/header.php'; ?>
<?php include '../../layout/sidebar.php'; ?>

<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-white">Data Sparepart</h2>
            <a href="tambah.php" class="w-full sm:w-auto text-center px-6 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Sparepart
            </a>
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

        <!-- Search -->
        <div class="mb-6">
            <form action="" method="GET" class="flex gap-3">
                <div class="relative flex-1 max-w-xs sm:max-w-md">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari sparepart (nama atau kode)..."
                        class="w-full pl-10 pr-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                </div>
                <button type="submit"
                    class="px-5 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white rounded-xl transition-colors">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search)): ?>
                    <a href="index.php"
                        class="px-5 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] overflow-hidden">
            <div class="table-responsive">
            <table class="table-custom w-full">
                <thead>
                    <tr class="bg-[#0a0a0f]">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Sparepart</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Foto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="var u=new (window.URL||URL)(location.href),s=u.searchParams.get('sort_stok');u.searchParams.set('sort_stok',s==='asc'?'desc':'asc');location.href=u.toString()">
                            Stok <?php if ($sort_stok === 'asc'): ?><span class="text-[#e60000] ml-1">↑</span><?php elseif ($sort_stok === 'desc'): ?><span class="text-[#e60000] ml-1">↓</span><?php endif; ?>
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Harga Beli</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Harga Jual</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a3a]">
                    <?php $no = $offset + 1; ?>
                    <?php while ($row = $sparepart->fetch_assoc()): ?>
                        <?php $stok_menipis = $row['stok'] < 5; ?>
                        <tr class="hover:bg-[#0a0a0f] transition-colors <?= $stok_menipis ? 'bg-red-500/5' : '' ?>">
                            <td class="px-4 py-3 text-sm text-gray-300"><?= $no++ ?></td>
                            <td class="px-4 py-3 text-sm">
                                <span class="font-mono text-xs bg-[#0a0a0f] px-2 py-1 rounded border border-[#2a2a3a] text-gray-300">
                                    <?= htmlspecialchars($row['kode'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-white font-medium"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if (!empty($row['gambar'])): ?>
                                    <img src="../../<?= htmlspecialchars($row['gambar']) ?>"
                                        class="w-10 h-10 rounded-lg object-cover border border-[#2a2a3a] mx-auto"
                                        alt="foto">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-lg bg-[#0a0a0f] border border-[#2a2a3a] flex items-center justify-center mx-auto">
                                        <i class="fas fa-oil-can text-gray-600 text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td class="px-4 py-3 text-sm text-center">
                                <?php if ($stok_menipis): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-500/20 text-red-400 rounded-lg font-semibold text-xs">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                        <?= $row['stok'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300 font-medium"><?= $row['stok'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-300">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-sm text-right text-green-400 font-medium">Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="edit.php?id=<?= $row['id'] ?>"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition-colors text-xs font-medium">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="log_stok.php?id=<?= $row['id'] ?>"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-500/10 text-gray-400 rounded-lg hover:bg-gray-500/20 transition-colors text-xs font-medium"
                                        title="Riwayat Stok">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id'] ?>"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors text-xs font-medium btn-hapus"
                                        data-nama="<?= htmlspecialchars($row['nama']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($sparepart->num_rows === 0): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                                <i class="fas fa-oil-can text-3xl mb-3 block text-gray-600"></i>
                                <?php if (!empty($search)): ?>
                                    Tidak ada sparepart yang cocok dengan "<strong><?= htmlspecialchars($search) ?></strong>". <br>
                                    <a href="index.php" class="text-[#e60000] hover:underline">Reset pencarian</a>
                                <?php else: ?>
                                    Belum ada data sparepart. <br>
                                    <a href="tambah.php" class="text-[#e60000] hover:underline">Tambah sparepart sekarang</a>
                                <?php endif; ?>
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

<script>
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const nama = this.dataset.nama;
            const href = this.getAttribute('href');
            Swal.fire({
                title: 'Hapus Sparepart?',
                text: `Yakin ingin menghapus sparepart "${nama}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e60000',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                background: '#161622',
                color: '#fff',
                iconColor: '#e60000',
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
</script>

<?php include '../../layout/footer.php'; ?>
