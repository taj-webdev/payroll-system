<?php
/**
 * public/logout.php
 * Destroy session & show SweetAlert success
 */

session_start();

/* ===============================
   Simpan nama user (opsional)
================================ */
$nama = $_SESSION['username'] ?? 'User';

/* ===============================
   Hapus semua session data
================================ */
$_SESSION = [];

/* ===============================
   Hapus cookie session (jika ada)
================================ */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/* ===============================
   Destroy session
================================ */
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Logout | Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: radial-gradient(circle at top, #020617, #000);
        }
    </style>
</head>
<body>

<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil LogOut',
    html: `
        <p class="text-sm mt-2">
            Sampai jumpa kembali,<br>
            <strong><?= htmlspecialchars(strtoupper($nama)) ?></strong>
        </p>
    `,
    confirmButtonText: 'OK',
    confirmButtonColor: '#16a34a',
    background: '#020617',
    color: '#e5e7eb',
    allowOutsideClick: false
}).then(() => {
    window.location.href = 'login.php';
});
</script>

</body>
</html>
