<?php
/**
 * dashboard/admin/gaji_detail_delete.php
 * Hapus data gaji karyawan (per bulan)
 */

require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);

/* Validasi ID */
if ($id <= 0) {
    header('Location: gaji_tahun.php');
    exit;
}

/* Ambil data gaji untuk redirect */
$stmt = $conn->prepare("
    SELECT id, gaji_bulan_id
    FROM gaji_detail
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: gaji_tahun.php');
    exit;
}

/* Hapus data gaji */
$delete = $conn->prepare("DELETE FROM gaji_detail WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header(
    'Location: gaji_detail.php?bulan_id=' .
    $data['gaji_bulan_id'] .
    '&success=delete'
);
exit;
