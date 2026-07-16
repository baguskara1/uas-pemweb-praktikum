<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Edit Kendaraan';

$id = $_GET['id'] ?? 0;

// Ambil data kendaraan
$stmt = $conn->prepare("SELECT * FROM kendaraan WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$kendaraan = $result->fetch_assoc();

if (!$kendaraan) {
    header('Location: index.php');
    exit;
}

// Ambil data pelanggan dan merek untuk dropdown
$pelanggan = $conn->query("SELECT id, nama FROM pelanggan ORDER BY nama ASC");
$merek = $conn->query("SELECT id, nama FROM merek ORDER BY nama ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan = $_POST['id_pelanggan'] ?? 0;
    $id_merek = $_POST['id_merek'] ?? 0;
    $plat_no = trim($_POST['plat_no'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $cc = $_POST['cc'] ?? 0;
    $tahun = $_POST['tahun'] ?? date('Y');

    if (empty($id_pelanggan) || empty($id_merek) || empty($plat_no)) {
        $error = 'Pemilik, merek, dan plat nomor wajib diisi.';
    } else {
        $stmt = $conn->prepare("UPDATE kendaraan SET id_pelanggan = ?, id_merek = ?, plat_no = ?, model = ?, cc = ?, tahun = ? WHERE id = ?");
        $stmt->bind_param('iissiii', $id_pelanggan, $id_merek, $plat_no, $model, $cc, $tahun, $id);

        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Gagal memperbarui data: ' . $conn->error;
        }
    }
}

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Edit Kendaraan</h2>
            <span class="text-gray-400 text-sm">
                <i class="far fa-calendar mr-2"></i><?= date('d F Y') ?>
            </span>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <div class="max-w-2xl mx-auto">
            <?php if (isset($error)): ?>
                <div class="mb-4 px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= $error ?>
                </div>
            <?php endif; ?>

            <div class="bg-[#161622] rounded-2xl p-8 border border-[#2a2a3a]">
                <form method="POST">
                    <div class="space-y-6">
                        <div>
                            <label for="id_pelanggan" class="block text-gray-300 text-sm font-medium mb-2">Pemilik <span class="text-[#e60000]">*</span></label>
                            <select id="id_pelanggan" name="id_pelanggan"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                required>
                                <option value="" class="bg-[#161622]">-- Pilih Pelanggan --</option>
                                <?php while ($p = $pelanggan->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>" class="bg-[#161622]"
                                        <?= (($_POST['id_pelanggan'] ?? $kendaraan['id_pelanggan']) == $p['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label for="id_merek" class="block text-gray-300 text-sm font-medium mb-2">Merek <span class="text-[#e60000]">*</span></label>
                            <select id="id_merek" name="id_merek"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                required>
                                <option value="" class="bg-[#161622]">-- Pilih Merek --</option>
                                <?php while ($m = $merek->fetch_assoc()): ?>
                                    <option value="<?= $m['id'] ?>" class="bg-[#161622]"
                                        <?= (($_POST['id_merek'] ?? $kendaraan['id_merek']) == $m['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label for="plat_no" class="block text-gray-300 text-sm font-medium mb-2">Plat Nomor <span class="text-[#e60000]">*</span></label>
                            <input type="text" id="plat_no" name="plat_no" value="<?= htmlspecialchars($_POST['plat_no'] ?? $kendaraan['plat_no']) ?>"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                placeholder="Contoh: B 1234 ABC" required>
                        </div>

                        <div>
                            <label for="model" class="block text-gray-300 text-sm font-medium mb-2">Model</label>
                            <input type="text" id="model" name="model" value="<?= htmlspecialchars($_POST['model'] ?? $kendaraan['model']) ?>"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                placeholder="Contoh: CBR 150R, NMAX, Satria F">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="cc" class="block text-gray-300 text-sm font-medium mb-2">CC</label>
                                <input type="number" id="cc" name="cc" value="<?= htmlspecialchars($_POST['cc'] ?? $kendaraan['cc']) ?>"
                                    class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                    placeholder="Contoh: 150">
                            </div>

                            <div>
                                <label for="tahun" class="block text-gray-300 text-sm font-medium mb-2">Tahun</label>
                                <select id="tahun" name="tahun"
                                    class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                                    <option value="" class="bg-[#161622]">-- Pilih Tahun --</option>
                                    <?php for ($th = date('Y'); $th >= 2000; $th--): ?>
                                        <option value="<?= $th ?>" class="bg-[#161622]"
                                            <?= (($_POST['tahun'] ?? $kendaraan['tahun']) == $th) ? 'selected' : '' ?>>
                                            <?= $th ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-8">
                        <button type="submit"
                            class="px-6 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                        <a href="index.php"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include '../../layout/footer.php'; ?>
