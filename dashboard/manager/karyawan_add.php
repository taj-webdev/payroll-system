<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$error = '';

/* ===============================
   DATA MASTER
================================ */

/* Ambil divisi */
$divisi = $conn->query("
    SELECT id, nama_divisi
    FROM divisi
    ORDER BY nama_divisi
")->fetchAll();

/*
 | Ambil user role KARYAWAN
 | yang BELUM dipakai di tabel karyawan
 */
$users = $conn->query("
    SELECT u.id, u.nama_lengkap, u.username
    FROM users u
    LEFT JOIN karyawan k ON k.user_id = u.id
    WHERE u.role_id = 4
      AND k.user_id IS NULL
    ORDER BY u.nama_lengkap
")->fetchAll();

/* ===============================
   PROSES SUBMIT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id        = (int)($_POST['user_id'] ?? 0);
    $divisi_id      = (int)($_POST['divisi_id'] ?? 0);
    $nik            = trim($_POST['nik'] ?? '');
    $no_ktp         = trim($_POST['no_ktp'] ?? '');
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
    $tempat_lahir   = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir  = $_POST['tanggal_lahir'] ?? null;
    $alamat         = trim($_POST['alamat'] ?? '');
    $jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
    $agama          = $_POST['agama'] ?? '';
    $status         = $_POST['status'] ?? '';

    /* VALIDASI */
    if (
        $user_id <= 0 ||
        $divisi_id <= 0 ||
        $nik === '' ||
        $no_ktp === '' ||
        $nama_lengkap === ''
    ) {
        $error = 'Semua field wajib harus diisi.';
    } else {

        /* Upload Foto */
        $fotoName = null;
        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fotoName = 'karyawan_' . time() . '.' . $ext;

            $uploadDir = __DIR__ . '/../../public/uploads/karyawan/';
            move_uploaded_file(
                $_FILES['foto']['tmp_name'],
                $uploadDir . $fotoName
            );
        }

        /* INSERT */
        $stmt = $conn->prepare("
            INSERT INTO karyawan
            (user_id, divisi_id, nik, no_ktp, nama_lengkap,
             tempat_lahir, tanggal_lahir, alamat,
             jenis_kelamin, agama, status, foto)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->execute([
            $user_id,
            $divisi_id,
            $nik,
            $no_ktp,
            $nama_lengkap,
            $tempat_lahir,
            $tanggal_lahir,
            $alamat,
            $jenis_kelamin,
            $agama,
            $status,
            $fotoName
        ]);

        header('Location: karyawan.php?success=add');
        exit;
    }
}

/* ===============================
   UI
================================ */
require_once __DIR__ . '/header_manager.php';
require_once __DIR__ . '/sidebar_manager.php';
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
    <i data-lucide="user-plus" class="w-7 h-7 text-green-400"></i>
    Tambah Karyawan
</h1>

<?php if($error): ?>
<div class="mb-4 text-red-400"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data"
      class="glass p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

<!-- USER -->
<div class="relative md:col-span-2">
    <i data-lucide="user-check" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="user_id" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">-- Pilih User Karyawan --</option>
        <?php foreach($users as $u): ?>
            <option value="<?= $u['id'] ?>" class="text-black">
                <?= htmlspecialchars($u['nama_lengkap']) ?> (<?= $u['username'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- INPUT -->
<?php
function input($icon,$name,$placeholder,$type='text'){
    echo "
    <div class='relative'>
        <i data-lucide='$icon' class='absolute left-3 top-3 w-5 h-5 text-white/60'></i>
        <input type='$type' name='$name' placeholder='$placeholder' required
            class='w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white
                   placeholder-white/60 focus:outline-none'>
    </div>";
}

input('hash','nik','NIK');
input('credit-card','no_ktp','No KTP');
input('user','nama_lengkap','Nama Lengkap');
input('map-pin','tempat_lahir','Tempat Lahir');
input('calendar','tanggal_lahir','Tanggal Lahir','date');
?>

<div class="relative md:col-span-2">
    <i data-lucide="home" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <textarea name="alamat" placeholder="Alamat"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white
               placeholder-white/60 focus:outline-none"></textarea>
</div>

<!-- DROPDOWN -->
<?php
function select($icon,$name,$options){
    echo "<div class='relative'>
        <i data-lucide='$icon' class='absolute left-3 top-3 w-5 h-5 text-white/60'></i>
        <select name='$name'
            class='w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none'>";
    foreach($options as $o){
        echo "<option value='$o' class='text-black'>$o</option>";
    }
    echo "</select></div>";
}

select('users','jenis_kelamin',['Laki-laki','Perempuan']);
select('heart','agama',['Kristen','Islam','Katholik','Buddha','Konghucu','Hindu']);
select('badge-check','status',['Lajang','Menikah','Janda','Duda']);
?>

<!-- DIVISI -->
<div class="relative">
    <i data-lucide="layers" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="divisi_id" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">-- Pilih Divisi --</option>
        <?php foreach($divisi as $d): ?>
            <option value="<?= $d['id'] ?>" class="text-black">
                <?= $d['nama_divisi'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- FOTO -->
<div class="relative">
    <i data-lucide="image" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="file" name="foto"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- BUTTON -->
<div class="md:col-span-2 flex gap-4 mt-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="save"></i> Simpan
    </button>

    <a href="karyawan.php"
       class="flex items-center gap-2 px-6 py-2 rounded-xl
              bg-gray-600 hover:bg-gray-700 transition">
        <i data-lucide="arrow-left"></i> Kembali
    </a>
</div>

</form>
</main>

<script>
lucide.createIcons();
</script>

<?php require_once __DIR__ . '/footer_manager.php'; ?>
