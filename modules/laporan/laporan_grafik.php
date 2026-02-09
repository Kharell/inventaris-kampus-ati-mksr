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

    /* Style Tombol Modern */
    .btn-modern-print {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #f8fafc;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.3);
    }
    .btn-modern-print:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.4);
        color: #fff;
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
            <div class="d-flex justify-content-between align-items-center mb-4 px-3 btn-print-wrapper">
                <div>
                    <h2 class="fw-bold text-dark mb-0">Dashboard Analitik</h2>
                    <p class="text-muted mb-0">Laporan Inventaris Real-time</p>
                </div>
                <button onclick="window.print()" class="btn-modern-print">
                    <i class="bi bi-printer-fill me-2"></i> Cetak Grafik (PDF)
                </button>
            </div>

            <h5 class="mb-3 px-3 text-black fw-bold">I. STOK & INVENTARIS</h5>
            <div class="row px-2">
                <?php 
                $charts_stok = [
                    ['id' => 'c1', 'title' => 'Kondisi Bahan'],
                    ['id' => 'c2', 'title' => 'Top 10 Stok Terbanyak'],
                    ['id' => 'c3', 'title' => 'Bahan Hampir Habis'],
                    ['id' => 'c4', 'title' => 'Sebaran Satuan'],
                    ['id' => 'c5', 'title' => 'Tren Masuk Barang'],
                ];
                foreach($charts_stok as $c): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white"><?= $c['title'] ?></div>
                        <div class="card-body chart-container"><canvas id="<?= $c['id'] ?>"></canvas></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <br>

            <h5 class="mb-3 mt-4 px-3 text-black fw-bold">II. DISTRIBUSI (GUDANG KE LAB)</h5>
            <div class="row px-2">
                <?php 
                $charts_dist = [
                    ['id' => 'c6', 'title' => 'Volume Distribusi per Lab'],
                    ['id' => 'c7', 'title' => 'Status Pengiriman'],
                    ['id' => 'c8', 'title' => 'Histori Distribusi Mingguan'],
                    ['id' => 'c9', 'title' => 'Distribusi per Jurusan'],
                    ['id' => 'c10', 'title' => 'Item Terpopuler'],
                ];
                foreach($charts_dist as $c): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white"><?= $c['title'] ?></div>
                        <div class="card-body chart-container"><canvas id="<?= $c['id'] ?>"></canvas></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <br>

            <h5 class="mb-3 mt-4 px-3 text-black fw-bold">III. PEMAKAIAN & PERENCANAAN</h5>
            <div class="row px-2">
                <?php 
                $charts_extra = [
                    ['id' => 'c11', 'title' => 'Tren Pemakaian Bulanan'],
                    ['id' => 'c12', 'title' => 'Efisiensi (Pakai vs Sisa)'],
                    ['id' => 'c13', 'title' => 'Lab Paling Aktif Melapor'],
                    ['id' => 'c14', 'title' => 'Rasio Persetujuan Permintaan'],
                    ['id' => 'c15', 'title' => 'Waktu Puncak Permintaan'],
                ];
                foreach($charts_extra as $c): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white"><?= $c['title'] ?></div>
                        <div class="card-body chart-container"><canvas id="<?= $c['id'] ?>"></canvas></div>
                    </div>
                </div>
                <?php endforeach; ?>
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