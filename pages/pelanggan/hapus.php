<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
cek_login();

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM pelanggan WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: index.php');
exit;
