<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function exportLaporanXLSX($bulan, $tahun, $conn) {
    $tgl_awal = "$tahun-$bulan-01";
    $tgl_akhir = date('Y-m-t', strtotime($tgl_awal));
    $nama_bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $judul_bulan = $nama_bulan[(int)$bulan] . ' ' . $tahun;

    $transaksi = $conn->query("SELECT t.*, p.nama AS nama_pelanggan, p.no_telp, k.plat_no, k.model, m.nama AS merek FROM transaksi t LEFT JOIN pelanggan p ON t.id_pelanggan = p.id LEFT JOIN kendaraan k ON t.id_kendaraan = k.id LEFT JOIN merek m ON k.id_merek = m.id WHERE DATE(t.tgl) BETWEEN '$tgl_awal' AND '$tgl_akhir' ORDER BY t.tgl DESC");

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Laporan $judul_bulan");

    // ── Config ──
    $red = 'FFC00000';
    $dark = 'FF1A1A2E';
    $bg_dark = 'FF0D0D1A';
    $bg_header = 'FFC00000';
    $white = 'FFFFFFFF';
    $gray = 'FF9CA3AF';

    // Row 1: Title
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', "Bengkel Racing Cihuy - Laporan Penghasilan $judul_bulan");
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($white));
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // Row 2: Periode
    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue('A2', "Periode: $tgl_awal s/d $tgl_akhir");
    $sheet->getStyle('A2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($gray));
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Row 4: Header table
    $headers = ['No', 'No. Nota', 'Tanggal', 'Pelanggan', 'Kendaraan', 'Status', 'Total'];
    $row = 4;
    foreach ($headers as $ci => $h) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
        $sheet->setCellValue($col . $row, $h);
        $sheet->getStyle($col . $row)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($white));
        $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($red);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($white));
    }
    $sheet->getRowDimension($row)->setRowHeight(20);

    // Data rows
    $no = 1;
    $row = 5;
    $total_all = 0;
    $bg_alt = 'FF161622';

    while ($t = $transaksi->fetch_assoc()) {
        $total_all += $t['total'];
        $kendaraan = trim(($t['merek'] ?? '') . ' ' . ($t['model'] ?? ''));
        $sheet->setCellValue("A$row", $no++);
        $sheet->setCellValue("B$row", '#TRX-' . str_pad($t['id'], 4, '0', STR_PAD_LEFT));
        $sheet->setCellValue("C$row", date('d/m/Y H:i', strtotime($t['tgl'])));
        $sheet->setCellValue("D$row", $t['nama_pelanggan'] ?? '-');
        $sheet->setCellValue("E$row", $kendaraan ?: $t['plat_no'] ?? '-');
        $sheet->setCellValue("F$row", strtoupper($t['status']));
        $sheet->setCellValue("G$row", $t['total']);

        // Styling
        $sheet->getStyle("A$row:G$row")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($white));
        if ($no % 2 === 1) {
            $sheet->getStyle("A$row:G$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg_dark);
        } else {
            $sheet->getStyle("A$row:G$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg_alt);
        }
        $sheet->getStyle("A$row:G$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF2A2A3A'));
        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode('#,##0');
        $row++;
    }

    // Total row
    $sheet->setCellValue("A$row", '');
    $sheet->mergeCells("A$row:F$row");
    $sheet->setCellValue("A$row", 'TOTAL');
    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($white));
    $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue("G$row", $total_all);
    $sheet->getStyle("G$row")->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF22C55E'));
    $sheet->getStyle("G$row")->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle("A$row:G$row")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($red));

    // Column widths
    $sheet->getColumnDimension('A')->setWidth(6);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(18);
    $sheet->getColumnDimension('D')->setWidth(25);
    $sheet->getColumnDimension('E')->setWidth(25);
    $sheet->getColumnDimension('F')->setWidth(12);
    $sheet->getColumnDimension('G')->setWidth(16);

    // Output
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Laporan_BengkelRacing_' . $tahun . $bulan . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
