<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: gaji_tahun.php');
    exit;
}

/* ===============================
   AMBIL DATA
================================ */
$stmt = $conn->prepare("SELECT * FROM gaji_tahun WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: gaji_tahun.php');
    exit;
}

$error = '';

/* ===============================
   PROSES UPDATE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tahun  = (int)($_POST['tahun'] ?? 0);
    $status = $_POST['status'] ?? 'draft';

    if ($tahun <= 0) {
        $error = 'Tahun gaji wajib diisi.';
    } else {

        // Cek duplikat tahun (kecuali dirinya sendiri)
        $cek = $conn->prepare("
            SELECT id FROM gaji_tahun 
            WHERE tahun = ? AND id != ?
        ");
        $cek->execute([$tahun, $id]);

        if ($cek->fetch()) {
            $error = 'Gaji untuk tahun tersebut sudah ada.';
        } else {

            $stmt = $conn->prepare("
                UPDATE gaji_tahun SET
                    tahun = ?,
                    status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $tahun,
                $status,
                $id
            ]);

            header('Location: gaji_tahun.php?success=edit');
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
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="calendar-cog" class="w-7 h-7 text-yellow-400"></i>
        Edit Gaji Tahunan
    </h1>

    <a href="gaji_tahun.php"
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

<!-- TAHUN -->
<div class="relative mb-4">
    <i data-lucide="calendar-range"
       class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>

    <input type="number"
           name="tahun"
           min="2000"
           max="2100"
           value="<?= htmlspecialchars($data['tahun']) ?>"
           required
           class="w-full pl-10 pr-4 py-2 rounded-xl
                  bg-white/20 text-white
                  focus:outline-none">
</div>

<!-- STATUS -->
<div class="relative mb-6">
    <i data-lucide="toggle-left"
       class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>

    <select name="status"
        class="w-full pl-10 pr-4 py-2 rounded-xl
               bg-white/20 text-white focus:outline-none">
        <option value="draft" <?= $data['status']=='draft'?'selected':'' ?> class="text-black">
            Draft
        </option>
        <option value="locked" <?= $data['status']=='locked'?'selected':'' ?> class="text-black">
            Locked
        </option>
    </select>
</div>

<!-- BUTTON -->
<div class="flex gap-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-yellow-500 hover:bg-yellow-600 transition">
        <i data-lucide="save"></i>
        Simpan Perubahan
    </button>

    <a href="gaji_tahun.php"
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

<?php require_once __DIR__ . '/footer_manager.php'; ?>
