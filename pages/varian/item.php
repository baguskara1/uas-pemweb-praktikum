<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Kelola Item Varian';

$id_varian = (int)($_GET['id'] ?? 0);

// Ambil info varian
$varian = $conn->query("
    SELECT v.*, j.nama AS nama_jasa, m.nama AS nama_merek
    FROM varian_jasa v
    JOIN jasa j ON v.id_jasa = j.id
    JOIN merek m ON v.id_merek = m.id
    WHERE v.id = $id_varian
");

if ($varian->num_rows === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Varian tidak ditemukan.'];
    header('Location: index.php');
    exit;
}

$varian_data = $varian->fetch_assoc();

// Ambil daftar master item untuk dropdown
$master_item = $conn->query("SELECT * FROM master_item ORDER BY nama_item ASC");

// ========== PROSES TAMBAH ITEM ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah') {
        $id_master_item = (int)($_POST['id_master_item'] ?? 0);
        $qty_default    = (int)($_POST['qty_default'] ?? 1);
        $harga_default  = (int)str_replace('.', '', $_POST['harga_default'] ?? 0);

        if (empty($id_master_item)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Master item harus dipilih.'];
        } else {
            // Cek duplikat
            $cek = $conn->query("SELECT id FROM item_varian WHERE id_varian = $id_varian AND id_master_item = $id_master_item");
            if ($cek->num_rows > 0) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Item sudah ada dalam varian ini.'];
            } else {
                $stmt = $conn->prepare("INSERT INTO item_varian (id_varian, id_master_item, qty_default, harga_default) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiii", $id_varian, $id_master_item, $qty_default, $harga_default);

                if ($stmt->execute()) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item berhasil ditambahkan.'];
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan item: ' . $conn->error];
                }
                $stmt->close();
            }
        }
        header("Location: item.php?id=$id_varian");
        exit;
    }

    // ========== PROSES EDIT ITEM (AJAX-like via POST) ==========
    if ($_POST['action'] === 'edit') {
        $id_item        = (int)($_POST['id_item'] ?? 0);
        $qty_default    = (int)($_POST['qty_default'] ?? 1);
        $harga_default  = (int)str_replace('.', '', $_POST['harga_default'] ?? 0);

        $stmt = $conn->prepare("UPDATE item_varian SET qty_default = ?, harga_default = ? WHERE id = ? AND id_varian = ?");
        $stmt->bind_param("iiii", $qty_default, $harga_default, $id_item, $id_varian);

        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item berhasil diperbarui.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal memperbarui item: ' . $conn->error];
        }
        $stmt->close();
        header("Location: item.php?id=$id_varian");
        exit;
    }

    // ========== PROSES HAPUS ITEM ==========
    if ($_POST['action'] === 'hapus') {
        $id_item = (int)($_POST['id_item'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM item_varian WHERE id = ? AND id_varian = ?");
        $stmt->bind_param("ii", $id_item, $id_varian);

        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item berhasil dihapus.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus item: ' . $conn->error];
        }
        $stmt->close();
        header("Location: item.php?id=$id_varian");
        exit;
    }
}

