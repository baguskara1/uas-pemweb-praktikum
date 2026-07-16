<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID transaksi tidak valid.'];
    header('Location: index.php');
    exit;
}

// Cek transaksi exists
$cek = $conn->query("SELECT id FROM transaksi WHERE id = $id");
if ($cek->num_rows === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Transaksi tidak ditemukan.'];
    header('Location: index.php');
    exit;
}

// Hapus (cascade ke detail_jasa, detail_item_jasa, detail_sparepart otomatis)
$stmt = $conn->prepare("DELETE FROM transaksi WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Transaksi berhasil dihapus.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus transaksi: ' . $conn->error];
}

header('Location: index.php');
exit;
