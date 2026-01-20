<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: lembur.php');
    exit;
}

/* ===============================
   AMBIL DATA LEMBUR
================================ */
$stmt = $conn->prepare("
    SELECT *
    FROM lembur
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: lembur.php');
    exit;
}

/* ===============================
   DATA MASTER
================================ */
$karyawan = $conn->query("
    SELECT id, nama_lengkap
    FROM karyawan
    ORDER BY nama_lengkap
")->fetchAll();

$error = '';

/* ===============================
   PROSES UPDATE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $karyawan_id = (int)($_POST['karyawan_id'] ?? 0);
    $tanggal     = $_POST['tanggal'] ?? '';
    $jumlah_jam  = (int)($_POST['jumlah_jam'] ?? 0);
    $tarif       = (float)($_POST['tarif_per_jam'] ?? 0);
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($karyawan_id <= 0 || $tanggal === '' || $jumlah_jam <= 0 || $tarif <= 0) {
        $error = 'Semua field wajib harus diisi dengan benar.';
    } else {

        $total = $jumlah_jam * $tarif;

        $stmt = $conn->prepare("
            UPDATE lembur SET
                karyawan_id   = ?,
                tanggal       = ?,
                jumlah_jam    = ?,
                tarif_per_jam = ?,
                total         = ?,
                keterangan    = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $karyawan_id,
            $tanggal,
            $jumlah_jam,
            $tarif,
            $total,
            $keterangan,
            $id
        ]);

        header('Location: lembur.php?success=edit');
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
        Edit Lembur
    </h1>

    <a href="lembur.php"
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
            <option value="<?= $k['id'] ?>"
                <?= $k['id'] == $data['karyawan_id'] ? 'selected' : '' ?>
                class="text-black">
                <?= htmlspecialchars($k['nama_lengkap']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- TANGGAL -->
<div class="relative">
    <i data-lucide="calendar" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="date" name="tanggal" required
           value="<?= $data['tanggal'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- JUMLAH JAM -->
<div class="relative">
    <i data-lucide="hourglass" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="jumlah_jam" min="1" required
           value="<?= $data['jumlah_jam'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- TARIF -->
<div class="relative">
    <i data-lucide="wallet" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="tarif_per_jam" min="0" step="0.01" required
           value="<?= $data['tarif_per_jam'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- TOTAL (INFO) -->
<div class="relative">
    <i data-lucide="calculator" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="text"
           value="Total dihitung otomatis"
           disabled
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/10 text-white/60 cursor-not-allowed">
</div>

<!-- KETERANGAN -->
<div class="relative md:col-span-2">
    <i data-lucide="align-left" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <textarea name="keterangan"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none"><?= htmlspecialchars($data['keterangan']) ?></textarea>
</div>

<!-- BUTTON -->
<div class="md:col-span-2 flex gap-4 mt-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-yellow-500 hover:bg-yellow-600 transition">
        <i data-lucide="save"></i> Simpan Perubahan
    </button>

    <a href="lembur.php"
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
