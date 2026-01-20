<?php
/**
 * dashboard/admin/potongan_delete.php
 * Hapus data potongan
 */

require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);

/* Validasi ID */
if ($id <= 0) {
    header('Location: potongan.php');
    exit;
}

/* Cek data potongan */
$stmt = $conn->prepare("SELECT id FROM potongan WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: potongan.php');
    exit;
}

/* Hapus data */
$delete = $conn->prepare("DELETE FROM potongan WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header('Location: potongan.php?success=delete');
exit;
