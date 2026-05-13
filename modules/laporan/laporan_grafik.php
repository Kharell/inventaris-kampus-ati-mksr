<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

// Helper function untuk mengambil data tunggal (Label & Value)
function getChartData($conn, $query) {
    $result = mysqli_query($conn, $query);
    $labels = []; $values = [];
    while ($row = mysqli_fetch_array($result)) {
        $labels[] = $row[0] ?? 'N/A';
        $values[] = (int)$row[1];
    }
    return ['labels' => $labels, 'data' => $values];
}

// --- PENGAMBILAN DATA ---
$q1 = getChartData($conn, "SELECT kondisi, COUNT(*) FROM bahan_praktek GROUP BY kondisi");
$q2 = getChartData($conn, "SELECT nama_bahan, stok FROM bahan_praktek ORDER BY stok DESC LIMIT 10");
$q3 = getChartData($conn, "SELECT nama_bahan, stok FROM bahan_praktek WHERE stok < 5 ORDER BY stok ASC");
$q4 = getChartData($conn, "SELECT satuan, COUNT(*) FROM bahan_praktek GROUP BY satuan");
$q5 = getChartData($conn, "SELECT DATE_FORMAT(tgl_masuk, '%M') as bulan, COUNT(*) FROM bahan_praktek GROUP BY DATE_FORMAT(tgl_masuk, '%M'), MONTH(tgl_masuk) ORDER BY MONTH(tgl_masuk)");
$q6 = getChartData($conn, "SELECT l.nama_lab, SUM(d.jumlah) FROM distribusi_lab d JOIN lab l ON d.id_lab = l.id_lab GROUP BY l.id_lab, l.nama_lab");
$q7 = getChartData($conn, "SELECT status, COUNT(*) FROM distribusi_lab GROUP BY status");
$q8 = getChartData($conn, "SELECT DATE_FORMAT(tanggal_distribusi, '%d %b') as tgl, COUNT(*) FROM distribusi_lab WHERE tanggal_distribusi > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE_FORMAT(tanggal_distribusi, '%d %b'), tanggal_distribusi ORDER BY tanggal_distribusi ASC");
$q9 = getChartData($conn, "SELECT j.nama_jurusan, SUM(d.jumlah) FROM distribusi_lab d JOIN lab l ON d.id_lab = l.id_lab JOIN jurusan j ON l.id_jurusan = j.id_jurusan GROUP BY j.id_jurusan, j.nama_jurusan");
$q10 = getChartData($conn, "SELECT b.nama_bahan, SUM(d.jumlah) as total FROM distribusi_lab d JOIN bahan_praktek b ON d.id_praktek = b.id_praktek GROUP BY b.nama_bahan ORDER BY total DESC LIMIT 5");
$q11 = getChartData($conn, "SELECT DATE_FORMAT(tgl_pakai, '%M') as bulan, SUM(jumlah_pakai) FROM pemakaian_lab GROUP BY DATE_FORMAT(tgl_pakai, '%M'), MONTH(tgl_pakai) ORDER BY MONTH(tgl_pakai)");

$q12_raw = mysqli_query($conn, "SELECT b.nama_bahan, SUM(p.jumlah_pakai) as pakai, b.stok as sisa FROM pemakaian_lab p JOIN bahan_praktek b ON p.id_praktek = b.id_praktek GROUP BY b.nama_bahan, b.stok LIMIT 5");
$q12_labels = []; $q12_pakai = []; $q12_sisa = [];
while($r = mysqli_fetch_assoc($q12_raw)){ $q12_labels[] = $r['nama_bahan']; $q12_pakai[] = $r['pakai']; $q12_sisa[] = $r['sisa']; }

