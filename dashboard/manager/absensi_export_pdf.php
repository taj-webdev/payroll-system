<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===============================
   SET TIMEZONE
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

$where = [];
$params = [];

if ($status !== '') {
    $where[] = 'a.status = ?';
    $params[] = $status;
}
if ($tanggal !== '') {
    $where[] = 'a.tanggal = ?';
    $params[] = $tanggal;
}
if ($karyawan_id > 0) {
    $where[] = 'a.karyawan_id = ?';
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
$judul = 'LAPORAN ABSENSI KARYAWAN';

$periode = 'Semua Data';
if ($tanggal) {
    $periode = 'Tanggal : ' . date('d M Y', strtotime($tanggal));
}
if ($status) {
    $periode .= ' | Status : ' . $status;
}

$waktuCetak = date('l, d F Y H:i:s') . ' WIB';

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
            color: #111;
        }
        h1 {
            text-align: center;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }
        .sub {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background: #1e293b;
            color: #fff;
            padding: 8px;
            font-size: 11px;
        }
        td {
            padding: 7px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 8px;
            color: #fff;
            font-size: 10px;
        }
        .Hadir { background:#16a34a; }
        .Izin { background:#2563eb; }
        .Sakit { background:#eab308; }
        .Alpha { background:#dc2626; }
        footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

<h1>'.$judul.'</h1>
<div class="sub">'.$periode.'</div>

<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Karyawan</th>
            <th width="15%">Tanggal</th>
            <th width="12%">Status</th>
            <th width="12%">Masuk</th>
            <th width="12%">Pulang</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>';

if (!$data) {
    $html .= '
        <tr>
            <td colspan="7" style="text-align:center;">Data tidak ditemukan</td>
        </tr>';
}

$no = 1;
foreach ($data as $r) {
    $html .= '
    <tr>
        <td align="center">'.$no++.'</td>
        <td align="center">'.htmlspecialchars($r['nama_lengkap']).'</td>
        <td align="center">'.date('d M Y', strtotime($r['tanggal'])).'</td>
        <td align="center">
            <span class="badge '.$r['status'].'">'.$r['status'].'</span>
        </td>
        <td align="center">'.($r['jam_masuk'] ?? '-').'</td>
        <td align="center">'.($r['jam_pulang'] ?? '-').'</td>
        <td align="center">'.htmlspecialchars($r['keterangan'] ?? '-').'</td>
    </tr>';
}

$html .= '
    </tbody>
</table>

<footer>
    Dicetak pada : '.$waktuCetak.'
</footer>

</body>
</html>
';

/* ===============================
   DOMPDF
================================ */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('laporan_absensi.pdf', ['Attachment' => false]);
exit;
