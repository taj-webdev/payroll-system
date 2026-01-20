<?php
require_once __DIR__ . '/../../app/config/database.php';

/* Pastikan session hidup */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$conn = $db->connect();

/* ===============================
   PROSES UBAH STATUS (DIREKTUR)
================================ */
if (isset($_GET['change_status'], $_GET['id'])) {

    if (
        !isset($_SESSION['user_id']) ||
        $_SESSION['role'] !== 'direktur'
    ) {
        die('Unauthorized');
    }

    $id     = (int) $_GET['id'];
    $status = $_GET['change_status'];

    if (in_array($status, ['pending','approved','rejected'])) {

        $stmt = $conn->prepare("
            UPDATE gaji_detail
            SET status = ?, 
                approved_by_direktur = ?, 
                approved_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $_SESSION['user_id'],   // ← inilah yg benar
            $id
        ]);

        header("Location: gaji_detail.php?bulan_id=".$_GET['bulan_id']."&success=status");
        exit;
    }
}

/* ===============================
   VALIDASI BULAN
================================ */
$bulan_id = (int)($_GET['bulan_id'] ?? 0);
if ($bulan_id <= 0) {
    header('Location: gaji_tahun.php');
    exit;
}

/* Ambil data bulan + tahun */
$stmtBulan = $conn->prepare("
    SELECT gb.*, gt.tahun
    FROM gaji_bulan gb
    JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
    WHERE gb.id = ?
    LIMIT 1
");
$stmtBulan->execute([$bulan_id]);
$bulan = $stmtBulan->fetch();

if (!$bulan) {
    header('Location: gaji_tahun.php');
    exit;
}

/* ===============================
   FILTER & SEARCH
================================ */
$search   = trim($_GET['search'] ?? '');
$status   = $_GET['status'] ?? '';
$karyawan = (int)($_GET['karyawan'] ?? 0);
$tanggal  = $_GET['tanggal'] ?? '';

$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where   = ['gd.gaji_bulan_id = ?'];
$params  = [$bulan_id];

if ($search !== '') {
    $where[] = "k.nama_lengkap LIKE ?";
    $params[] = "%$search%";
}
if ($status !== '') {
    $where[] = "gd.status = ?";
    $params[] = $status;
}
if ($karyawan > 0) {
    $where[] = "gd.karyawan_id = ?";
    $params[] = $karyawan;
}
if ($tanggal !== '') {
    $where[] = "DATE(gd.created_at) = ?";
    $params[] = $tanggal;
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

/* ===============================
   TOTAL DATA
================================ */
$stmtTotal = $conn->prepare("
    SELECT COUNT(*)
    FROM gaji_detail gd
    JOIN karyawan k ON k.id = gd.karyawan_id
    $whereSql
");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* ===============================
   DATA LIST
================================ */
$stmt = $conn->prepare("
    SELECT gd.*, k.nama_lengkap
    FROM gaji_detail gd
    JOIN karyawan k ON k.id = gd.karyawan_id
    $whereSql
    ORDER BY k.nama_lengkap ASC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

/* ===============================
   MASTER KARYAWAN (FILTER)
================================ */
$listKaryawan = $conn->query("
    SELECT id, nama_lengkap
    FROM karyawan
    ORDER BY nama_lengkap
")->fetchAll();

/* ===============================
   UI
================================ */
require_once __DIR__ . '/header_direktur.php';
require_once __DIR__ . '/sidebar_direktur.php';

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
<div class="flex items-center justify-between mb-6 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <i data-lucide="wallet" class="w-7 h-7 text-cyan-400"></i>
            Gaji Karyawan
        </h1>
        <p class="text-white/60 text-sm">
            <?= date('F', mktime(0,0,0,$bulan['bulan'],1)) ?> <?= $bulan['tahun'] ?>
        </p>
    </div>

    <div class="flex gap-2 flex-wrap">
        <a href="gaji_detail_export_pdf.php?<?= http_build_query($_GET) ?>"
           class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700">
            <i data-lucide="file-text"></i> PDF
        </a>

        <a href="gaji_detail_export_excel.php?<?= http_build_query($_GET) ?>"
           class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700">
            <i data-lucide="file-spreadsheet"></i> Excel
        </a>

        <a href="gaji_detail_add.php?bulan_id=<?= $bulan_id ?>"
           class="flex items-center gap-2 px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700">
            <i data-lucide="plus-circle"></i> Tambah
        </a>
    </div>
</div>

<!-- FILTER -->
<form method="GET" class="glass p-4 mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
    <input type="hidden" name="bulan_id" value="<?= $bulan_id ?>">

    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
           placeholder="Cari karyawan"
           class="px-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">

    <select name="status"
        class="px-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">Semua Status</option>
        <?php foreach(['pending','approved','rejected'] as $s): ?>
            <option value="<?= $s ?>" <?= $status==$s?'selected':'' ?> class="text-black">
                <?= ucfirst($s) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="karyawan"
        class="px-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">
        <option value="">Semua Karyawan</option>
        <?php foreach($listKaryawan as $k): ?>
            <option value="<?= $k['id'] ?>" <?= $karyawan==$k['id']?'selected':'' ?> class="text-black">
                <?= $k['nama_lengkap'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>"
           class="px-4 py-2 rounded-xl bg-white/20 text-white focus:outline-none">

    <button class="bg-cyan-600 hover:bg-cyan-700 rounded-xl px-4 py-2 flex items-center gap-2">
        <i data-lucide="filter"></i> Filter
    </button>
</form>

<!-- TABLE -->
<div class="glass overflow-x-auto">
<table class="w-full text-sm">
<thead class="border-b border-white/20 text-white/70">
<tr>
    <th class="p-3 text-center">#</th>
    <th class="p-3 text-center">Karyawan</th>
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
    <td class="p-3 text-center font-semibold"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
    <td class="p-3 text-center"><?= rupiah($r['gaji_bersih']) ?></td>
    <td class="p-3 text-center">
        <span class="px-3 py-1 rounded-full text-xs
            <?= $r['status']=='approved'?'bg-green-500/20 text-green-300':
               ($r['status']=='rejected'?'bg-red-500/20 text-red-300':'bg-yellow-500/20 text-yellow-300') ?>">
            <?= strtoupper($r['status']) ?>
        </span>
    </td>
    <td class="p-3 flex gap-2 justify-center">
        <!-- UBAH STATUS -->
        <button onclick="changeStatus(<?= $r['id'] ?>,'approved')"
                class="px-3 py-1 rounded-lg bg-green-600 hover:bg-green-700"
                title="Approve">
                <i data-lucide="check-circle"></i>
        </button>

        <button onclick="changeStatus(<?= $r['id'] ?>,'rejected')"
                class="px-3 py-1 rounded-lg bg-red-600 hover:bg-red-700"
                title="Reject">
                <i data-lucide="x-circle"></i>
        </button>

        <button onclick="changeStatus(<?= $r['id'] ?>,'pending')"
                class="px-3 py-1 rounded-lg bg-yellow-500 hover:bg-yellow-600"
                title="Pending">
                <i data-lucide="clock"></i>
        </button>

        <a href="gaji_detail_edit.php?id=<?= $r['id'] ?>"
           class="px-3 py-1 rounded-lg bg-yellow-500 hover:bg-yellow-600">
            <i data-lucide="edit"></i>
        </a>

        <button onclick="confirmDelete(<?= $r['id'] ?>)"
            class="px-3 py-1 rounded-lg bg-red-600 hover:bg-red-700">
            <i data-lucide="trash"></i>
        </button>

        <a href="gaji_detail_print.php?id=<?= $r['id'] ?>"
           class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700">
            <i data-lucide="printer"></i>
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

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
lucide.createIcons();

function confirmDelete(id){
    Swal.fire({
        title:'Hapus Data Gaji?',
        text:'Data gaji karyawan ini akan dihapus',
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#dc2626',
        confirmButtonText:'Ya, hapus'
    }).then(r=>{
        if(r.isConfirmed){
            window.location='gaji_detail_delete.php?id='+id;
        }
    })
}

function changeStatus(id,status){
    Swal.fire({
        title: 'Ubah Status Gaji?',
        text: 'Yakin ingin mengubah status gaji ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Ubah'
    }).then((r)=>{
        if(r.isConfirmed){
            window.location = 
                'gaji_detail.php?bulan_id=<?= $bulan_id ?>&id='+id+'&change_status='+status;
        }
    })
}

<?php if(isset($_GET['success'])): ?>
Swal.fire({
    icon:'success',
    title:'Berhasil',
    text:
    <?= json_encode(match($_GET['success']){
        'add'    => 'Berhasil Menambah Data Gaji',
        'edit'   => 'Berhasil Mengubah Data Gaji',
        'delete' => 'Berhasil Menghapus Data Gaji',
        'status' => 'Berhasil Mengubah Status Gaji',
        default  => ''
    }) ?>
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer_direktur.php'; ?>
