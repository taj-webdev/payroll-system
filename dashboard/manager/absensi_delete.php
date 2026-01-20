<?php
/**
 * dashboard/manager/absensi_delete.php
 * Hapus data absensi
 */

require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);

/* Validasi ID */
if ($id <= 0) {
    header('Location: absensi.php');
    exit;
}

/* Cek data absensi */
$stmt = $conn->prepare("SELECT id FROM absensi WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: absensi.php');
    exit;
}

/* Hapus absensi */
$delete = $conn->prepare("DELETE FROM absensi WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header('Location: absensi.php?success=delete');
exit;
