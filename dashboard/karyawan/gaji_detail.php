<?php
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/header_karyawan.php';
require_once __DIR__ . '/sidebar_karyawan.php';

$db = new Database();
$conn = $db->connect();

/* =========================
   Ambil karyawan_id dari user login
========================= */
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt->execute([$user_id]);
$karyawan_id = $stmt->fetchColumn();

if (!$karyawan_id) {
    die('Data karyawan tidak ditemukan');
}

/* ===============================
   FILTER & PAGINATION
================================ */
$status  = $_GET['status'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where  = ['gd.karyawan_id = ?'];
$params = [$karyawan_id];

if ($status !== '') {
    $where[]  = "gd.status = ?";
    $params[] = $status;
}
if ($tanggal !== '') {
    $where[]  = "DATE(gd.created_at) = ?";
    $params[] = $tanggal;
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

/* ===============================
   TOTAL DATA
================================ */
$stmtTotal = $conn->prepare("
    SELECT COUNT(*)
    FROM gaji_detail gd
    $whereSql
");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* ===============================
   DATA LIST
================================ */
$stmt = $conn->prepare("
    SELECT *
    FROM gaji_detail gd
    $whereSql
    ORDER BY gd.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
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
        <i data-lucide="wallet" class="w-7 h-7 text-cyan-400"></i>
        Riwayat Gaji Saya
    </h1>
</div>

<!-- FILTER -->
<form method="GET" class="glass p-4 mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">

    <div class="relative">
        <i data-lucide="calendar" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>"
               class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
    </div>

    <div class="relative">
        <i data-lucide="filter" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <select name="status"
                class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
            <option value="">Semua Status</option>
            <?php foreach(['pending','approved','rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $status==$s?'selected':'' ?> class="text-black">
                    <?= ucfirst($s) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button class="flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-700 rounded-xl">
        <i data-lucide="search"></i> Filter
    </button>
</form>

<!-- TABLE -->
<div class="glass overflow-x-auto">
<table class="w-full text-sm">
<thead class="border-b border-white/20 text-white/70">
<tr>
    <th class="p-3 text-center">#</th>
    <th class="p-3 text-center">Tanggal</th>
    <th class="p-3 text-center">Gaji Bersih</th>
    <th class="p-3 text-center">Status</th>
    <th class="p-3 text-center">Aksi</th>
</tr>
</thead>
<tbody>

<?php if(!$rows): ?>
<tr>
    <td colspan="5" class="p-4 text-center text-white/60">
        Data gaji belum tersedia
    </td>
</tr>
<?php endif; ?>

<?php foreach($rows as $i => $r): ?>
<tr class="border-b border-white/10 hover:bg-white/5">
    <td class="p-3 text-center"><?= $offset + $i + 1 ?></td>
    <td class="p-3 text-center"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
    <td class="p-3 text-center"><?= rupiah($r['gaji_bersih']) ?></td>
    <td class="p-3 text-center">
        <span class="px-3 py-1 rounded-full text-xs
            <?= $r['status']=='approved'?'bg-green-500/20 text-green-300':
               ($r['status']=='rejected'?'bg-red-500/20 text-red-300':'bg-yellow-500/20 text-yellow-300') ?>">
            <?= strtoupper($r['status']) ?>
        </span>
    </td>
    <td class="p-3 text-center">
        <a href="gaji_detail_print.php?id=<?= $r['id'] ?>"
           class="inline-flex items-center gap-1 px-3 py-1 rounded-lg
                  bg-blue-600 hover:bg-blue-700">
            <i data-lucide="printer"></i>
            Cetak
        </a>
    </td>
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
       <?= $i==$page?'bg-cyan-600':'bg-white/20 hover:bg-white/30' ?>">
       <?= $i ?>
    </a>
<?php endfor; ?>
</div>

</main>

<script>
lucide.createIcons();
</script>

<?php require_once __DIR__ . '/footer_karyawan.php'; ?>
