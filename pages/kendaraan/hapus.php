<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID kendaraan tidak valid.'];
    header('Location: index.php');
    exit;
}

// Cek apakah kendaraan masih punya transaksi aktif (antrian/dikerjakan)
$cek = $conn->query("SELECT COUNT(*) AS total FROM transaksi WHERE id_kendaraan = $id AND status IN ('antrian','dikerjakan')")->fetch_assoc();

if ($cek['total'] > 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Kendaraan tidak bisa dihapus karena masih memiliki transaksi aktif.'];
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM kendaraan WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kendaraan berhasil dihapus.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus kendaraan: ' . $conn->error];
}

header('Location: index.php');
exit;