// Ambil daftar item varian
$items = $conn->query("
    SELECT iv.*, mi.nama_item, mi.satuan
    FROM item_varian iv
    JOIN master_item mi ON iv.id_master_item = mi.id
    WHERE iv.id_varian = $id_varian
    ORDER BY mi.nama_item ASC
");
?>
<?php include '../../layout/header.php'; ?>
<?php include '../../layout/sidebar.php'; ?>

<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">Kelola Item Varian</h2>
                <p class="text-gray-400 text-sm mt-1">
                    <a href="index.php" class="text-[#e60000] hover:underline">Varian Jasa</a>
                    <i class="fas fa-chevron-right text-xs mx-2 text-gray-600"></i>
                    <?= htmlspecialchars($varian_data['nama_varian'] ?: $varian_data['nama_jasa'] . ' - ' . $varian_data['nama_merek']) ?>
                </p>
            </div>
            <a href="index.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
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

        <!-- Info Varian -->
        <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">Jasa</p>
                    <p class="text-white font-medium"><?= htmlspecialchars($varian_data['nama_jasa']) ?></p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">Merek</p>
                    <p class="text-white font-medium"><?= htmlspecialchars($varian_data['nama_merek']) ?></p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">CC Range</p>
                    <p class="text-white font-medium"><?= $varian_data['cc_min'] ?> - <?= $varian_data['cc_max'] ?> CC</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wider mb-1">Total Harga</p>
                    <p class="text-green-400 font-bold">Rp <?= number_format($varian_data['total_harga'], 0, ',', '.') ?></p>
                </div>
            </div>
            <?php if ($varian_data['is_custom']): ?>
                <div class="mt-3">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-[#ff6600]/20 text-[#ff6600] rounded-lg text-xs font-medium">
                        <i class="fas fa-pen"></i> Varian Custom — item dapat diedit saat transaksi
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Item -->
            <div class="lg:col-span-1">
                <div class="bg-[#161622] rounded-2xl p-6 border border-[#2a2a3a]">
                    <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-green-400"></i>
                        Tambah Item
                    </h3>
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="tambah">

                        <div class="mb-4">
                            <label for="id_master_item" class="block text-gray-300 text-sm font-medium mb-2">Master Item <span class="text-[#e60000]">*</span></label>
                            <select id="id_master_item" name="id_master_item" required
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white focus:outline-none focus:border-[#e60000]">
                                <option value="">-- Pilih Item --</option>
                                <?php while ($mi = $master_item->fetch_assoc()): ?>
                                    <option value="<?= $mi['id'] ?>">
                                        <?= htmlspecialchars($mi['nama_item']) ?> (<?= htmlspecialchars($mi['satuan']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label for="qty_default" class="block text-gray-300 text-sm font-medium mb-2">Qty Default</label>
                                <input type="number" id="qty_default" name="qty_default" min="1" value="1"
                                    class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white focus:outline-none focus:border-[#e60000]">
                            </div>
                            <div>
                                <label for="harga_default" class="block text-gray-300 text-sm font-medium mb-2">Harga (Rp)</label>
                                <input type="text" id="harga_default" name="harga_default"
                                    class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000] input-rupiah"
                                    placeholder="0">
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full px-4 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-plus mr-2"></i>Tambah Item
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Item -->
            <div class="lg:col-span-2">
                <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#2a2a3a] flex items-center justify-between">
                        <h3 class="text-white font-semibold flex items-center gap-2">
                            <i class="fas fa-list text-blue-400"></i>
                            Daftar Item Penyusun
                        </h3>
                        <span class="text-xs text-gray-500 bg-[#0a0a0f] px-3 py-1 rounded-full">
                            <?= $items->num_rows ?> item
                        </span>
                    </div>

                    <table class="table-custom w-full">
                        <thead>
                            <tr class="bg-[#0a0a0f]">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Item</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Satuan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Qty Default</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Harga Default</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#2a2a3a]">
                            <?php $no = 1; ?>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <tr class="hover:bg-[#0a0a0f] transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-300"><?= $no++ ?></td>
                                    <td class="px-4 py-3 text-sm text-white font-medium">
                                        <?= htmlspecialchars($item['nama_item']) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-400">
                                        <?= htmlspecialchars($item['satuan']) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-300">
                                        <span class="font-mono"><?= $item['qty_default'] ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-green-400 font-medium">
                                        Rp <?= number_format($item['harga_default'], 0, ',', '.') ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition-colors text-xs font-medium btn-edit-item"
                                                data-id="<?= $item['id'] ?>"
                                                data-nama="<?= htmlspecialchars($item['nama_item']) ?>"
                                                data-qty="<?= $item['qty_default'] ?>"
                                                data-harga="<?= $item['harga_default'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="" method="POST" class="inline" onsubmit="return confirmHapus(event, '<?= htmlspecialchars($item['nama_item']) ?>')">
                                                <input type="hidden" name="action" value="hapus">
                                                <input type="hidden" name="id_item" value="<?= $item['id'] ?>">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors text-xs font-medium">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($items->num_rows === 0): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                        <i class="fas fa-cube text-3xl mb-3 block text-gray-600"></i>
                                        Belum ada item penyusun untuk varian ini. <br>
                                        <span class="text-xs text-gray-600">Tambahkan item dari form di sebelah kiri.</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Edit Item -->
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-[#161622] rounded-2xl border border-[#2a2a3a] p-6 w-full max-w-md mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-white font-semibold text-lg flex items-center gap-2">
                <i class="fas fa-edit text-blue-400"></i>
                Edit Item
            </h3>
            <button type="button" id="closeModal" class="text-gray-500 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_item" id="edit_id_item">

            <div class="mb-4">
                <label class="block text-gray-300 text-sm font-medium mb-2">Nama Item</label>
                <p id="edit_nama_item" class="text-white font-medium px-4 py-3 bg-[#0a0a0f] rounded-xl border border-[#2a2a3a]"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <div>
                    <label for="edit_qty_default" class="block text-gray-300 text-sm font-medium mb-2">Qty Default</label>
                    <input type="number" id="edit_qty_default" name="qty_default" min="1"
                        class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white focus:outline-none focus:border-[#e60000]">
                </div>
                <div>
                    <label for="edit_harga_default" class="block text-gray-300 text-sm font-medium mb-2">Harga Default (Rp)</label>
                    <input type="text" id="edit_harga_default" name="harga_default"
                        class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000] input-rupiah"
                        placeholder="0">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="flex-1 px-4 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
                <button type="button" id="cancelEdit"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ===== Rupiah formatter =====
    document.querySelectorAll('.input-rupiah').forEach(input => {
        input.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val) {
                this.value = new Intl.NumberFormat('id-ID').format(parseInt(val));
            } else {
                this.value = '';
            }
        });
    });

    // ===== Edit Modal =====
    const modal = document.getElementById('editModal');
    const closeModal = document.getElementById('closeModal');
    const cancelEdit = document.getElementById('cancelEdit');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModalFn() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    closeModal.addEventListener('click', closeModalFn);
    cancelEdit.addEventListener('click', closeModalFn);
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModalFn();
    });

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModalFn();
        }
    });

    // ===== Fill edit form =====
    document.querySelectorAll('.btn-edit-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nama = this.dataset.nama;
            const qty = this.dataset.qty;
            const harga = this.dataset.harga;

            document.getElementById('edit_id_item').value = id;
            document.getElementById('edit_nama_item').textContent = nama;
            document.getElementById('edit_qty_default').value = qty;
            document.getElementById('edit_harga_default').value = new Intl.NumberFormat('id-ID').format(parseInt(harga));

            openModal();
        });
    });

    // ===== Confirm delete =====
    function confirmHapus(event, namaItem) {
        event.preventDefault();
        const form = event.target.closest('form');
        Swal.fire({
            title: 'Hapus Item?',
            text: `Yakin ingin menghapus item "${namaItem}" dari varian ini?`,
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
                form.submit();
            }
        });
        return false;
    }
</script>

<?php include '../../layout/footer.php'; ?>
