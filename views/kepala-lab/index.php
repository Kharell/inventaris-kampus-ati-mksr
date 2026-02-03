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
                                   JOIN lab l ON k.id_lab = l.id_lab 
                                   JOIN jurusan j ON l.id_jurusan = j.id_jurusan 
                                   WHERE k.id_kepala = '$id_user'");
$data = mysqli_fetch_assoc($query_user);
$id_lab_user = $data['id_lab'] ?? 0;

// 2. Statistik (Gunakan nama tabel 'permintaan_barang' dan 'bahan_praktek' sesuai kode Anda)
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

// 3. Data Grafik Status Distribusi (Default kosong jika tabel tidak ada)
$label_status = []; $count_status = [];
$check_d = mysqli_query($conn, "SHOW TABLES LIKE 'distribusi_lab'");
if(mysqli_num_rows($check_d) > 0) {
    $query_status = mysqli_query($conn, "SELECT status, COUNT(*) as jumlah FROM distribusi_lab WHERE id_lab = '$id_lab_user' GROUP BY status");
    while($row = mysqli_fetch_assoc($query_status)) {
        $label_status[] = ucfirst($row['status']);
        $count_status[] = $row['jumlah'];
    }
}
// Fallback jika data kosong agar chart tidak error
if(empty($label_status)) { $label_status = ['Belum Ada Data']; $count_status = [1]; }

// 4. Data Grafik Stok Terbanyak
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --navy-deep: #00152b; --navy-light: #001f3f; --gold: #ffcc00; }
        body { background: #f0f4f8; font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }

        .main-content { transition: all 0.3s; min-height: 100vh; }
        
        @media (min-width: 992px) {
            .main-content { margin-left: 260px; padding-top: 70px; }
        }

        .hero-banner {
            background: linear-gradient(135deg, var(--navy-deep) 0%, var(--navy-light) 100%);
            border-radius: 20px; padding: 30px 40px; color: white;
            position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,31,63,0.1);
        }

        .badge-role {
            background: rgba(255, 204, 0, 0.2); color: var(--gold);
            padding: 5px 12px; border-radius: 8px; font-size: 0.75rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        }

        .avatar-img { width: 90px; height: 90px; border: 4px solid rgba(255,255,255,0.2); object-fit: cover; }

        .glass-card {
            background: white; border-radius: 20px; padding: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: none; height: 100%;
        }

        .stat-circle {
            width: 70px; height: 70px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #f8f9fa; border: 4px solid var(--gold); margin: 0 auto 10px;
        }

        .stat-number { font-size: 1.5rem; font-weight: 800; color: var(--navy-deep); }
        .chart-box { height: 250px; width: 100%; }

    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100 p-3 p-lg-4">
        <?php include "../../includes/header.php"; ?>

    <div class="hero-banner mb-4 p-4 rounded-4 shadow-sm" style="margin-top: 70px; background: linear-gradient(135deg, #001f3f 0%, #112240 100%); color: white;">
        <div class="row align-items-center">
            <div class="col-md-9">
                <span class="badge bg-warning text-dark mb-2 px-3">Kepala Laboratorium</span>
                <h2 class="fw-bold mb-1 text-white"><?= $data['nama_lab'] ?? 'Lab Tidak Terdaftar'; ?></h2>
                <p class="opacity-75 small mb-0">
                    Pengelolaan data jurusan <strong class="text-warning"><?= $data['nama_jurusan'] ?? '-'; ?></strong> terpusat dan efisien.
                </p>
            </div>

            <div class="col-md-3 text-end d-none d-md-block">
                <div class="position-relative d-inline-block">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($data['nama'] ?? 'User'); ?>&background=ffcc00&color=001f3f&size=100&bold=true" 
                        class="rounded-circle shadow-lg border border-3 border-white" 
                        alt="Avatar" 
                        style="width: 80px; height: 80px; object-fit: cover;">
                    
                    <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-2 border-white rounded-circle" 
                        style="margin-right: 5px; margin-bottom: 5px;">
                        <span class="visually-hidden">Active</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0"><i class="bi bi-compass-fill me-2 text-warning"></i>Distribusi Barang</h5>
                    </div>
                    <div class="chart-box">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="glass-card text-center py-3">
                            <div class="stat-circle"><span class="stat-number"><?= $total_permintaan ?></span></div>
                            <p class="text-muted fw-bold small mb-0">PERMINTAAN</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-card text-center py-3">
                            <div class="stat-circle" style="border-color: var(--navy-light);">
                                <span class="stat-number"><?= number_format($total_inventaris) ?></span>
                            </div>
                            <p class="text-muted fw-bold small mb-0">TOTAL UNIT</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="glass-card">
                            <h6 class="fw-bold mb-3 small text-uppercase">Stok Terbanyak (Top 5)</h6>
                            <div style="height: 180px;">
                                <canvas id="chartBahan"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Grafik Distribusi (Doughnut)
    const ctxStatus = document.getElementById('chartStatus').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($label_status) ?>,
            datasets: [{
                data: <?= json_encode($count_status) ?>,
                backgroundColor: ['#ffcc00', '#00c853', '#ff3d00', '#007bff'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });

    // 2. Grafik Stok (Horizontal Bar)
    const ctxBahan = document.getElementById('chartBahan').getContext('2d');
    new Chart(ctxBahan, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_bahan) ?>,
            datasets: [{
                data: <?= json_encode($stok_bahan) ?>,
                backgroundColor: '#001f3f',
                borderRadius: 5,
                barThickness: 15
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>