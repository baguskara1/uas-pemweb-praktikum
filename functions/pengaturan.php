<?php
function getSetting($kunci, $default = '') {
    global $conn;
    $q = $conn->query("SELECT nilai FROM pengaturan WHERE kunci = '$kunci'");
    if ($q && $r = $q->fetch_assoc()) {
        return $r['nilai'];
    }
    return $default;
}

function getAllSettings() {
    global $conn;
    $result = [];
    $q = $conn->query("SELECT * FROM pengaturan ORDER BY grup, kunci");
    while ($r = $q->fetch_assoc()) {
        $result[] = $r;
    }
    return $result;
}

function getSettingsByGroup($grup) {
    global $conn;
    $result = [];
    $q = $conn->query("SELECT * FROM pengaturan WHERE grup = '$grup' ORDER BY kunci");
    while ($r = $q->fetch_assoc()) {
        $result[] = $r;
    }
    return $result;
}

function catatLogStok($conn, $id_sparepart, $tipe, $qty, $referensi = '', $catatan = '', $user_id = 0) {
    $q = $conn->query("SELECT stok FROM sparepart WHERE id = $id_sparepart");
    $r = $q->fetch_assoc();
    $stok_sebelum = (int)($r['stok'] ?? 0);

    // Hitung stok sesudah
    if ($tipe === 'masuk') {
        $stok_sesudah = $stok_sebelum + $qty;
    } elseif ($tipe === 'keluar') {
        $stok_sesudah = $stok_sebelum - $qty;
    } else {
        $stok_sesudah = $stok_sebelum + $qty;
    }

    $stmt = $conn->prepare("INSERT INTO log_stok (id_sparepart, tipe, qty, stok_sebelum, stok_sesudah, referensi, id_user, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiiiss", $id_sparepart, $tipe, $qty, $stok_sebelum, $stok_sesudah, $referensi, $user_id, $catatan);
    return $stmt->execute();
}

function backupDatabase($conn) {
    $tables = [];
    $q = $conn->query("SHOW TABLES");
    while ($r = $q->fetch_row()) {
        $tables[] = $r[0];
    }

    $sql = "-- Backup: " . date('Y-m-d H:i:s') . "\n-- Bengkel Racing Cihuy\n\n";
    $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sql .= "SET AUTOCOMMIT = 0;\n";
    $sql .= "START TRANSACTION;\n";
    $sql .= "SET time_zone = '+07:00';\n\n";

    foreach ($tables as $table) {
        // Drop table
        $q = $conn->query("SHOW CREATE TABLE $table");
        $r = $q->fetch_row();
        $sql .= "\nDROP TABLE IF EXISTS `$table`;\n";
        $sql .= $r[1] . ";\n\n";

        // Data
        $q = $conn->query("SELECT * FROM $table");
        if ($q->num_rows > 0) {
            $fields = [];
            $finfo = $q->fetch_fields();
            foreach ($finfo as $f) {
                $fields[] = "`{$f->name}`";
            }
            $fields_str = implode(', ', $fields);

            while ($r = $q->fetch_row()) {
                $vals = [];
                foreach ($r as $v) {
                    if ($v === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = "'" . $conn->real_escape_string($v) . "'";
                    }
                }
                $vals_str = implode(', ', $vals);
                $sql .= "INSERT INTO `$table` ($fields_str) VALUES ($vals_str);\n";
            }
            $sql .= "\n";
        }
    }

    $sql .= "COMMIT;\n";
    return $sql;
}
