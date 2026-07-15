<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/nota.php';
cek_login();

$id = (int)($_GET['id'] ?? 0);

$transaksi = $conn->query("SELECT t.*, p.nama AS nama_pelanggan, p.no_telp, p.alamat, k.plat_no, k.model, k.cc, k.tahun, m.nama AS merek, u.nama AS nama_kasir
                            FROM transaksi t
                            LEFT JOIN pelanggan p ON t.id_pelanggan = p.id
                            LEFT JOIN kendaraan k ON t.id_kendaraan = k.id
                            LEFT JOIN merek m ON k.id_merek = m.id
                            LEFT JOIN users u ON t.id_user = u.id
                            WHERE t.id = $id")->fetch_assoc();

if (!$transaksi) {
    die('Transaksi tidak ditemukan');
}

$detail_jasa = $conn->query("SELECT dj.*, v.nama_varian FROM detail_jasa dj JOIN varian_jasa v ON dj.id_varian = v.id WHERE dj.id_transaksi = $id");
$detail_sparepart = $conn->query("SELECT ds.*, s.nama FROM detail_sparepart ds JOIN sparepart s ON ds.id_sparepart = s.id WHERE ds.id_transaksi = $id");

$pdf = generateNotaPDF($transaksi, $detail_jasa, $detail_sparepart, $conn);

$filename = 'Nota_TRX_' . str_pad($transaksi['id'], 4, '0', STR_PAD_LEFT) . '.pdf';

if (($_GET['save'] ?? '') === '1') {
    $dir = __DIR__ . '/../../nota';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $pdf->Output('F', $dir . '/' . $filename);
} else {
    $pdf->Output('I', $filename);
}
