<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/pengaturan.php';
cek_login();

$title = 'Pengaturan';

// Handle settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_settings'])) {
    foreach ($_POST as $kunci => $nilai) {
        if (in_array($kunci, ['simpan_settings', 'backup', 'restore'])) continue;
        $conn->query("UPDATE pengaturan SET nilai = '" . $conn->real_escape_string($nilai) . "' WHERE kunci = '$kunci'");
    }
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pengaturan berhasil disimpan!'];
    header('Location: index.php');
    exit;
}

// Handle backup
if (isset($_POST['backup'])) {
    $sql = backupDatabase($conn);
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="backup_bengkel_racing_' . date('Ymd_His') . '.sql"');
    echo $sql;
    exit;
}

// Handle restore
if (isset($_POST['restore']) && isset($_FILES['file_sql']) && $_FILES['file_sql']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['file_sql']['tmp_name'];
    $content = file_get_contents($tmp);
    $queries = explode(';', $content);
    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query) && stripos($query, 'INSERT') === 0) {
            if ($conn->query($query)) $count++;
        }
    }
    $_SESSION['flash'] = ['type' => 'success', 'message' => "Restore selesai! $count query dieksekusi."];
    header('Location: index.php');
    exit;
}

$settings = getAllSettings();
$groups = ['umum' => 'Umum', 'nota' => 'PDF Nota', 'wa' => 'WhatsApp', 'garansi' => 'Garansi', 'sistem' => 'Sistem'];

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-white">
                <i class="fas fa-cog text-[#ccff00] mr-2"></i>Pengaturan
            </h2>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto">
        <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ icon: '<?= $flash['type'] ?>', title: '<?= $flash['message'] ?>', timer: 3000, showConfirmButton: false, background: '#161622', color: '#fff', iconColor: '<?= $flash['type'] === 'success' ? '#22c55e' : '#ccff00' ?>', toast: true, position: 'top-end' });
        });
        </script>
        <?php endif; ?>

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Form Settings -->
            <form method="POST" class="bg-[#161622] rounded-2xl p-8 border border-[#2a2a3a] space-y-8">
                <?php foreach ($groups as $grup_key => $grup_label): ?>
                <?php $items = array_filter($settings, fn($s) => $s['grup'] === $grup_key); ?>
                <?php if (empty($items)) continue; ?>
                <div>
                    <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                        <?php $icons = ['umum' => 'fa-building', 'nota' => 'fa-file-pdf', 'wa' => 'fa-whatsapp', 'garansi' => 'fa-shield', 'sistem' => 'fa-gear']; ?>
                        <i class="fas <?= $icons[$grup_key] ?? 'fa-cog' ?> text-[#ccff00]"></i>
                        <?= $grup_label ?>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($items as $s): ?>
                        <div class="<?= in_array($s['kunci'], ['alamat', 'nota_footer']) ? 'md:col-span-2' : '' ?>">
                            <label class="block text-gray-400 text-sm font-medium mb-1.5">
                                <?= htmlspecialchars($s['deskripsi'] ?: $s['kunci']) ?>
                            </label>
                            <?php if (in_array($s['kunci'], ['alamat', 'nota_footer'])): ?>
                            <textarea name="<?= $s['kunci'] ?>" rows="2"
                                class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#ccff00]"><?= htmlspecialchars($s['nilai']) ?></textarea>
                            <?php elseif ($s['kunci'] === 'timezone'): ?>
                            <select name="<?= $s['kunci'] ?>"
                                class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#ccff00]">
                                <option value="Asia/Jakarta" <?= $s['nilai'] === 'Asia/Jakarta' ? 'selected' : '' ?>>WIB (Asia/Jakarta)</option>
                                <option value="Asia/Makassar" <?= $s['nilai'] === 'Asia/Makassar' ? 'selected' : '' ?>>WITA (Asia/Makassar)</option>
                                <option value="Asia/Jayapura" <?= $s['nilai'] === 'Asia/Jayapura' ? 'selected' : '' ?>>WIT (Asia/Jayapura)</option>
                            </select>
                            <?php elseif ($s['kunci'] === 'fonnte_token'): ?>
                            <input type="password" name="<?= $s['kunci'] ?>" value="<?= htmlspecialchars($s['nilai']) ?>"
                                class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#ccff00]" placeholder="Token API Fonnte">
                            <?php else: ?>
                            <input type="text" name="<?= $s['kunci'] ?>" value="<?= htmlspecialchars($s['nilai']) ?>"
                                class="w-full px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#ccff00]">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <button type="submit" name="simpan_settings" value="1"
                    class="px-6 py-3 bg-[#ccff00] hover:bg-[#ff0066] text-white font-semibold rounded-xl transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                </button>
            </form>

            <!-- Backup & Restore -->
            <div class="bg-[#161622] rounded-2xl p-8 border border-[#2a2a3a]">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-database text-[#ccff00]"></i>Backup & Restore Database
                </h3>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <form method="POST">
                        <button type="submit" name="backup" value="1"
                            class="w-full h-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-download mr-2"></i>Download Backup SQL
                        </button>
                    </form>
                    <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2">
                        <input type="file" name="file_sql" accept=".sql" required
                            class="flex-1 w-full min-w-0 px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#ccff00] file:text-white hover:file:bg-red-600 cursor-pointer">
                        <button type="submit" name="restore" value="1"
                            class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors text-sm whitespace-nowrap shrink-0"
                            onclick="return confirm('Yakin restore database? Data yang ada akan dihapus dan diganti!')">
                            <i class="fas fa-upload mr-1"></i>Restore
                        </button>
                    </form>
                </div>
                <p class="text-gray-500 text-xs mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Backup: download seluruh struktur + data. Restore: upload file .sql (hanya menjalankan INSERT).
                </p>
            </div>
        </div>
    </main>
</div>
<?php include '../../layout/footer.php'; ?>
