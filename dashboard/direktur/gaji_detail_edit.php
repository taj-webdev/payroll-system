<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

/* ===============================
   VALIDASI ID
================================ */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: gaji_tahun.php');
    exit;
}

/* ===============================
   AMBIL DATA GAJI
================================ */
$stmt = $conn->prepare("
    SELECT gd.*, gb.bulan, gt.tahun
    FROM gaji_detail gd
    JOIN gaji_bulan gb ON gb.id = gd.gaji_bulan_id
    JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
    WHERE gd.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: gaji_tahun.php');
    exit;
}

$bulan_id = $data['gaji_bulan_id'];

/* ===============================
   MASTER KARYAWAN
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

    $karyawan_id     = (int)($_POST['karyawan_id'] ?? 0);
    $gaji_pokok      = (float)($_POST['gaji_pokok'] ?? 0);
    $total_tunjangan = (float)($_POST['total_tunjangan'] ?? 0);
    $total_lembur    = (float)($_POST['total_lembur'] ?? 0);
    $total_potongan  = (float)($_POST['total_potongan'] ?? 0);
    $catatan         = trim($_POST['catatan'] ?? '');

    if ($karyawan_id <= 0 || $gaji_pokok <= 0) {
        $error = 'Karyawan dan gaji pokok wajib diisi.';
    } else {

        // Cegah duplikat (kecuali dirinya sendiri)
        $cek = $conn->prepare("
            SELECT id FROM gaji_detail
            WHERE gaji_bulan_id = ? AND karyawan_id = ? AND id != ?
        ");
        $cek->execute([$bulan_id, $karyawan_id, $id]);

        if ($cek->fetch()) {
            $error = 'Gaji karyawan untuk bulan ini sudah ada.';
        } else {

            $gaji_bersih = $gaji_pokok
                         + $total_tunjangan
                         + $total_lembur
                         - $total_potongan;

            $stmt = $conn->prepare("
                UPDATE gaji_detail SET
                    karyawan_id     = ?,
                    gaji_pokok      = ?,
                    total_tunjangan = ?,
                    total_lembur    = ?,
                    total_potongan  = ?,
                    gaji_bersih     = ?,
                    catatan         = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $karyawan_id,
                $gaji_pokok,
                $total_tunjangan,
                $total_lembur,
                $total_potongan,
                $gaji_bersih,
                $catatan,
                $id
            ]);

            header('Location: gaji_detail.php?bulan_id=' . $bulan_id . '&success=edit');
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
            Edit Gaji Karyawan
        </h1>
        <p class="text-white/60 text-sm">
            <?= namaBulan($data['bulan']) ?> <?= $data['tahun'] ?>
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
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <?php foreach($karyawan as $k): ?>
            <option value="<?= $k['id'] ?>"
                <?= $data['karyawan_id']==$k['id']?'selected':'' ?>
                class="text-black">
                <?= htmlspecialchars($k['nama_lengkap']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- GAJI POKOK -->
<div class="relative">
    <i data-lucide="credit-card" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="gaji_pokok" min="0" step="0.01"
           value="<?= $data['gaji_pokok'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- TUNJANGAN -->
<div class="relative">
    <i data-lucide="plus-circle" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="total_tunjangan" min="0" step="0.01"
           value="<?= $data['total_tunjangan'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- LEMBUR -->
<div class="relative">
    <i data-lucide="clock" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="total_lembur" min="0" step="0.01"
           value="<?= $data['total_lembur'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- POTONGAN -->
<div class="relative">
    <i data-lucide="minus-circle" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="total_potongan" min="0" step="0.01"
           value="<?= $data['total_potongan'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- CATATAN -->
<div class="relative md:col-span-2">
    <i data-lucide="align-left" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <textarea name="catatan"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none"><?= htmlspecialchars($data['catatan']) ?></textarea>
</div>

<!-- BUTTON -->
<div class="md:col-span-2 flex gap-4 mt-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-yellow-500 hover:bg-yellow-600 transition">
        <i data-lucide="save"></i> Simpan Perubahan
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

<?php require_once __DIR__ . '/footer_direktur.php'; ?>
