<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: karyawan.php');
    exit;
}

/* ===============================
   AMBIL DATA KARYAWAN + USER
================================ */
$stmt = $conn->prepare("
    SELECT k.*, u.nama_lengkap AS nama_user, u.username
    FROM karyawan k
    JOIN users u ON u.id = k.user_id
    WHERE k.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: karyawan.php');
    exit;
}

/* Ambil divisi */
$divisi = $conn->query("
    SELECT id, nama_divisi
    FROM divisi
    ORDER BY nama_divisi
")->fetchAll();

$error = '';

/* ===============================
   PROSES UPDATE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $divisi_id     = (int)($_POST['divisi_id'] ?? 0);
    $nik           = trim($_POST['nik'] ?? '');
    $no_ktp        = trim($_POST['no_ktp'] ?? '');
    $nama_lengkap  = trim($_POST['nama_lengkap'] ?? '');
    $tempat_lahir  = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
    $alamat        = trim($_POST['alamat'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $agama         = $_POST['agama'] ?? '';
    $status        = $_POST['status'] ?? '';

    if (
        $divisi_id <= 0 ||
        $nik === '' ||
        $no_ktp === '' ||
        $nama_lengkap === ''
    ) {
        $error = 'Semua field wajib harus diisi.';
    } else {

        /* FOTO */
        $fotoName = $data['foto'];
        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fotoName = 'karyawan_' . time() . '.' . $ext;

            $uploadDir = __DIR__ . '/../../public/uploads/karyawan/';
            move_uploaded_file(
                $_FILES['foto']['tmp_name'],
                $uploadDir . $fotoName
            );
        }

        /* UPDATE */
        $stmt = $conn->prepare("
            UPDATE karyawan SET
                divisi_id      = ?,
                nik            = ?,
                no_ktp         = ?,
                nama_lengkap   = ?,
                tempat_lahir   = ?,
                tanggal_lahir  = ?,
                alamat         = ?,
                jenis_kelamin  = ?,
                agama          = ?,
                status         = ?,
                foto           = ?
            WHERE id = ?
        ");

        $stmt->execute([
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
            $fotoName,
            $id
        ]);

        header('Location: karyawan.php?success=edit');
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

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="edit" class="w-7 h-7 text-yellow-400"></i>
        Edit Karyawan
    </h1>

    <a href="karyawan_detail.php?id=<?= $data['id'] ?>"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-blue-600 hover:bg-blue-700 transition">
        <i data-lucide="eye"></i>
        Detail
    </a>
</div>

<?php if($error): ?>
<div class="mb-4 text-red-400"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data"
      class="glass p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

<!-- USER (LOCKED) -->
<div class="relative md:col-span-2 opacity-70">
    <i data-lucide="user-check" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="text"
        value="<?= htmlspecialchars($data['nama_user'].' ('.$data['username'].')') ?>"
        disabled
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white cursor-not-allowed">
</div>

<?php
function inputEdit($icon,$name,$value,$type='text'){
    $value = htmlspecialchars($value ?? '');
    echo "
    <div class='relative'>
        <i data-lucide='$icon' class='absolute left-3 top-3 w-5 h-5 text-white/60'></i>
        <input type='$type' name='$name' value='$value' required
            class='w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none'>
    </div>";
}

inputEdit('hash','nik',$data['nik']);
inputEdit('credit-card','no_ktp',$data['no_ktp']);
inputEdit('user','nama_lengkap',$data['nama_lengkap']);
inputEdit('map-pin','tempat_lahir',$data['tempat_lahir']);
inputEdit('calendar','tanggal_lahir',$data['tanggal_lahir'],'date');
?>

<div class="relative md:col-span-2">
    <i data-lucide="home" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <textarea name="alamat"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white
               focus:outline-none"><?= htmlspecialchars($data['alamat']) ?></textarea>
</div>

<?php
function selectEdit($icon,$name,$options,$selected){
    echo "<div class='relative'>
        <i data-lucide='$icon' class='absolute left-3 top-3 w-5 h-5 text-white/60'></i>
        <select name='$name'
            class='w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none'>";
    foreach($options as $o){
        $sel = $o == $selected ? 'selected' : '';
        echo "<option value='$o' $sel class='text-black'>$o</option>";
    }
    echo "</select></div>";
}

selectEdit('users','jenis_kelamin',['Laki-laki','Perempuan'],$data['jenis_kelamin']);
selectEdit('heart','agama',['Kristen','Islam','Katholik','Buddha','Konghucu','Hindu'],$data['agama']);
selectEdit('badge-check','status',['Lajang','Menikah','Janda','Duda'],$data['status']);
?>

<!-- DIVISI -->
<div class="relative">
    <i data-lucide="layers" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="divisi_id" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <?php foreach($divisi as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $d['id']==$data['divisi_id']?'selected':'' ?>
                class="text-black">
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
               bg-yellow-500 hover:bg-yellow-600 transition">
        <i data-lucide="save"></i> Simpan Perubahan
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
