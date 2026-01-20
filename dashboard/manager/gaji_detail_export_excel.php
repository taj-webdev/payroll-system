<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

date_default_timezone_set('Asia/Jakarta');

$db = new Database();
$conn = $db->connect();

/* ===============================
   VALIDASI BULAN
================================ */
$bulan_id = (int)($_GET['bulan_id'] ?? 0);
if ($bulan_id <= 0) {
    die('Bulan tidak valid');
}

/* ===============================
   FILTER
================================ */
$status   = $_GET['status'] ?? '';
$karyawan = (int)($_GET['karyawan'] ?? 0);
$tanggal  = $_GET['tanggal'] ?? '';

$where  = ['gd.gaji_bulan_id = ?'];
$params = [$bulan_id];

if ($status !== '') {
    $where[] = 'gd.status = ?';
    $params[] = $status;
}
if ($karyawan > 0) {
    $where[] = 'gd.karyawan_id = ?';
    $params[] = $karyawan;
}
if ($tanggal !== '') {
    $where[] = 'DATE(gd.created_at) = ?';
    $params[] = $tanggal;
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

/* ===============================
   PERIODE
================================ */
$stmtBulan = $conn->prepare("
    SELECT gb.bulan, gt.tahun
    FROM gaji_bulan gb
    JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
    WHERE gb.id = ?
");
$stmtBulan->execute([$bulan_id]);
$periode = $stmtBulan->fetch();

/* ===============================
   DATA GAJI
================================ */
$stmt = $conn->prepare("
    SELECT gd.*, k.nama_lengkap, d.nama_divisi
    FROM gaji_detail gd
    JOIN karyawan k ON k.id = gd.karyawan_id
    LEFT JOIN divisi d ON d.id = k.divisi_id
    $whereSql
    ORDER BY k.nama_lengkap
");
$stmt->execute($params);
$data = $stmt->fetchAll();

/* ===============================
   TERBILANG
================================ */
function terbilang($angka) {
    $angka = abs($angka);
    $baca  = ["","Satu","Dua","Tiga","Empat","Lima","Enam","Tujuh","Delapan","Sembilan","Sepuluh","Sebelas"];
    if ($angka < 12) return $baca[$angka];
    if ($angka < 20) return terbilang($angka-10)." Belas";
    if ($angka < 100) return terbilang(intval($angka/10))." Puluh ".terbilang($angka%10);
    if ($angka < 200) return "Seratus ".terbilang($angka-100);
    if ($angka < 1000) return terbilang(intval($angka/100))." Ratus ".terbilang($angka%100);
    if ($angka < 2000) return "Seribu ".terbilang($angka-1000);
    if ($angka < 1000000) return terbilang(intval($angka/1000))." Ribu ".terbilang($angka%1000);
    if ($angka < 1000000000) return terbilang(intval($angka/1000000))." Juta ".terbilang($angka%1000000);
    return "Terlalu Besar";
}

/* ===============================
   SPREADSHEET
================================ */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$bulanNama = date('F', mktime(0,0,0,$periode['bulan'],1));
$tanggalCetak = date('l, d F Y H:i:s') . ' WIB';

/* HEADER */
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', 'SLIP GAJI - PAYROLL SYSTEM');

$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Periode: '.$bulanNama.' '.$periode['tahun']);

$sheet->getStyle('A1:A2')->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A2')->getFont()->setItalic(true);

/* TABLE HEADER */
$headerRow = 4;
$headers = [
    'No','Nama Karyawan','Divisi',
    'Gaji Pokok','Tunjangan','Lembur',
    'Potongan','Gaji Bersih','Status'
];

$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col.$headerRow, $h);
    $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
    $sheet->getStyle($col.$headerRow)->getFill()
          ->setFillType(Fill::FILL_SOLID)
          ->getStartColor()->setRGB('E5E7EB');
    $sheet->getStyle($col.$headerRow)->getBorders()->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN);
    $col++;
}

/* DATA */
$row = 5;
$no = 1;
$totalGaji = 0;

foreach ($data as $d) {
    $sheet->setCellValue("A$row", $no++);
    $sheet->setCellValue("B$row", $d['nama_lengkap']);
    $sheet->setCellValue("C$row", $d['nama_divisi'] ?? '-');
    $sheet->setCellValue("D$row", $d['gaji_pokok']);
    $sheet->setCellValue("E$row", $d['total_tunjangan']);
    $sheet->setCellValue("F$row", $d['total_lembur']);
    $sheet->setCellValue("G$row", $d['total_potongan']);
    $sheet->setCellValue("H$row", $d['gaji_bersih']);
    $sheet->setCellValue("I$row", strtoupper($d['status']));

    $sheet->getStyle("A$row:I$row")->getBorders()->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN);

    $totalGaji += $d['gaji_bersih'];
    $row++;
}

/* TOTAL */
$row += 1;
$sheet->mergeCells("A$row:G$row");
$sheet->setCellValue("A$row", 'TOTAL SELURUH GAJI');
$sheet->setCellValue("H$row", $totalGaji);

$sheet->getStyle("A$row:I$row")->getFont()->setBold(true);
$sheet->getStyle("A$row:I$row")->getBorders()->getTop()
      ->setBorderStyle(Border::BORDER_THICK);

$row++;
$sheet->mergeCells("A$row:I$row");
$sheet->setCellValue("A$row", 'Terbilang: '.terbilang($totalGaji).' Rupiah');
$sheet->getStyle("A$row")->getFont()->setItalic(true);

/* FOOTER */
$row += 2;
$sheet->mergeCells("A$row:I$row");
$sheet->setCellValue("A$row", 'Dicetak pada: '.$tanggalCetak);
$sheet->getStyle("A$row")->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

/* AUTO WIDTH */
foreach (range('A','I') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

/* OUTPUT */
$filename = "Laporan_Gaji_{$bulanNama}_{$periode['tahun']}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
