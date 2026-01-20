<!-- SIDEBAR -->
<aside class="
    fixed top-0 left-0 z-50
    w-64 h-screen
    bg-white/10 backdrop-blur-xl
    border-r border-white/20
    px-5 py-6
">

    <!-- Logo -->
    <div class="flex items-center gap-3 mb-10">
        <img src="../../public/assets/logo.png" class="w-10 h-10">
        <span class="font-bold text-lg">Payroll Direktur</span>
    </div>

    <!-- Menu -->
    <nav class="space-y-2 text-sm">

        <?php
        $menus = [
            ['Dashboard','layout-dashboard','index.php'],
            ['Data Karyawan','users','karyawan.php'],
            ['Data Divisi','layers','divisi.php'],
            ['Data Absensi','calendar-check','absensi.php'],
            ['Data Gaji','wallet','gaji_tahun.php'],
            ['Data Tunjangan','plus-circle','tunjangan.php'],
            ['Data Lembur','clock','lembur.php'],
            ['Data Potongan','minus-circle','potongan.php'],
            ['Kelola Pengguna','user-cog','users.php'],
        ];
        foreach ($menus as $m):
        ?>
        <a href="<?= $m[2] ?>"
           class="
                flex items-center gap-3 px-4 py-3 rounded-xl
                hover:bg-white/20 transition
           ">
            <i data-lucide="<?= $m[1] ?>" class="w-5 h-5 text-cyan-400"></i>
            <?= $m[0] ?>
        </a>
        <?php endforeach; ?>

        <!-- Logout -->
        <button onclick="confirmLogout()"
           class="
                w-full flex items-center gap-3 px-4 py-3 rounded-xl
                bg-red-600/80 hover:bg-red-700 transition
                text-left
           ">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            LogOut
        </button>
    </nav>
</aside>

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
</script>