$q13 = getChartData($conn, "SELECT l.nama_lab, COUNT(p.id_pemakaian) FROM pemakaian_lab p JOIN lab l ON p.id_lab = l.id_lab GROUP BY l.id_lab, l.nama_lab");
$q14 = getChartData($conn, "SELECT status, COUNT(*) FROM permintaan_barang GROUP BY status");
$q15 = getChartData($conn, "SELECT DAYNAME(tgl_permintaan) as hari, COUNT(*) FROM permintaan_barang GROUP BY DAYNAME(tgl_permintaan), DAYOFWEEK(tgl_permintaan) ORDER BY DAYOFWEEK(tgl_permintaan)");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisasi Dashboard Analitik - Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
    :root { 
        --navy: #001f3f; 
        --navy-light: #003366; 
        --gold: #ffcc00; 
        --bg-soft: #f4f7fc;
    }
    body { background-color: var(--bg-soft); font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }

    /* Layout Standard */
    .main-content { margin-left: 260px; transition: 0.3s ease; min-height: 100vh; }
    @media (max-width: 992px) { .main-content { margin-left: 0; } }

    /* Header Banner */
    .header-card { 
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); 
        color: white; 
        border-radius: 20px; 
        padding: 35px 40px; 
        margin-bottom: 30px; 
        box-shadow: 0 10px 30px rgba(0, 31, 63, 0.15);
        border-bottom: 4px solid var(--gold);
        position: relative;
        overflow: hidden;
    }
    .header-card::after {
        content: ''; position: absolute; right: -50px; top: -100px; width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(255,204,0,0.1) 0%, transparent 70%); border-radius: 50%;
    }

    .btn-modern-print {
        background: var(--gold);
        color: var(--navy);
        border: none;
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 800;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 15px rgba(255, 204, 0, 0.3);
        z-index: 2;
        position: relative;
    }
    .btn-modern-print:hover { background: #e6b800; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(255, 204, 0, 0.4); }

    /* Section & Cards */
    .dashboard-section { margin-bottom: 50px; }
    .premium-divider { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
    .premium-divider .line { height: 2px; flex-grow: 1; background: linear-gradient(90deg, #cbd5e1, transparent); }
    .premium-divider .section-label { font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--navy); font-size: 0.95rem; }

    .vibrant-card {
        border: none; border-radius: 20px; background: #ffffff; overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03); transition: all 0.3s ease;
        border-top: 5px solid var(--accent-color);
    }
    .vibrant-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08); }

    .vibrant-header { padding: 20px 20px 0 20px; display: flex; align-items: center; gap: 12px; }
    .icon-circle { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--accent-color); color: white; box-shadow: 0 4px 10px var(--shadow-color); font-size: 1.2rem; }
    .vibrant-title { font-weight: 800; color: var(--navy); font-size: 0.95rem; margin: 0; }

    .vibrant-body { padding: 15px 20px 20px; height: 280px; position: relative; }
    .chart-container { position: relative; height: 100%; width: 100%; }

    /* --- PENGATURAN KHUSUS CETAK (PRINT) --- */
    @media print {
        .sidebar, .btn-modern-print, header, .header, nav, .btn-print-wrapper { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
        body { background: white !important; }
        
        .header-card { background: white !important; color: black !important; box-shadow: none !important; border: none !important; border-bottom: 2px solid black !important; padding: 0 0 20px 0 !important; }
        .header-card p { color: #666 !important; }
        
        .col-md-4 { width: 48% !important; display: inline-block !important; margin-bottom: 20px !important; }
        .vibrant-card { border: 1px solid #ddd !important; box-shadow: none !important; page-break-inside: avoid; border-top-width: 2px !important; }
        .icon-circle { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../includes/header.php"; ?>

        <main class="p-4" style="margin-top: 70px;">
            
            <div class="btn-print-wrapper">
                <div class="header-card d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center position-relative" style="z-index: 2;">
                        <div class="me-4 d-none d-md-block">
                            <i class="bi bi-bar-chart-line-fill text-warning" style="font-size: 3.5rem;"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1">Dashboard Analitik Inventaris</h2>
                            <p class="mb-0 text-white-50 fs-6">Ringkasan statistik stok, manajemen distribusi, dan laporan pemakaian unit</p>
                        </div>
                    </div>
                    <button onclick="window.print()" class="btn-modern-print">
                        <i class="bi bi-printer-fill me-2"></i> Cetak PDF
                    </button>
                </div>
            </div>

            <!-- SECTION I: STOK -->
            <div class="dashboard-section">
                <div class="premium-divider">
                    <span class="section-label"><i class="bi bi-box-seam me-2 text-warning"></i> I. STOK & INVENTARIS</span>
                    <div class="line"></div>
                </div>
                <div class="row g-4">
                    <?php 
                    $charts_stok = [
                        ['id' => 'c1', 'title' => 'Kondisi Bahan', 'icon' => 'bi-heart-pulse', 'color' => '#6366f1'],
                        ['id' => 'c2', 'title' => 'Top 10 Stok Terbanyak', 'icon' => 'bi-trophy', 'color' => '#f59e0b'], 
                        ['id' => 'c3', 'title' => 'Stok Hampir Habis', 'icon' => 'bi-bell-fill', 'color' => '#ef4444'], 
                        ['id' => 'c4', 'title' => 'Sebaran Satuan Ukur', 'icon' => 'bi-tag-fill', 'color' => '#8b5cf6'],
                        ['id' => 'c5', 'title' => 'Tren Masuk per Bulan', 'icon' => 'bi-graph-up', 'color' => '#10b981'],
                    ];
                    foreach($charts_stok as $c): 
                        $shadow = $c['color'] . '4D';
                    ?>
                    <div class="col-md-4">
                        <div class="card vibrant-card h-100" style="--accent-color: <?= $c['color'] ?>; --shadow-color: <?= $shadow ?>;">
                            <div class="vibrant-header">
                                <div class="icon-circle"><i class="bi <?= $c['icon'] ?>"></i></div>
                                <h6 class="vibrant-title"><?= $c['title'] ?></h6>
                            </div>
                            <div class="vibrant-body">
                                <div class="chart-container"><canvas id="<?= $c['id'] ?>"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SECTION II: DISTRIBUSI -->
            <div class="dashboard-section">
                <div class="premium-divider">
                    <span class="section-label"><i class="bi bi-truck me-2 text-primary"></i> II. DISTRIBUSI LOGISTIK</span>
                    <div class="line"></div>
                </div>
                <div class="row g-4">
                    <?php 
                    $charts_dist = [
                        ['id' => 'c6', 'title' => 'Volume Distribusi per Lab', 'icon' => 'bi-geo-alt-fill', 'color' => '#0ea5e9'],
                        ['id' => 'c7', 'title' => 'Status Pengiriman', 'icon' => 'bi-check-circle', 'color' => '#22c55e'], 
                        ['id' => 'c8', 'title' => 'Histori Harian (30 Hari)', 'icon' => 'bi-clock-history', 'color' => '#f43f5e'], 
                        ['id' => 'c9', 'title' => 'Distribusi Per Jurusan', 'icon' => 'bi-diagram-3-fill', 'color' => '#ec4899'],
                        ['id' => 'c10', 'title' => 'Item Paling Sering Didistribusi', 'icon' => 'bi-lightning-charge', 'color' => '#fbbf24'],
                    ];
                    foreach($charts_dist as $c): 
                        $shadow = $c['color'] . '4D';
                    ?>
                    <div class="col-md-4">
                        <div class="card vibrant-card h-100" style="--accent-color: <?= $c['color'] ?>; --shadow-color: <?= $shadow ?>;">
                            <div class="vibrant-header">
                                <div class="icon-circle"><i class="bi <?= $c['icon'] ?>"></i></div>
                                <h6 class="vibrant-title"><?= $c['title'] ?></h6>
                            </div>
                            <div class="vibrant-body">
                                <div class="chart-container"><canvas id="<?= $c['id'] ?>"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SECTION III: ANALITIK -->
            <div class="dashboard-section">
                <div class="premium-divider">
                    <span class="section-label"><i class="bi bi-cpu me-2 text-danger"></i> III. ANALITIK PEMAKAIAN & REQUEST</span>
                    <div class="line"></div>
                </div>
                <div class="row g-4">
                    <?php 
                    $charts_extra = [
                        ['id' => 'c11', 'title' => 'Tren Pemakaian Bulanan', 'icon' => 'bi-activity', 'color' => '#6366f1'],
                        ['id' => 'c12', 'title' => 'Sisa vs Terpakai (Top 5)', 'icon' => 'bi-droplet-fill', 'color' => '#3b82f6'],
                        ['id' => 'c13', 'title' => 'Lab Teraktif Melapor', 'icon' => 'bi-award-fill', 'color' => '#fb923c'],
                        ['id' => 'c14', 'title' => 'Rasio Persetujuan Admin', 'icon' => 'bi-patch-check', 'color' => '#10b981'],
                        ['id' => 'c15', 'title' => 'Hari Tersibuk (Request)', 'icon' => 'bi-calendar-week', 'color' => '#64748b'], 
                    ];
                    foreach($charts_extra as $c): 
                        $shadow = $c['color'] . '4D';
                    ?>
                    <div class="col-md-4">
                        <div class="card vibrant-card h-100" style="--accent-color: <?= $c['color'] ?>; --shadow-color: <?= $shadow ?>;">
                            <div class="vibrant-header">
                                <div class="icon-circle"><i class="bi <?= $c['icon'] ?>"></i></div>
                                <h6 class="vibrant-title"><?= $c['title'] ?></h6>
                            </div>
                            <div class="vibrant-body">
                                <div class="chart-container"><canvas id="<?= $c['id'] ?>"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Set Font Family default Chart.js agar seragam dengan Plus Jakarta Sans
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = "#64748b"; // Warna teks default sedikit lebih gelap

    function createChart(id, type, labels, data, colors, isStacked = false) {
        const canvas = document.getElementById(id);
        if (!canvas) return; 
        
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: Array.isArray(data[0]) ? data : [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: Array.isArray(colors) ? '#fff' : colors,
                    borderWidth: Array.isArray(colors) ? 2 : 1, // Border lebih tebal untuk pie/doughnut
                    fill: type === 'line',
                    tension: 0.4 // Membuat line chart lebih melengkung (smooth)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: (type === 'pie' || type === 'doughnut' || isStacked),
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 15, font: { weight: '600' } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 31, 63, 0.9)', // Tooltip warna Navy
                        titleFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif", weight: 'bold' },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: (type === 'pie' || type === 'doughnut' || type === 'polarArea') ? {} : {
                    y: { 
                        beginAtZero: true, 
                        stacked: isStacked,
                        grid: { color: '#f1f5f9', borderDash: [5, 5] }, // Garis putus-putus
                        border: { display: false }
                    },
                    x: { 
                        stacked: isStacked,
                        grid: { display: false },
                        border: { display: false },
                        ticks: { font: { weight: '600' } }
                    }
                }
            }
        });
    }

    // --- Render Semua Grafik ---
    createChart('c1', 'pie', <?= json_encode($q1['labels']) ?>, <?= json_encode($q1['data']) ?>, ['#22c55e', '#f59e0b', '#ef4444']);
    createChart('c2', 'bar', <?= json_encode($q2['labels']) ?>, <?= json_encode($q2['data']) ?>, 'rgba(14, 165, 233, 0.8)');
    createChart('c3', 'bar', <?= json_encode($q3['labels']) ?>, <?= json_encode($q3['data']) ?>, 'rgba(239, 68, 68, 0.8)');
    createChart('c4', 'doughnut', <?= json_encode($q4['labels']) ?>, <?= json_encode($q4['data']) ?>, ['#8b5cf6', '#a855f7', '#d946ef', '#f43f5e', '#f97316']);
    createChart('c5', 'line', <?= json_encode($q5['labels']) ?>, <?= json_encode($q5['data']) ?>, '#10b981');

    createChart('c6', 'bar', <?= json_encode($q6['labels']) ?>, <?= json_encode($q6['data']) ?>, 'rgba(16, 185, 129, 0.8)');
    createChart('c7', 'pie', <?= json_encode($q7['labels']) ?>, <?= json_encode($q7['data']) ?>, ['#0ea5e9', '#22c55e', '#ef4444']);
    createChart('c8', 'line', <?= json_encode($q8['labels']) ?>, <?= json_encode($q8['data']) ?>, '#f59e0b');
    createChart('c9', 'doughnut', <?= json_encode($q9['labels']) ?>, <?= json_encode($q9['data']) ?>, ['#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e']);
    createChart('c10', 'bar', <?= json_encode($q10['labels']) ?>, <?= json_encode($q10['data']) ?>, 'rgba(245, 158, 11, 0.8)');

    createChart('c11', 'line', <?= json_encode($q11['labels']) ?>, <?= json_encode($q11['data']) ?>, '#0ea5e9');
    
    // Q12 Stacked
    const ds12 = [
        { label: 'Terpakai', data: <?= json_encode($q12_pakai) ?>, backgroundColor: '#ef4444', borderRadius: 4 },
        { label: 'Sisa di Lab', data: <?= json_encode($q12_sisa) ?>, backgroundColor: '#22c55e', borderRadius: 4 }
    ];
    createChart('c12', 'bar', <?= json_encode($q12_labels) ?>, ds12, null, true);
    
    createChart('c13', 'polarArea', <?= json_encode($q13['labels']) ?>, <?= json_encode($q13['data']) ?>, ['rgba(59, 130, 246, 0.7)', 'rgba(34, 197, 94, 0.7)', 'rgba(245, 158, 11, 0.7)', 'rgba(239, 68, 68, 0.7)']);
    createChart('c14', 'doughnut', <?= json_encode($q14['labels']) ?>, <?= json_encode($q14['data']) ?>, ['#22c55e', '#f59e0b', '#ef4444']);
    createChart('c15', 'bar', <?= json_encode($q15['labels']) ?>, <?= json_encode($q15['data']) ?>, 'rgba(100, 116, 139, 0.8)');
});
</script>

</body>
</html>