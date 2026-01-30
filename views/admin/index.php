<?php
include "../../config/database.php";
include "../../config/auth.php";

checkAccess('admin'); // Jalankan satpam khusus admin


// ... sisa kode ...
// Ambil Nama Admin dari session (pastikan session 'nama' sudah diset saat login)
$nama_admin = $_SESSION['nama'] ?? 'Administrator';

// 1. Ambil statistik singkat
$total_bahan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_praktek"))['total'] ?? 0;
$total_lab = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lab"))['total'] ?? 0;
$stok_menipis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_praktek WHERE stok < 10"))['total'] ?? 0;
$total_distribusi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab"))['total'] ?? 0;

// 2. Logika Grafik Tren Distribusi (7 Hari Terakhir)
$labels = [];
$counts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $display_date = date('d M', strtotime($date));
    $sql_graph = "SELECT SUM(jumlah) as total FROM distribusi_lab WHERE DATE(tanggal_distribusi) = '$date'";
    $res_graph = mysqli_query($conn, $sql_graph);
    $data_graph = mysqli_fetch_assoc($res_graph);
    $labels[] = $display_date;
    $counts[] = $data_graph['total'] ?? 0;
}

// 3. Data Status Permintaan untuk Pie Chart
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
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root { --navy: #0a192f; --gold: #ffcc00; --soft-blue: #e6f0ff; }
        
        .main-content { background-color: #f8f9fa; min-height: 100vh; }
        
        /* Banner Styling */
        .welcome-banner {
            background: linear-gradient(135deg, var(--navy) 0%, #112240 100%);
            color: white; border-radius: 20px; padding: 35px; position: relative; overflow: hidden;
        }

        /* Avatar Styling sesuai permintaan */
        .admin-avatar-wrapper {
            position: relative;
            display: inline-block;
            transition: 0.3s;
        }
        .admin-avatar-wrapper:hover {
            transform: scale(1.05);
        }
        .admin-avatar-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.2);
        }

        /* Chart & Card Styling */
        .stat-card { background: white; border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
        .glass-card { background: white; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none; }
        .chart-container { position: relative; height: 300px; width: 100%; }
        .chart-container-sm { position: relative; height: 220px; width: 100%; }
    </style>
</head>
<body>
<div class="d-flex" style="margin-top: 60px;">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../includes/header.php"; ?>
        
        <main class="p-4 mt-3">
            <div class="welcome-banner shadow-sm mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-2">Selamat Datang, <span style="color: var(--gold);"><?= $nama_admin; ?>!</span></h2>
                        <p class="opacity-75 mb-0">Anda masuk sebagai <strong>Administrator System</strong>. Pantau pergerakan stok laboratorium secara real-time.</p>
                    </div>
                    <div class="col-md-4 text-center text-md-end d-none d-md-block">
                        <div class="admin-avatar-wrapper">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_admin); ?>&background=ffcc00&color=0a192f&size=128&bold=true" 
                                 class="rounded-circle admin-avatar-img shadow-lg" alt="Admin Avatar">
                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success p-2 border border-3 border-dark" style="margin-right: 10px; margin-bottom: 5px;">
                                <span class="visually-hidden">Active</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3 bg-light text-primary p-3 rounded-3"><i class="bi bi-box-seam fs-4"></i></div>
                            <div><small class="text-muted d-block small fw-bold">TOTAL BAHAN</small><h4 class="fw-bold mb-0"><?= $total_bahan; ?></h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3 bg-light text-warning p-3 rounded-3"><i class="bi bi-building fs-4"></i></div>
                            <div><small class="text-muted d-block small fw-bold">TOTAL LAB</small><h4 class="fw-bold mb-0"><?= $total_lab; ?></h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 border-start border-4 border-danger">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3 text-danger p-3 rounded-3" style="background: #fff5f5;"><i class="bi bi-exclamation-triangle fs-4"></i></div>
                            <div><small class="text-muted d-block small fw-bold text-danger">STOK TIPIS</small><h4 class="fw-bold mb-0 text-danger"><?= $stok_menipis; ?></h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3 text-success p-3 rounded-3" style="background: #f0fff4;"><i class="bi bi-truck fs-4"></i></div>
                            <div><small class="text-muted d-block small fw-bold">DISTRIBUSI</small><h4 class="fw-bold mb-0"><?= $total_distribusi; ?></h4></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0" style="color: var(--navy);">Tren Distribusi (7 Hari Terakhir)</h6>
                            <span class="badge rounded-pill bg-primary-subtle text-primary px-3">Live Data</span>
                        </div>
                        <div class="chart-container">
                            <canvas id="distribusiChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <h6 class="fw-bold mb-4" style="color: var(--navy);">Status Request Lab</h6>
                        <div class="chart-container-sm">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="mt-4">
                            <a href="../../modules/distribusi/index.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center py-2">
                                <div class="icon-box me-3 bg-light p-2 rounded text-success"><i class="bi bi-truck"></i></div>
                                <div><h6 class="mb-0 fw-bold small">Kelola Distribusi</h6><small class="text-muted small">Update pengiriman</small></div>
                            </a>
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
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    };

    // 1. Chart Tren
    new Chart(document.getElementById('distribusiChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                data: <?= json_encode($counts); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.08)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Chart Status
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels_status); ?>,
            datasets: [{
                data: <?= json_encode($counts_status); ?>,
                backgroundColor: ['#ffc107', '#198754', '#dc3545'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                legend: { display: true, position: 'bottom', labels: { boxWidth: 10, padding: 20 } }
            }
        }
    });
</script>
</body>
</html>