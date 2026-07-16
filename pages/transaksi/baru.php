<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/transaksi.php';
cek_login();

$title = 'POS Baru';
?>
<script id="pos-data" type="application/json"><?php
$pelanggan_data = [];
$q = $conn->query("SELECT * FROM pelanggan ORDER BY nama ASC");
while ($r = $q->fetch_assoc()) {
    $pelanggan_data[] = [
        'id' => (int)$r['id'],
        'text' => $r['nama'],
        'subtext' => $r['no_telp'] ?? ''
    ];
}

$selected_pelanggan = (int)($_GET['pelanggan_id'] ?? $_POST['pelanggan_id'] ?? 0);
$kendaraan_data = [];
if ($selected_pelanggan) {
    $q = $conn->query("SELECT k.*, m.nama AS merek FROM kendaraan k JOIN merek m ON k.id_merek = m.id WHERE k.id_pelanggan = $selected_pelanggan ORDER BY k.plat_no ASC");
    while ($r = $q->fetch_assoc()) {
        $kendaraan_data[] = [
            'id' => (int)$r['id'],
            'text' => ($r['model'] ?? '') . ' - ' . $r['merek'],
            'subtext' => $r['plat_no']
        ];
    }
}

echo json_encode([
    'pelanggan' => $pelanggan_data,
    'kendaraan' => $kendaraan_data,
    'pelanggan_id' => $selected_pelanggan,
    'kendaraan_id' => (int)($_GET['kendaraan_id'] ?? $_POST['kendaraan_id'] ?? 0),
]);
?></script>
<?php

$selected_pelanggan = (int)($_GET['pelanggan_id'] ?? $_POST['pelanggan_id'] ?? 0);
$kendaraan_data = [];
if ($selected_pelanggan) {
    $q = $conn->query("SELECT k.*, m.nama AS merek FROM kendaraan k JOIN merek m ON k.id_merek = m.id WHERE k.id_pelanggan = $selected_pelanggan ORDER BY k.plat_no ASC");
    while ($r = $q->fetch_assoc()) {
        $kendaraan_data[] = [
            'id' => (int)$r['id'],
            'text' => ($r['model'] ?? '') . ' - ' . $r['merek'],
            'subtext' => $r['plat_no']
        ];
    }
}

$kategori_data = [];
$q = $conn->query("SELECT * FROM kategori_jasa ORDER BY id ASC");
while ($r = $q->fetch_assoc()) {
    $kategori_data[] = [
        'id' => (int)$r['id'],
        'text' => $r['nama'],
        'subtext' => $r['icon'] ?? ''
    ];
}

// Initialize cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = ['jasa' => [], 'sparepart' => []];
}
$cart = &$_SESSION['cart'];

// Handle cart actions
$action = $_POST['action'] ?? '';

