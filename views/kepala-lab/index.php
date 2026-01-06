<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Proteksi Halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../index.php");
    exit();
}
include "../../config/database.php";

// Query JOIN
$id_user = $_SESSION['id_user'];
$query = mysqli_query($conn, "SELECT k.*, l.nama_lab, j.nama_jurusan 
                              FROM kepala_lab k
                              JOIN lab l ON k.id_lab = l.id_lab
                              JOIN jurusan j ON l.id_jurusan = j.id_jurusan
                              WHERE k.id_kepala = '$id_user'");
$data = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Lab | Navy Yellow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --navy-primary: #001f3f;   /* Navy Pekat */
            --yellow-bright: #ffcc00;  /* Kuning Cerah */
            --bg-body: #f0f2f5;
        }

        body { 
            background-color: var(--bg-body); 
            font-family: 'Inter', sans-serif;
            color: var(--navy-primary);
        }

        .wrapper { display: flex; width: 100%; }

        .content-area {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Banner Identitas: Kuning Kentara */
        .identity-banner {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .text-jurusan {
            color: #666;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .text-lab {
            color: var(--navy-primary);
            font-weight: 800;
            font-size: 2.3rem;
            margin-bottom: 15px;
        }

        /* Badge Kuning & Navy */
        .tag-jurusan {
            background: var(--yellow-bright);
            color: var(--navy-primary);
            padding: 5px 15px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .tag-nip {
            background: var(--navy-primary);
            color: white;
            padding: 5px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        /* Stat Cards */
        .stat-card-custom {
            border: none;
            border-radius: 15px;
            padding: 25px;
            transition: 0.3s;
            background: white;
        }

        .card-prio {
            background: var(--navy-primary);
            color: white;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .bg-yellow-soft { background: var(--yellow-bright); color: var(--navy-primary); }
        .text-yellow { color: var(--yellow-bright) !important; }

        .btn-yellow {
            background: var(--yellow-bright);
            color: var(--navy-primary);
            border: none;
            font-weight: 700;
            padding: 8px 20px;
        }
        .btn-yellow:hover { background: #e6b800; }

        @media (max-width: 992px) {
            .content-area { width: 100%; margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="content-area">
        <?php include "../../includes/header.php"; ?>

        <main class="p-4 p-lg-5">
            <div class="identity-banner mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-9">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="tag-jurusan text-uppercase"><?= $data['nama_jurusan']; ?></span>
                        </div>
                        <h1 class="text-lab"><?= $data['nama_lab']; ?></h1>
                        <p class="text-muted mb-0">Selamat datang kembali, <strong><?= $data['nama_kepala']; ?></strong>. Pantau aktivitas laboratorium Anda hari ini.</p>
                    </div>
                    <div class="col-lg-3 text-end d-none d-lg-block">
                        <div class="p-3 bg-light rounded-circle d-inline-block border-3 border border-warning">
                             <img src="https://ui-avatars.com/api/?name=<?= urlencode($data['nama_kepala']); ?>&background=001f3f&color=ffcc00&size=100&bold=true" class="rounded-circle shadow-sm" alt="Avatar">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card-custom shadow-sm h-100">
                        <div class="icon-box bg-yellow-soft">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <h6 class="fw-bold text-muted small">KONFIRMASI BARANG</h6>
                        <h2 class="fw-bold">05</h2>
                        <hr>
                        <a href="#" class="text-decoration-none fw-bold" style="color: var(--navy-primary);">Lihat Daftar <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-card-custom card-prio shadow-lg h-100">
                        <div class="icon-box" style="background: rgba(255,255,255,0.1);">
                            <i class="bi bi-calendar-check-fill text-yellow"></i>
                        </div>
                        <h6 class="fw-bold text-white-50 small">LAPORAN PEMAKAIAN</h6>
                        <h2 class="fw-bold text-white">12 <span class="fs-6 fw-normal text-white-50">Sesi</span></h2>
                        <div class="mt-4">
                            <a href="#" class="btn btn-yellow w-100 rounded-pill shadow-sm">INPUT DATA SEKARANG</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="stat-card-custom shadow-sm h-100">
                        <div class="icon-box" style="background: var(--bg-body);">
                            <i class="bi bi-box-seam-fill" style="color: var(--navy-primary);"></i>
                        </div>
                        <h6 class="fw-bold text-muted small">INVENTARIS AKTIF</h6>
                        <h2 class="fw-bold">48</h2>
                        <hr>
                        <a href="#" class="text-decoration-none fw-bold" style="color: var(--navy-primary);">Manajemen Stok <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>