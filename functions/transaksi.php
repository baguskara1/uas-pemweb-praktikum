<?php
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function simpan_transaksi($conn, $user_id, $pelanggan_id, $kendaraan_id, $cart, $catatan = '') {
    $conn->begin_transaction();
    try {
        // Hitung total
        $total = 0;
        foreach ($cart['jasa'] as $j) {
            $total += $j['total_harga'];
        }
        foreach ($cart['sparepart'] as $s) {
            $total += $s['harga_jual'] * $s['qty'];
        }

        // Insert transaksi
        $stmt = $conn->prepare("INSERT INTO transaksi (id_pelanggan, id_kendaraan, id_user, total, status, catatan) VALUES (?, ?, ?, ?, 'antrian', ?)");
        $stmt->bind_param("iiiis", $pelanggan_id, $kendaraan_id, $user_id, $total, $catatan);
        $stmt->execute();
        $transaksi_id = $conn->insert_id;

        // Insert detail jasa
        foreach ($cart['jasa'] as $j) {
            $stmt = $conn->prepare("INSERT INTO detail_jasa (id_transaksi, id_varian, nama_jasa, total_harga, catatan) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisis", $transaksi_id, $j['id_varian'], $j['nama_jasa'], $j['total_harga'], $j['catatan']);
            $stmt->execute();
            $detail_jasa_id = $conn->insert_id;

            // Insert detail item jasa (breakdown) jika ada
            if (!empty($j['items'])) {
                foreach ($j['items'] as $item) {
                    $subtotal = $item['harga_satuan'] * $item['qty'];
                    $stmt = $conn->prepare("INSERT INTO detail_item_jasa (id_detail_jasa, id_master_item, nama_item, qty, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iisiii", $detail_jasa_id, $item['id_master_item'], $item['nama_item'], $item['qty'], $item['harga_satuan'], $subtotal);
                    $stmt->execute();
                }
            }
        }

        // Insert detail sparepart + kurangi stok + log
        require_once __DIR__ . '/pengaturan.php';
        foreach ($cart['sparepart'] as $s) {
            $stmt = $conn->prepare("INSERT INTO detail_sparepart (id_transaksi, id_sparepart, qty, harga_jual) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $transaksi_id, $s['id_sparepart'], $s['qty'], $s['harga_jual']);
            $stmt->execute();

            // Kurangi stok
            $stmt = $conn->prepare("UPDATE sparepart SET stok = stok - ? WHERE id = ?");
            $stmt->bind_param("ii", $s['qty'], $s['id_sparepart']);
            $stmt->execute();

            // Catat log stok
            $ref = 'TRX-' . str_pad($transaksi_id, 4, '0', STR_PAD_LEFT);
            catatLogStok($conn, $s['id_sparepart'], 'keluar', $s['qty'], $ref, 'Penjualan via POS', $user_id);
        }

        $conn->commit();
        return $transaksi_id;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
