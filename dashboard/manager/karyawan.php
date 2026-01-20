<?php
require_once __DIR__ . '/header_manager.php';
require_once __DIR__ . '/sidebar_manager.php';
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

/* =========================
   SEARCH & PAGINATION
========================= */
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where  = '';
$params = [];

if ($search !== '') {
    $where = "WHERE nama_lengkap LIKE ? OR nik LIKE ? OR no_ktp LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

/* Total data */
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM karyawan $where");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetchColumn();
$totalPage = ceil($totalData / $limit);

/* Data */
$sql = "
    SELECT k.*, d.nama_divisi
    FROM karyawan k
    LEFT JOIN divisi d ON k.divisi_id = d.id
    $where
    ORDER BY k.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $conn->prepare($sql);
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
        <i data-lucide="users" class="w-7 h-7 text-cyan-400"></i>
        Data Karyawan
    </h1>

    <a href="karyawan_add.php"
       class="flex items-center gap-2 px-4 py-2 rounded-xl
              bg-green-600 hover:bg-green-700 transition">
        <i data-lucide="user-plus" class="w-5 h-5"></i>
        Tambah
    </a>
</div>

<!-- SEARCH -->
<form method="GET" class="mb-4">
    <div class="relative w-72">
        <i data-lucide="search" class="absolute left-3 top-3 w-5 h-5 text-white/60"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
            placeholder="Cari NIK / Nama / KTP"
            class="w-full pl-10 pr-4 py-2 rounded-xl bg-white/20
                   text-white placeholder-white/60 focus:outline-none">
    </div>
</form>

<!-- TABLE -->
<div class="glass overflow-x-auto">
<table class="w-full text-sm">
    <thead class="text-white/70 border-b border-white/20">
        <tr>
            <th class="p-3 text-center">#</th>
            <th class="p-3 text-center">NIK</th>
            <th class="p-3 text-center">Nama</th>
            <th class="p-3 text-center">Divisi</th>
            <th class="p-3 text-center">JK</th>
            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php if(!$rows): ?>
        <tr>
            <td colspan="6" class="p-4 text-center text-white/60">
                Data tidak ditemukan
            </td>
        </tr>
    <?php endif; ?>

    <?php foreach($rows as $i => $r): ?>
        <tr class="border-b border-white/10 hover:bg-white/5">
            <td class="p-3 text-center"><?= ($offset+$i+1) ?></td>
            <td class="p-3 text-center"><?= htmlspecialchars($r['nik']) ?></td>
            <td class="p-3 text-center"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
            <td class="p-3 text-center"><?= htmlspecialchars($r['nama_divisi'] ?? '-') ?></td>
            <td class="p-3 text-center"><?= $r['jenis_kelamin'] ?></td>
            <td class="p-3 flex gap-2 justify-center">

                <a href="karyawan_detail.php?id=<?= $r['id'] ?>"
                   class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </a>

                <a href="karyawan_edit.php?id=<?= $r['id'] ?>"
                   class="px-3 py-1 rounded-lg bg-yellow-500 hover:bg-yellow-600">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                </a>

                <button onclick="confirmDelete(<?= $r['id'] ?>)"
                        class="px-3 py-1 rounded-lg bg-red-600 hover:bg-red-700">
                    <i data-lucide="trash" class="w-4 h-4"></i>
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
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
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

/* DELETE CONFIRM */
function confirmDelete(id){
    Swal.fire({
        title: 'Hapus Data?',
        text: 'Data karyawan akan dihapus permanen',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, hapus'
    }).then((r)=>{
        if(r.isConfirmed){
            window.location = 'karyawan_delete.php?id=' + id;
        }
    })
}

/* NOTIF */
<?php if(isset($_GET['success'])): ?>
Swal.fire({
    icon:'success',
    title:'Berhasil',
    text:
    <?= json_encode(match($_GET['success']){
        'add'    => 'Berhasil Menambahkan Karyawan',
        'edit'   => 'Berhasil Mengubah Data Karyawan',
        'delete' => 'Berhasil Menghapus Data Karyawan',
        default  => ''
    }) ?>
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer_manager.php'; ?>
