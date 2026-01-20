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

/* ===============================
   SEARCH & PAGINATION
================================ */
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where = ["gaji_tahun_id = ?"];
$params = [$tahun_id];

if ($search !== '') {
    $where[]  = "bulan LIKE ?";
    $params[] = "%$search%";
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

/* TOTAL DATA */
$stmtTotal = $conn->prepare("
    SELECT COUNT(*)
    FROM gaji_bulan
    $whereSql
");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* DATA */
$stmt = $conn->prepare("
    SELECT *
    FROM gaji_bulan
    $whereSql
    ORDER BY bulan ASC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

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
            <i data-lucide="calendar-days" class="w-7 h-7 text-cyan-400"></i>
            Gaji Bulanan
        </h1>
        <p class="text-white/60 text-sm">
            Tahun <?= htmlspecialchars($tahun['tahun']) ?>
        </p>
    </div>

    <a href="gaji_bulan_add.php?tahun_id=<?= $tahun_id ?>"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="plus-circle"></i>
        Tambah
    </a>
</div>

<!-- SEARCH -->
<form method="GET" class="glass p-4 mb-4">
    <input type="hidden" name="tahun_id" value="<?= $tahun_id ?>">
    <div class="relative max-w-md">
        <i data-lucide="search"
           class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <input type="text" name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Cari bulan (1 - 12)"
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
            <th class="p-3 text-center">Bulan</th>
            <th class="p-3 text-center">Status</th>
            <th class="p-3 text-center">Dibuat</th>
            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>

    <?php if(!$rows): ?>
        <tr>
            <td colspan="5" class="p-4 text-center text-white/60">
                Data gaji bulanan belum tersedia
            </td>
        </tr>
    <?php endif; ?>

    <?php foreach($rows as $i => $r): ?>
        <tr class="border-b border-white/10 hover:bg-white/5">
            <td class="p-3 text-center"><?= $offset + $i + 1 ?></td>

            <td class="p-3 text-center font-semibold">
                <a href="gaji_detail.php?bulan_id=<?= $r['id'] ?>"
                   class="text-cyan-400 hover:underline">
                    <?= namaBulan($r['bulan']) ?>
                </a>
            </td>

            <td class="p-3 text-center">
                <span class="px-3 py-1 rounded-full text-xs
                    <?= $r['status']=='closed'
                        ? 'bg-red-500/20 text-red-300'
                        : ($r['status']=='open'
                            ? 'bg-green-500/20 text-green-300'
                            : 'bg-yellow-500/20 text-yellow-300') ?>">
                    <?= strtoupper($r['status']) ?>
                </span>
            </td>

            <td class="p-3 text-center">
                <?= date('d M Y', strtotime($r['created_at'])) ?>
            </td>

            <td class="p-3 text-center flex justify-center gap-2">

                <!-- DETAIL -->
                <a href="gaji_detail.php?bulan_id=<?= $r['id'] ?>"
                   class="inline-flex items-center gap-1 px-3 py-1 rounded-lg
                          bg-cyan-600 hover:bg-cyan-700">
                    <i data-lucide="list"></i>
                    Detail
                </a>

                <!-- EDIT -->
                <a href="gaji_bulan_edit.php?id=<?= $r['id'] ?>&tahun_id=<?= $tahun_id ?>"
                   class="inline-flex items-center gap-1 px-3 py-1 rounded-lg
                          bg-yellow-500 hover:bg-yellow-600">
                    <i data-lucide="edit"></i>
                    Edit
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
       <?= $i==$page ? 'bg-cyan-600' : 'bg-white/20 hover:bg-white/30' ?>">
       <?= $i ?>
    </a>
<?php endfor; ?>
</div>

</main>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
lucide.createIcons();

<?php if(isset($_GET['success']) && $_GET['success']=='add'): ?>
Swal.fire({
    icon:'success',
    title:'Berhasil',
    text:'Berhasil Menambah Gaji Bulanan'
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer_direktur.php'; ?>
