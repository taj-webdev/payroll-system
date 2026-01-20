<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: potongan.php');
    exit;
}

/* ===============================
   AMBIL DATA POTONGAN
================================ */
$stmt = $conn->prepare("
    SELECT *
    FROM potongan
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: potongan.php');
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

    $karyawan_id = $_POST['karyawan_id'] !== '' ? (int)$_POST['karyawan_id'] : null;
    $divisi_id   = $_POST['divisi_id'] !== '' ? (int)$_POST['divisi_id'] : null;
    $nama        = trim($_POST['nama'] ?? '');
    $nominal     = (float)($_POST['nominal'] ?? 0);
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($nama === '' || $nominal <= 0) {
        $error = 'Nama potongan dan nominal wajib diisi.';
    } else {

        $stmt = $conn->prepare("
            UPDATE potongan SET
                karyawan_id = ?,
                divisi_id   = ?,
                nama        = ?,
                nominal     = ?,
                keterangan  = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $karyawan_id,
            $divisi_id,
            $nama,
            $nominal,
            $keterangan,
            $id
        ]);

        header('Location: potongan.php?success=edit');
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
        <i data-lucide="edit" class="w-7 h-7 text-yellow-400"></i>
        Edit Potongan
    </h1>

    <a href="potongan.php"
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
    <i data-lucide="user-x" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <select name="karyawan_id"
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">-- Potongan Umum / Divisi --</option>
        <?php foreach($karyawan as $k): ?>
            <option value="<?= $k['id'] ?>"
                <?= $data['karyawan_id']==$k['id']?'selected':'' ?>
                class="text-black">
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
        <option value="">-- Potongan Umum / Karyawan --</option>
        <?php foreach($divisi as $d): ?>
            <option value="<?= $d['id'] ?>"
                <?= $data['divisi_id']==$d['id']?'selected':'' ?>
                class="text-black">
                <?= $d['nama_divisi'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- NAMA -->
<div class="relative md:col-span-2">
    <i data-lucide="tag" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required
        class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
</div>

<!-- NOMINAL -->
<div class="relative md:col-span-2">
    <i data-lucide="wallet" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
    <input type="number" name="nominal" min="0" step="0.01"
           value="<?= $data['nominal'] ?>"
           class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
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

    <a href="potongan.php"
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
