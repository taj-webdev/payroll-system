<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/* ===============================
   TIMEZONE
================================ */
date_default_timezone_set('Asia/Jakarta');

/* ===============================
   DB
================================ */
$db = new Database();
$conn = $db->connect();

/* ===============================
   FILTER
================================ */
$status      = $_GET['status'] ?? '';
$tanggal     = $_GET['tanggal'] ?? '';
$karyawan_id = (int)($_GET['karyawan_id'] ?? 0);

$where  = [];
$params = [];

if ($status !== '') {
    $where[]  = 'a.status = ?';
    $params[] = $status;
}
if ($tanggal !== '') {
    $where[]  = 'a.tanggal = ?';
    $params[] = $tanggal;
}
if ($karyawan_id > 0) {
    $where[]  = 'a.karyawan_id = ?';
    $params[] = $karyawan_id;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* ===============================
   QUERY DATA
================================ */
$stmt = $conn->prepare("
    SELECT a.*, k.nama_lengkap
    FROM absensi a
    JOIN karyawan k ON k.id = a.karyawan_id
    $whereSql
    ORDER BY a.tanggal ASC
");
$stmt->execute($params);
$data = $stmt->fetchAll();

/* ===============================
   HEADER INFO
================================ */
$judul   = 'LAPORAN ABSENSI KARYAWAN';
$periode = 'Semua Data';

if ($tanggal) {
    $periode = 'Tanggal : ' . date('d M Y', strtotime($tanggal));
}
if ($status) {
    $periode .= ' | Status : ' . $status;
}

$waktuCetak = date('l, d F Y H:i:s') . ' WIB';

/* ===============================
   SPREADSHEET
================================ */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Absensi');

/* HEADER */
$sheet->mergeCells('A1:G1');
$sheet->mergeCells('A2:G2');

$sheet->setCellValue('A1', $judul);
$sheet->setCellValue('A2', $periode);

$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold'=>true,'size'=>14],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER]
]);
$sheet->getStyle('A2')->applyFromArray([
    'font' => ['italic'=>true],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER]
]);

/* TABLE HEADER */
$headerRow = 4;
$headers = ['No','Karyawan','Tanggal','Status','Jam Masuk','Jam Pulang','Keterangan'];
$col = 'A';

foreach ($headers as $h) {
    $sheet->setCellValue($col.$headerRow, $h);
    $col++;
}

$sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
    'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
    'fill' => [
        'fillType'=>Fill::FILL_SOLID,
        'startColor'=>['rgb'=>'1E293B']
    ],
    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER]
]);

/* DATA */
$rowNum = $headerRow + 1;
$no = 1;

foreach ($data as $r) {
    $sheet->setCellValue("A{$rowNum}", $no++);
    $sheet->setCellValue("B{$rowNum}", $r['nama_lengkap']);
    $sheet->setCellValue("C{$rowNum}", date('d M Y', strtotime($r['tanggal'])));
    $sheet->setCellValue("D{$rowNum}", $r['status']);
    $sheet->setCellValue("E{$rowNum}", $r['jam_masuk'] ?? '-');
    $sheet->setCellValue("F{$rowNum}", $r['jam_pulang'] ?? '-');
    $sheet->setCellValue("G{$rowNum}", $r['keterangan'] ?? '-');
    $rowNum++;
}

/* BORDER */
$sheet->getStyle("A{$headerRow}:G".($rowNum-1))->applyFromArray([
    'borders'=>[
        'allBorders'=>[
            'borderStyle'=>Border::BORDER_THIN
        ]
    ]
]);

/* AUTO WIDTH */
foreach (range('A','G') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

/* FOOTER */
$footerRow = $rowNum + 2;
$sheet->mergeCells("A{$footerRow}:G{$footerRow}");
$sheet->setCellValue("A{$footerRow}", "Dicetak pada : {$waktuCetak}");
$sheet->getStyle("A{$footerRow}")->applyFromArray([
    'font'=>['italic'=>true,'size'=>10],
    'alignment'=>['horizontal'=>Alignment::HORIZONTAL_RIGHT]
]);

/* ===============================
   OUTPUT
================================ */
$filename = 'laporan_absensi.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
