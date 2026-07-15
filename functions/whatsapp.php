<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

function kirimWA($target, $message, $attachment = null) {
    $token = $_ENV['FONNTE_TOKEN'] ?? '';

    if (empty($token) || $token === 'your_fonnte_token_here') {
        $phone = preg_replace('/^0/', '62', $target);
        return ['status' => false, 'method' => 'wa.me', 'url' => 'https://wa.me/' . $phone . '?text=' . urlencode($message)];
    }

    $post_fields = [
        'target' => $target,
        'message' => $message,
        'countryCode' => '62',
    ];

    if ($attachment && file_exists($attachment)) {
        $post_fields['file'] = new \CURLFile($attachment, mime_content_type($attachment) ?: 'application/octet-stream', basename($attachment));
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post_fields,
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $token
        ),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);

    if ($error) {
        return ['status' => false, 'reason' => $error];
    }

    $result = json_decode($response, true);
    return [
        'status' => $result['status'] ?? false,
        'reason' => $result['reason'] ?? '',
        'response' => $result,
    ];
}

function formatPesanWA($transaksi, $detail_jasa, $detail_sparepart) {
    $app_name = $_ENV['APP_NAME'] ?? 'Bengkel Racing Cihuy';
    $link_garansi = $_ENV['LINK_GARANSI'] ?? 'https://example.com/garansi';
    $no_nota = 'TRX-' . str_pad($transaksi['id'], 4, '0', STR_PAD_LEFT);

    $msg = "Halo *" . ($transaksi['nama_pelanggan'] ?? 'Pelanggan') . "*,\n";
    $msg .= "Terima kasih sudah mengunjungi *$app_name*.\n\n";
    $msg .= "Data Anda berupa :\n";
    $msg .= "No Plat Motor : *" . ($transaksi['plat_no'] ?? '-') . "*\n";
    $msg .= "Jenis Motor : *" . ($transaksi['merek'] ?? '') . " " . ($transaksi['model'] ?? '') . " " . ($transaksi['tahun'] ?? '') . "*\n";
    $msg .= "Nama : *" . ($transaksi['nama_pelanggan'] ?? '-') . "*\n\n";

    if ($detail_jasa->num_rows > 0) {
        $msg .= "Rincian Servis :\n";
        while ($d = $detail_jasa->fetch_assoc()) {
            $msg .= "- " . ($d['nama_jasa'] ?: $d['nama_varian']) . "\n";
        }
    }

    if ($detail_sparepart->num_rows > 0) {
        $msg .= "Sparepart :\n";
        while ($d = $detail_sparepart->fetch_assoc()) {
            $msg .= "- " . $d['nama'] . " (" . $d['qty'] . "x Rp " . number_format($d['harga_jual'], 0, ',', '.') . ")\n";
        }
    }

    $msg .= "\nTotal : Rp " . number_format($transaksi['total'], 0, ',', '.') . "\n";
    $msg .= "Status : ✅ LUNAS\n\n";
    $msg .= "Untuk pengajuan garansi, silahkan klik link berikut :\n";
    $msg .= $link_garansi . $no_nota . "\n\n";
    $msg .= "_Nota PDF tersedia di bengkel._";

    return $msg;
}
