<?php
/**
 * dashboard/admin/karyawan_delete.php
 * Hapus data karyawan + foto
 */

require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: karyawan.php');
    exit;
}

/* Ambil data karyawan (untuk foto) */
$stmt = $conn->prepare("SELECT foto FROM karyawan WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: karyawan.php');
    exit;
}

/* Hapus foto fisik jika ada */
if (!empty($data['foto'])) {
    $fotoPath = '../../public/uploads/karyawan/' . $data['foto'];
    if (file_exists($fotoPath)) {
        unlink($fotoPath);
    }
}

/* Hapus data karyawan */
$delete = $conn->prepare("DELETE FROM karyawan WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header('Location: karyawan.php?success=delete');
exit;
