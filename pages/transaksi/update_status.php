<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$valid_status = ['antrian', 'dikerjakan', 'selesai', 'lunas'];

if ($id && in_array($status, $valid_status)) {
    $stmt = $conn->prepare("UPDATE transaksi SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

header('Location: index.php');
exit;
