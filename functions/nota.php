<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Fpdf\Fpdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class PDFNota extends Fpdf {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(255, 51, 51);
        $this->Cell(0, 8, 'Bengkel Racing Cihuy', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 5, 'Service & Racing Parts', 0, 1, 'C');
        $this->Cell(0, 5, 'Pogung Baru Blok G No.1, Yogyakarta', 0, 1, 'C');
        $this->SetDrawColor(255, 51, 51);
        $this->Line(10, 28, 200, 28);
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Terima Kasih - Bengkel Racing Cihuy', 0, 0, 'C');
    }
}

function generateNotaPDF($transaksi, $detail_jasa, $detail_sparepart, $conn) {
    $pdf = new PDFNota();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 20);

    $black = [0, 0, 0];
    $gray = [80, 80, 80];
    $light = [120, 120, 120];
    $red = [200, 30, 30];
    $green = [0, 140, 0];
    $orange = [255, 215, 0];

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$light);
    $pdf->Cell(40, 5, 'No. Nota', 0, 0);
    $pdf->SetTextColor(...$black);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 5, '#TRX-' . str_pad($transaksi['id'], 4, '0', STR_PAD_LEFT), 0, 1);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$light);
    $pdf->Cell(40, 5, 'Tanggal', 0, 0);
    $pdf->SetTextColor(...$black);
    $pdf->Cell(0, 5, date('d/m/Y H:i', strtotime($transaksi['tgl'])), 0, 1);

    $pdf->SetTextColor(...$light);
    $pdf->Cell(40, 5, 'Kasir', 0, 0);
    $pdf->SetTextColor(...$black);
    $pdf->Cell(0, 5, $transaksi['nama_kasir'], 0, 1);

    $pdf->SetTextColor(...$light);
    $pdf->Cell(40, 5, 'Status', 0, 0);
    $status_colors = ['antrian' => $orange, 'dikerjakan' => $orange, 'selesai' => [0, 100, 200], 'lunas' => $green];
    $sc = $status_colors[$transaksi['status']] ?? $gray;
    $pdf->SetTextColor(...$sc);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 5, strtoupper($transaksi['status']), 0, 1);

    $pdf->Ln(5);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(...$red);
    $pdf->Cell(0, 6, 'DATA PELANGGAN', 0, 1);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$light);
    $pdf->Cell(30, 5, 'Nama', 0, 0);
    $pdf->SetTextColor(...$black);
    $pdf->Cell(0, 5, $transaksi['nama_pelanggan'] ?? '-', 0, 1);

    $pdf->SetTextColor(...$light);
    $pdf->Cell(30, 5, 'No. Telp', 0, 0);
    $pdf->SetTextColor(...$black);
    $pdf->Cell(0, 5, $transaksi['no_telp'] ?? '-', 0, 1);

    $pdf->SetTextColor(...$light);
    $pdf->Cell(30, 5, 'Kendaraan', 0, 0);
    $pdf->SetTextColor(...$black);
    $pdf->Cell(0, 5, ($transaksi['merek'] ?? '') . ' ' . ($transaksi['model'] ?? '') . ' (' . $transaksi['cc'] . 'cc) ' . $transaksi['tahun'], 0, 1);

    $pdf->SetTextColor(...$light);
    $pdf->Cell(30, 5, 'Plat No', 0, 0);
    $pdf->SetTextColor(...$black);
    $pdf->Cell(0, 5, $transaksi['plat_no'] ?? '-', 0, 1);

    $pdf->Ln(5);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(...$red);
    $pdf->Cell(0, 6, 'JASA SERVIS', 0, 1);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(...$gray);
    $pdf->Cell(120, 5, 'Jasa', 0, 0);
    $pdf->Cell(30, 5, 'Qty', 0, 0, 'R');
    $pdf->Cell(40, 5, 'Subtotal', 0, 1, 'R');

    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

    $pdf->SetTextColor(...$black);
    $pdf->SetFont('Arial', '', 9);
    while ($dj = $detail_jasa->fetch_assoc()) {
        $pdf->Cell(120, 6, $dj['nama_jasa'] ?: $dj['nama_varian'], 0, 0);
        $pdf->Cell(30, 6, '1', 0, 0, 'R');
        $pdf->Cell(40, 6, 'Rp ' . number_format($dj['total_harga'], 0, ',', '.'), 0, 1, 'R');

        $items = $conn->query("SELECT * FROM detail_item_jasa WHERE id_detail_jasa = {$dj['id']}");
        while ($item = $items->fetch_assoc()) {
            $pdf->SetTextColor(...$gray);
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(120, 4, '  - ' . $item['nama_item'] . ' x ' . $item['qty'], 0, 0);
            $pdf->Cell(40, 4, 'Rp ' . number_format($item['subtotal'], 0, ',', '.'), 0, 1, 'R');
            $pdf->SetTextColor(...$black);
            $pdf->SetFont('Arial', '', 9);
        }
    }

    $pdf->Ln(3);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

    if ($detail_sparepart->num_rows > 0) {
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(...$red);
        $pdf->Cell(0, 6, 'SPAREPART', 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(...$gray);
        $pdf->Cell(120, 5, 'Sparepart', 0, 0);
        $pdf->Cell(30, 5, 'Qty', 0, 0, 'R');
        $pdf->Cell(40, 5, 'Subtotal', 0, 1, 'R');
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

        $pdf->SetTextColor(...$black);
        $pdf->SetFont('Arial', '', 9);
        while ($ds = $detail_sparepart->fetch_assoc()) {
            $subtotal = $ds['harga_jual'] * $ds['qty'];
            $pdf->Cell(120, 6, $ds['nama'], 0, 0);
            $pdf->Cell(30, 6, $ds['qty'] . ' x', 0, 0, 'R');
            $pdf->Cell(40, 6, 'Rp ' . number_format($subtotal, 0, ',', '.'), 0, 1, 'R');
        }
    }

    $pdf->Ln(5);
    $pdf->SetDrawColor(...$red);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(3);

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(...$black);
    $pdf->Cell(0, 10, 'Total: Rp ' . number_format($transaksi['total'], 0, ',', '.'), 0, 1, 'R');

    if ($transaksi['catatan']) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(...$gray);
        $pdf->Cell(0, 5, 'Catatan: ' . $transaksi['catatan'], 0, 1);
    }

    // ── QR Code (Link Garansi) ────────────────────────────
    $no_nota = 'TRX-' . str_pad($transaksi['id'], 4, '0', STR_PAD_LEFT);
    $link_garansi = getenv('LINK_GARANSI') ?: ($_ENV['LINK_GARANSI'] ?? '');
    $qr_data = $link_garansi . urlencode($no_nota);

    $qr_tmp = tempnam(sys_get_temp_dir(), 'qr_') . '.png';

    try {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $qr_data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
        );
        $result = $builder->build();
        $result->saveToFile($qr_tmp);

        // Label di atas QR
        $pdf->SetY(max($pdf->GetY() + 5, 200));
        $label_y = $pdf->GetY();
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(...$gray);
        $pdf->Cell(0, 4, 'Scan untuk garansi', 0, 1, 'R');
        $pdf->SetX(160);
        $pdf->Image($qr_tmp, 160, $pdf->GetY() + 1, 30, 30);
    } catch (\Exception $e) {
        // Jika QR gagal, abaikan saja
    } finally {
        if (file_exists($qr_tmp)) {
            unlink($qr_tmp);
        }
    }

    return $pdf;
}
