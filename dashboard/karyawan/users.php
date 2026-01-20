<?php
require_once __DIR__ . '/../../app/config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];

/* Ambil data user */
$stmt = $conn->prepare("SELECT username, password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die('User tidak ditemukan');
}

$error = '';
$success = '';

/* ===============================
   PROSES GANTI PASSWORD
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$old || !$new || !$confirm) {
        $error = 'Semua field wajib diisi.';
    } elseif ($new !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (!password_verify($old, $user['password'])) {
        $error = 'Password lama salah.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $user_id]);

        header('Location: users.php?success=1');
        exit;
    }
}

require_once __DIR__ . '/header_karyawan.php';
require_once __DIR__ . '/sidebar_karyawan.php';
?>

<main class="ml-64 mt-24 mb-16 px-6 fade-in">

<style>
@keyframes fadeIn {
    from {opacity:0; transform:translateY(20px);}
    to {opacity:1; transform:translateY(0);}
}
.fade-in{animation:fadeIn 1.2s ease-out;}
.glass{
    background: rgba(255,255,255,.10);
    backdrop-filter: blur(14px);
    border:1px solid rgba(255,255,255,.18);
    border-radius:1rem;
}
</style>

<h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
    <i data-lucide="key" class="w-7 h-7 text-cyan-400"></i>
    Kelola Pengguna
</h1>

<?php if(isset($_GET['success'])): ?>
<script>
document.addEventListener("DOMContentLoaded",()=>{
    Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Password berhasil diperbarui",
        confirmButtonColor: "#22c55e"
    });
});
</script>
<?php endif; ?>

<?php if($error): ?>
<script>
document.addEventListener("DOMContentLoaded",()=>{
    Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "<?= addslashes($error) ?>"
    });
});
</script>
<?php endif; ?>

<form method="POST" class="glass p-6 max-w-xl">

<!-- USERNAME -->
<div class="mb-4 relative">
    <i data-lucide="user" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white">
</div>

<!-- PASSWORD LAMA -->
<div class="mb-4 relative">
    <i data-lucide="lock" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="password" name="old_password" placeholder="Password Lama" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- PASSWORD BARU -->
<div class="mb-4 relative">
    <i data-lucide="lock-keyhole" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="password" name="new_password" placeholder="Password Baru" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- KONFIRMASI -->
<div class="mb-6 relative">
    <i data-lucide="check-circle" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="password" name="confirm_password" placeholder="Konfirmasi Password Baru" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<div class="flex gap-4">
    <button type="submit"
        onclick="return confirmChange()"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-yellow-500 hover:bg-yellow-600 transition">
        <i data-lucide="save"></i>
        Simpan
    </button>

    <a href="index.php"
       class="flex items-center gap-2 px-6 py-2 rounded-xl
              bg-gray-600 hover:bg-gray-700 transition">
        <i data-lucide="arrow-left"></i>
        Kembali
    </a>
</div>

</form>
</main>

<script>
lucide.createIcons();

function confirmChange(){
    Swal.fire({
        title: "Ubah Password?",
        text: "Password lama akan diganti dengan yang baru",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#22c55e",
        cancelButtonColor: "#ef4444",
        confirmButtonText: "Ya, Ubah",
        cancelButtonText: "Batal"
    }).then((result)=>{
        if(result.isConfirmed){
            document.querySelector("form").submit();
        }
    });
    return false;
}
</script>

<?php require_once __DIR__ . '/footer_karyawan.php'; ?>
