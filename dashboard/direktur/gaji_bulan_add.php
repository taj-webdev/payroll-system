<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

/* ===============================
   VALIDASI TAHUN
================================ */
$tahun_id = (int)($_GET['tahun_id'] ?? 0);
if ($tahun_id <= 0) {
    header('Location: gaji_tahun.php');
    exit;
}

/* Ambil data tahun */
$stmtTahun = $conn->prepare("SELECT * FROM gaji_tahun WHERE id = ? LIMIT 1");
$stmtTahun->execute([$tahun_id]);
$tahun = $stmtTahun->fetch();

if (!$tahun) {
    header('Location: gaji_tahun.php');
    exit;
}

$error = '';

/* ===============================
   PROSES SIMPAN
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bulan = (int)($_POST['bulan'] ?? 0);

    if ($bulan < 1 || $bulan > 12) {
        $error = 'Bulan tidak valid.';
    } else {

        // Cek duplikat bulan dalam tahun yang sama
        $cek = $conn->prepare("
            SELECT id FROM gaji_bulan 
            WHERE gaji_tahun_id = ? AND bulan = ?
        ");
        $cek->execute([$tahun_id, $bulan]);

        if ($cek->fetch()) {
            $error = 'Gaji untuk bulan tersebut sudah ada.';
        } else {

            $stmt = $conn->prepare("
                INSERT INTO gaji_bulan
                (gaji_tahun_id, bulan, status)
                VALUES (?, ?, 'draft')
            ");
            $stmt->execute([$tahun_id, $bulan]);

            header('Location: gaji_bulan.php?tahun_id=' . $tahun_id . '&success=add');
            exit;
        }
    }
}

/* ===============================
   UI
================================ */
require_once __DIR__ . '/header_direktur.php';
require_once __DIR__ . '/sidebar_direktur.php';

function namaBulan($b){
    return date('F', mktime(0,0,0,$b,1));
}
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
    <div>
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <i data-lucide="calendar-plus" class="w-7 h-7 text-green-400"></i>
            Tambah Gaji Bulanan
        </h1>
        <p class="text-white/60 text-sm">
            Tahun <?= htmlspecialchars($tahun['tahun']) ?>
        </p>
    </div>

    <a href="gaji_bulan.php?tahun_id=<?= $tahun_id ?>"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-gray-600 hover:bg-gray-700 transition">
        <i data-lucide="arrow-left"></i>
        Kembali
    </a>
</div>

<?php if($error): ?>
<div class="mb-4 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST"
      class="glass p-6 max-w-md">

<!-- BULAN -->
<div class="relative mb-6">
    <i data-lucide="calendar-days"
       class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>

    <select name="bulan"
        class="w-full pl-10 pr-4 py-2 rounded-xl
               bg-white/20 text-white focus:outline-none" required>
        <option value="">-- Pilih Bulan --</option>
        <?php for($b=1;$b<=12;$b++): ?>
            <option value="<?= $b ?>" class="text-black">
                <?= namaBulan($b) ?>
            </option>
        <?php endfor; ?>
    </select>
</div>

<!-- BUTTON -->
<div class="flex gap-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="save"></i>
        Simpan
    </button>

    <a href="gaji_bulan.php?tahun_id=<?= $tahun_id ?>"
       class="flex items-center gap-2 px-6 py-2 rounded-xl
              bg-gray-600 hover:bg-gray-700 transition">
        <i data-lucide="x-circle"></i>
        Batal
    </a>
</div>

</form>
</main>

<script>
lucide.createIcons();
</script>

<?php require_once __DIR__ . '/footer_direktur.php'; ?>
