<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$title = 'Tambah Sparepart';

// Ambil data kategori
$kategori = $conn->query("SELECT * FROM kategori_sparepart ORDER BY nama ASC");

function handle_upload($id)
{
    if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['gambar'];

    // Validate error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengupload file (error code: ' . $file['error'] . ').'];
        return false;
    }

    // Validate type
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tipe file tidak didukung. Gunakan JPG, PNG, atau WebP.'];
        return false;
    }

    // Validate size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ukuran file maksimal 2MB.'];
        return false;
    }

    // Generate unique filename
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'sp_' . $id . '_' . time() . '.' . $ext;
    $destination = 'assets/img/sparepart/' . $filename;

    if (move_uploaded_file($file['tmp_name'], '../../' . $destination)) {
        return $destination;
    }

    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menyimpan file.'];
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $kode        = trim($_POST['kode'] ?? '');
    $nama        = trim($_POST['nama'] ?? '');
    $stok        = (int)($_POST['stok'] ?? 0);
    $harga_beli  = (int)str_replace('.', '', $_POST['harga_beli'] ?? 0);
    $harga_jual  = (int)str_replace('.', '', $_POST['harga_jual'] ?? 0);

    if (empty($nama) || empty($id_kategori)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nama dan kategori harus diisi.'];
    } else {
        $stmt = $conn->prepare("INSERT INTO sparepart (id_kategori, kode, nama, stok, harga_beli, harga_jual) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiii", $id_kategori, $kode, $nama, $stok, $harga_beli, $harga_jual);

        if ($stmt->execute()) {
            $insert_id = $conn->insert_id;

            // Handle file upload
            $upload_result = handle_upload($insert_id);
            if ($upload_result === false) {
                // Upload failed — still save the sparepart, just warn
                $warn = ' Sparepart tersimpan tanpa foto.';
            } elseif ($upload_result !== null) {
                $conn->query("UPDATE sparepart SET gambar = '$upload_result' WHERE id = $insert_id");
            }

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Sparepart berhasil ditambahkan.' . ($warn ?? '')];
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan sparepart: ' . $conn->error];
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
            <h2 class="text-xl font-bold text-white">Tambah Sparepart</h2>
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
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kategori -->
                        <div class="md:col-span-2">
                            <label for="id_kategori" class="block text-gray-300 text-sm font-medium mb-2">Kategori <span class="text-[#ccff00]">*</span></label>
                            <select id="id_kategori" name="id_kategori" required
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white focus:outline-none focus:border-[#ccff00]">
                                <option value="">-- Pilih Kategori --</option>
                                <?php while ($kat = $kategori->fetch_assoc()): ?>
                                    <option value="<?= $kat['id'] ?>" <?= ($_POST['id_kategori'] ?? '') == $kat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kat['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Kode -->
                        <div>
                            <label for="kode" class="block text-gray-300 text-sm font-medium mb-2">Kode Sparepart</label>
                            <input type="text" id="kode" name="kode"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00]"
                                placeholder="Mis: OLI-001"
                                value="<?= htmlspecialchars($_POST['kode'] ?? '') ?>">
                        </div>

                        <!-- Nama -->
                        <div>
                            <label for="nama" class="block text-gray-300 text-sm font-medium mb-2">Nama Sparepart <span class="text-[#ccff00]">*</span></label>
                            <input type="text" id="nama" name="nama" required
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00]"
                                placeholder="Nama sparepart"
                                value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                        </div>

                        <!-- Stok -->
                        <div>
                            <label for="stok" class="block text-gray-300 text-sm font-medium mb-2">Stok</label>
                            <input type="number" id="stok" name="stok" min="0"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00]"
                                placeholder="0"
                                value="<?= htmlspecialchars($_POST['stok'] ?? 0) ?>">
                        </div>

                        <!-- Harga Beli -->
                        <div>
                            <label for="harga_beli" class="block text-gray-300 text-sm font-medium mb-2">Harga Beli (Rp)</label>
                            <input type="text" id="harga_beli" name="harga_beli"
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00] input-rupiah"
                                placeholder="0"
                                value="<?= isset($_POST['harga_beli']) ? number_format((int)$_POST['harga_beli'], 0, ',', '.') : '' ?>">
                        </div>

                        <!-- Harga Jual -->
                        <div>
                            <label for="harga_jual" class="block text-gray-300 text-sm font-medium mb-2">Harga Jual (Rp) <span class="text-[#ccff00]">*</span></label>
                            <input type="text" id="harga_jual" name="harga_jual" required
                                class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00] input-rupiah"
                                placeholder="0"
                                value="<?= isset($_POST['harga_jual']) ? number_format((int)$_POST['harga_jual'], 0, ',', '.') : '' ?>">
                        </div>

                        <!-- Foto -->
                        <div class="md:col-span-2">
                            <label class="block text-gray-300 text-sm font-medium mb-2">Foto Sparepart</label>
                            <div class="relative">
                                <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp"
                                    class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#ccff00] file:text-white hover:file:bg-red-600 cursor-pointer">
                            </div>
                            <p class="text-gray-500 text-xs mt-1">Format: JPG, PNG, WebP. Maks 2MB</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-8">
                        <button type="submit"
                            class="px-6 py-3 bg-[#ccff00] hover:bg-[#ff0066] text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan
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
    // Format rupiah on input
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
</script>

<?php include '../../layout/footer.php'; ?>