if ($action === 'add_jasa') {
    $id_varian = (int)($_POST['id_varian'] ?? 0);
    $q = $conn->query("SELECT v.*, j.nama AS nama_jasa, k.nama AS kategori, k.punya_breakdown
                        FROM varian_jasa v
                        JOIN jasa j ON v.id_jasa = j.id
                        JOIN kategori_jasa k ON j.id_kategori = k.id
                        WHERE v.id = $id_varian");
    if ($v = $q->fetch_assoc()) {
        $item = [
            'id_varian' => $v['id'],
            'nama_jasa' => $v['nama_varian'] ?: $v['nama_jasa'],
            'total_harga' => (int)$v['total_harga'],
            'catatan' => $_POST['catatan'] ?? '',
            'items' => [],
        ];

        // If punya_breakdown, load default items
        if ($v['punya_breakdown']) {
            $iq = $conn->query("SELECT mi.*, iv.qty_default, iv.harga_default, iv.id AS item_varian_id
                                FROM item_varian iv
                                JOIN master_item mi ON iv.id_master_item = mi.id
                                WHERE iv.id_varian = $id_varian");
            while ($i = $iq->fetch_assoc()) {
                $item['items'][] = [
                    'id_master_item' => $i['id'],
                    'nama_item' => $i['nama_item'],
                    'qty' => (int)$i['qty_default'],
                    'harga_satuan' => (int)$i['harga_default'],
                ];
            }
        }

        $cart['jasa'][] = $item;
    }
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    header("Location: baru.php?pelanggan_id=$pelanggan_id&kendaraan_id=$kendaraan_id");
    exit;
}

if ($action === 'add_sparepart') {
    $id_sparepart = (int)($_POST['id_sparepart'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $q = $conn->query("SELECT * FROM sparepart WHERE id = $id_sparepart");
    if ($s = $q->fetch_assoc()) {
        $cart['sparepart'][] = [
            'id_sparepart' => $s['id'],
            'nama' => $s['nama'],
            'harga_jual' => (int)$s['harga_jual'],
            'qty' => $qty,
        ];
    }
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    header("Location: baru.php?pelanggan_id=$pelanggan_id&kendaraan_id=$kendaraan_id");
    exit;
}

if ($action === 'remove_jasa') {
    $idx = (int)($_POST['index'] ?? 0);
    if (isset($cart['jasa'][$idx])) {
        array_splice($cart['jasa'], $idx, 1);
    }
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    header("Location: baru.php?pelanggan_id=$pelanggan_id&kendaraan_id=$kendaraan_id");
    exit;
}

if ($action === 'remove_sparepart') {
    $idx = (int)($_POST['index'] ?? 0);
    if (isset($cart['sparepart'][$idx])) {
        array_splice($cart['sparepart'], $idx, 1);
    }
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    header("Location: baru.php?pelanggan_id=$pelanggan_id&kendaraan_id=$kendaraan_id");
    exit;
}

if ($action === 'update_jasa_item') {
    $j_idx = (int)($_POST['j_idx'] ?? 0);
    $i_idx = (int)($_POST['i_idx'] ?? 0);
    if (isset($cart['jasa'][$j_idx]['items'][$i_idx])) {
        $cart['jasa'][$j_idx]['items'][$i_idx]['qty'] = max(1, (int)($_POST['qty'] ?? 1));
        $cart['jasa'][$j_idx]['items'][$i_idx]['harga_satuan'] = max(0, (int)($_POST['harga_satuan'] ?? 0));
        // Recalculate total
        $total = 0;
        foreach ($cart['jasa'][$j_idx]['items'] as $it) {
            $total += $it['harga_satuan'] * $it['qty'];
        }
        $cart['jasa'][$j_idx]['total_harga'] = $total;
    }
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    header("Location: baru.php?pelanggan_id=$pelanggan_id&kendaraan_id=$kendaraan_id");
    exit;
}

if ($action === 'add_custom_item') {
    $j_idx = (int)($_POST['j_idx'] ?? 0);
    if (isset($cart['jasa'][$j_idx])) {
        $nama_item = trim($_POST['nama_item'] ?? '');
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $harga = max(0, (int)($_POST['harga_satuan'] ?? 0));
        if ($nama_item) {
            $cart['jasa'][$j_idx]['items'][] = [
                'id_master_item' => 0,
                'nama_item' => $nama_item,
                'qty' => $qty,
                'harga_satuan' => $harga,
            ];
        }
    }
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    header("Location: baru.php?pelanggan_id=$pelanggan_id&kendaraan_id=$kendaraan_id");
    exit;
}

if ($action === 'clear_cart') {
    $_SESSION['cart'] = ['jasa' => [], 'sparepart' => []];
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    header("Location: baru.php?pelanggan_id=$pelanggan_id&kendaraan_id=$kendaraan_id");
    exit;
}

if ($action === 'simpan') {
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);
    $kendaraan_id = (int)($_POST['kendaraan_id'] ?? 0);
    $catatan = $_POST['catatan'] ?? '';

    if ($pelanggan_id && $kendaraan_id && (!empty($cart['jasa']) || !empty($cart['sparepart']))) {
        try {
            $transaksi_id = simpan_transaksi($conn, $_SESSION['user_id'], $pelanggan_id, $kendaraan_id, $cart, $catatan);
            $_SESSION['cart'] = ['jasa' => [], 'sparepart' => []];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Transaksi berhasil disimpan!'];
            header("Location: detail.php?id=$transaksi_id");
            exit;
        } catch (Exception $e) {
            $error = 'Gagal menyimpan transaksi: ' . $e->getMessage();
        }
    } else {
        $error = 'Lengkapi pelanggan, kendaraan, dan minimal 1 jasa/sparepart!';
    }
}

// Hitung total cart
$cart_total = 0;
foreach ($cart['jasa'] as $j) {
    $cart_total += $j['total_harga'];
}
foreach ($cart['sparepart'] as $s) {
    $cart_total += $s['harga_jual'] * $s['qty'];
}

include '../../layout/header.php';
include '../../layout/sidebar.php';
?>
<div class="flex-1 flex flex-col w-full min-w-0" x-data="{
    activeTab: 'jasa',
    searchJasa: '',
    searchSparepart: '',
    selectedKategori: 0,
    showPelangganModal: false,
    showSparepartModal: false
}">
    <header class="bg-[#0d0d1a] border-b border-[#2a2a3a] px-4 md:px-6 py-4 pl-16 md:pl-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-white">
                <i class="fas fa-cart-plus text-[#e60000] mr-2"></i>POS Baru
            </h2>
            <div class="flex items-center gap-3">
                <span class="text-gray-400 text-sm"><?= date('d F Y H:i') ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-6 overflow-y-auto flex flex-col md:flex-row gap-6">
        <!-- LEFT PANEL: Selection -->
        <div class="w-full md:w-3/5 flex flex-col gap-4 md:pr-2 min-h-0">
            <!-- Customer + Vehicle Selection -->
            <div class="bg-[#161622] rounded-xl p-5 border border-[#2a2a3a]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase tracking-wider mb-2">Pelanggan</label>
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: <?= $selected_pelanggan ?>,
                            selectedText: '',
                            options: [],
                            init() {
                                const d = JSON.parse(document.getElementById('pos-data').textContent);
                                this.options = d.pelanggan;
                                const m = this.options.find(o => o.id === this.selectedId);
                                this.selectedText = m ? m.text + (m.subtext ? ' - ' + m.subtext : '') : '-- Cari Pelanggan --';
                            },
                            get filteredOptions() {
                                if (!this.search) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o => o.text.toLowerCase().includes(q) || (o.subtext && o.subtext.toLowerCase().includes(q)));
                            },
                            pilih(opt) {
                                if (opt.id) window.location.href = window.location.href.split('?')[0] + '?pelanggan_id=' + opt.id + '&kendaraan_id=0';
                            }
                        }" @click.away="open = false" class="relative">
                            <button @click="open = !open" type="button"
                                class="w-full flex items-center justify-between px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm hover:border-[#e60000]/50 transition-colors">
                                <span class="truncate" :class="selectedId ? 'text-white' : 'text-gray-500'" x-text="selectedText"></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs ml-2 transition-transform" :class="open && 'rotate-180'"></i>
                            </button>
                            <div x-show="open" x-cloak
                                class="absolute z-50 mt-1 w-full bg-[#1a1a2e] border border-[#2a2a3a] rounded-xl shadow-2xl overflow-hidden">
                                <div class="p-2 border-b border-[#2a2a3a]">
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                                        <input x-model="search" type="text" placeholder="Cari nama / no telp..."
                                            class="w-full px-3 py-2 pl-8 bg-[#0a0a0f] border border-[#2a2a3a] rounded-lg text-white text-xs placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                                    </div>
                                </div>
                                <div class="max-h-52 overflow-y-auto">
                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <button @click="pilih(opt)" type="button"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-[#e60000]/10 transition-colors border-b border-[#2a2a3a]/50 last:border-0"
                                            :class="selectedId === opt.id ? 'bg-[#e60000]/10 text-[#e60000]' : 'text-gray-300 hover:text-white'">
                                            <div class="flex items-center justify-between">
                                                <span x-text="opt.text" class="font-medium"></span>
                                                <span x-text="opt.subtext" class="text-gray-500 text-xs"></span>
                                            </div>
                                        </button>
                                    </template>
                                    <div x-show="filteredOptions.length === 0"
                                        class="px-4 py-6 text-gray-500 text-xs text-center">
                                        <i class="fas fa-user-slash text-2xl mb-2 block opacity-30"></i>
                                        Pelanggan tidak ditemukan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase tracking-wider mb-2">Kendaraan</label>
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: <?= (int)($_GET['kendaraan_id'] ?? $_POST['kendaraan_id'] ?? 0) ?>,
                            selectedText: '',
                            options: [],
                            init() {
                                const d = JSON.parse(document.getElementById('pos-data').textContent);
                                this.options = d.kendaraan;
                                const m = this.options.find(o => o.id === this.selectedId);
                                this.selectedText = m ? m.text + (m.subtext ? ' - ' + m.subtext : '') : '-- Pilih Kendaraan --';
                            },
                            get filteredOptions() {
                                if (!this.search) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o => o.text.toLowerCase().includes(q) || (o.subtext && o.subtext.toLowerCase().includes(q)));
                            },
                            pilih(opt) {
                                if (opt.id) { const url = new URL(window.location); url.searchParams.set('kendaraan_id', opt.id); window.location.href = url.toString(); }
                            }
                        }" @click.away="open = false" class="relative">
                            <button @click="open = !open" type="button"
                                class="w-full flex items-center justify-between px-4 py-2.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-sm hover:border-[#e60000]/50 transition-colors"
                                :class="!<?= $selected_pelanggan ?> ? 'opacity-50 cursor-not-allowed' : ''">
                                <span class="truncate" :class="selectedId ? 'text-white' : 'text-gray-500'" x-text="selectedText"></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs ml-2 transition-transform" :class="open && 'rotate-180'"></i>
                            </button>
                            <?php if ($selected_pelanggan): ?>
                            <div x-show="open" x-cloak
                                class="absolute z-50 mt-1 w-full bg-[#1a1a2e] border border-[#2a2a3a] rounded-xl shadow-2xl overflow-hidden">
                                <div class="p-2 border-b border-[#2a2a3a]">
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                                        <input x-model="search" type="text" placeholder="Cari kendaraan..."
                                            class="w-full px-3 py-2 pl-8 bg-[#0a0a0f] border border-[#2a2a3a] rounded-lg text-white text-xs placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                                    </div>
                                </div>
                                <div class="max-h-52 overflow-y-auto">
                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                        <button @click="pilih(opt)" type="button"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-[#e60000]/10 transition-colors border-b border-[#2a2a3a]/50 last:border-0"
                                            :class="selectedId === opt.id ? 'bg-[#e60000]/10 text-[#e60000]' : 'text-gray-300 hover:text-white'">
                                            <div class="flex items-center justify-between">
                                                <span x-text="opt.text" class="font-medium"></span>
                                                <span x-text="opt.subtext" class="text-gray-500 text-xs font-mono"></span>
                                            </div>
                                        </button>
                                    </template>
                                    <div x-show="filteredOptions.length === 0"
                                        class="px-4 py-6 text-gray-500 text-xs text-center">
                                        <i class="fas fa-motorcycle text-2xl mb-2 block opacity-30"></i>
                                        Belum ada kendaraan
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="absolute z-50 mt-1 w-full bg-[#1a1a2e] border border-[#2a2a3a] rounded-xl shadow-2xl px-4 py-3 text-gray-500 text-xs text-center"
                                x-show="open" x-cloak>
                                Pilih pelanggan terlebih dahulu
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="bg-[#161622] rounded-xl border border-[#2a2a3a] overflow-hidden flex flex-col flex-1 min-h-0">
                <div class="flex border-b border-[#2a2a3a]">
                    <button @click="activeTab = 'jasa'" :class="activeTab === 'jasa' ? 'bg-[#e60000]/10 text-[#e60000] border-b-2 border-[#e60000]' : 'text-gray-400 hover:text-white'" class="px-6 py-3 text-sm font-medium transition-colors">
                        <i class="fas fa-wrench mr-2"></i>Jasa
                    </button>
                    <button @click="activeTab = 'sparepart'" :class="activeTab === 'sparepart' ? 'bg-[#e60000]/10 text-[#e60000] border-b-2 border-[#e60000]' : 'text-gray-400 hover:text-white'" class="px-6 py-3 text-sm font-medium transition-colors">
                        <i class="fas fa-oil-can mr-2"></i>Sparepart
                    </button>
                </div>

                <!-- Tab: JASA -->
                <div x-show="activeTab === 'jasa'" class="p-4 space-y-3 overflow-y-auto flex-1 min-h-0">
                    <div class="flex gap-3 flex-col sm:flex-row shrink-0">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input type="text" x-model="searchJasa" placeholder="Cari jasa..."
                                class="w-full px-4 py-2 pl-8 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                        </div>
                        <select x-model="selectedKategori"
                            class="px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm focus:outline-none focus:border-[#e60000] appearance-none cursor-pointer">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($kategori_data as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['text']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <?php
                        $jasa_query = "SELECT v.*, j.nama AS nama_jasa, j.id_kategori, k.nama AS kategori, k.icon, k.punya_breakdown
                                        FROM varian_jasa v
                                        JOIN jasa j ON v.id_jasa = j.id
                                        JOIN kategori_jasa k ON j.id_kategori = k.id
                                        ORDER BY k.id ASC, j.nama ASC, v.nama_varian ASC";
                        $jasas = $conn->query($jasa_query);
                        $current_kategori = '';
                        while ($j = $jasas->fetch_assoc()):
                            if ($j['kategori'] !== $current_kategori):
                                $current_kategori = $j['kategori'];
                        ?>
                        <div class="text-xs uppercase tracking-wider text-gray-500 mt-3 mb-1 flex items-center gap-2">
                            <i class="fas <?= $j['icon'] ?? 'fa-wrench' ?> text-[#e60000]"></i>
                            <?= htmlspecialchars($current_kategori) ?>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between px-4 py-2.5 bg-[#0a0a0f] rounded-xl border border-[#2a2a3a] hover:border-[#e60000]/30 transition-colors"
                            x-show="(selectedKategori == 0 || selectedKategori == <?= $j['id_kategori'] ?>) && (searchJasa === '' || '<?= strtolower($j['nama_varian'] ?: $j['nama_jasa']) ?>'.includes(searchJasa.toLowerCase()))">
                            <div>
                                <p class="text-white text-sm font-medium"><?= htmlspecialchars($j['nama_varian'] ?: $j['nama_jasa']) ?></p>
                                <p class="text-gray-500 text-xs">
                                    <?= htmlspecialchars($j['kategori']) ?> —
                                    <span class="text-green-400"><?= format_rupiah($j['total_harga']) ?></span>
                                    <?php if ($j['is_custom']): ?>
                                    <span class="text-[#ff6600]">(Custom)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <form method="POST" class="flex gap-2 items-center">
                                <input type="hidden" name="action" value="add_jasa">
                                <input type="hidden" name="id_varian" value="<?= $j['id'] ?>">
                                <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                                <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                                <button type="submit"
                                    class="px-3 py-1.5 bg-[#e60000] hover:bg-[#ffd700] text-white text-xs font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-1"></i>Tambah
                                </button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Tab: SPAREPART -->
                <div x-show="activeTab === 'sparepart'" class="p-4 space-y-3 overflow-y-auto flex-1 min-h-0">
                    <div class="flex gap-2 items-center shrink-0">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input type="text" x-model="searchSparepart" placeholder="Cari sparepart..."
                                class="w-full px-4 py-2 pl-8 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:border-[#e60000]">
                        </div>
                        <?php
                        $sort_sparepart = $_GET['sort_sparepart'] ?? '';
                        $order_clause = "k.nama ASC, s.nama ASC";
                        if ($sort_sparepart === 'stok_asc') $order_clause = "s.stok ASC, s.nama ASC";
                        if ($sort_sparepart === 'stok_desc') $order_clause = "s.stok DESC, s.nama ASC";
                        $url_base = explode('?', $_SERVER['REQUEST_URI'])[0];
                        ?>
                        <span class="text-gray-500 text-xs whitespace-nowrap">Urut:</span>
                        <a href="<?= $url_base ?>?sort_sparepart=<?= $sort_sparepart === 'stok_asc' ? 'stok_desc' : 'stok_asc' ?>&pelanggan_id=<?= $_GET['pelanggan_id'] ?? 0 ?>&kendaraan_id=<?= $_GET['kendaraan_id'] ?? 0 ?>"
                            class="text-xs font-medium px-3 py-2 rounded-xl border transition-colors <?= $sort_sparepart ? 'bg-[#e60000]/10 text-[#e60000] border-[#e60000]/30' : 'bg-[#0a0a0f] text-gray-400 border-[#2a2a3a] hover:text-white' ?>">
                            Stok <?php if ($sort_sparepart === 'stok_asc'): ?>↑<?php elseif ($sort_sparepart === 'stok_desc'): ?>↓<?php endif; ?>
                        </a>
                    </div>
                    <div class="space-y-2">
                        <?php
                        $spareparts = $conn->query("SELECT s.*, k.nama AS kategori FROM sparepart s JOIN kategori_sparepart k ON s.id_kategori = k.id WHERE s.stok > 0 ORDER BY $order_clause");
                        while ($s = $spareparts->fetch_assoc()):
                        ?>
                        <div class="flex items-center justify-between px-4 py-2.5 bg-[#0a0a0f] rounded-xl border border-[#2a2a3a] hover:border-[#e60000]/30 transition-colors"
                            x-show="searchSparepart === '' || '<?= strtolower($s['nama'] . ' ' . ($s['kode'] ?? '')) ?>'.includes(searchSparepart.toLowerCase())">
                            <div>
                                <p class="text-white text-sm font-medium"><?= htmlspecialchars($s['nama']) ?></p>
                                <p class="text-gray-500 text-xs">
                                    Stok: <?= $s['stok'] ?> |
                                    <span class="text-green-400"><?= format_rupiah($s['harga_jual']) ?></span>
                                </p>
                            </div>
                            <form method="POST" class="flex gap-2 items-center">
                                <input type="hidden" name="action" value="add_sparepart">
                                <input type="hidden" name="id_sparepart" value="<?= $s['id'] ?>">
                                <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                                <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                                <input type="number" name="qty" value="1" min="1" max="<?= $s['stok'] ?>"
                                    class="w-16 px-2 py-1.5 bg-[#0a0a0f] border border-[#2a2a3a] rounded-lg text-white text-xs text-center focus:outline-none focus:border-[#e60000]">
                                <button type="submit"
                                    class="px-3 py-1.5 bg-[#e60000] hover:bg-[#ffd700] text-white text-xs font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-1"></i>
                                </button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Cart -->
        <div class="w-full md:w-2/5 flex flex-col gap-4 min-h-0">
            <div class="bg-[#161622] rounded-xl border border-[#2a2a3a] flex flex-col flex-1">
                <div class="px-5 py-4 border-b border-[#2a2a3a] flex justify-between items-center">
                    <h3 class="text-white font-semibold">
                        <i class="fas fa-shopping-cart text-[#e60000] mr-2"></i>Keranjang
                    </h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="clear_cart">
                        <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                        <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                        <button type="submit" class="text-xs text-gray-500 hover:text-red-400 transition-colors"
                            onclick="return confirm('Kosongkan keranjang?')">
                            <i class="fas fa-trash mr-1"></i>Kosongkan
                        </button>
                    </form>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <!-- Jasa Items -->
                    <?php foreach ($cart['jasa'] as $j_idx => $j): ?>
                    <div class="bg-[#0a0a0f] rounded-xl p-3 border border-[#2a2a3a]">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-white text-sm font-medium"><?= htmlspecialchars($j['nama_jasa']) ?></p>
                                <p class="text-green-400 text-sm font-semibold"><?= format_rupiah($j['total_harga']) ?></p>
                            </div>
<form method="POST">
                            <input type="hidden" name="action" value="remove_jasa">
                            <input type="hidden" name="index" value="<?= $j_idx ?>">
                            <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                            <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        </div>

                        <!-- Breakdown Items (if any) -->
                        <?php if (!empty($j['items'])): ?>
                        <div class="mt-2 pt-2 border-t border-[#2a2a3a] space-y-1">
                            <?php foreach ($j['items'] as $i_idx => $item): ?>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400"><?= htmlspecialchars($item['nama_item']) ?></span>
                                <div class="flex items-center gap-2">
                                    <form method="POST" class="flex items-center gap-1">
                                        <input type="hidden" name="action" value="update_jasa_item">
                                        <input type="hidden" name="j_idx" value="<?= $j_idx ?>">
                                        <input type="hidden" name="i_idx" value="<?= $i_idx ?>">
                                        <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                                        <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                                        <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1"
                                            class="w-10 px-1 py-0.5 bg-[#161622] border border-[#2a2a3a] rounded text-white text-xs text-center">
                                        <span class="text-gray-500">×</span>
                                        <input type="number" name="harga_satuan" value="<?= $item['harga_satuan'] ?>"
                                            class="w-20 px-1 py-0.5 bg-[#161622] border border-[#2a2a3a] rounded text-white text-xs text-center">
                                        <button type="submit" class="text-blue-400 hover:text-blue-300 ml-1">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <!-- Add custom item -->
                            <form method="POST" class="flex items-center gap-1 mt-2 pt-1 border-t border-[#2a2a3a]/50">
                                <input type="hidden" name="action" value="add_custom_item">
                                <input type="hidden" name="j_idx" value="<?= $j_idx ?>">
                                <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                                <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                                <input type="text" name="nama_item" placeholder="+ item custom" required
                                    class="flex-1 px-2 py-1 bg-[#161622] border border-[#2a2a3a] rounded text-white text-xs placeholder-gray-500">
                                <input type="number" name="qty" value="1" min="1"
                                    class="w-10 px-1 py-1 bg-[#161622] border border-[#2a2a3a] rounded text-white text-xs text-center">
                                <input type="number" name="harga_satuan" placeholder="Rp"
                                    class="w-16 px-1 py-1 bg-[#161622] border border-[#2a2a3a] rounded text-white text-xs text-center">
                                <button type="submit" class="text-green-400 hover:text-green-300">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <!-- Sparepart Items -->
                    <?php foreach ($cart['sparepart'] as $s_idx => $s): ?>
                    <div class="flex items-center justify-between bg-[#0a0a0f] rounded-xl px-4 py-3 border border-[#2a2a3a]">
                        <div>
                            <p class="text-white text-sm"><?= htmlspecialchars($s['nama']) ?></p>
                            <p class="text-gray-500 text-xs"><?= $s['qty'] ?> × <?= format_rupiah($s['harga_jual']) ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-green-400 text-sm font-semibold"><?= format_rupiah($s['harga_jual'] * $s['qty']) ?></span>
                            <form method="POST">
                                <input type="hidden" name="action" value="remove_sparepart">
                                <input type="hidden" name="index" value="<?= $s_idx ?>">
                                <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                                <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($cart['jasa']) && empty($cart['sparepart'])): ?>
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-shopping-cart text-4xl mb-3 block opacity-30"></i>
                        <p class="text-sm">Keranjang masih kosong</p>
                        <p class="text-xs text-gray-600">Pilih jasa atau sparepart dari panel kiri</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Cart Footer: Total + Save -->
                <div class="border-t border-[#2a2a3a] p-5">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-400 text-sm">Total</span>
                        <span class="text-white text-2xl font-bold"><?= format_rupiah($cart_total) ?></span>
                    </div>

                    <?php if (isset($error)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-2 rounded-lg mb-3 text-xs"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="simpan">
                        <input type="hidden" name="pelanggan_id" value="<?= $_GET['pelanggan_id'] ?? 0 ?>">
                        <input type="hidden" name="kendaraan_id" value="<?= $_GET['kendaraan_id'] ?? 0 ?>">
                        <input type="text" name="catatan" placeholder="Catatan (opsional)"
                            class="w-full px-4 py-2 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white text-sm placeholder-gray-500 mb-3 focus:outline-none focus:border-[#e60000]">
                        <button type="submit"
                            class="w-full py-3 bg-[#e60000] hover:bg-[#ffd700] text-white font-semibold rounded-xl transition-colors text-sm"
                            onclick="return confirm('Simpan transaksi ini?')">
                            <i class="fas fa-save mr-2"></i>Simpan Transaksi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../layout/footer.php'; ?>
