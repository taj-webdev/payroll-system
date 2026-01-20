<?php
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/header_karyawan.php';
require_once __DIR__ . '/sidebar_karyawan.php';

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];

/* =========================
   Ambil karyawan_id
========================= */
$stmt = $conn->prepare("SELECT id FROM karyawan WHERE user_id = ?");
$stmt->execute([$user_id]);
$karyawan_id = $stmt->fetchColumn();

if (!$karyawan_id) {
    die('Data karyawan tidak ditemukan');
}

/* =========================
   Bulan & Tahun sekarang
========================= */
$bulan = date('m');
$tahun = date('Y');

/* =========================
   Status gaji terakhir
========================= */
$stmt = $conn->prepare("
    SELECT status, gaji_bersih
    FROM gaji_detail
    WHERE karyawan_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$karyawan_id]);
$gajiTerakhir = $stmt->fetch();

/* =========================
   Total gaji bulan ini
========================= */
$stmt = $conn->prepare("
    SELECT SUM(gaji_bersih)
    FROM gaji_detail
    WHERE karyawan_id = ?
    AND MONTH(created_at) = ?
    AND YEAR(created_at) = ?
");
$stmt->execute([$karyawan_id,$bulan,$tahun]);
$totalGaji = $stmt->fetchColumn() ?? 0;

/* =========================
   Total absensi bulan ini
========================= */
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM absensi
    WHERE karyawan_id = ?
    AND MONTH(tanggal) = ?
    AND YEAR(tanggal) = ?
");
$stmt->execute([$karyawan_id,$bulan,$tahun]);
$totalAbsensi = $stmt->fetchColumn();

/* =========================
   Total lembur bulan ini
========================= */
$stmt = $conn->prepare("
    SELECT SUM(total)
    FROM lembur
    WHERE karyawan_id = ?
    AND MONTH(tanggal) = ?
    AND YEAR(tanggal) = ?
");
$stmt->execute([$karyawan_id,$bulan,$tahun]);
$totalLembur = $stmt->fetchColumn() ?? 0;

/* =========================
   DATA GRAFIK GAJI BULANAN
========================= */
$stmt = $conn->prepare("
    SELECT 
        gt.tahun,
        gb.bulan,
        gd.gaji_bersih
    FROM gaji_detail gd
    JOIN gaji_bulan gb ON gb.id = gd.gaji_bulan_id
    JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
    WHERE gd.karyawan_id = ?
    ORDER BY gt.tahun, gb.bulan
");
$stmt->execute([$karyawan_id]);
$chartRows = $stmt->fetchAll();

$chartLabels = [];
$chartData   = [];

foreach ($chartRows as $r) {
    $chartLabels[] = date('M Y', mktime(0,0,0,$r['bulan'],1,$r['tahun']));
    $chartData[]   = (float)$r['gaji_bersih'];
}

/* ========================= */
function rupiah($n){
    return 'Rp ' . number_format($n,0,',','.');
}

$status = $gajiTerakhir['status'] ?? 'pending';

$warnaStatus = [
    'approved' => 'text-green-400',
    'rejected' => 'text-red-400',
    'pending'  => 'text-yellow-400'
][$status] ?? 'text-gray-400';

$labelStatus = strtoupper($status);
?>

<main class="ml-64 mt-24 mb-16 px-6 fade-in">

<style>
@keyframes fadeIn {
    from {opacity:0; transform:translateY(30px);}
    to {opacity:1; transform:translateY(0);}
}
.fade-in{animation:fadeIn 1.2s ease-out;}

.card{
    background: rgba(255,255,255,.1);
    backdrop-filter: blur(16px);
    border-radius: 1.2rem;
    padding: 1.8rem;
    position: relative;
    overflow: hidden;
    transition: .4s;
}
.card:hover{
    transform: translateY(-6px) scale(1.03);
}
.glow-green{ box-shadow: 0 0 30px rgba(34,197,94,.7); }
.glow-blue{ box-shadow: 0 0 30px rgba(59,130,246,.7); }
.glow-yellow{ box-shadow: 0 0 30px rgba(234,179,8,.7); }
.glow-purple{ box-shadow: 0 0 30px rgba(168,85,247,.7); }

.pulse{
    animation: pulseGlow 2s ease-in-out infinite;
}
@keyframes pulseGlow {
    0%{transform:scale(1);filter:brightness(1);}
    50%{transform:scale(1.04);filter:brightness(1.7);}
    100%{transform:scale(1);filter:brightness(1);}
}
</style>

<h1 class="text-3xl font-bold mb-8 flex items-center gap-3">
    <i data-lucide="layout-dashboard" class="w-8 h-8 text-cyan-400"></i>
    Dashboard Karyawan
</h1>

<!-- CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    <div class="card glow-yellow pulse">
        <div class="flex items-center justify-between mb-4">
            <i data-lucide="wallet" class="w-10 h-10 text-yellow-400"></i>
            <span class="text-sm text-white/60">Status Gaji</span>
        </div>
        <h2 class="text-3xl font-bold <?= $warnaStatus ?>"><?= $labelStatus ?></h2>
        <p class="text-white/60 mt-2">Gaji terakhir kamu</p>
    </div>

    <div class="card glow-green pulse">
        <div class="flex items-center justify-between mb-4">
            <i data-lucide="dollar-sign" class="w-10 h-10 text-green-400"></i>
            <span class="text-sm text-white/60">Bulan Ini</span>
        </div>
        <h2 class="text-3xl font-bold text-green-300"><?= rupiah($totalGaji) ?></h2>
        <p class="text-white/60 mt-2">Total Gaji Bulan Ini</p>
    </div>

    <div class="card glow-blue pulse">
        <div class="flex items-center justify-between mb-4">
            <i data-lucide="calendar-check" class="w-10 h-10 text-blue-400"></i>
            <span class="text-sm text-white/60">Bulan Ini</span>
        </div>
        <h2 class="text-3xl font-bold text-blue-300"><?= $totalAbsensi ?> Hari</h2>
        <p class="text-white/60 mt-2">Total Kehadiran</p>
    </div>

    <div class="card glow-purple pulse">
        <div class="flex items-center justify-between mb-4">
            <i data-lucide="clock" class="w-10 h-10 text-purple-400"></i>
            <span class="text-sm text-white/60">Bulan Ini</span>
        </div>
        <h2 class="text-3xl font-bold text-purple-300"><?= rupiah($totalLembur) ?></h2>
        <p class="text-white/60 mt-2">Total Lembur</p>
    </div>

</div>

<!-- GRAFIK -->
<div class="mt-10 card glow-blue">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
        <i data-lucide="line-chart" class="text-cyan-400"></i>
        Perkembangan Gaji Bulanan
    </h2>
    <canvas id="gajiChart" height="100"></canvas>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
lucide.createIcons();

const ctx = document.getElementById('gajiChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Gaji Bersih',
            data: <?= json_encode($chartData) ?>,
            fill: true,
            tension: 0.4,
            borderWidth: 3
        }]
    },
    options: {
        plugins:{
            legend:{display:false}
        },
        scales:{
            y:{
                ticks:{
                    callback:function(value){
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/footer_karyawan.php'; ?>
