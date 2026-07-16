<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Tambah Merek Motor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');

    if (empty($nama)) {
        $error = 'Nama merek tidak boleh kosong!';
    } else {
        $stmt = $conn->prepare("INSERT INTO merek (nama) VALUES (?)");
        $stmt->bind_param("s", $nama);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Gagal menyimpan data!';
        }
    }
}

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <h2 class="text-xl font-bold text-white">Tambah Merek Motor</h2>
    </header>
    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <div class="max-w-2xl mx-auto">
            <div class="bg-[#161622] rounded-2xl p-8 border border-[#2a2a3a]">
                <?php if (isset($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Nama Merek</label>
                        <input type="text" name="nama" required
                            class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                            placeholder="Contoh: Honda, Yamaha" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="px-6 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan
                        </button>
                        <a href="index.php"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors flex items-center">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include '../../layout/footer.php'; ?>
