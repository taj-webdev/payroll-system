<?php
require_once __DIR__ . '/../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$conn = $db->connect();

/* ===============================
   VALIDASI
================================ */
$id       = (int)($_GET['id'] ?? 0);
$tahun_id = (int)($_GET['tahun_id'] ?? 0);

if ($id <= 0 || $tahun_id <= 0) {
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

/* Ambil data bulan */
$stmt = $conn->prepare("
    SELECT * FROM gaji_bulan 
    WHERE id = ? AND gaji_tahun_id = ?
    LIMIT 1
");
$stmt->execute([$id, $tahun_id]);
$data = $stmt->fetch();

if (!$data) {
    header("Location: gaji_bulan.php?tahun_id=".$tahun_id);
    exit;
}

$error = '';

/* ===============================
   PROSES UPDATE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bulan   = (int)($_POST['bulan'] ?? 0);
    $status  = $_POST['status'] ?? 'draft';
    $catatan = trim($_POST['catatan'] ?? '');

    if ($bulan < 1 || $bulan > 12) {
        $error = 'Bulan tidak valid.';
    } elseif (!in_array($status, ['draft','open','closed'])) {
        $error = 'Status tidak valid.';
    } else {

        // Cek duplikat bulan di tahun yg sama (kecuali dirinya)
        $cek = $conn->prepare("
            SELECT id FROM gaji_bulan
            WHERE gaji_tahun_id = ? AND bulan = ? AND id != ?
        ");
        $cek->execute([$tahun_id, $bulan, $id]);

        if ($cek->fetch()) {
            $error = 'Gaji untuk bulan tersebut sudah ada.';
        } else {

            $stmt = $conn->prepare("
                UPDATE gaji_bulan
                SET bulan = ?, status = ?, catatan = ?
                WHERE id = ?
            ");
            $stmt->execute([$bulan, $status, $catatan, $id]);

            header("Location: gaji_bulan.php?tahun_id=".$tahun_id."&success=edit");
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
            <i data-lucide="edit" class="w-7 h-7 text-yellow-400"></i>
            Edit Gaji Bulanan
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

<form method="POST" class="glass p-6 max-w-md">

<!-- BULAN -->
<div class="relative mb-6">
    <i data-lucide="calendar-days"
       class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>

    <select name="bulan"
        class="w-full pl-10 pr-4 py-2 rounded-xl
               bg-white/20 text-white focus:outline-none" required>
        <?php for($b=1;$b<=12;$b++): ?>
            <option value="<?= $b ?>"
                <?= $data['bulan']==$b?'selected':'' ?>
                class="text-black">
                <?= namaBulan($b) ?>
            </option>
        <?php endfor; ?>
    </select>
</div>

<!-- STATUS -->
<div class="relative mb-6">
    <i data-lucide="activity"
       class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>

    <select name="status"
        class="w-full pl-10 pr-4 py-2 rounded-xl
               bg-white/20 text-white focus:outline-none">
        <?php foreach(['draft','open','closed'] as $s): ?>
            <option value="<?= $s ?>"
                <?= $data['status']==$s?'selected':'' ?>
                class="text-black">
                <?= strtoupper($s) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- CATATAN -->
<div class="relative mb-6">
    <i data-lucide="file-text"
       class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>

    <textarea name="catatan" rows="3"
        class="w-full pl-10 pr-4 py-2 rounded-xl
               bg-white/20 text-white focus:outline-none"
        placeholder="Catatan..."><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
</div>

<!-- BUTTON -->
<div class="flex gap-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-yellow-500 hover:bg-yellow-600 text-black transition">
        <i data-lucide="save"></i>
        Update
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
