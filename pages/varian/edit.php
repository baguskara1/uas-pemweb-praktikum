<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Edit Varian Jasa';

$id = (int)($_GET['id'] ?? 0);

// Ambil data varian
$varian = $conn->query("SELECT * FROM varian_jasa WHERE id = $id");
if ($varian->num_rows === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Varian tidak ditemukan.'];
    header('Location: index.php');
    exit;
}
$data = $varian->fetch_assoc();

// Ambil data jasa dan merek
$daftar_jasa  = $conn->query("SELECT * FROM jasa ORDER BY nama ASC");
$daftar_merek = $conn->query("SELECT * FROM merek ORDER BY nama ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jasa     = (int)($_POST['id_jasa'] ?? 0);
    $id_merek    = (int)($_POST['id_merek'] ?? 0);
    $cc_min      = (int)($_POST['cc_min'] ?? 0);
    $cc_max      = (int)($_POST['cc_max'] ?? 0);
    $nama_varian = trim($_POST['nama_varian'] ?? '');
    $total_harga = (int)str_replace('.', '', $_POST['total_harga'] ?? 0);
    $is_custom   = isset($_POST['is_custom']) ? 1 : 0;

    if (empty($id_jasa) || empty($id_merek)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Jasa dan merek harus dipilih.'];
    } else {
        $stmt = $conn->prepare("UPDATE varian_jasa SET id_jasa = ?, id_merek = ?, cc_min = ?, cc_max = ?, nama_varian = ?, total_harga = ?, is_custom = ? WHERE id = ?");
        $stmt->bind_param("iiiisiii", $id_jasa, $id_merek, $cc_min, $cc_max, $nama_varian, $total_harga, $is_custom, $id);

        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Varian jasa berhasil diperbarui.'];
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal memperbarui varian: ' . $conn->error];
        }
        $stmt->close();
    }
}
?>
<?php include '../../layout/header.php'; ?>
<?php include '../../layout/sidebar.php'; ?>

<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Edit Varian Jasa</h2>
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
                        iconColor: '<?= $flash['type'] === 'success' ? '#22c55e' : '#ccff00' ?>',
                        toast: true,
                        position: 'top-end'
                    });
                });
            </script>
        <?php endif; ?>

        <div class="max-w-2xl mx-auto">
            <div class="bg-[#161622] rounded-2xl p-8 border border-[#2a2a3a]">
                <form action="" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Jasa -->
                        <div>
                            <label for="id_jasa" class="block text-gray-300 text-sm font-medium mb-2">Jasa <span class="text-[#ccff00]">*</span></label>
                            <select id="id_jasa" name="id_jasa" required
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white focus:outline-none focus:border-[#ccff00]">
                                <option value="">-- Pilih Jasa --</option>
                                <?php while ($j = $daftar_jasa->fetch_assoc()): ?>
                                    <option value="<?= $j['id'] ?>" <?= $data['id_jasa'] == $j['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($j['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Merek -->
                        <div>
                            <label for="id_merek" class="block text-gray-300 text-sm font-medium mb-2">Merek Motor <span class="text-[#ccff00]">*</span></label>
                            <select id="id_merek" name="id_merek" required
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white focus:outline-none focus:border-[#ccff00]">
                                <option value="">-- Pilih Merek --</option>
                                <?php while ($m = $daftar_merek->fetch_assoc()): ?>
                                    <option value="<?= $m['id'] ?>" <?= $data['id_merek'] == $m['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Nama Varian -->
                        <div class="md:col-span-2">
                            <label for="nama_varian" class="block text-gray-300 text-sm font-medium mb-2">Nama Varian</label>
                            <input type="text" id="nama_varian" name="nama_varian"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00]"
                                placeholder="Mis: Paket A, Standar, Racing"
                                value="<?= htmlspecialchars($data['nama_varian'] ?? '') ?>">
                        </div>

                        <!-- CC Min -->
                        <div>
                            <label for="cc_min" class="block text-gray-300 text-sm font-medium mb-2">CC Minimum</label>
                            <input type="number" id="cc_min" name="cc_min" min="0"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00]"
                                placeholder="0"
                                value="<?= $data['cc_min'] ?>">
                        </div>

                        <!-- CC Max -->
                        <div>
                            <label for="cc_max" class="block text-gray-300 text-sm font-medium mb-2">CC Maksimum</label>
                            <input type="number" id="cc_max" name="cc_max" min="0"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00]"
                                placeholder="0"
                                value="<?= $data['cc_max'] ?>">
                        </div>

                        <!-- Total Harga -->
                        <div>
                            <label for="total_harga" class="block text-gray-300 text-sm font-medium mb-2">Total Harga (Rp) <span class="text-[#ccff00]">*</span></label>
                            <input type="text" id="total_harga" name="total_harga" required
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00] input-rupiah"
                                placeholder="0"
                                value="<?= number_format($data['total_harga'], 0, ',', '.') ?>">
                        </div>

                        <!-- Is Custom -->
                        <div class="flex items-end pb-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_custom" value="1" <?= $data['is_custom'] ? 'checked' : '' ?>
                                    class="w-5 h-5 bg-[#0a0a0f] border-[#2a2a3a] text-[#ccff00] rounded focus:ring-[#ccff00] focus:ring-offset-0">
                                <span class="text-gray-300 text-sm font-medium">Varian Custom (bisa diedit saat transaksi)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-8">
                        <button type="submit"
                            class="px-6 py-3 bg-[#ccff00] hover:bg-[#ff0066] text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                        <a href="index.php"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    document.querySelectorAll('.input-rupiah').forEach(input => {
        input.addEventListener('click', function() {
            // Select all text on click for easy replacement
            this.select();
        });
        input.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val) {
                this.value = new Intl.NumberFormat('id-ID').format(parseInt(val));
            } else {
                this.value = '';
            }
        });
    });
</script>

<?php include '../../layout/footer.php'; ?>
