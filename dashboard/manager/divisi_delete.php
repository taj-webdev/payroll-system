<?php
/**
 * dashboard/manager/divisi_delete.php
 * Hapus data divisi
 */

require_once __DIR__ . '/header_manager.php';
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: divisi.php');
    exit;
}

/* Cek data divisi */
$stmt = $conn->prepare("SELECT id FROM divisi WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: divisi.php');
    exit;
}

/*
 |---------------------------------------------------------
 | OPTIONAL (KEAMANAN TAMBAHAN)
 | Cegah hapus jika divisi masih dipakai karyawan
 |---------------------------------------------------------
 */
$cek = $conn->prepare("SELECT COUNT(*) FROM karyawan WHERE divisi_id = ?");
$cek->execute([$id]);
$dipakai = $cek->fetchColumn();

if ($dipakai > 0) {
    // Kalau mau, bisa redirect dengan pesan error khusus
    header('Location: divisi.php');
    exit;
}

/* Hapus divisi */
$delete = $conn->prepare("DELETE FROM divisi WHERE id = ?");
$delete->execute([$id]);

/* Redirect + SweetAlert */
header('Location: divisi.php?success=delete');
exit;
