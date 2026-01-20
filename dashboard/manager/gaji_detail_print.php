<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('Asia/Jakarta');

$db = new Database();
$conn = $db->connect();

/* ===============================
   VALIDASI ID
================================ */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID tidak valid');
}

/* ===============================
   AMBIL DATA GAJI
================================ */
$stmt = $conn->prepare("
    SELECT gd.*, 
           k.nama_lengkap, d.nama_divisi,
           gb.bulan, gt.tahun,
           um.nama_lengkap AS manager_nama, rm.name AS manager_role,
           ud.nama_lengkap AS direktur_nama, rd.name AS direktur_role
    FROM gaji_detail gd
    JOIN karyawan k ON k.id = gd.karyawan_id
    LEFT JOIN divisi d ON d.id = k.divisi_id
    JOIN gaji_bulan gb ON gb.id = gd.gaji_bulan_id
    JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
    LEFT JOIN users um ON um.id = gd.approved_by_manager
    LEFT JOIN roles rm ON rm.id = um.role_id
    LEFT JOIN users ud ON ud.id = gd.approved_by_direktur
    LEFT JOIN roles rd ON rd.id = ud.role_id
    WHERE gd.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    die('Data gaji tidak ditemukan');
}

/* ===============================
   TERBILANG
================================ */
function terbilang($angka) {
    $angka = abs($angka);
    $baca  = ["","Satu","Dua","Tiga","Empat","Lima","Enam","Tujuh","Delapan","Sembilan","Sepuluh","Sebelas"];

    if ($angka < 12) return $baca[$angka];
    if ($angka < 20) return terbilang($angka - 10)." Belas";
    if ($angka < 100) return terbilang(intval($angka/10))." Puluh ".terbilang($angka % 10);
    if ($angka < 200) return "Seratus ".terbilang($angka - 100);
    if ($angka < 1000) return terbilang(intval($angka/100))." Ratus ".terbilang($angka % 100);
    if ($angka < 2000) return "Seribu ".terbilang($angka - 1000);
    if ($angka < 1000000) return terbilang(intval($angka/1000))." Ribu ".terbilang($angka % 1000);
    if ($angka < 1000000000) return terbilang(intval($angka/1000000))." Juta ".terbilang($angka % 1000000);

    return "Terlalu Besar";
}

$bulanNama = date('F', mktime(0,0,0,$data['bulan'],1));
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
    font-size: 12px;
    margin-bottom: 80px; /* ruang agar konten tidak ketiban footer */
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
    margin-top: 15px;
}

.table th, .table td {
    border: 1px solid #555;
    padding: 8px;
}

.table th {
    background: #f0f0f0;
}

.right { text-align: right; }

.approve {
    margin-top: 40px;
}

/* === FOOTER FIXED BOTTOM === */
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
    <div class="title">
        <h2>SLIP GAJI</h2>
        <strong>PAYROLL SYSTEM</strong><br>
        Periode: '.$bulanNama.' '.$data['tahun'].'
    </div>
</div>

<table class="table">
<tr><th>Nama Karyawan</th><td>'.$data['nama_lengkap'].'</td></tr>
<tr><th>Divisi</th><td>'.($data['nama_divisi'] ?? '-').'</td></tr>
<tr><th>Status Gaji</th><td>'.strtoupper($data['status']).'</td></tr>
</table>

<table class="table">
<tr><th>Komponen</th><th class="right">Jumlah</th></tr>
<tr><td>Gaji Pokok</td><td class="right">Rp '.number_format($data['gaji_pokok'],0,',','.').'</td></tr>
<tr><td>Total Tunjangan</td><td class="right">Rp '.number_format($data['total_tunjangan'],0,',','.').'</td></tr>
<tr><td>Total Lembur</td><td class="right">Rp '.number_format($data['total_lembur'],0,',','.').'</td></tr>
<tr><td>Total Potongan</td><td class="right">Rp '.number_format($data['total_potongan'],0,',','.').'</td></tr>
<tr>
    <th>Total Gaji Bersih</th>
    <th class="right">Rp '.number_format($data['gaji_bersih'],0,',','.').'</th>
</tr>
</table>

<p><strong>Terbilang:</strong> '.terbilang($data['gaji_bersih']).' Rupiah</p>

<p><strong>Catatan:</strong><br>'.nl2br(htmlspecialchars($data['catatan'])).'</p>

<div class="approve">
<strong>DISETUJUI OLEH:</strong><br><br><br>';

if ($data['approved_by_direktur']) {
    $html .= $data['direktur_nama'].'<br>'.$data['direktur_role'];
} elseif ($data['approved_by_manager']) {
    $html .= $data['manager_nama'].'<br>'.$data['manager_role'];
} else {
    $html .= 'Belum Disetujui';
}

$html .= '
</div>

<div class="footer">
Dicetak Pada : '.$tanggalCetak.'
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
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Slip_Gaji_".$data['nama_lengkap'].".pdf", ["Attachment"=>false]);
