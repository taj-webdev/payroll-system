<?php
/**
 * dashboard/admin/tunjangan_delete.php
 * Hapus data tunjangan
 */

require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);

/* Validasi ID */
if ($id <= 0) {
    header('Location: tunjangan.php');
    exit;
}

/* Cek data tunjangan */
$stmt = $conn->prepare("SELECT id FROM tunjangan WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: tunjangan.php');
    exit;
}

/* Hapus data */
$delete = $conn->prepare("DELETE FROM tunjangan WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header('Location: tunjangan.php?success=delete');
exit;
