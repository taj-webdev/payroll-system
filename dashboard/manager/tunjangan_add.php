<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$error = '';

/* ===============================
   DATA MASTER
================================ */
$karyawan = $conn->query("
    SELECT id, nama_lengkap
    FROM karyawan
    ORDER BY nama_lengkap
")->fetchAll();

$divisi = $conn->query("
    SELECT id, nama_divisi
    FROM divisi
    ORDER BY nama_divisi
")->fetchAll();

/* ===============================
   PROSES SUBMIT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $karyawan_id = $_POST['karyawan_id'] !== '' ? (int)$_POST['karyawan_id'] : null;
    $divisi_id   = $_POST['divisi_id'] !== '' ? (int)$_POST['divisi_id'] : null;
    $nama        = trim($_POST['nama'] ?? '');
    $nominal     = (float)($_POST['nominal'] ?? 0);
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($nama === '' || $nominal <= 0) {
        $error = 'Nama tunjangan dan nominal wajib diisi.';
    } else {

        $stmt = $conn->prepare("
            INSERT INTO tunjangan
            (karyawan_id, divisi_id, nama, nominal, keterangan)
            VALUES (?,?,?,?,?)
        ");
        $stmt->execute([
            $karyawan_id,
            $divisi_id,
            $nama,
            $nominal,
            $keterangan
        ]);

        header('Location: tunjangan.php?success=add');
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
        <i data-lucide="plus-square" class="w-7 h-7 text-green-400"></i>
        Tambah Tunjangan
    </h1>

    <a href="tunjangan.php"
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
<div class="relative">
    <i data-lucide="user-check" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="karyawan_id"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">-- Tunjangan Umum / Divisi --</option>
        <?php foreach($karyawan as $k): ?>
            <option value="<?= $k['id'] ?>" class="text-black">
                <?= htmlspecialchars($k['nama_lengkap']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- DIVISI -->
<div class="relative">
    <i data-lucide="layers" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="divisi_id"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">-- Tunjangan Umum / Karyawan --</option>
        <?php foreach($divisi as $d): ?>
            <option value="<?= $d['id'] ?>" class="text-black">
                <?= $d['nama_divisi'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- NAMA -->
<div class="relative md:col-span-2">
    <i data-lucide="tag" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="text" name="nama" placeholder="Nama Tunjangan" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- NOMINAL -->
<div class="relative md:col-span-2">
    <i data-lucide="wallet" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="nominal" min="0" step="0.01" placeholder="Nominal"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- KETERANGAN -->
<div class="relative md:col-span-2">
    <i data-lucide="align-left" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <textarea name="keterangan" placeholder="Keterangan (opsional)"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none"></textarea>
</div>

<!-- BUTTON -->
<div class="md:col-span-2 flex gap-4 mt-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="save"></i> Simpan
    </button>

    <a href="tunjangan.php"
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

<?php require_once __DIR__ . '/footer_manager.php'; ?>
