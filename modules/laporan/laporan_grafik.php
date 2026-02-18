<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

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

// --- PENGAMBILAN DATA (Logika SQL Tetap Sama) ---
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
    :root { --navy: #001f3f; --navy-light: #112240; --gold: #ffcc00; }
    body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }

    /* Tampilan di Layar (Monitor) */
    @media (min-width: 992px) {
        .main-content { margin-left: 260px !important; }
    }

    .chart-container { position: relative; height: 260px; width: 100%; padding: 10px; }
    canvas { display: block; max-width: 100%; }

    .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .card-header { font-weight: 700; text-transform: uppercase; font-size: 0.85rem; border-bottom: 1px solid #f0f0f0; }

    /* --- PENGATURAN KHUSUS CETAK (PRINT) --- */
    @media print {
        /* Sembunyikan elemen navigasi agar bersih */
        .sidebar, .btn-print, header, .header, nav, .mobile-nav, .btn-print-wrapper { 
            display: none !important; 
        }
        
        .main-content { 
            margin-left: 0 !important; 
            padding: 0 !important; 
            width: 100% !important;
        }

        body { background: white !important; padding-top: 0 !important; }
        
        /* Membuat grafik jadi 1 kolom (penuh ke samping) */
        .col-md-4 { 
            width: 100% !important; 
            flex: 0 0 100% !important; 
            max-width: 100% !important;
            float: none !important; 
            margin-bottom: 30px !important;
        }

        /* Mengatur agar container grafik lebih tinggi saat di print supaya proporsional */
        .chart-container { 
            height: 350px !important; 
        }

        .card { 
            box-shadow: none !important; 
            border: 1px solid #eee !important; 
            page-break-inside: avoid; /* Mencegah grafik terpotong antar halaman */
        }

        .container-fluid { 
            width: 100% !important; 
            max-width: none !important; 
            padding: 0 !important;
        }

        h5 { 
            margin-top: 20px !important; 
            page-break-after: avoid; 
        }
    }


/* Container Utama */
        .header-card { 
            background: linear-gradient(135deg, #1a2a6c 0%, #2a4858 100%); /* Warna Navy ke Light */
            color: white; 
            border-radius: 15px; 
            padding: 20px 40px; /* Padding atas-bawah 20px, kiri-kanan 40px agar memanjang */
            margin-bottom: 25px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border: none;
        }

        /* Tombol Cetak di Dalam Card */
        .btn-modern-print {
            background: #ffc107; /* Warna Kuning Warning agar kontras dengan Navy */
            color: #000;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }

        .btn-modern-print:hover {
            background: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
            color: #000;
        }

</style>

<style>
    /* Container Utama Section */
    .dashboard-section {
        margin-bottom: 50px;
    }

    /* Judul Section Modern */
    .premium-divider {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }
    .premium-divider .line {
        height: 2px;
        flex-grow: 1;
        background: linear-gradient(90deg, #e2e8f0, transparent);
    }
    .premium-divider .section-label {
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #1e293b;
        font-size: 0.9rem;
    }

    /* Vibrant Card Design */
    .vibrant-card {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border-top: 5px solid var(--accent-color); /* Warna unik di atas */
    }

    .vibrant-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
    }

    .vibrant-header {
        padding: 20px 20px 0 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--accent-color);
        color: white;
        box-shadow: 0 4px 10px var(--shadow-color);
    }

    .vibrant-title {
        font-weight: 700;
        color: #334155;
        font-size: 0.95rem;
        margin: 0;
    }

    .vibrant-body {
        padding: 15px 20px 20px;
        height: 260px;
    }

    /* Print Adjustment */
    @media print {
        .vibrant-card { border: 1px solid #ddd !important; break-inside: avoid; }
        .icon-circle { border: 1px solid #000; color: #000; background: none !important; }
    }
</style>


</head>
<body>

<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <div class="header">
            <?php include "../../includes/header.php"; ?>
        </div>

        <div class="container-fluid py-4" style="margin-top: 60px;">

            <div class="px-3 btn-print-wrapper no-print">
                <div class="header-card shadow-sm d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <i class="bi bi-printer-fill text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1">Dashboard Analitik Inventaris</h2>
                            <p class="mb-0 text-white-50">Ringkasan statistik stok, manajemen distribusi, dan laporan pemakaian unit</p>
                        </div>
                    </div>

                    <div>
                        <button onclick="window.print()" class="btn-modern-print">
                            <i class="bi bi-printer-fill me-2"></i> Cetak Grafik (PDF)
                        </button>
                    </div>
                </div>
            </div>

            <div class="section-divider px-3">
    <div class="dashboard-section px-3">
    <div class="premium-divider">
        <span class="section-label text-black"><i class="bi bi-box-seam me-2"></i> I. STOK & INVENTARIS</span>
        <div class="line"></div>
    </div>
    <div class="row">
        <?php 
        $charts_stok = [
            ['id' => 'c1', 'title' => 'Kondisi Bahan', 'icon' => 'bi-heart-pulse', 'color' => '#6366f1'], // Indigo
            ['id' => 'c2', 'title' => 'Top 10 Stok', 'icon' => 'bi-trophy', 'color' => '#f59e0b'],      // Amber
            ['id' => 'c3', 'title' => 'Hampir Habis', 'icon' => 'bi-bell-fill', 'color' => '#ef4444'], // Red
            ['id' => 'c4', 'title' => 'Sebaran Satuan', 'icon' => 'bi-tag-fill', 'color' => '#8b5cf6'], // Violet
            ['id' => 'c5', 'title' => 'Tren Masuk', 'icon' => 'bi-graph-up', 'color' => '#10b981'],    // Emerald
        ];
        foreach($charts_stok as $c): 
            $shadow = $c['color'] . '4D'; // Transparansi 30% untuk shadow
        ?>
        <div class="col-md-4 mb-4">
            <div class="card vibrant-card h-100" style="--accent-color: <?= $c['color'] ?>; --shadow-color: <?= $shadow ?>;">
                <div class="vibrant-header">
                    <div class="icon-circle">
                        <i class="bi <?= $c['icon'] ?>"></i>
                    </div>
                    <h6 class="vibrant-title"><?= $c['title'] ?></h6>
                </div>
                <div class="vibrant-body">
                    <canvas id="<?= $c['id'] ?>"></canvas>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="dashboard-section px-3">
    <div class="premium-divider">
        <span class="section-label text-black"><i class="bi bi-truck me-2"></i> II. DISTRIBUSI LOGISTIK</span>
        <div class="line"></div>
    </div>
    <div class="row">
        <?php 
        $charts_dist = [
            ['id' => 'c6', 'title' => 'Volume per Lab', 'icon' => 'bi-geo-alt-fill', 'color' => '#0ea5e9'], // Sky
            ['id' => 'c7', 'title' => 'Status Pengiriman', 'icon' => 'bi-check-circle', 'color' => '#22c55e'], // Green
            ['id' => 'c8', 'title' => 'Histori Mingguan', 'icon' => 'bi-clock-history', 'color' => '#f43f5e'], // Rose
            ['id' => 'c9', 'title' => 'Per Jurusan', 'icon' => 'bi-diagram-3-fill', 'color' => '#ec4899'], // Pink
            ['id' => 'c10', 'title' => 'Item Terpopuler', 'icon' => 'bi-lightning-charge', 'color' => '#fbbf24'], // Yellow
        ];
        foreach($charts_dist as $c): 
            $shadow = $c['color'] . '4D';
        ?>
        <div class="col-md-4 mb-4">
            <div class="card vibrant-card h-100" style="--accent-color: <?= $c['color'] ?>; --shadow-color: <?= $shadow ?>;">
                <div class="vibrant-header">
                    <div class="icon-circle">
                        <i class="bi <?= $c['icon'] ?>"></i>
                    </div>
                    <h6 class="vibrant-title"><?= $c['title'] ?></h6>
                </div>
                <div class="vibrant-body">
                    <canvas id="<?= $c['id'] ?>"></canvas>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="dashboard-section px-3">
    <div class="premium-divider">
        <span class="section-label text-black"><i class="bi bi-cpu me-2"></i> III. ANALITIK PEMAKAIAN</span>
        <div class="line"></div>
    </div>
    <div class="row">
        <?php 
        $charts_extra = [
            ['id' => 'c11', 'title' => 'Tren Bulanan', 'icon' => 'bi-activity', 'color' => '#6366f1'],
            ['id' => 'c12', 'title' => 'Efisiensi Pakai', 'icon' => 'bi-droplet-fill', 'color' => '#3b82f6'],
            ['id' => 'c13', 'title' => 'Lab Teraktif', 'icon' => 'bi-award-fill', 'color' => '#fb923c'],
            ['id' => 'c14', 'title' => 'Rasio Persetujuan', 'icon' => 'bi-patch-check', 'color' => '#10b981'],
            ['id' => 'c15', 'title' => 'Waktu Puncak', 'icon' => 'bi-hourglass-split', 'color' => '#64748b'], // Slate
        ];
        foreach($charts_extra as $c): 
            $shadow = $c['color'] . '4D';
        ?>
        <div class="col-md-4 mb-4">
            <div class="card vibrant-card h-100" style="--accent-color: <?= $c['color'] ?>; --shadow-color: <?= $shadow ?>;">
                <div class="vibrant-header">
                    <div class="icon-circle">
                        <i class="bi <?= $c['icon'] ?>"></i>
                    </div>
                    <h6 class="vibrant-title"><?= $c['title'] ?></h6>
                </div>
                <div class="vibrant-body">
                    <canvas id="<?= $c['id'] ?>"></canvas>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi General dipindahkan ke dalam listener agar aman
document.addEventListener("DOMContentLoaded", function() {
    function createChart(id, type, labels, data, colors, isStacked = false) {
        const canvas = document.getElementById(id);
        if (!canvas) return; // Mencegah error jika elemen tidak ada
        
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: Array.isArray(data[0]) ? data : [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: Array.isArray(colors) ? '#fff' : colors,
                    borderWidth: 1,
                    fill: type === 'line',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: (type === 'pie' || type === 'doughnut' || isStacked),
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 11 } }
                    }
                },
                scales: (type === 'pie' || type === 'doughnut' || type === 'polarArea') ? {} : {
                    y: { 
                        beginAtZero: true, 
                        stacked: isStacked,
                        grid: { color: '#f0f0f0' }
                    },
                    x: { 
                        stacked: isStacked,
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // --- Render Semua Grafik ---
    createChart('c1', 'pie', <?= json_encode($q1['labels']) ?>, <?= json_encode($q1['data']) ?>, ['#28a745', '#ffc107', '#dc3545']);
    createChart('c2', 'bar', <?= json_encode($q2['labels']) ?>, <?= json_encode($q2['data']) ?>, '#0d6efd');
    createChart('c3', 'bar', <?= json_encode($q3['labels']) ?>, <?= json_encode($q3['data']) ?>, '#e83e8c');
    createChart('c4', 'doughnut', <?= json_encode($q4['labels']) ?>, <?= json_encode($q4['data']) ?>, ['#6610f2', '#6f42c1', '#d63384', '#fd7e14']);
    createChart('c5', 'line', <?= json_encode($q5['labels']) ?>, <?= json_encode($q5['data']) ?>, '#20c997');

    createChart('c6', 'bar', <?= json_encode($q6['labels']) ?>, <?= json_encode($q6['data']) ?>, '#198754');
    createChart('c7', 'pie', <?= json_encode($q7['labels']) ?>, <?= json_encode($q7['data']) ?>, ['#0dcaf0', '#198754', '#dc3545']);
    createChart('c8', 'line', <?= json_encode($q8['labels']) ?>, <?= json_encode($q8['data']) ?>, '#ffc107');
    createChart('c9', 'doughnut', <?= json_encode($q9['labels']) ?>, <?= json_encode($q9['data']) ?>, ['#0d6efd', '#6610f2', '#6f42c1', '#d63384']);
    createChart('c10', 'bar', <?= json_encode($q10['labels']) ?>, <?= json_encode($q10['data']) ?>, '#fd7e14');

    createChart('c11', 'line', <?= json_encode($q11['labels']) ?>, <?= json_encode($q11['data']) ?>, '#0dcaf0');
    
    // Q12 Stacked
    const ds12 = [
        { label: 'Terpakai', data: <?= json_encode($q12_pakai) ?>, backgroundColor: '#dc3545' },
        { label: 'Sisa di Lab', data: <?= json_encode($q12_sisa) ?>, backgroundColor: '#198754' }
    ];
    createChart('c12', 'bar', <?= json_encode($q12_labels) ?>, ds12, null, true);
    
    createChart('c13', 'polarArea', <?= json_encode($q13['labels']) ?>, <?= json_encode($q13['data']) ?>, ['#0d6efd', '#198754', '#ffc107', '#dc3545']);
    createChart('c14', 'doughnut', <?= json_encode($q14['labels']) ?>, <?= json_encode($q14['data']) ?>, ['#198754', '#ffc107', '#dc3545']);
    createChart('c15', 'bar', <?= json_encode($q15['labels']) ?>, <?= json_encode($q15['data']) ?>, '#6f42c1');
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>