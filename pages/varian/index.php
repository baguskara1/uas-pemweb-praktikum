<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Data Varian Jasa';

// Filter by jasa
$filter_jasa = (int)($_GET['id_jasa'] ?? 0);
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Ambil daftar jasa untuk dropdown filter
$daftar_jasa = $conn->query("SELECT * FROM jasa ORDER BY nama ASC");

// Bangun query
$where = '';
if ($filter_jasa > 0) {
    $where = "WHERE v.id_jasa = $filter_jasa";
}

$total = $conn->query("SELECT COUNT(*) FROM varian_jasa v JOIN jasa j ON v.id_jasa = j.id JOIN merek m ON v.id_merek = m.id $where")->fetch_row()[0];
$total_pages = ceil($total / $limit);

$sql = "SELECT v.*, j.nama AS nama_jasa, m.nama AS nama_merek
        FROM varian_jasa v
        JOIN jasa j ON v.id_jasa = j.id
        JOIN merek m ON v.id_merek = m.id
        $where
        ORDER BY j.nama ASC, v.nama_varian ASC
        LIMIT $limit OFFSET $offset";

$varian = $conn->query($sql);

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
            <h2 class="text-xl font-bold text-white">Data Varian Jasa</h2>
            <a href="tambah.php" class="w-full sm:w-auto text-center px-6 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Varian
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

        <!-- Filter -->
        <div class="mb-6">
            <form action="" method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                <div class="w-full sm:max-w-xs">
                    <label for="id_jasa" class="block text-gray-400 text-xs font-medium mb-1.5">Filter Berdasarkan Jasa</label>
                    <select id="id_jasa" name="id_jasa"
                        class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white focus:outline-none focus:border-[#e60000]"
                        onchange="this.form.submit()">
                        <option value="">-- Semua Jasa --</option>
                        <?php while ($j = $daftar_jasa->fetch_assoc()): ?>
                            <option value="<?= $j['id'] ?>" <?= $filter_jasa == $j['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($j['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php if ($filter_jasa > 0): ?>
                    <a href="index.php" class="w-full sm:w-auto text-center px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition-colors text-sm">
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Varian</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Jasa</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Merek</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">CC Range</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Total Harga</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a3a]">
                    <?php $no = $offset + 1; ?>
                    <?php while ($row = $varian->fetch_assoc()): ?>
                        <tr class="hover:bg-[#0a0a0f] transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-300"><?= $no++ ?></td>
                            <td class="px-4 py-3 text-sm text-white font-medium">
                                <?= htmlspecialchars($row['nama_varian'] ?: '-') ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($row['nama_jasa']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($row['nama_merek']) ?></td>
                            <td class="px-4 py-3 text-sm text-center text-gray-300">
                                <span class="font-mono text-xs bg-[#0a0a0f] px-2 py-1 rounded border border-[#2a2a3a]">
                                    <?= $row['cc_min'] ?> - <?= $row['cc_max'] ?> CC
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-green-400 font-medium">
                                Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <?php if ($row['is_custom']): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-[#ff6600]/20 text-[#ff6600] rounded-lg text-xs font-medium">
                                        <i class="fas fa-pen"></i> Custom
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-500/20 text-blue-400 rounded-lg text-xs font-medium">
                                        <i class="fas fa-box"></i> Paket
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="item.php?id=<?= $row['id'] ?>"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors text-xs font-medium"
                                        title="Kelola Item">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $row['id'] ?>"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition-colors text-xs font-medium">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id'] ?>"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors text-xs font-medium btn-hapus"
                                        data-nama="<?= htmlspecialchars($row['nama_varian'] ?: $row['nama_jasa'] . ' - ' . $row['nama_merek']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($varian->num_rows === 0): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                <i class="fas fa-list text-3xl mb-3 block text-gray-600"></i>
                                <?php if ($filter_jasa > 0): ?>
                                    Tidak ada varian untuk jasa yang dipilih. <br>
                                    <a href="index.php" class="text-[#e60000] hover:underline">Tampilkan semua varian</a>
                                <?php else: ?>
                                    Belum ada data varian jasa. <br>
                                    <a href="tambah.php" class="text-[#e60000] hover:underline">Tambah varian sekarang</a>
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
                title: 'Hapus Varian?',
                text: `Yakin ingin menghapus varian "${nama}"?`,
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
