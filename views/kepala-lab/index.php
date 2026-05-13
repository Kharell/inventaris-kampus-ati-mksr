<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Proteksi akses
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'kepala_lab' && $_SESSION['role'] !== 'kepala-lab')) {
    header("Location: ../../index.php"); exit();
}

include "../../config/database.php";
include "../../config/auth.php";

checkAccess('kepala_lab');

$id_user = $_SESSION['id_user'];

// 1. Ambil data User, Lab, dan Jurusan
$query_user = mysqli_query($conn, "SELECT k.*, l.nama_lab, j.nama_jurusan 
                                   FROM kepala_lab k 
                                   LEFT JOIN lab l ON k.id_lab = l.id_lab 
                                   LEFT JOIN jurusan j ON l.id_jurusan = j.id_jurusan 
                                   WHERE k.id_kepala = '$id_user'");
$data = mysqli_fetch_assoc($query_user);

$nama_tampil = $data['nama'] ?? $data['nama_kepala'] ?? 'User';
$id_lab_user = $data['id_lab'] ?? 0;

// 2. Statistik
$total_permintaan = 0;
$check_p = mysqli_query($conn, "SHOW TABLES LIKE 'permintaan_barang'");
if(mysqli_num_rows($check_p) > 0) {
    $total_permintaan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM permintaan_barang WHERE id_kepala = '$id_user'"))['total'] ?? 0;
}

$total_inventaris = 0;
$check_b = mysqli_query($conn, "SHOW TABLES LIKE 'bahan_praktek'");
if(mysqli_num_rows($check_b) > 0) {
    $total_inventaris = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) as total FROM bahan_praktek WHERE id_lab = '$id_lab_user'"))['total'] ?? 0;
}

// 3. Data Grafik
$label_status = []; $count_status = [];
$check_d = mysqli_query($conn, "SHOW TABLES LIKE 'distribusi_lab'");
if(mysqli_num_rows($check_d) > 0) {
    $query_status = mysqli_query($conn, "SELECT status, COUNT(*) as jumlah FROM distribusi_lab WHERE id_lab = '$id_lab_user' GROUP BY status");
    while($row = mysqli_fetch_assoc($query_status)) {
        $label_status[] = ucfirst($row['status']);
        $count_status[] = $row['jumlah'];
    }
}
if(empty($label_status)) { $label_status = ['Belum Ada Data']; $count_status = [1]; }

