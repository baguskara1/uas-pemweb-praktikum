<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/transaksi.php';
require_once '../../functions/whatsapp.php';
require_once '../../functions/nota.php';
cek_login();

$title = 'Detail Transaksi';
$id = (int)($_GET['id'] ?? 0);

$transaksi = $conn->query("SELECT t.*, p.nama AS nama_pelanggan, p.no_telp, k.plat_no, k.model, k.cc, k.tahun, m.nama AS merek, u.nama AS nama_kasir
                            FROM transaksi t
                            LEFT JOIN pelanggan p ON t.id_pelanggan = p.id
                            LEFT JOIN kendaraan k ON t.id_kendaraan = k.id
                            LEFT JOIN merek m ON k.id_merek = m.id
                            LEFT JOIN users u ON t.id_user = u.id
                            WHERE t.id = $id")->fetch_assoc();

if (!$transaksi) {
    header('Location: index.php');
    exit;
}

// Get detail jasa
$detail_jasa = $conn->query("SELECT dj.*, v.nama_varian
                            FROM detail_jasa dj
                            JOIN varian_jasa v ON dj.id_varian = v.id
                            WHERE dj.id_transaksi = $id");

// Get detail sparepart
$detail_sparepart = $conn->query("SELECT ds.*, s.nama FROM detail_sparepart ds JOIN sparepart s ON ds.id_sparepart = s.id WHERE ds.id_transaksi = $id");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-white">Detail Transaksi</h2>
            <div class="flex items-center gap-3">
                <a href="index.php" class="w-full sm:w-auto text-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </header>
    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <?php if ($flash): ?>
        <script>
        Swal.fire({
            icon: '<?= $flash['type'] ?>',
            title: '<?= $flash['message'] ?>',
            background: '#161622',
            color: '#fff',
            timer: 3000,
            showConfirmButton: false
        });
        </script>
        <?php endif; ?>

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Info Card -->
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider">No. Transaksi</p>
                        <p class="text-white font-semibold">#<?= str_pad($transaksi['id'], 4, '0', STR_PAD_LEFT) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Tanggal</p>
                        <p class="text-white font-semibold"><?= date('d/m/Y H:i', strtotime($transaksi['tgl'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Status</p>
                        <?php
                        $badge_colors = [
                            'antrian' => 'bg-[#ffd700]/10 text-[#ffd700]',
                            'dikerjakan' => 'bg-yellow-500/10 text-yellow-400',
                            'selesai' => 'bg-blue-500/10 text-blue-400',
                            'lunas' => 'bg-green-500/10 text-green-400',
                        ];
                        $bc = $badge_colors[$transaksi['status']] ?? 'bg-gray-500/10 text-gray-400';
                        ?>
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?= $bc ?> uppercase"><?= $transaksi['status'] ?></span>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Kasir</p>
                        <p class="text-white font-semibold"><?= htmlspecialchars($transaksi['nama_kasir']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Pelanggan & Kendaraan -->
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <h3 class="text-white font-semibold mb-4"><i class="fas fa-user mr-2 text-[#ccff00]"></i>Data Pelanggan</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 text-xs">Nama</p>
                        <p class="text-white"><?= htmlspecialchars($transaksi['nama_pelanggan'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">No. Telepon</p>
                        <p class="text-white"><?= htmlspecialchars($transaksi['no_telp'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Kendaraan</p>
                        <p class="text-white"><?= htmlspecialchars($transaksi['merek'] ?? '') ?> <?= htmlspecialchars($transaksi['model'] ?? '') ?> (<?= $transaksi['cc'] ?>cc) <?= $transaksi['tahun'] ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs">Plat No</p>
                        <p class="text-white font-mono"><?= htmlspecialchars($transaksi['plat_no'] ?? '-') ?></p>
                    </div>
                </div>
            </div>

            <!-- Detail Jasa -->
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <h3 class="text-white font-semibold mb-4"><i class="fas fa-wrench mr-2 text-[#ccff00]"></i>Jasa Servis</h3>
                <div class="space-y-3">
                    <?php while ($dj = $detail_jasa->fetch_assoc()):
                        // Get items for this jasa
                        $items = $conn->query("SELECT * FROM detail_item_jasa WHERE id_detail_jasa = {$dj['id']}");
                    ?>
                    <div class="bg-[#0a0a0f] rounded-xl p-4 border border-[#2a2a3a]">
                        <div class="flex justify-between items-center">
                            <span class="text-white font-medium"><?= htmlspecialchars($dj['nama_jasa'] ?: $dj['nama_varian']) ?></span>
                            <span class="text-green-400 font-semibold"><?= format_rupiah($dj['total_harga']) ?></span>
                        </div>
                        <?php if ($items->num_rows > 0): ?>
                        <div class="mt-2 pt-2 border-t border-[#2a2a3a] space-y-1">
                            <?php while ($item = $items->fetch_assoc()): ?>
                            <div class="flex justify-between text-xs text-gray-400">
                                <span><?= htmlspecialchars($item['nama_item']) ?> × <?= $item['qty'] ?></span>
                                <span><?= format_rupiah($item['subtotal']) ?></span>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Detail Sparepart -->
            <?php if ($detail_sparepart->num_rows > 0): ?>
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <h3 class="text-white font-semibold mb-4"><i class="fas fa-oil-can mr-2 text-[#ccff00]"></i>Sparepart</h3>
                <div class="space-y-2">
                    <?php while ($ds = $detail_sparepart->fetch_assoc()): ?>
                    <div class="flex justify-between items-center bg-[#0a0a0f] rounded-xl px-4 py-3 border border-[#2a2a3a]">
                        <span class="text-white text-sm"><?= htmlspecialchars($ds['nama']) ?> × <?= $ds['qty'] ?></span>
                        <span class="text-green-400 text-sm font-medium"><?= format_rupiah($ds['harga_jual'] * $ds['qty']) ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Total & Actions -->
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-gray-400 text-lg">Total</span>
                    <span class="text-white text-3xl font-bold"><?= format_rupiah($transaksi['total']) ?></span>
                </div>

                <?php if ($transaksi['catatan']): ?>
                <div class="bg-[#0a0a0f] rounded-xl px-4 py-3 border border-[#2a2a3a] mb-4">
                    <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">Catatan</p>
                    <p class="text-gray-300 text-sm"><?= htmlspecialchars($transaksi['catatan']) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($transaksi['status'] !== 'lunas'): ?>
                <form method="POST" action="update_status.php">
                    <input type="hidden" name="id" value="<?= $transaksi['id'] ?>">
                    <?php
                    $next_status = [
                        'antrian' => ['dikerjakan', 'blue'],
                        'dikerjakan' => ['selesai', 'green'],
                        'selesai' => ['lunas', 'green'],
                    ];
                    $next = $next_status[$transaksi['status']] ?? null;
                    if ($next):
                    ?>
                    <input type="hidden" name="status" value="<?= $next[0] ?>">
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-3 bg-<?= $next[1] ?>-600 hover:bg-<?= $next[1] ?>-700 text-white font-semibold rounded-xl transition-colors">
                        Set <?= ucfirst($next[0]) ?>
                    </button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>

            <!-- WhatsApp & PDF Buttons -->
            <?php if ($transaksi['no_telp']): ?>
            <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="" class="flex-1">
                        <button type="submit" name="kirim_wa" value="1"
                            class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors">
                            <i class="fab fa-whatsapp mr-2"></i>Kirim WA ke Pelanggan
                        </button>
                    </form>
                    <a href="cetak_nota.php?id=<?= $transaksi['id'] ?>" target="_blank"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors text-center">
                        <i class="fas fa-file-pdf mr-2"></i>Cetak Nota PDF
                    </a>
                </div>
            </div>

            <?php
            if (isset($_POST['kirim_wa'])) {
                $dj_wa = $conn->query("SELECT dj.*, v.nama_varian FROM detail_jasa dj JOIN varian_jasa v ON dj.id_varian = v.id WHERE dj.id_transaksi = $id");
                $ds_wa = $conn->query("SELECT ds.*, s.nama FROM detail_sparepart ds JOIN sparepart s ON ds.id_sparepart = s.id WHERE ds.id_transaksi = $id");

                $pesan = formatPesanWA($transaksi, $dj_wa, $ds_wa);
                $phone = preg_replace('/^0/', '62', $transaksi['no_telp']);
                $wa_url = 'https://wa.me/' . $phone . '?text=' . urlencode($pesan);

                // Generate PDF untuk arsip admin
                $nota_dir = __DIR__ . '/../../nota';
                if (!is_dir($nota_dir)) mkdir($nota_dir, 0755, true);
                $nota_path = $nota_dir . '/Nota_TRX_' . str_pad($transaksi['id'], 4, '0', STR_PAD_LEFT) . '.pdf';
                $dj_pdf = $conn->query("SELECT dj.*, v.nama_varian FROM detail_jasa dj JOIN varian_jasa v ON dj.id_varian = v.id WHERE dj.id_transaksi = $id");
                $ds_pdf = $conn->query("SELECT ds.*, s.nama FROM detail_sparepart ds JOIN sparepart s ON ds.id_sparepart = s.id WHERE ds.id_transaksi = $id");
                $pdf = generateNotaPDF($transaksi, $dj_pdf, $ds_pdf, $conn);
                $pdf->Output('F', $nota_path);

                // Catatan: Fonnte free plan = teks only, PDF tidak bisa diattach
                // PDF tersimpan di folder nota/ untuk admin
                $result = kirimWA($transaksi['no_telp'], $pesan);

                if ($result['status']) {
                    echo '<script>Swal.fire({icon:"success",title:"WA berhasil dikirim!",text:"Link garansi sudah termasuk no nota. PDF tersimpan di server.",background:"#161622",color:"#fff",timer:4000,showConfirmButton:false});</script>';
                } elseif (isset($result['url'])) {
                    echo '<script>window.open(' . json_encode($result['url']) . ', "_blank");</script>';
                } else {
                    $reason = json_encode($result['reason'] ?? 'Gagal mengirim WA');
                    $js_url = json_encode($wa_url);
                    echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "WA gagal dikirim",
                        text: ' . $reason . ' + ". Kirim manual?",
                        background: "#161622",
                        color: "#fff",
                        iconColor: "#ccff00",
                        showCancelButton: true,
                        confirmButtonColor: "#22c55e",
                        cancelButtonColor: "#6b7280",
                        confirmButtonText: "Buka WA Manual",
                        cancelButtonText: "Tutup"
                    }).then(r => { if (r.isConfirmed) window.open(' . $js_url . ', "_blank"); });
                    </script>';
                }
            }
            ?>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include '../../layout/footer.php'; ?>
