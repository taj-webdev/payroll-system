<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$error = '';

/* ===============================
   DATA MASTER
================================ */

/* Ambil karyawan */
$karyawan = $conn->query("
    SELECT id, nama_lengkap
    FROM karyawan
    ORDER BY nama_lengkap
")->fetchAll();

/* ===============================
   PROSES SUBMIT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $karyawan_id = (int)($_POST['karyawan_id'] ?? 0);
    $tanggal     = $_POST['tanggal'] ?? '';
    $status      = $_POST['status'] ?? '';
    $jam_masuk   = $_POST['jam_masuk'] ?: null;
    $jam_pulang  = $_POST['jam_pulang'] ?: null;
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($karyawan_id <= 0 || $tanggal === '' || $status === '') {
        $error = 'Karyawan, tanggal, dan status wajib diisi.';
    } else {

        /* INSERT */
        $stmt = $conn->prepare("
            INSERT INTO absensi
            (karyawan_id, tanggal, status, jam_masuk, jam_pulang, keterangan)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->execute([
            $karyawan_id,
            $tanggal,
            $status,
            $jam_masuk,
            $jam_pulang,
            $keterangan
        ]);

        header('Location: absensi.php?success=add');
        exit;
    }
}

/* ===============================
   UI
================================ */
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/sidebar_admin.php';
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
        <i data-lucide="calendar-plus" class="w-7 h-7 text-green-400"></i>
        Tambah Absensi
    </h1>

    <a href="absensi.php"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-gray-600 hover:bg-gray-700 transition">
        <i data-lucide="arrow-left"></i>
        Kembali
    </a>
</div>

<?php if($error): ?>
<div class="mb-4 text-red-400"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST"
      class="glass p-6 grid grid-cols-1 md:grid-cols-2 gap-5 max-w-3xl">

<!-- KARYAWAN -->
<div class="relative md:col-span-2">
    <i data-lucide="user-check" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="karyawan_id" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">-- Pilih Karyawan --</option>
        <?php foreach($karyawan as $k): ?>
            <option value="<?= $k['id'] ?>" class="text-black">
                <?= htmlspecialchars($k['nama_lengkap']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- TANGGAL -->
<div class="relative">
    <i data-lucide="calendar" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="date" name="tanggal" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- STATUS -->
<div class="relative">
    <i data-lucide="check-circle" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="status" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">-- Pilih Status --</option>
        <?php foreach(['Hadir','Izin','Sakit','Alpha'] as $s): ?>
            <option value="<?= $s ?>" class="text-black"><?= $s ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- JAM MASUK -->
<div class="relative">
    <i data-lucide="clock" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="time" name="jam_masuk"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- JAM PULANG -->
<div class="relative">
    <i data-lucide="clock-4" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="time" name="jam_pulang"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- KETERANGAN -->
<div class="relative md:col-span-2">
    <i data-lucide="align-left" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <textarea name="keterangan" placeholder="Keterangan (opsional)"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white
               placeholder-white/60 focus:outline-none"></textarea>
</div>

<!-- BUTTON -->
<div class="md:col-span-2 flex gap-4 mt-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="save"></i> Simpan
    </button>

    <a href="absensi.php"
       class="flex items-center gap-2 px-6 py-2 rounded-xl
              bg-gray-600 hover:bg-gray-700 transition">
        <i data-lucide="x-circle"></i> Batal
    </a>
</div>

</form>
</main>

<script>
lucide.createIcons();
</script>

<?php require_once __DIR__ . '/footer_admin.php'; ?>
