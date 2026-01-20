<?php
/**
 * dashboard/admin/lembur_delete.php
 * Hapus data lembur
 */

require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);

/* Validasi ID */
if ($id <= 0) {
    header('Location: lembur.php');
    exit;
}

/* Cek data lembur */
$stmt = $conn->prepare("SELECT id FROM lembur WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: lembur.php');
    exit;
}

/* Hapus data */
$delete = $conn->prepare("DELETE FROM lembur WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header('Location: lembur.php?success=delete');
exit;
