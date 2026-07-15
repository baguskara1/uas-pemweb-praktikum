<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/pengaturan.php';
cek_login();

$id_sparepart = (int)($_POST['id_sparepart'] ?? 0);
$tipe = $_POST['tipe'] ?? '';
$qty = max(1, (int)($_POST['qty'] ?? 1));
$catatan = trim($_POST['catatan'] ?? '');

if (!$id_sparepart || !in_array($tipe, ['masuk', 'keluar', 'penyesuaian'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak valid!'];
    header("Location: log_stok.php?id=$id_sparepart");
    exit;
}

$sparepart = $conn->query("SELECT * FROM sparepart WHERE id = $id_sparepart")->fetch_assoc();
if (!$sparepart) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sparepart tidak ditemukan!'];
    header("Location: index.php");
    exit;
}

// Update stok
if ($tipe === 'masuk') {
    $conn->query("UPDATE sparepart SET stok = stok + $qty WHERE id = $id_sparepart");
} elseif ($tipe === 'keluar') {
    if ($sparepart['stok'] < $qty) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Stok tidak mencukupi! (Stok: ' . $sparepart['stok'] . ', diminta: ' . $qty . ')'];
        header("Location: log_stok.php?id=$id_sparepart");
        exit;
    }
    $conn->query("UPDATE sparepart SET stok = stok - $qty WHERE id = $id_sparepart");
    $qty = -$qty;
} else {
    $conn->query("UPDATE sparepart SET stok = stok + ($qty) WHERE id = $id_sparepart");
}

// Catat log
$user_id = $_SESSION['user_id'] ?? 0;
catatLogStok($conn, $id_sparepart, $tipe, abs($qty), 'ADJ-' . time(), $catatan, $user_id);

$_SESSION['flash'] = ['type' => 'success', 'message' => 'Stok berhasil diperbarui!'];
header("Location: log_stok.php?id=$id_sparepart");
exit;
