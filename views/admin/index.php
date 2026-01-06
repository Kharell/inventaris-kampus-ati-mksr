<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// 1. Ambil statistik singkat (Gunakan variabel yang konsisten)
$total_bahan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang"))['total'] ?? 0;
$total_lab = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lab"))['total'] ?? 0;
$stok_menipis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE stok < 5"))['total'] ?? 0;
$total_distribusi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab"))['total'] ?? 0;

// 2. Logika Grafik (7 Hari Terakhir)
$labels = [];
$counts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $display_date = date('d M', strtotime($date));
    
    $sql_graph = "SELECT COUNT(*) as total FROM distribusi_lab WHERE DATE(tanggal_distribusi) = '$date'";
    $res_graph = mysqli_query($conn, $sql_graph);
    $data_graph = mysqli_fetch_assoc($res_graph);
    
    $labels[] = $display_date;
    $counts[] = $data_graph['total'] ?? 0;
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
        /* CSS Tambahan agar grafik dan card rapi */
        :root { --navy: #0a192f; --gold: #ffcc00; }
        .stat-card { background: white; border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .glass-card { background: white; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none; }
        /* Memastikan area konten tidak tertutup sidebar */
        .content-area { min-height: 100vh; }
        /* Styling Banner Selamat Datang */
.welcome-banner {
    background: linear-gradient(135deg, var(--dark-blue) 0%, var(--primary-blue) 100%);
    color: white;
    border-radius: 20px;
    padding: 40px;
    position: relative;
    overflow: hidden;
    border: none;
}

/* Tambahan dekorasi agar banner tidak flat */
.welcome-banner::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}
/* Membuat ikon memiliki efek bayangan dan berpijar */
.hero-icon-wrapper {
    position: relative;
    padding-right: 20px;
}

.shadow-icon {
    color: white;
    filter: drop-shadow(0 0 20px rgba(255, 204, 0, 0.4)); /* Efek glow emas */
    font-size: 8rem; /* Ukuran sangat besar */
}

/* Ikon dekorasi di belakang ikon utama */
.hero-icon-wrapper .bi-speedometer2 {
    position: absolute;
    right: 50px;
    top: -20px;
    font-size: 10rem;
    color: rgba(255,255,255,0.1);
    transform: rotate(-15deg);
}
    </style>
</head>
<body>
<div class="main-wrapper">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="content-area">
        <?php include "../../includes/header.php"; ?>

        <main class="p-4">
            <div class="welcome-banner shadow-sm mb-4">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h1 class="fw-bold mb-2">
                            Selamat Datang, <span style="color: var(--accent-yellow);">Admin!</span>
                        </h1>
                        <p class="opacity-75 mb-0">Pantau stok dan distribusi material praktek secara real-time.</p>
                    </div>
                    <div class="col-md-5 text-md-end d-none d-md-block">
                        <div class="hero-icon-wrapper">
                            <i class="bi bi-speedometer2 display-1 opacity-25"></i>
                            <i class="bi bi-bar-chart-line-fill display-1 shadow-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            


            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3" style="background: rgba(10, 25, 47, 0.1); color: var(--navy);"><i class="bi bi-box-seam"></i></div>
                            <div><small class="text-muted d-block small fw-bold">TOTAL BAHAN</small><h4 class="fw-bold mb-0"><?= $total_bahan; ?></h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3" style="background: rgba(255, 204, 0, 0.2); color: #856404;"><i class="bi bi-building"></i></div>
                            <div><small class="text-muted d-block small fw-bold">TOTAL LAB</small><h4 class="fw-bold mb-0"><?= $total_lab; ?></h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 border-start border-4 border-danger">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3 text-danger" style="background: rgba(220, 53, 69, 0.1);"><i class="bi bi-exclamation-triangle"></i></div>
                            <div><small class="text-muted d-block small fw-bold">STOK TIPIS</small><h4 class="fw-bold mb-0 text-danger"><?= $stok_menipis; ?></h4></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3 text-success" style="background: rgba(25, 135, 84, 0.1);"><i class="bi bi-truck"></i></div>
                            <div><small class="text-muted d-block small fw-bold">DISTRIBUSI</small><h4 class="fw-bold mb-0"><?= $total_distribusi; ?></h4></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="glass-card p-4 h-100">
                        <h5 class="fw-bold mb-4" style="color: var(--navy);">Tren Distribusi (7 Hari Terakhir)</h5>
                        <div style="height: 300px; width: 100%;">
                            <canvas id="distribusiChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <h5 class="fw-bold mb-4" style="color: var(--navy);">Aksi Cepat</h5>
                        <div class="list-group list-group-flush">
                            <a href="../../modules/gudang/bahan-praktek.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center py-3">
                                <div class="icon-box me-3 bg-light"><i class="bi bi-plus-circle text-primary"></i></div>
                                <div><h6 class="mb-0 fw-bold">Kelola Bahan</h6><small class="text-muted">Update stok pusat</small></div>
                            </a>
                            <a href="../../modules/distribusi/index.php" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center py-3">
                                <div class="icon-box me-3 bg-light"><i class="bi bi-send text-success"></i></div>
                                <div><h6 class="mb-0 fw-bold">Kirim ke Lab</h6><small class="text-muted">Distribusi material</small></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php if(isset($_GET['status']) && $_GET['status'] == 'update_success'): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Keamanan Diperbarui',
        text: 'Data profil Anda berhasil disimpan!',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('distribusiChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Jumlah Distribusi',
                    data: <?= json_encode($counts); ?>,
                    borderColor: '#0a192f',
                    backgroundColor: 'rgba(10, 25, 47, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffcc00'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
</body>
</html>