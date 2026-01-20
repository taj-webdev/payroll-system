<?php
require_once __DIR__ . '/header_direktur.php';
require_once __DIR__ . '/sidebar_direktur.php';
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

/* ===============================
   SEARCH & PAGINATION
================================ */
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[]  = "tahun LIKE ?";
    $params[] = "%$search%";
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* TOTAL DATA */
$stmtTotal = $conn->prepare("
    SELECT COUNT(*)
    FROM gaji_tahun
    $whereSql
");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* DATA */
$stmt = $conn->prepare("
    SELECT *
    FROM gaji_tahun
    $whereSql
    ORDER BY tahun DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$rows = $stmt->fetchAll();
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
        <i data-lucide="calendar-range" class="w-7 h-7 text-cyan-400"></i>
        Gaji Tahunan
    </h1>

    <a href="gaji_tahun_add.php"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="plus-circle"></i>
        Tambah
    </a>
</div>

<!-- SEARCH -->
<form method="GET" class="glass p-4 mb-4">
    <div class="relative max-w-md">
        <i data-lucide="search"
           class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <input type="text" name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Cari Tahun Gaji (contoh: 2026)"
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
            <th class="p-3 text-center">Tahun</th>
            <th class="p-3 text-center">Status</th>
            <th class="p-3 text-center">Dibuat</th>
            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>

    <?php if(!$rows): ?>
        <tr>
            <td colspan="5" class="p-4 text-center text-white/60">
                Data gaji tahunan belum tersedia
            </td>
        </tr>
    <?php endif; ?>

    <?php foreach($rows as $i => $r): ?>
        <tr class="border-b border-white/10 hover:bg-white/5">
            <td class="p-3 text-center"><?= $offset + $i + 1 ?></td>

            <td class="p-3 text-center font-semibold">
                <a href="gaji_bulan.php?tahun_id=<?= $r['id'] ?>"
                   class="text-cyan-400 hover:underline">
                    <?= $r['tahun'] ?>
                </a>
            </td>

            <td class="p-3 text-center">
                <span class="px-3 py-1 rounded-full text-xs
                    <?= $r['status']=='locked'
                        ? 'bg-red-500/20 text-red-300'
                        : 'bg-green-500/20 text-green-300' ?>">
                    <?= strtoupper($r['status']) ?>
                </span>
            </td>

            <td class="p-3 text-center">
                <?= date('d M Y', strtotime($r['created_at'])) ?>
            </td>

            <td class="p-3 flex gap-2 justify-center">

                <a href="gaji_tahun_edit.php?id=<?= $r['id'] ?>"
                   class="px-3 py-1 rounded-lg bg-yellow-500 hover:bg-yellow-600">
                    <i data-lucide="edit"></i>
                </a>

                <button onclick="confirmDelete(<?= $r['id'] ?>)"
                        class="px-3 py-1 rounded-lg bg-red-600 hover:bg-red-700">
                    <i data-lucide="trash"></i>
                </button>

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

function confirmDelete(id){
    Swal.fire({
        title: 'Hapus Gaji Tahunan?',
        text: 'Semua data gaji dalam tahun ini akan ikut terhapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, hapus'
    }).then(r=>{
        if(r.isConfirmed){
            window.location = 'gaji_tahun_delete.php?id=' + id;
        }
    })
}

<?php if(isset($_GET['success'])): ?>
Swal.fire({
    icon:'success',
    title:'Berhasil',
    text:
    <?= json_encode(match($_GET['success']){
        'add'    => 'Berhasil Menambahkan Gaji Tahunan',
        'edit'   => 'Berhasil Mengubah Gaji Tahunan',
        'delete' => 'Berhasil Menghapus Gaji Tahunan',
        default  => ''
    }) ?>
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer_direktur.php'; ?>
