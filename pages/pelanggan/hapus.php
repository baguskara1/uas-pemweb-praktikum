<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID pelanggan tidak valid.'];
    header('Location: index.php');
    exit;
}

// Cek apakah pelanggan masih punya transaksi aktif (antrian/dikerjakan)
$cek = $conn->query("SELECT COUNT(*) AS total FROM transaksi t WHERE (t.id_pelanggan = $id OR t.id_kendaraan IN (SELECT id FROM kendaraan WHERE id_pelanggan = $id)) AND t.status IN ('antrian','dikerjakan')")->fetch_assoc();

if ($cek['total'] > 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pelanggan tidak bisa dihapus karena masih memiliki transaksi aktif.'];
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM pelanggan WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pelanggan berhasil dihapus.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus pelanggan: ' . $conn->error];
}

header('Location: index.php');
exit;
