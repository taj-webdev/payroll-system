<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$error = '';
$successLogin = false;
$namaLengkap  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan Password wajib diisi.';
    } else {

        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.password, u.nama_lengkap, r.name AS role
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.username = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            $successLogin = true;
            $namaLengkap  = $user['nama_lengkap'];

            // Tentukan redirect
            switch ($user['role']) {
                case 'admin_hrd':
                    $redirect = '../dashboard/admin/index.php';
                    break;
                case 'manager':
                    $redirect = '../dashboard/manager/index.php';
                    break;
                case 'direktur':
                    $redirect = '../dashboard/direktur/index.php';
                    break;
                case 'karyawan':
                    $redirect = '../dashboard/karyawan/index.php';
                    break;
                default:
                    session_destroy();
                    $error = 'Role tidak dikenali.';
            }

        } else {
            $error = 'Username atau Password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="assets/logo.png">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @keyframes fadeIn {
            from { opacity:0; transform:translateY(30px); }
            to { opacity:1; transform:translateY(0); }
        }
        .fade-in { animation: fadeIn 1.2s ease-out forwards; }

        .card-glow::before{
            content:'';
            position:absolute;
            inset:-2px;
            background:linear-gradient(
                120deg,
                rgba(59,130,246,.6),
                rgba(34,197,94,.6),
                rgba(168,85,247,.6)
            );
            filter:blur(40px);
            opacity:.55;
            z-index:-1;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center relative overflow-hidden bg-black">

<!-- BACKGROUND -->
<div class="absolute inset-0 bg-cover bg-center scale-110"
     style="background-image:url('assets/payroll.png');"></div>
<div class="absolute inset-0 bg-gradient-to-br from-blue-900/60 via-black/60 to-purple-900/60"></div>
<div class="absolute inset-0 backdrop-blur-lg"></div>

<!-- LOGIN CARD -->
<div class="relative z-10 fade-in">
<div class="
    relative card-glow
    w-[90vw] max-w-md
    rounded-3xl
    bg-white/15
    backdrop-blur-2xl
    border border-white/20
    shadow-2xl
    px-10 py-12
    text-center
">

    <!-- LOGO -->
    <div class="flex justify-center mb-6">
        <div class="p-4 rounded-2xl bg-white/20 shadow-lg">
            <img src="assets/logo.png" class="w-20 h-20 object-contain">
        </div>
    </div>

    <h1 class="text-3xl font-extrabold tracking-widest text-white mb-2">
        LOGIN
    </h1>
    <p class="text-white/70 mb-8 text-sm">
        Payroll System Secure Access
    </p>

    <?php if ($error): ?>
        <div class="mb-4 text-red-300 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" id="loginForm" class="space-y-5">

        <!-- USERNAME -->
        <div class="relative">
            <i data-lucide="user"
               class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70"></i>
            <input type="text" name="username" required
                placeholder="Username"
                class="w-full pl-12 pr-4 py-3 rounded-xl
                       bg-white/20 text-white placeholder-white/60
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- PASSWORD -->
        <div class="relative">
            <i data-lucide="lock"
               class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70"></i>
            <input type="password" name="password" required
                placeholder="Password"
                class="w-full pl-12 pr-4 py-3 rounded-xl
                       bg-white/20 text-white placeholder-white/60
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- BUTTON -->
        <button type="submit"
            class="
                w-full py-3 rounded-2xl
                bg-gradient-to-r from-blue-600 to-blue-700
                text-white font-semibold tracking-wide
                flex items-center justify-center gap-3
                transition-all duration-300
                hover:scale-105 active:scale-95
            ">
            <i data-lucide="log-in"></i>
            Login
        </button>
    </form>

    <p class="mt-6 text-white/60 text-sm">
        Belum punya akun?
        <a href="register.php" class="text-blue-300 hover:underline">
            Register
        </a>
    </p>

</div>
</div>

<script>
lucide.createIcons();

<?php if ($successLogin): ?>
Swal.fire({
    title: 'Berhasil Login',
    html: 'Selamat Datang Kembali,<br><b><?= htmlspecialchars($namaLengkap) ?></b>',
    icon: 'success',
    timer: 2200,
    showConfirmButton: false,
    backdrop: true
}).then(()=>{
    window.location.href = "<?= $redirect ?>";
});
<?php endif; ?>

// LOADING SPINNER
document.getElementById('loginForm')?.addEventListener('submit', ()=>{
    Swal.fire({
        title: 'Sedang Memproses Login',
        html: 'Mohon tunggu sebentar...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});
</script>

</body>
</html>
