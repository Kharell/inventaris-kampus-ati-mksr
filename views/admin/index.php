<?php
include "../../config/database.php";
include "../../config/auth.php";

checkAccess('admin'); 

$nama_admin = $_SESSION['nama'] ?? 'Administrator';

// 1. Ambil statistik
$total_bahan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_praktek"))['total'] ?? 0;
$total_lab = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lab"))['total'] ?? 0;
$stok_menipis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_praktek WHERE stok < 10"))['total'] ?? 0;
$total_distribusi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab"))['total'] ?? 0;

// 2. Logika Grafik Tren (7 Hari Terakhir)
$labels = []; $counts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $display_date = date('d M', strtotime($date));
    $sql_graph = "SELECT SUM(jumlah) as total FROM distribusi_lab WHERE DATE(tanggal_distribusi) = '$date'";
    $res_graph = mysqli_query($conn, $sql_graph);
    $data_graph = mysqli_fetch_assoc($res_graph);
    $labels[] = $display_date;
    $counts[] = $data_graph['total'] ?? 0;
}

// 3. Data Status Permintaan
$labels_status = []; $counts_status = [];
$res_status = mysqli_query($conn, "SELECT status, COUNT(*) as jml FROM permintaan_barang GROUP BY status");
while($row = mysqli_fetch_assoc($res_status)) {
    $labels_status[] = ucfirst($row['status']);
    $counts_status[] = $row['jml'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Lab Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --navy: #0a192f; --gold: #ffcc00; --accent: #64ffda; }
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; }
        
        .main-content { min-height: 100vh; padding-bottom: 50px; }
        
        /* Banner & Profile */
        .welcome-banner {
            background: linear-gradient(135deg, var(--navy) 0%, #112240 100%);
            color: white; border-radius: 24px; padding: 40px; position: relative;
            box-shadow: 0 10px 30px rgba(10, 25, 47, 0.15);
        }

        .admin-avatar-img {
            width: 110px; height: 110px; object-fit: cover;
            border: 4px solid rgba(255, 204, 0, 0.3);
        }

        /* Cards */
        .stat-card { 
            background: white; border-radius: 18px; border: none; 
            transition: all 0.3s ease; cursor: default;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }
        
        .glass-card { 
            background: white; border-radius: 20px; border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .btn-download {
            background-color: var(--gold); color: var(--navy);
            border: none; font-weight: 600; border-radius: 10px;
            padding: 10px 20px; transition: 0.3s;
        }
        .btn-download:hover { background-color: #e6b800; transform: scale(1.02); }

        .quick-link {
            text-decoration: none; color: inherit;
            background: white; padding: 15px; border-radius: 15px;
            display: flex; align-items: center; transition: 0.2s;
            border: 1px solid #edf2f7;
        }
        .quick-link:hover { background: var(--soft-blue); border-color: #d1d9e6; }
        
        .chart-container { position: relative; height: 320px; width: 100%; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100 px-4"> 
        <?php include "../../includes/header.php"; ?>
        
         <main class="p-3 p-md-3" style="margin-top: 80px;">
            <div class="welcome-banner mb-4">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h1 class="fw-bold mb-2">Halo, <span style="color: var(--gold);"><?= $nama_admin; ?>!</span></h1>
                        <p class="opacity-75 mb-4">Pantau aktivitas inventaris, kelola permintaan bahan, dan unduh laporan terbaru dari dashboard Anda.</p>
                        
                        <div class="d-flex gap-2">
                            <a href="../../assets/docs/panduan_admin.pdf" class="btn btn-download shadow-sm" download>
                                <i class="bi bi-file-earmark-pdf-fill me-2"></i>Comming Zoon panduanApp
                            </a>
                         
                        </div>
                    </div>
                    <div class="col-md-5 text-center text-md-end d-none d-md-block">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_admin); ?>&background=ffcc00&color=0a192f&size=128&bold=true" 
                             class="rounded-circle admin-avatar-img shadow-lg mb-2" alt="Admin">
                        <div class="mt-2">
                            <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> Online</span>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-4 shadow-sm">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small fw-bold text-uppercase">Total Bahan</p>
                                <h3 class="fw-bold mb-0"><?= $total_bahan; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4"><i class="bi bi-box-seam text-primary fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 shadow-sm">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small fw-bold text-uppercase">Laboratorium</p>
                                <h3 class="fw-bold mb-0"><?= $total_lab; ?></h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-4"><i class="bi bi-building text-info fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 shadow-sm border-bottom border-danger border-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-danger mb-1 small fw-bold text-uppercase">Stok Tipis</p>
                                <h3 class="fw-bold mb-0 text-danger"><?= $stok_menipis; ?></h3>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded-4"><i class="bi bi-exclamation-triangle text-danger fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 shadow-sm">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 small fw-bold text-uppercase">Distribusi</p>
                                <h3 class="fw-bold mb-0"><?= $total_distribusi; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-4"><i class="bi bi-truck text-success fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Tren Distribusi Barang</h5>
                            <select class="form-select form-select-sm w-auto">
                                <option>7 Hari Terakhir</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="distribusiChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <h5 class="fw-bold mb-4">Status Request</h5>
                        <div style="height: 250px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                             <div class="d-grid">
                                <a href="../../modules/distribusi/index.php" class="btn btn-light fw-bold text-primary">
                                    Lihat Detail Request <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Konfigurasi Chart Tren (Line)
    new Chart(document.getElementById('distribusiChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                label: 'Jumlah Distribusi',
                data: <?= json_encode($counts); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Konfigurasi Chart Status (Doughnut)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels_status); ?>,
            datasets: [{
                data: <?= json_encode($counts_status); ?>,
                backgroundColor: ['#ffc107', '#198754', '#dc3545'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });
</script>
</body>
</html>