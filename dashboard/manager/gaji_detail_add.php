<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

/* ===============================
   VALIDASI BULAN
================================ */
$bulan_id = (int)($_GET['bulan_id'] ?? 0);
if ($bulan_id <= 0) {
    header('Location: gaji_tahun.php');
    exit;
}

/* Ambil data bulan + tahun */
$stmtBulan = $conn->prepare("
    SELECT gb.*, gt.tahun
    FROM gaji_bulan gb
    JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
    WHERE gb.id = ?
    LIMIT 1
");
$stmtBulan->execute([$bulan_id]);
$bulan = $stmtBulan->fetch();

if (!$bulan) {
    header('Location: gaji_tahun.php');
    exit;
}

/* ===============================
   MASTER DATA
================================ */
$karyawan = $conn->query("
    SELECT id, nama_lengkap
    FROM karyawan
    ORDER BY nama_lengkap
")->fetchAll();

$error = '';

/* ===============================
   PROSES SIMPAN
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $karyawan_id     = (int)($_POST['karyawan_id'] ?? 0);
    $gaji_pokok      = (float)($_POST['gaji_pokok'] ?? 0);
    $total_tunjangan = (float)($_POST['total_tunjangan'] ?? 0);
    $total_lembur    = (float)($_POST['total_lembur'] ?? 0);
    $total_potongan  = (float)($_POST['total_potongan'] ?? 0);
    $catatan         = trim($_POST['catatan'] ?? '');

    if ($karyawan_id <= 0 || $gaji_pokok <= 0) {
        $error = 'Karyawan dan gaji pokok wajib diisi.';
    } else {

        // Cegah duplikat (1 karyawan 1 gaji per bulan)
        $cek = $conn->prepare("
            SELECT id FROM gaji_detail
            WHERE gaji_bulan_id = ? AND karyawan_id = ?
        ");
        $cek->execute([$bulan_id, $karyawan_id]);

        if ($cek->fetch()) {
            $error = 'Gaji karyawan untuk bulan ini sudah ada.';
        } else {

            $gaji_bersih = $gaji_pokok
                         + $total_tunjangan
                         + $total_lembur
                         - $total_potongan;

            $stmt = $conn->prepare("
                INSERT INTO gaji_detail
                (gaji_bulan_id, karyawan_id,
                 gaji_pokok, total_tunjangan, total_lembur, total_potongan,
                 gaji_bersih, status, catatan)
                VALUES (?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $bulan_id,
                $karyawan_id,
                $gaji_pokok,
                $total_tunjangan,
                $total_lembur,
                $total_potongan,
                $gaji_bersih,
                'pending',
                $catatan
            ]);

            header('Location: gaji_detail.php?bulan_id=' . $bulan_id . '&success=add');
            exit;
        }
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
    <div>
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <i data-lucide="wallet" class="w-7 h-7 text-green-400"></i>
            Tambah Gaji Karyawan
        </h1>
        <p class="text-white/60 text-sm">
            <?= date('F', mktime(0,0,0,$bulan['bulan'],1)) ?> <?= $bulan['tahun'] ?>
        </p>
    </div>

    <a href="gaji_detail.php?bulan_id=<?= $bulan_id ?>"
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
    <i data-lucide="users" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="karyawan_id" required
        class="w-full pl-10 pr-4 py-2 rounded-xl
               bg-white/20 text-white focus:outline-none">
        <option value="">-- Pilih Karyawan --</option>
        <?php foreach($karyawan as $k): ?>
            <option value="<?= $k['id'] ?>" class="text-black">
                <?= htmlspecialchars($k['nama_lengkap']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- GAJI POKOK -->
<div class="relative">
    <i data-lucide="credit-card" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="gaji_pokok" min="0" step="0.01" placeholder="Gaji Pokok" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- TUNJANGAN -->
<div class="relative">
    <i data-lucide="plus-circle" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="total_tunjangan" min="0" step="0.01" placeholder="Total Tunjangan"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- LEMBUR -->
<div class="relative">
    <i data-lucide="clock" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="total_lembur" min="0" step="0.01" placeholder="Total Lembur"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- POTONGAN -->
<div class="relative">
    <i data-lucide="minus-circle" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="total_potongan" min="0" step="0.01" placeholder="Total Potongan"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- CATATAN -->
<div class="relative md:col-span-2">
    <i data-lucide="align-left" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <textarea name="catatan" placeholder="Catatan (opsional)"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none"></textarea>
</div>

<!-- BUTTON -->
<div class="md:col-span-2 flex gap-4 mt-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="save"></i> Simpan
    </button>

    <a href="gaji_detail.php?bulan_id=<?= $bulan_id ?>"
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
