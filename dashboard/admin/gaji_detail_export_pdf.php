<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
   AMBIL DATA BULAN
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
   TOTAL
================================ */
$totalGaji = 0;
foreach ($data as $d) {
    $totalGaji += $d['gaji_bersih'];
}

$bulanNama = date('F', mktime(0,0,0,$periode['bulan'],1));
$tanggalCetak = date('l, d F Y H:i:s') . ' WIB';

/* ===============================
   HTML PDF
================================ */
$html = '
<!DOCTYPE html>
<html>
<head>
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    margin-bottom: 90px;
}

.header {
    text-align: center;
    border-bottom: 2px solid #333;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

h2 { margin: 0; }

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    border: 1px solid #555;
    padding: 6px;
}

.table th {
    background: #f0f0f0;
}

.right { text-align: right; }

.total {
    margin-top: 15px;
    font-size: 12px;
}

/* FOOTER FIXED */
.footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    border-top: 2px solid #333;
    padding-top: 8px;
    text-align: center;
    font-size: 11px;
    color: #555;
}
</style>
</head>
<body>

<div class="header">
    <h2>LAPORAN GAJI</h2>
    <strong>PAYROLL SYSTEM</strong><br>
    Periode: '.$bulanNama.' '.$periode['tahun'].'
</div>

<table class="table">
<tr>
    <th>No</th>
    <th>Nama Karyawan</th>
    <th>Divisi</th>
    <th>Gaji Pokok</th>
    <th>Tunjangan</th>
    <th>Lembur</th>
    <th>Potongan</th>
    <th>Gaji Bersih</th>
    <th>Status</th>
</tr>';

$no = 1;
foreach ($data as $d) {
    $html .= '
    <tr>
        <td>'.$no++.'</td>
        <td>'.$d['nama_lengkap'].'</td>
        <td>'.($d['nama_divisi'] ?? '-').'</td>
        <td class="right">Rp '.number_format($d['gaji_pokok'],0,',','.').'</td>
        <td class="right">Rp '.number_format($d['total_tunjangan'],0,',','.').'</td>
        <td class="right">Rp '.number_format($d['total_lembur'],0,',','.').'</td>
        <td class="right">Rp '.number_format($d['total_potongan'],0,',','.').'</td>
        <td class="right"><strong>Rp '.number_format($d['gaji_bersih'],0,',','.').'</strong></td>
        <td>'.strtoupper($d['status']).'</td>
    </tr>';
}

$html .= '
</table>

<div class="total">
    <strong>Total Seluruh Gaji:</strong><br>
    Rp '.number_format($totalGaji,0,',','.').'<br>
    <em>Terbilang: '.terbilang($totalGaji).' Rupiah</em>
</div>

<div class="footer">
    Dicetak pada: '.$tanggalCetak.'
</div>

</body>
</html>
';

/* ===============================
   GENERATE PDF
================================ */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream(
    "Laporan_Gaji_".$bulanNama."_".$periode['tahun'].".pdf",
    ["Attachment"=>false]
);
