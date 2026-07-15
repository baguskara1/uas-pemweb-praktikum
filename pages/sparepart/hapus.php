<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID sparepart tidak valid.'];
    header('Location: index.php');
    exit;
}

// Cek apakah sparepart sedang digunakan di transaksi
$cek = $conn->query("SELECT COUNT(*) AS total FROM detail_sparepart WHERE id_sparepart = $id")->fetch_assoc();
if ($cek['total'] > 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sparepart tidak bisa dihapus karena masih digunakan di transaksi.'];
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM sparepart WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Sparepart berhasil dihapus.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus sparepart: ' . $conn->error];
}

$stmt->close();
header('Location: index.php');
exit;
