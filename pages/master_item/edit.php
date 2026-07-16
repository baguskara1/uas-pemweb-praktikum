<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Edit Produk';

$id = (int)$_GET['id'];

// Fetch current data
$data = $conn->query("SELECT * FROM master_item WHERE id = $id");
if (!$data || $data->num_rows === 0) {
    echo "<script>Swal.fire({icon:'error',title:'Error',text:'Data item tidak ditemukan'}).then(()=>{window.location='index.php'})</script>";
    exit;
}
$item = $data->fetch_assoc();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_item = trim($_POST['nama_item']);
    $satuan = trim($_POST['satuan']);

    if ($nama_item === '') {
        $error = 'Nama item tidak boleh kosong';
    } else {
        $stmt = $conn->prepare("UPDATE master_item SET nama_item = ?, satuan = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama_item, $satuan, $id);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Gagal memperbarui data: ' . $conn->error;
        }
    }

    // Update variable for re-display
    $item['nama_item'] = $nama_item;
    $item['satuan'] = $satuan;
}

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>

<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Edit Produk</h2>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <?php if ($error): ?>
            <script>Swal.fire({icon:'error',title:'Gagal',text:<?= json_encode($error) ?>})</script>
        <?php endif; ?>

        <div class="max-w-2xl mx-auto">
            <div class="bg-[#161622] rounded-2xl p-8 border border-[#2a2a3a]">
                <form method="POST">
                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Nama Item</label>
                        <input type="text" name="nama_item" required
                               value="<?= htmlspecialchars($item['nama_item']) ?>"
                               class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                               placeholder="Masukkan nama item">
                    </div>

                    <div class="mb-8">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Satuan</label>
                        <input type="text" name="satuan" required
                               value="<?= htmlspecialchars($item['satuan']) ?>"
                               class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#e60000]"
                               placeholder="Contoh: pcs, liter, box">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-6 py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                        <a href="index.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include '../../layout/footer.php'; ?>
