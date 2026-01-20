<?php
require_once __DIR__ . '/header_manager.php';
require_once __DIR__ . '/sidebar_manager.php';
require_once __DIR__ . '/../../app/config/database.php';

$db   = new Database();
$conn = $db->connect();

/* ==============================
   PERIODE
============================== */
$bulan = (int)date('n');
$tahun = (int)date('Y');

$prevTime  = strtotime('-1 month');
$prevBulan = (int)date('n', $prevTime);
$prevTahun = (int)date('Y', $prevTime);

/* ==============================
   SUMMARY
============================== */
function getSummary(PDO $conn, string $status, int $bulan, int $tahun): array {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) jumlah,
            COALESCE(SUM(gd.gaji_bersih),0) total
        FROM gaji_detail gd
        JOIN gaji_bulan gb ON gb.id = gd.gaji_bulan_id
        JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
        WHERE gd.status = :status
          AND gb.bulan  = :bulan
          AND gt.tahun  = :tahun
    ");
    $stmt->execute(compact('status','bulan','tahun'));
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['jumlah'=>0,'total'=>0];
}

/* ==============================
   SPARKLINE (6 BULAN + LABEL)
============================== */
function sparkline(PDO $conn, string $status): array {
    $stmt = $conn->prepare("
        SELECT gt.tahun, gb.bulan, SUM(gd.gaji_bersih) total
        FROM gaji_detail gd
        JOIN gaji_bulan gb ON gb.id = gd.gaji_bulan_id
        JOIN gaji_tahun gt ON gt.id = gb.gaji_tahun_id
        WHERE gd.status = :status
        GROUP BY gt.tahun, gb.bulan
        ORDER BY gt.tahun DESC, gb.bulan DESC
        LIMIT 6
    ");
    $stmt->execute(['status'=>$status]);

    $rows = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

    $labels = [];
    $data   = [];

    foreach($rows as $r){
        $labels[] = date('M Y', mktime(0,0,0,$r['bulan'],1,$r['tahun']));
        $data[]   = (float)$r['total'];
    }

    while(count($labels) < 6){
        array_unshift($labels,'');
        array_unshift($data,0);
    }

    return ['labels'=>$labels,'data'=>$data];
}

/* ==============================
   HELPER
============================== */
function percent($now,$prev){
    return $prev==0 ? 100 : round((($now-$prev)/$prev)*100,1);
}

/* ==============================
   DATA
============================== */
$pending   = getSummary($conn,'pending',$bulan,$tahun);
$approved  = getSummary($conn,'approved',$bulan,$tahun);
$rejected  = getSummary($conn,'rejected',$bulan,$tahun);

$pendingPrev   = getSummary($conn,'pending',$prevBulan,$prevTahun);
$approvedPrev  = getSummary($conn,'approved',$prevBulan,$prevTahun);
$rejectedPrev  = getSummary($conn,'rejected',$prevBulan,$prevTahun);

$pendingPercent   = percent($pending['total'],$pendingPrev['total']);
$approvedPercent  = percent($approved['total'],$approvedPrev['total']);
$rejectedPercent  = percent($rejected['total'],$rejectedPrev['total']);

$pendingQtyPercent  = percent($pending['jumlah'],$pendingPrev['jumlah']);
$approvedQtyPercent = percent($approved['jumlah'],$approvedPrev['jumlah']);

$sparkPending  = sparkline($conn,'pending');
$sparkApproved = sparkline($conn,'approved');
$sparkRejected = sparkline($conn,'rejected');
?>

<main class="ml-64 mt-24 mb-16 px-6 fade-in">

<style>
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px)}
    to{opacity:1}
}
.fade-in{animation:fadeIn 1s ease}

/* ================= GLASS ================= */
.glass-card{
    background:rgba(255,255,255,.10);
    backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,.18);
    border-radius:1rem;
    transition:all .35s ease;
    position:relative;
    overflow:hidden;
}

/* Lift on hover */
.glass-card:hover{
    transform:translateY(-6px) scale(1.025);
}

/* ================= GLOW PULSE ================= */
@keyframes glowPulse {
    0%   { filter:drop-shadow(0 0 8px rgba(255,255,255,.15)); }
    50%  { filter:drop-shadow(0 0 18px rgba(255,255,255,.45)); }
    100% { filter:drop-shadow(0 0 8px rgba(255,255,255,.15)); }
}

/* All glowing cards pulse */
.glow-indigo,
.glow-green,
.glow-red,
.glow-yellow,
.glow-emerald,
.glow-cyan{
    animation:glowPulse 4s ease-in-out infinite;
}

/* ================= BASE GLOW ================= */
.glow-indigo{box-shadow:0 0 25px rgba(99,102,241,.45)}
.glow-green{box-shadow:0 0 25px rgba(34,197,94,.45)}
.glow-red{box-shadow:0 0 25px rgba(239,68,68,.45)}
.glow-yellow{box-shadow:0 0 25px rgba(234,179,8,.45)}
.glow-emerald{box-shadow:0 0 25px rgba(16,185,129,.45)}
.glow-cyan{box-shadow:0 0 25px rgba(34,211,238,.45)}

/* ================= HOVER BOOST ================= */
.glow-indigo:hover{box-shadow:0 0 40px rgba(99,102,241,.85)}
.glow-green:hover{box-shadow:0 0 40px rgba(34,197,94,.85)}
.glow-red:hover{box-shadow:0 0 40px rgba(239,68,68,.85)}
.glow-yellow:hover{box-shadow:0 0 40px rgba(234,179,8,.85)}
.glow-emerald:hover{box-shadow:0 0 40px rgba(16,185,129,.85)}
.glow-cyan:hover{box-shadow:0 0 40px rgba(34,211,238,.85)}
</style>

