<?php
$base = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../../' : '';
$menu = [
    ['label' => 'Dashboard', 'icon' => 'fa-gauge-high', 'link' => $base . 'dashboard.php'],
    ['label' => 'POS Baru', 'icon' => 'fa-cart-plus', 'link' => $base . 'pages/transaksi/baru.php', 'color' => 'text-[#ccff00]'],
    ['label' => 'Transaksi', 'icon' => 'fa-receipt', 'link' => $base . 'pages/transaksi/index.php'],
    ['label' => 'Laporan', 'icon' => 'fa-chart-simple', 'link' => $base . 'pages/laporan/index.php'],
    [],
    ['label' => 'Pelanggan', 'icon' => 'fa-users', 'link' => $base . 'pages/pelanggan/index.php'],
    ['label' => 'Kendaraan', 'icon' => 'fa-motorcycle', 'link' => $base . 'pages/kendaraan/index.php'],
    [],
    ['label' => 'Jasa', 'icon' => 'fa-wrench', 'link' => $base . 'pages/jasa/index.php'],
    ['label' => 'Kategori Jasa', 'icon' => 'fa-tag', 'link' => $base . 'pages/kategori_jasa/index.php'],
    ['label' => 'Varian Jasa', 'icon' => 'fa-list', 'link' => $base . 'pages/varian/index.php'],
    ['label' => 'Produk', 'icon' => 'fa-cube', 'link' => $base . 'pages/master_item/index.php'],
    [],
    ['label' => 'Sparepart', 'icon' => 'fa-oil-can', 'link' => $base . 'pages/sparepart/index.php'],
    ['label' => 'Merek Motor', 'icon' => 'fa-tag', 'link' => $base . 'pages/merek/index.php'],
    [],
    ['label' => 'Pengaturan', 'icon' => 'fa-cog', 'link' => $base . 'pages/pengaturan/index.php'],
];

$current_path = $_SERVER['PHP_SELF'];
?>
<!-- Hamburger button (mobile only, shown when sidebar is closed) -->
<button @click="sidebarOpen = true"
        x-show="!sidebarOpen"
        x-cloak
        class="fixed top-4 left-4 z-50 md:hidden flex items-center justify-center w-10 h-10 bg-[#ccff00] rounded-xl text-white shadow-lg hover:bg-[#ff0066] transition-colors"
        aria-label="Buka menu navigasi">
    <i class="fas fa-bars"></i>
</button>

<!-- Backdrop overlay (mobile only) -->
<div x-show="sidebarOpen"
     @click="sidebarOpen = false"
     x-cloak
     class="fixed inset-0 bg-black/60 z-40 md:hidden"
     aria-hidden="true">
</div>

<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0d0d1a] border-r border-[#2a2a3a] flex flex-col transform transition-transform duration-300 ease-in-out md:relative md:translate-x-0 md:z-auto"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="p-5 border-b border-[#2a2a3a] flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#ccff00] flex items-center justify-center overflow-hidden">
                <img src="<?= $base ?>assets/img/logo.png" alt="Racing Cihuy" class="w-10 h-10 object-cover">
            </div>
            <div>
                <h1 class="text-white font-bold text-sm leading-tight">Bengkel Racing</h1>
                <p class="text-gray-500 text-xs">Cihuy POS</p>
            </div>
        </div>
        <!-- Close button (mobile only) -->
        <button @click="sidebarOpen = false"
                class="md:hidden text-gray-400 hover:text-white transition-colors p-1"
                aria-label="Tutup menu navigasi">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <?php foreach ($menu as $item): ?>
            <?php if (empty($item)): ?>
                <div class="border-t border-[#2a2a3a] my-2"></div>
            <?php else:
                $link_clean = str_replace(['../../', '../'], '', $item['link']);
                $is_active = strpos($current_path, $link_clean) !== false && $link_clean !== 'dashboard.php';
            ?>
                <a href="<?= $item['link'] ?>"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-colors duration-200
                    <?= $is_active
                        ? 'bg-[#ccff00]/10 text-[#ccff00] border-l-2 border-[#ccff00]'
                        : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                    <i class="fas <?= $item['icon'] ?> w-5 text-center <?= $item['color'] ?? '' ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="p-4 border-t border-[#2a2a3a]">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full bg-[#ccff00]/20 flex items-center justify-center">
                <i class="fas fa-user text-[#ccff00] text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-medium truncate"><?= $_SESSION['nama'] ?? 'User' ?></p>
                <p class="text-gray-500 text-xs capitalize"><?= $_SESSION['role'] ?? '' ?></p>
            </div>
        </div>
        <a href="logout.php"
            class="flex items-center gap-3 px-4 py-2 rounded-xl text-sm text-gray-400 hover:text-red-400 hover:bg-red-500/5 transition-colors">
            <i class="fas fa-right-from-bracket w-5 text-center"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
