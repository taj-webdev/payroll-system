<?php
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/sidebar_admin.php';
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
    $where[] = "(t.nama LIKE ? OR k.nama_lengkap LIKE ? OR d.nama_divisi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* TOTAL DATA */
$stmtTotal = $conn->prepare("
    SELECT COUNT(*)
    FROM tunjangan t
    LEFT JOIN karyawan k ON k.id = t.karyawan_id
    LEFT JOIN divisi d ON d.id = t.divisi_id
    $whereSql
");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* DATA */
$stmt = $conn->prepare("
    SELECT t.*, 
           k.nama_lengkap,
           d.nama_divisi
    FROM tunjangan t
    LEFT JOIN karyawan k ON k.id = t.karyawan_id
    LEFT JOIN divisi d ON d.id = t.divisi_id
    $whereSql
    ORDER BY t.created_at DESC
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
        <i data-lucide="plus-square" class="w-7 h-7 text-emerald-400"></i>
        Data Tunjangan
    </h1>

    <a href="tunjangan_add.php"
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
               placeholder="Cari nama tunjangan / karyawan / divisi"
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
            <th class="p-3 text-center">Karyawan</th>
            <th class="p-3 text-center">Divisi</th>
            <th class="p-3 text-center">Nominal</th>
            <th class="p-3 text-center">Keterangan</th>
            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>

    <?php if(!$rows): ?>
        <tr>
            <td colspan="7" class="p-4 text-center text-white/60">
                Data tidak ditemukan
            </td>
        </tr>
    <?php endif; ?>

    <?php foreach($rows as $i => $r): ?>
        <tr class="border-b border-white/10 hover:bg-white/5">
            <td class="p-3 text-center"><?= $offset + $i + 1 ?></td>
            <td class="p-3 text-center"><?= htmlspecialchars($r['nama']) ?></td>
            <td class="p-3 text-center"><?= $r['nama_lengkap'] ?? '-' ?></td>
            <td class="p-3 text-center"><?= $r['nama_divisi'] ?? '-' ?></td>
            <td class="p-3 text-center">
                Rp <?= number_format($r['nominal'],0,',','.') ?>
            </td>
            <td class="p-3 text-center"><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
            <td class="p-3 flex gap-2 justify-center">

                <a href="tunjangan_edit.php?id=<?= $r['id'] ?>"
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
       <?= $i==$page ? 'bg-emerald-600' : 'bg-white/20 hover:bg-white/30' ?>">
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
        title: 'Hapus Data Tunjangan?',
        text: 'Data tunjangan akan dihapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, hapus'
    }).then(r=>{
        if(r.isConfirmed){
            window.location = 'tunjangan_delete.php?id=' + id;
        }
    })
}

<?php if(isset($_GET['success'])): ?>
Swal.fire({
    icon:'success',
    title:'Berhasil',
    text:
    <?= json_encode(match($_GET['success']){
        'add'    => 'Berhasil Menambahkan Data Tunjangan',
        'edit'   => 'Berhasil Mengubah Data Tunjangan',
        'delete' => 'Berhasil Menghapus Data Tunjangan',
        default  => ''
    }) ?>
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer_admin.php'; ?>
