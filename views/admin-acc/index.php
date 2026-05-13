<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include "../../config/database.php";

// Proteksi akses khusus Admin ACC (Operasional)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin-acc') {
    header("Location: ../../login.php");
    exit();
}

$nama_admin = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? 'Admin Operasional';

// 1. Ambil statistik Global
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin ACC | Lab Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --navy: #0a192f; 
            --navy-light: #112240;
            --gold: #ffcc00; 
            --gold-hover: #e6b800;
        }
        
        body { 
            background-color: #f4f7fe; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        
        .main-content { min-height: 100vh; padding-bottom: 50px; transition: 0.3s ease; }
        @media (min-width: 992px) { .main-content { margin-left: 260px; } }
        
        /* Banner Style */
        .welcome-banner {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: white; 
            border-radius: 24px; 
            padding: 40px; 
            position: relative;
            box-shadow: 0 10px 30px rgba(10, 25, 47, 0.15);
            overflow: hidden;
        }
        .welcome-banner::after {
            content: ''; position: absolute; right: -50px; top: -100px; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,204,0,0.1) 0%, transparent 70%); border-radius: 50%;
        }

        .admin-avatar-img {
            width: 110px; height: 110px; object-fit: cover;
            border: 4px solid rgb(245, 244, 244);
        }

        .badge-role {
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--gold);
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            backdrop-filter: blur(5px);
        }

        /* Stat Cards */
        .stat-card { 
            background: white; border-radius: 18px; border: none; 
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }
        
        .glass-card { 
            background: white; border-radius: 20px; border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        
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
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-md-8">
                        <span class="badge-role mb-3 d-inline-block"><i class="bi bi-shield-check me-2"></i>ADMIN OPERASIONAL (ACC)</span>
                        <h1 class="fw-bold mb-2" style="letter-spacing: -0.5px;">Halo, <span style="color: var(--gold);"><?= htmlspecialchars($nama_admin); ?>!</span></h1>
                        <p class="opacity-75 mb-0 fs-6">Selamat datang di Panel Operasional. Anda memiliki kendali penuh untuk menerima (ACC) permintaan bahan dari laboratorium dan menambahkan data material baru ke dalam gudang.</p>
                    </div>
                    <div class="col-md-4 text-center text-md-end d-none d-md-block">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_admin); ?>&background=ffffff&color=0a192f&size=128&bold=true" 
                             class="rounded-circle admin-avatar-img shadow-lg" alt="Admin">
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small fw-bold text-uppercase">Total Material</p>
                                <h3 class="fw-bold mb-0 text-navy"><?= $total_bahan; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4"><i class="bi bi-box-seam text-primary fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small fw-bold text-uppercase">Total Lab</p>
                                <h3 class="fw-bold mb-0 text-navy"><?= $total_lab; ?></h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-4"><i class="bi bi-building text-info fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-4 shadow-sm border-bottom border-danger border-4">
                        <div class="d-flex justify-content-between align-items-center">
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
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small fw-bold text-uppercase">Distribusi</p>
                                <h3 class="fw-bold mb-0 text-navy"><?= $total_distribusi; ?></h3>
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
                            <h5 class="fw-bold mb-0 text-navy">Tren Distribusi Barang</h5>
                            <span class="badge bg-light text-dark px-3 py-2 border shadow-sm">7 Hari Terakhir</span>
                        </div>
                        <div class="chart-container">
                            <canvas id="distribusiChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <h5 class="fw-bold mb-4 text-navy">Status Permintaan</h5>
                        <div style="height: 250px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="mt-4 pt-3 border-top text-center">
                             <div class="d-grid">
                                <a href="../../modules/distribusi/index.php" class="btn btn-light fw-bold text-primary py-2 border shadow-sm" style="border-radius: 12px;">
                                    Buka Panel ACC <i class="bi bi-arrow-right ms-1"></i>
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
    // Atur font default Chart.js agar senada dengan Plus Jakarta Sans
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    // Line Chart: Tren Distribusi
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

    // Doughnut Chart: Status Request
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels_status); ?>,
            datasets: [{
                data: <?= json_encode($counts_status); ?>,
                backgroundColor: ['#ffc107', '#198754', '#dc3545', '#0dcaf0'],
                hoverOffset: 10,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: 'bold' } } }
            },
            cutout: '70%'
        }
    });
</script>
</body>
</html>