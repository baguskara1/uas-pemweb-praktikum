<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)$_GET['id'];

// Check if jasa has related varian_jasa
$check = $conn->query("SELECT COUNT(*) AS total FROM varian_jasa WHERE id_jasa = $id");
$related = $check->fetch_assoc();

if ($related['total'] > 0) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Tidak Dapat Dihapus',
            text: 'Jasa ini masih memiliki " . $related['total'] . " varian jasa. Hapus semua varian terlebih dahulu.',
            confirmButtonColor: '#ccff00'
        }).then(() => { window.location = 'index.php'; });
    </script>";
    exit;
}

$stmt = $conn->prepare("DELETE FROM jasa WHERE id = ?");
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
