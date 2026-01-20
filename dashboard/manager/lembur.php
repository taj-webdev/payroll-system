<?php
require_once __DIR__ . '/header_manager.php';
require_once __DIR__ . '/sidebar_manager.php';
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

/* ===============================
   SEARCH, FILTER & PAGINATION
================================ */
$search     = trim($_GET['search'] ?? '');
$tgl_dari   = $_GET['tgl_dari'] ?? '';
$tgl_sampai = $_GET['tgl_sampai'] ?? '';

$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where  = [];
$params = [];

/* SEARCH */
if ($search !== '') {
    $where[]  = "(k.nama_lengkap LIKE ? OR l.keterangan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

/* FILTER TANGGAL */
if ($tgl_dari && $tgl_sampai) {
    $where[]  = "l.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_dari;
    $params[] = $tgl_sampai;
} elseif ($tgl_dari) {
    $where[]  = "l.tanggal >= ?";
    $params[] = $tgl_dari;
} elseif ($tgl_sampai) {
    $where[]  = "l.tanggal <= ?";
    $params[] = $tgl_sampai;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* TOTAL DATA */
$stmtTotal = $conn->prepare("
    SELECT COUNT(*)
    FROM lembur l
    JOIN karyawan k ON k.id = l.karyawan_id
    $whereSql
");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* DATA */
$stmt = $conn->prepare("
    SELECT l.*, k.nama_lengkap
    FROM lembur l
    JOIN karyawan k ON k.id = l.karyawan_id
    $whereSql
    ORDER BY l.tanggal DESC
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
        <i data-lucide="clock-4" class="w-7 h-7 text-purple-400"></i>
        Data Lembur
    </h1>

    <a href="lembur_add.php"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="plus-circle"></i>
        Tambah
    </a>
</div>

<!-- SEARCH & FILTER -->
<form method="GET" class="glass p-4 mb-4">
    <div class="flex flex-wrap items-center gap-3">

        <!-- SEARCH -->
        <div class="relative w-full md:w-72">
            <i data-lucide="search"
               class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
            <input type="text" name="search"
                   value="<?= htmlspecialchars($search) ?>"
                   placeholder="Cari nama karyawan / keterangan"
                   class="w-full pl-10 pr-4 py-2 rounded-xl
                          bg-white/20 text-white focus:outline-none">
        </div>

        <!-- FILTER TANGGAL -->
        <div class="relative">
            <i data-lucide="calendar"
               class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
            <input type="date" name="tgl_dari"
                   value="<?= htmlspecialchars($tgl_dari) ?>"
                   class="pl-10 pr-4 py-2 rounded-xl
                          bg-white/20 text-white focus:outline-none">
        </div>

        <span class="text-white/60">-</span>

        <div class="relative">
            <i data-lucide="calendar-check"
               class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
            <input type="date" name="tgl_sampai"
                   value="<?= htmlspecialchars($tgl_sampai) ?>"
                   class="pl-10 pr-4 py-2 rounded-xl
                          bg-white/20 text-white focus:outline-none">
        </div>

        <!-- BUTTON FILTER -->
        <button type="submit"
                class="flex items-center gap-2 px-4 py-2 rounded-xl
                       bg-purple-600 hover:bg-purple-700 transition">
            <i data-lucide="filter"></i>
            Filter
        </button>

    </div>
</form>

<!-- TABLE -->
<div class="glass overflow-x-auto">
<table class="w-full text-sm">
    <thead class="border-b border-white/20 text-white/70">
        <tr>
            <th class="p-3 text-center">#</th>
            <th class="p-3 text-center">Karyawan</th>
            <th class="p-3 text-center">Tanggal</th>
            <th class="p-3 text-center">Jumlah Jam</th>
            <th class="p-3 text-center">Tarif / Jam</th>
            <th class="p-3 text-center">Total</th>
            <th class="p-3 text-center">Keterangan</th>
            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>

    <?php if(!$rows): ?>
        <tr>
            <td colspan="8" class="p-4 text-center text-white/60">
                Data tidak ditemukan
            </td>
        </tr>
    <?php endif; ?>

    <?php foreach($rows as $i => $r): ?>
        <tr class="border-b border-white/10 hover:bg-white/5">
            <td class="p-3 text-center"><?= $offset + $i + 1 ?></td>
            <td class="p-3 text-center"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
            <td class="p-3 text-center"><?= $r['tanggal'] ?></td>
            <td class="p-3 text-center"><?= $r['jumlah_jam'] ?> jam</td>
            <td class="p-3 text-center">
                Rp <?= number_format($r['tarif_per_jam'],0,',','.') ?>
            </td>
            <td class="p-3 text-center font-semibold text-emerald-400">
                Rp <?= number_format($r['total'],0,',','.') ?>
            </td>
            <td class="p-3 text-center"><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
            <td class="p-3 flex gap-2 justify-center">

                <a href="lembur_edit.php?id=<?= $r['id'] ?>"
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
       <?= $i==$page ? 'bg-purple-600' : 'bg-white/20 hover:bg-white/30' ?>">
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
        title: 'Hapus Data Lembur?',
        text: 'Data lembur akan dihapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, hapus'
    }).then(r=>{
        if(r.isConfirmed){
            window.location = 'lembur_delete.php?id=' + id;
        }
    })
}

<?php if(isset($_GET['success'])): ?>
Swal.fire({
    icon:'success',
    title:'Berhasil',
    text:
    <?= json_encode(match($_GET['success']){
        'add'    => 'Berhasil Menambahkan Data Lembur',
        'edit'   => 'Berhasil Mengubah Data Lembur',
        'delete' => 'Berhasil Menghapus Data Lembur',
        default  => ''
    }) ?>
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer_manager.php'; ?>