<!-- ===================== CARD ===================== -->
<div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6 mb-10">
<?php
$cards = [
 ['Total Pending','wallet','indigo',$pending['total'],$pendingPercent,$sparkPending,true,'rupiah'],
 ['Total Approved','check-circle','green',$approved['total'],$approvedPercent,$sparkApproved,false,'rupiah'],
 ['Total Rejected','x-circle','red',$rejected['total'],$rejectedPercent,$sparkRejected,false,'rupiah'],
 ['Jumlah Pending','clock','yellow',$pending['jumlah'],$pendingQtyPercent,$sparkPending,false,'number'],
 ['Jumlah Approved','check','emerald',$approved['jumlah'],$approvedQtyPercent,$sparkApproved,false,'number'],
];

foreach($cards as $c):
?>
<div class="glass-card glow-<?= $c[2] ?> p-5">
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-white/70"><?= $c[0] ?></p>
            <h3 class="text-2xl font-bold mt-1">
                <?= $c[7]=='rupiah' ? 'Rp '.number_format($c[3],0,',','.') : $c[3] ?>
            </h3>
        </div>
        <i data-lucide="<?= $c[1] ?>" class="<?= $c[6]?'w-10 h-10':'w-8 h-8' ?> text-<?= $c[2] ?>-400"></i>
    </div>

    <p class="mt-2 text-xs <?= $c[4]>=0?'text-green-300':'text-red-300' ?>">
        <?= $c[4]>0?'+':'' ?><?= $c[4] ?>% dari bulan lalu
    </p>

    <canvas class="sparkline mt-3"
        data-labels='<?= json_encode($c[5]['labels']) ?>'
        data-values='<?= json_encode($c[5]['data']) ?>'
        data-color="<?= $c[2] ?>"
        data-type="<?= $c[7] ?>">
    </canvas>
</div>
<?php endforeach; ?>
</div>

<!-- ===================== CHART ===================== -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
  
  <div class="glass-card glow-indigo p-5">
    <h2 class="text-sm font-semibold mb-3 text-white/80">Statistik Jumlah</h2>
    <div class="h-52">
        <canvas id="barChart"></canvas>
    </div>
  </div>

  <div class="glass-card glow-green p-5">
    <h2 class="text-sm font-semibold mb-3 text-white/80">Statistik Total Gaji</h2>
    <div class="h-52">
        <canvas id="lineChart"></canvas>
    </div>
  </div>

  <div class="glass-card glow-cyan p-5">
    <h2 class="text-sm font-semibold mb-3 text-white/80">Proporsi Status</h2>

    <div class="grid grid-cols-2 gap-4 items-center h-52">
        
        <!-- LEFT : LABEL -->
        <div class="space-y-3 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-indigo-400"></span>
                <span>Pending</span>
                <span class="ml-auto font-semibold"><?= $pending['jumlah'] ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-400"></span>
                <span>Approved</span>
                <span class="ml-auto font-semibold"><?= $approved['jumlah'] ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-400"></span>
                <span>Rejected</span>
                <span class="ml-auto font-semibold"><?= $rejected['jumlah'] ?></span>
            </div>
        </div>

        <!-- RIGHT : PIE -->
        <div class="w-full h-full flex items-center justify-center">
            <canvas id="pieChart"></canvas>
        </div>

    </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
lucide.createIcons();

const COLORS = {
  indigo:'rgba(99,102,241,.9)',
  green:'rgba(34,197,94,.9)',
  red:'rgba(239,68,68,.9)',
  yellow:'rgba(234,179,8,.9)',
  emerald:'rgba(16,185,129,.9)'
};

/* Sparkline */
document.querySelectorAll('.sparkline').forEach(el=>{
  const labels = JSON.parse(el.dataset.labels);
  const data   = JSON.parse(el.dataset.values);
  const color  = COLORS[el.dataset.color];
  const type   = el.dataset.type;

  new Chart(el,{
    type:'line',
    data:{labels,datasets:[{data,borderColor:color,borderWidth:2,tension:.4,fill:false}]},
    options:{
      plugins:{
        legend:{display:false},
        tooltip:{
          callbacks:{
            label:(ctx)=>{
              const v = ctx.raw;
              return type==='rupiah'
                ? 'Rp '+v.toLocaleString('id-ID')
                : v+' data';
            }
          }
        }
      },
      scales:{x:{display:false},y:{display:false,suggestedMin:0}}
    }
  });
});

/* Charts bawah */
new Chart(barChart,{type:'bar',data:{labels:['Pending','Approved','Rejected'],datasets:[{data:[<?= $pending['jumlah'] ?>,<?= $approved['jumlah'] ?>,<?= $rejected['jumlah'] ?>]}]}});
new Chart(lineChart,{type:'line',data:{labels:['Pending','Approved','Rejected'],datasets:[{data:[<?= $pending['total'] ?>,<?= $approved['total'] ?>,<?= $rejected['total'] ?>],tension:.4}]}});
new Chart(pieChart,{
    type:'pie',
    data:{
        labels:['Pending','Approved','Rejected'],
        datasets:[{
            data:[<?= $pending['jumlah'] ?>,<?= $approved['jumlah'] ?>,<?= $rejected['jumlah'] ?>],
            backgroundColor:[
                'rgba(99,102,241,.9)',   // indigo
                'rgba(34,197,94,.9)',    // green
                'rgba(239,68,68,.9)'     // red
            ],
            borderWidth:0
        }]
    },
    options:{
        plugins:{
            legend:{display:false}
        }
    }
});
</script>

<?php require_once __DIR__ . '/footer_manager.php'; ?>
