<?php
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/header_karyawan.php';
require_once __DIR__ . '/sidebar_karyawan.php';

$db = new Database();
$conn = $db->connect();

/* ===============================
   Ambil karyawan_id dari session
================================ */
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt->execute([$user_id]);
$karyawan = $stmt->fetch();

if (!$karyawan) {
    die('Data karyawan tidak ditemukan');
}

$karyawan_id = $karyawan['id'];

/* ===============================
   FILTER & PAGINATION
================================ */
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where  = ["t.karyawan_id = ?"];
$params = [$karyawan_id];

if ($search !== '') {
    $where[]  = "(t.nama LIKE ? OR t.keterangan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

/* ===============================
   TOTAL DATA
================================ */
$stmtTotal = $conn->prepare("
    SELECT COUNT(*)
    FROM tunjangan t
    WHERE t.karyawan_id = ?
");
$stmtTotal->execute([$karyawan_id]);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* ===============================
   DATA
================================ */
$stmt = $conn->prepare("
    SELECT t.*
    FROM tunjangan t
    WHERE t.karyawan_id = ?
    ORDER BY t.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute([$karyawan_id]);
$rows = $stmt->fetchAll();

function rupiah($n){
    return 'Rp ' . number_format($n,0,',','.');
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
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-7 h-7 text-green-400"></i>
        Data Tunjangan
    </h1>
</div>

<!-- SEARCH -->
<form method="GET" class="glass p-4 mb-4 max-w-md">
    <div class="relative">
        <i data-lucide="search" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Cari nama / keterangan"
               class="w-full pl-10 pr-4 py-2 rounded-xl
                      bg-white/20 text-white focus:outline-none">
    </div>
</form>

<!-- TABLE -->
<div class="glass overflow-x-auto">
<table class="w-full text-sm">
<thead class="border-b border-white/20 text-white/70">
<tr>
    <th class="p-3 text-center">#</th>
    <th class="p-3 text-center">Nama Tunjangan</th>
    <th class="p-3 text-center">Nominal</th>
    <th class="p-3 text-center">Keterangan</th>
</tr>
</thead>
<tbody>

<?php if(!$rows): ?>
<tr>
    <td colspan="4" class="p-4 text-center text-white/60">
        Data tunjangan tidak ditemukan
    </td>
</tr>
<?php endif; ?>

<?php foreach($rows as $i => $r): ?>
<tr class="border-b border-white/10 hover:bg-white/5">
    <td class="p-3 text-center"><?= $offset + $i + 1 ?></td>
    <td class="p-3 text-center font-semibold"><?= htmlspecialchars($r['nama']) ?></td>
    <td class="p-3 text-center text-green-300"><?= rupiah($r['nominal']) ?></td>
    <td class="p-3 text-center text-white/70"><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

<!-- PAGINATION -->
<div class="flex gap-2 mt-6">
<?php for($i=1;$i<=$totalPage;$i++): ?>
<a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"
   class="px-3 py-1 rounded-lg
   <?= $i==$page ? 'bg-green-600' : 'bg-white/20 hover:bg-white/30' ?>">
   <?= $i ?>
</a>
<?php endfor; ?>
</div>

</main>

<script>
lucide.createIcons();
</script>

<?php require_once __DIR__ . '/footer_karyawan.php'; ?>
