<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = $_POST['password'] ?? '';
    $password2    = $_POST['password_confirm'] ?? '';
    $role_id      = (int)($_POST['role_id'] ?? 0);

    if ($nama_lengkap === '' || $username === '' || $password === '' || $password2 === '' || $role_id === 0) {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {

        // Cek username unik
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $check->execute([$username]);

        if ($check->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (role_id, nama_lengkap, username, password)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$role_id, $nama_lengkap, $username, $hash]);

            $success = true;
        }
    }
}

$roles = $conn->query("SELECT id, name FROM roles ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register | Payroll System</title>
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
                rgba(34,197,94,.6),
                rgba(59,130,246,.6),
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
<div class="absolute inset-0 bg-gradient-to-br from-green-900/60 via-black/60 to-blue-900/60"></div>
<div class="absolute inset-0 backdrop-blur-lg"></div>

<!-- REGISTER CARD -->
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
        REGISTER
    </h1>
    <p class="text-white/70 mb-8 text-sm">
        Payroll System Account
    </p>

    <?php if ($error): ?>
        <div class="mb-4 text-red-300 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" id="registerForm" class="space-y-5">

        <div class="relative">
            <i data-lucide="id-card"
               class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70"></i>
            <input type="text" name="nama_lengkap" required
                placeholder="Nama Lengkap"
                class="w-full pl-12 pr-4 py-3 rounded-xl
                       bg-white/20 text-white placeholder-white/60
                       focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <div class="relative">
            <i data-lucide="user"
               class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70"></i>
            <input type="text" name="username" required
                placeholder="Username"
                class="w-full pl-12 pr-4 py-3 rounded-xl
                       bg-white/20 text-white placeholder-white/60
                       focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <div class="relative">
            <i data-lucide="users"
               class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70"></i>
            <select name="role_id" required
                class="w-full pl-12 pr-4 py-3 rounded-xl
                       bg-white/20 text-white
                       focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="" class="text-black">-- Pilih Role --</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" class="text-black">
                        <?= ucfirst(str_replace('_',' ', $r['name'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="relative">
            <i data-lucide="lock"
               class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70"></i>
            <input type="password" name="password" required
                placeholder="Password"
                class="w-full pl-12 pr-4 py-3 rounded-xl
                       bg-white/20 text-white placeholder-white/60
                       focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <div class="relative">
            <i data-lucide="lock-keyhole"
               class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70"></i>
            <input type="password" name="password_confirm" required
                placeholder="Konfirmasi Password"
                class="w-full pl-12 pr-4 py-3 rounded-xl
                       bg-white/20 text-white placeholder-white/60
                       focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <button type="submit"
            class="
                w-full py-3 rounded-2xl
                bg-gradient-to-r from-green-600 to-green-700
                text-white font-semibold tracking-wide
                flex items-center justify-center gap-3
                transition-all duration-300
                hover:scale-105 active:scale-95
            ">
            <i data-lucide="user-plus"></i>
            Register
        </button>
    </form>

    <p class="mt-6 text-white/60 text-sm">
        Sudah punya akun?
        <a href="login.php" class="text-green-300 hover:underline">
            Login
        </a>
    </p>

</div>
</div>

<script>
lucide.createIcons();

// LOADING REGISTRASI
document.getElementById('registerForm')?.addEventListener('submit', ()=>{
    Swal.fire({
        title: 'Sedang Memproses Registrasi',
        html: 'Mohon tunggu sebentar...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
});

<?php if ($success): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil Register',
    text: 'Silahkan Login',
    timer: 2200,
    showConfirmButton: false
}).then(()=>{
    window.location.href = 'login.php';
});
<?php endif; ?>
</script>

</body>
</html>
