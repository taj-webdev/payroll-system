<?php
// dashboard/admin/header_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_hrd') {
    header('Location: ../../public/login.php');
    exit;
}

$nama = strtoupper($_SESSION['username'] ?? 'USER');
$role = strtoupper(str_replace('_', ' ', $_SESSION['role']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../public/assets/logo.png">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        @keyframes wave {
            0% { transform: rotate(0deg); }
            20% { transform: rotate(14deg); }
            40% { transform: rotate(-8deg); }
            60% { transform: rotate(14deg); }
            80% { transform: rotate(-4deg); }
            100% { transform: rotate(0deg); }
        }
        .wave {
            display: inline-block;
            animation: wave 2s infinite;
            transform-origin: 70% 70%;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">

<!-- HEADER -->
<header class="
    fixed top-0 left-64 right-0 z-40
    h-20 px-6
    flex items-center justify-between
    bg-white/10 backdrop-blur-xl
    border-b border-white/20
">

    <!-- Welcome -->
    <div class="text-lg font-semibold">
        <span class="wave">🖐️</span>
        Selamat Datang,
        <span class="text-blue-300"><?= $nama ?></span>
        <span class="text-sm text-white/70">(<?= $role ?>)</span>
    </div>

    <!-- Date & Logout -->
    <div class="flex items-center gap-4">

        <div class="flex items-center gap-2 text-sm text-white/80">
            <i data-lucide="calendar" class="w-5 h-5 text-indigo-400"></i>
            <span id="datetime"></span>
        </div>

        <button onclick="confirmLogout()"
           class="
                flex items-center gap-2
                px-4 py-2 rounded-xl
                bg-red-600 hover:bg-red-700
                transition-all duration-300
                hover:scale-105
           ">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            Logout
        </button>
    </div>
</header>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
lucide.createIcons();

function confirmLogout() {
    Swal.fire({
        title: 'Apakah Anda ingin LogOut?',
        text: 'Sesi Anda akan diakhiri',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batalkan',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#16a34a',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Memproses LogOut',
                html: `
                    <div class="flex flex-col items-center gap-4 mt-4">
                        <svg class="animate-spin h-10 w-10 text-red-500"
                             xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <p class="text-sm text-white/70">
                            Sedang memproses LogOut...
                        </p>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                background: '#0f172a'
            });

            setTimeout(() => {
                window.location.href = '../../public/logout.php';
            }, 1200);
        }
    });
}
    function updateDateTime() {
        const now = new Date();
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        document.getElementById('datetime').innerText =
            now.toLocaleDateString('id-ID', options);
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();

    lucide.createIcons();
</script>
