<?php
/**
 * dashboard/manager/gaji_tahun_delete.php
 * Hapus data gaji tahunan
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

/* Cek data gaji tahun */
$stmt = $conn->prepare("
    SELECT id, status 
    FROM gaji_tahun 
    WHERE id = ? 
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: gaji_tahun.php');
    exit;
}

/* Optional: cegah hapus jika locked */
if ($data['status'] === 'locked') {
    header('Location: gaji_tahun.php?error=locked');
    exit;
}

/* Hapus data */
$delete = $conn->prepare("DELETE FROM gaji_tahun WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header('Location: gaji_tahun.php?success=delete');
exit;
