<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Riwayat Stok';

$id_sparepart = (int)($_GET['id'] ?? 0);
$sparepart = $conn->query("SELECT * FROM sparepart WHERE id = $id_sparepart")->fetch_assoc();
if (!$sparepart) {
    header('Location: index.php');
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) FROM log_stok WHERE id_sparepart = $id_sparepart")->fetch_row()[0];
$total_pages = ceil($total / $limit);

$logs = $conn->query("SELECT l.*, u.nama AS nama_user FROM log_stok l LEFT JOIN users u ON l.id_user = u.id WHERE l.id_sparepart = $id_sparepart ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset");

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-white">
                <i class="fas fa-history text-[#e60000] mr-2"></i>Riwayat Stok
            </h2>
            <div class="flex items-center gap-3">
                <a href="index.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Info Sparepart -->
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <div class="flex items-center gap-4">
                    <?php if ($sparepart['gambar']): ?>
                    <img src="../../<?= $sparepart['gambar'] ?>" class="w-16 h-16 rounded-xl object-cover border border-[#2a2a3a]">
                    <?php else: ?>
                    <div class="w-16 h-16 rounded-xl bg-[#0a0a0f] border border-[#2a2a3a] flex items-center justify-center">
                        <i class="fas fa-oil-can text-gray-600 text-2xl"></i>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="text-white font-semibold text-lg"><?= htmlspecialchars($sparepart['nama']) ?></h3>
                        <p class="text-gray-400 text-sm">
                            Kode: <?= htmlspecialchars($sparepart['kode'] ?? '-') ?> |
                            Stok Saat Ini: <span class="text-white font-semibold"><?= $sparepart['stok'] ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Log Table -->
            <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#2a2a3a]">
                    <h3 class="text-white font-semibold">Riwayat Pergerakan Stok</h3>
                </div>
                <div class="table-responsive">
                    <table class="table-custom w-full">
                        <thead>
                            <tr class="bg-[#0a0a0f]">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Tipe</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Stok Awal</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Stok Akhir</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Referensi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#2a2a3a]">
                            <?php if ($logs->num_rows > 0): ?>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr class="hover:bg-[#0a0a0f] transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-300 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <?php if ($log['tipe'] === 'masuk'): ?>
                                    <span class="px-2.5 py-1 bg-green-500/20 text-green-400 rounded-lg text-xs font-semibold">MASUK</span>
                                    <?php elseif ($log['tipe'] === 'keluar'): ?>
                                    <span class="px-2.5 py-1 bg-red-500/20 text-red-400 rounded-lg text-xs font-semibold">KELUAR</span>
                                    <?php else: ?>
                                    <span class="px-2.5 py-1 bg-yellow-500/20 text-yellow-400 rounded-lg text-xs font-semibold">PENYESUAIAN</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold <?= $log['qty'] > 0 ? 'text-green-400' : 'text-red-400' ?>"><?= $log['qty'] > 0 ? '+' : '' ?><?= $log['qty'] ?></td>
                                <td class="px-4 py-3 text-sm text-right text-gray-400"><?= $log['stok_sebelum'] ?></td>
                                <td class="px-4 py-3 text-sm text-right text-white font-medium"><?= $log['stok_sesudah'] ?></td>
                                <td class="px-4 py-3 text-sm text-gray-300 font-mono"><?= htmlspecialchars($log['referensi'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($log['nama_user'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-sm text-gray-400 max-w-[200px] truncate" title="<?= htmlspecialchars($log['catatan'] ?? '') ?>"><?= htmlspecialchars($log['catatan'] ?? '-') ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                    <i class="fas fa-history text-3xl mb-3 block text-gray-600"></i>
                                    Belum ada riwayat stok untuk sparepart ini.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-6 py-4 border-t border-[#2a2a3a]">
                    <p class="text-sm text-gray-400">Menampilkan <?= $offset + 1 ?>-<?= min($offset + $limit, $total) ?> dari <?= $total ?></p>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($page > 1): ?>
                        <a href="?id=<?= $id_sparepart ?>&page=<?= $page - 1 ?>" class="px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm text-gray-300 hover:text-white hover:border-[#e60000] transition-colors">« Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?id=<?= $id_sparepart ?>&page=<?= $i ?>" class="px-4 py-2 rounded-xl text-sm transition-colors <?= $i == $page ? 'bg-[#e60000] text-white' : 'bg-[#0a0a0f] border border-[#2a2a3a] text-gray-300 hover:text-white hover:border-[#e60000]' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?id=<?= $id_sparepart ?>&page=<?= $page + 1 ?>" class="px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm text-gray-300 hover:text-white hover:border-[#e60000] transition-colors">Next »</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tombol Tambah Stok -->
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <h3 class="text-white font-semibold mb-4">Tambah / Sesuaikan Stok</h3>
                <form method="POST" action="log_stok_aksi.php" class="flex flex-col sm:flex-row gap-3 items-end">
                    <input type="hidden" name="id_sparepart" value="<?= $id_sparepart ?>">
                    <div class="flex-1">
                        <label class="block text-gray-400 text-xs mb-1">Tipe</label>
                        <select name="tipe" class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#e60000]">
                            <option value="masuk">Stok Masuk</option>
                            <option value="keluar">Stok Keluar</option>
                            <option value="penyesuaian">Penyesuaian (+/-)</option>
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="block text-gray-400 text-xs mb-1">Jumlah</label>
                        <input type="number" name="qty" value="1" min="1" required
                            class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm text-center focus:outline-none focus:border-[#e60000]">
                    </div>
                    <div class="flex-1">
                        <label class="block text-gray-400 text-xs mb-1">Catatan</label>
                        <input type="text" name="catatan" placeholder="Mis: Pembelian dari supplier"
                            class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#e60000]">
                    </div>
                    <button type="submit"
                        class="px-5 py-2.5 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors whitespace-nowrap">
                        <i class="fas fa-check mr-1"></i>Simpan
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include '../../layout/footer.php'; ?>
