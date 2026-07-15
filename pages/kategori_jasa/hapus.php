<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = (int)($_GET['id'] ?? 0);

$check = $conn->query("SELECT * FROM kategori_jasa WHERE id = $id");
if ($check->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM kategori_jasa WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header('Location: index.php');
exit;
