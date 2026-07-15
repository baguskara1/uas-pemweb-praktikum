<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)$_GET['id'];

// Check if master_item is used in item_varian
$check = $conn->query("SELECT COUNT(*) AS total FROM item_varian WHERE id_master_item = $id");
$related = $check->fetch_assoc();

if ($related['total'] > 0) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Tidak Dapat Dihapus',
            text: 'Item ini masih digunakan di " . $related['total'] . " varian jasa.',
            confirmButtonColor: '#ccff00'
        }).then(() => { window.location = 'index.php'; });
    </script>";
    exit;
}

$stmt = $conn->prepare("DELETE FROM master_item WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: index.php');
    exit;
} else {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal menghapus data: " . addslashes($conn->error) . "',
            confirmButtonColor: '#ccff00'
        }).then(() => { window.location = 'index.php'; });
    </script>";
}