$labels_bahan = []; $stok_bahan = [];
if(mysqli_num_rows($check_b) > 0) {
    $query_stok = mysqli_query($conn, "SELECT nama_bahan, stok FROM bahan_praktek WHERE id_lab = '$id_lab_user' ORDER BY stok DESC LIMIT 5");
    while($row = mysqli_fetch_assoc($query_stok)) {
        $labels_bahan[] = $row['nama_bahan'];
        $stok_bahan[] = $row['stok'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Lab | SI-INVENTARIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --navy-deep: #00152b; 
            --navy-light: #001f3f; 
            --gold: #ffcc00; 
            --gold-light: #fff3cd;
            --bg-color: #f4f7fb;
        }
        body { background: var(--bg-color); font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; color: #2d3436; }
        .main-content { transition: all 0.3s; min-height: 100vh; padding-top: 70px; }
        @media (min-width: 992px) { .main-content { margin-left: 260px; } }
        
        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(135deg, var(--navy-deep) 0%, var(--navy-light) 100%);
            border-radius: 24px; padding: 40px; color: white; position: relative; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,31,63,0.15);
        }
        .hero-banner::after {
            content: ''; position: absolute; top: -50%; right: -10%; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,204,0,0.15) 0%, transparent 70%); border-radius: 50%;
        }

        /* Glass & Stat Cards */
        .glass-card { 
            background: white; border-radius: 24px; padding: 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); height: 100%; 
        }
        
        .stat-card {
            background: white; border-radius: 20px; padding: 25px; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px;
            border-left: 5px solid transparent;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.06); }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.warning { border-left-color: var(--gold); }
        
        .stat-icon {
            width: 65px; height: 65px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0;
        }
        .stat-icon.primary { background: #e7f1ff; color: #0d6efd; }
        .stat-icon.warning { background: var(--gold-light); color: #b28e00; }
        
        .stat-number { font-size: 1.8rem; font-weight: 800; color: var(--navy-deep); line-height: 1; margin-bottom: 5px; }
        .stat-label { font-size: 0.85rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }

        /* Quick Action Cards */
        .action-card {
            display: block; text-decoration: none; color: inherit; background: white; border-radius: 20px;
            padding: 20px; text-align: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #edf2f7; box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }
        .action-card:hover {
            transform: translateY(-8px); border-color: var(--gold);
            box-shadow: 0 15px 30px rgba(255,204,0,0.15);
        }
        .action-icon {
            width: 60px; height: 60px; margin: 0 auto 15px; background: #f8f9fa; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--navy-light); transition: 0.3s;
        }
        .action-card:hover .action-icon { background: var(--navy-light); color: var(--gold); }
        .action-title { font-weight: 700; font-size: 0.95rem; margin-bottom: 5px; color: var(--navy-deep); }
        .action-desc { font-size: 0.75rem; color: #8795a1; line-height: 1.4; margin: 0; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100 p-3 p-lg-4">
        <?php include "../../includes/header.php"; ?>

        <!-- HERO BANNER -->
        <div class="hero-banner mb-4" style="margin-top: 80px;">
            <div class="row align-items-center">
                <div class="col-md-8 position-relative" style="z-index: 2;">
                    <span class="badge bg-white text-dark mb-3 px-3 py-2 rounded-pill shadow-sm" style="font-weight: 700;">Kepala Laboratorium</span>
                    <h2 class="fw-bold mb-2" style="font-size: 2.2rem; letter-spacing: -0.5px;">Selamat Datang, <?= explode(' ', $nama_tampil)[0]; ?>!</h2>
                    <p class="mb-4" style="color: #e2e8f0; font-size: 1.05rem;">Anda saat ini mengelola <strong><?= $data['nama_lab'] ?? 'Lab Belum Diatur'; ?></strong>. Pantau terus pergerakan stok dan sampaikan permintaan barang jika diperlukan.</p>
                    
                    <div class="d-flex gap-3">
                        <!-- <a href="permintaan.php" class="btn btn-warning fw-bold px-4 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-plus-circle-fill me-2"></i> Buat Permintaan
                        </a> -->
                        <a href="../../assets/docs/panduan_kepala_lab.pdf" class="btn btn-outline-light fw-bold px-4 py-2 rounded-pill" download>
                            <i class="bi bi-file-earmark-pdf me-2"></i> Unduh Panduan
                        </a>
                    </div>
                </div>
                <div class="col-md-4 text-end d-none d-md-block position-relative" style="z-index: 2;">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_tampil); ?>&background=ffffff&color=001f3f&size=120&bold=true" 
                         class="rounded-circle border border-4 border-warning shadow-lg" alt="Avatar" style="transform: rotate(-5deg);">
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- LEFT COLUMN -->
            <div class="col-xl-7">
                
                <!-- Statistik Row -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <div class="stat-card primary">
                            <div class="stat-icon primary"><i class="bi bi-box-seam-fill"></i></div>
                            <div>
                                <h3 class="stat-number"><?= number_format($total_inventaris) ?></h3>
                                <p class="stat-label">Total Stok Unit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="stat-card warning">
                            <div class="stat-icon warning"><i class="bi bi-cart-check-fill"></i></div>
                            <div>
                                <h3 class="stat-number"><?= $total_permintaan ?></h3>
                                <p class="stat-label">Total Permintaan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Akses Cepat (Pengganti Accordion Panduan) -->
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-navy-deep">Akses Cepat</h5>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4 col-sm-6">
                            <a href="lab/stok.php" class="action-card">
                                <div class="action-icon"><i class="bi bi-layers-fill"></i></div>
                                <h6 class="action-title">Cek Stok Lab</h6>
                                <p class="action-desc">Pantau ketersediaan barang/bahan secara real-time.</p>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <a href="lab/pemakaian.php" class="action-card">
                                <div class="action-icon"><i class="bi bi-clipboard2-minus-fill"></i></div>
                                <h6 class="action-title">Lapor Pemakaian</h6>
                                <p class="action-desc">Catat pengurangan stok saat bahan digunakan praktikum.</p>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <a href="lab/laporan.php" class="action-card">
                                <div class="action-icon"><i class="bi bi-printer-fill"></i></div>
                                <h6 class="action-title">Cetak Dokumen</h6>
                                <p class="action-desc">Unduh rekapitulasi data dalam format PDF/Excel.</p>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: CHARTS -->
            <div class="col-xl-5">
                <div class="glass-card d-flex flex-column h-100">
                    <h5 class="fw-bold mb-4 text-navy-deep"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Analitik Laboratorium</h5>
                    
                    <div class="flex-grow-1 mb-4">
                        <h6 class="small fw-bold text-muted text-center mb-3 text-uppercase">Status Permintaan & Distribusi</h6>
                        <div style="height: 220px; position: relative;">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                    
                    <hr class="opacity-10 my-4">

                    <div>
                        <h6 class="small fw-bold text-muted text-center mb-3 text-uppercase">5 Stok Bahan Terbanyak</h6>
                        <div style="height: 180px; position: relative;">
                            <canvas id="chartBahan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Konfigurasi Font Default Chart
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#6c757d';

    // Chart Donut (Status)
    const ctxStatus = document.getElementById('chartStatus').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($label_status) ?>,
            datasets: [{
                data: <?= json_encode($count_status) ?>,
                backgroundColor: ['#ffcc00', '#198754', '#dc3545', '#0d6efd', '#6c757d'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            cutout: '70%',
            plugins: { 
                legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 10, padding: 15 } } 
            } 
        }
    });

    // Chart Bar (Top Stok)
    const ctxBahan = document.getElementById('chartBahan').getContext('2d');
    new Chart(ctxBahan, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_bahan) ?>,
            datasets: [{
                data: <?= json_encode($stok_bahan) ?>,
                backgroundColor: '#001f3f',
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { padding: 10, cornerRadius: 8 } },
            scales: { 
                x: { display: false }, 
                y: { grid: { display: false }, ticks: { font: { weight: 'bold' }, color: '#00152b' } } 
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>