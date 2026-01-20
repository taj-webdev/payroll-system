<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$error = '';

/* ===============================
   PROSES SIMPAN
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tahun = (int)($_POST['tahun'] ?? 0);

    if ($tahun <= 0) {
        $error = 'Tahun gaji wajib diisi.';
    } else {

        // Cek duplikat tahun
        $cek = $conn->prepare("SELECT id FROM gaji_tahun WHERE tahun = ?");
        $cek->execute([$tahun]);

        if ($cek->fetch()) {
            $error = 'Gaji untuk tahun tersebut sudah ada.';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO gaji_tahun (tahun, status)
                VALUES (?, 'draft')
            ");
            $stmt->execute([$tahun]);

            header('Location: gaji_tahun.php?success=add');
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
        <i data-lucide="calendar-plus" class="w-7 h-7 text-green-400"></i>
        Tambah Gaji Tahunan
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
<div class="relative mb-6">
    <i data-lucide="calendar-range"
       class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>

    <input type="number"
           name="tahun"
           min="2000"
           max="2100"
           placeholder="Contoh: 2026"
           required
           class="w-full pl-10 pr-4 py-2 rounded-xl
                  bg-white/20 text-white placeholder-white/60
                  focus:outline-none">
</div>

<!-- BUTTON -->
<div class="flex gap-4">
    <button type="submit"
        class="flex items-center gap-2 px-6 py-2 rounded-xl
               bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="save"></i>
        Simpan
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
