<?php
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: divisi.php');
    exit;
}

/* Ambil data divisi */
$stmt = $conn->prepare("SELECT * FROM divisi WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: divisi.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_divisi = trim($_POST['nama_divisi'] ?? '');
    $keterangan  = trim($_POST['keterangan'] ?? '');

    if ($nama_divisi === '') {
        $error = 'Nama divisi wajib diisi.';
    } else {
        $stmt = $conn->prepare("
            UPDATE divisi SET
                nama_divisi = ?,
                keterangan  = ?
            WHERE id = ?
        ");
        $stmt->execute([$nama_divisi, $keterangan, $id]);

        header('Location: divisi.php?success=edit');
        exit;
    }
}

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
        Edit Divisi
    </h1>

    <a href="divisi.php"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-gray-600 hover:bg-gray-700 transition">
        <i data-lucide="arrow-left"></i>
        Kembali
    </a>
</div>

<?php if ($error): ?>
<div class="mb-4 text-red-400">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- FORM -->
<form method="POST" class="glass p-6 space-y-5 max-w-xl">

    <!-- Nama Divisi -->
    <div class="relative">
        <i data-lucide="layers"
           class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <input
            type="text"
            name="nama_divisi"
            value="<?= htmlspecialchars($data['nama_divisi']) ?>"
            placeholder="Nama Divisi"
            class="w-full pl-10 pr-4 py-2 rounded-xl
                   bg-white/20 text-white
                   focus:outline-none"
            required
        >
    </div>

    <!-- Keterangan -->
    <div class="relative">
        <i data-lucide="align-left"
           class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <textarea
            name="keterangan"
            rows="4"
            placeholder="Keterangan"
            class="w-full pl-10 pr-4 py-2 rounded-xl
                   bg-white/20 text-white
                   focus:outline-none"
        ><?= htmlspecialchars($data['keterangan']) ?></textarea>
    </div>

    <!-- BUTTON -->
    <div class="flex gap-4 pt-2">
        <button
            type="submit"
            class="flex items-center gap-2 px-6 py-2 rounded-xl
                   bg-yellow-500 hover:bg-yellow-600 transition"
        >
            <i data-lucide="save"></i>
            Simpan Perubahan
        </button>

        <a href="divisi.php"
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

<?php require_once __DIR__ . '/footer_admin.php'; ?>
