<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID varian tidak valid.'];
    header('Location: index.php');
    exit;
}

// Cek apakah varian sedang digunakan di transaksi
$cek = $conn->query("SELECT COUNT(*) AS total FROM detail_jasa WHERE id_varian = $id")->fetch_assoc();
if ($cek['total'] > 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Varian tidak bisa dihapus karena masih digunakan di transaksi.'];
    header('Location: index.php');
    exit;
}

// Hapus item_varian terkait terlebih dahulu, lalu varian
$conn->begin_transaction();
try {
    $conn->query("DELETE FROM item_varian WHERE id_varian = $id");
    $stmt = $conn->prepare("DELETE FROM varian_jasa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $conn->commit();

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Varian jasa berhasil dihapus.'];
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus varian: ' . $conn->error];
}

header('Location: index.php');
exit;
