<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Tambah Pelanggan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_telp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($nama)) {
        $error = 'Nama pelanggan wajib diisi.';
    } else {
        $stmt = $conn->prepare("INSERT INTO pelanggan (nama, no_telp, alamat) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $nama, $no_telp, $alamat);

        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Gagal menyimpan data: ' . $conn->error;
        }
    }
}

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Tambah Pelanggan</h2>
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
                            <label for="nama" class="block text-gray-300 text-sm font-medium mb-2">Nama Pelanggan <span class="text-[#e60000]">*</span></label>
                            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                placeholder="Masukkan nama pelanggan" required>
                        </div>

                        <div>
                            <label for="no_telp" class="block text-gray-300 text-sm font-medium mb-2">No. Telepon</label>
                            <input type="text" id="no_telp" name="no_telp" value="<?= htmlspecialchars($_POST['no_telp'] ?? '') ?>"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                placeholder="Masukkan no. telepon">
                        </div>

                        <div>
                            <label for="alamat" class="block text-gray-300 text-sm font-medium mb-2">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="4"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                                placeholder="Masukkan alamat"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-8">
                        <button type="submit"
                            class="px-6 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan
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
