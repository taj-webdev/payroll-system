<?php
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/sidebar_admin.php';
require_once __DIR__ . '/../../app/config/database.php';

$db = new Database();
$conn = $db->connect();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: karyawan.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT k.*, d.nama_divisi
    FROM karyawan k
    LEFT JOIN divisi d ON k.divisi_id = d.id
    WHERE k.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: karyawan.php');
    exit;
}

$foto = $data['foto']
    ? '../../public/uploads/karyawan/' . $data['foto']
    : '../../public/assets/user-default.png';
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

.avatar{
    width:160px;
    height:160px;
    border-radius:1rem;
    object-fit:cover;
    box-shadow:0 0 30px rgba(255,255,255,.25);
    border:2px solid rgba(255,255,255,.25);
}
</style>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="user" class="w-7 h-7 text-blue-400"></i>
        Detail Karyawan
    </h1>

    <div class="flex gap-3">
        <a href="karyawan_edit.php?id=<?= $data['id'] ?>"
           class="flex items-center gap-2 px-4 py-2 rounded-xl
                  bg-yellow-500 hover:bg-yellow-600 transition">
            <i data-lucide="edit"></i>
            Edit
        </a>

        <a href="karyawan.php"
           class="flex items-center gap-2 px-4 py-2 rounded-xl
                  bg-gray-600 hover:bg-gray-700 transition">
            <i data-lucide="arrow-left"></i>
            Kembali
        </a>
    </div>
</div>

<!-- CONTENT -->
<div class="glass p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- FOTO -->
    <div class="flex flex-col items-center gap-4">
        <img src="<?= $foto ?>" alt="Foto Karyawan" class="avatar">
        <span class="text-white/70 text-sm flex items-center gap-1">
            <i data-lucide="camera" class="w-4 h-4"></i>
            Foto Karyawan
        </span>
    </div>

    <!-- DETAIL -->
    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">

        <?php
        function detail($icon,$label,$value){
            echo "
            <div class='flex gap-3'>
                <i data-lucide='$icon' class='w-5 h-5 text-cyan-400 mt-1'></i>
                <div>
                    <p class='text-xs text-white/60'>$label</p>
                    <p class='font-semibold'>$value</p>
                </div>
            </div>";
        }

        detail('hash','NIK',$data['nik']);
        detail('credit-card','No KTP',$data['no_ktp']);
        detail('user','Nama Lengkap',$data['nama_lengkap']);
        detail('layers','Divisi',$data['nama_divisi'] ?? '-');
        detail('users','Jenis Kelamin',$data['jenis_kelamin']);
        detail('heart','Agama',$data['agama']);
        detail('badge-check','Status',$data['status']);
        detail('map-pin','Tempat Lahir',$data['tempat_lahir'] ?? '-');
        detail('calendar','Tanggal Lahir',$data['tanggal_lahir'] ?? '-');
        detail('home','Alamat',$data['alamat'] ?? '-');
        ?>

    </div>
</div>

</main>

<script>
lucide.createIcons();
</script>

<?php require_once __DIR__ . '/footer_admin.php'; ?>
