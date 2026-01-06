<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Laporan Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    
    <style>
        :root {
            --navy: #0a192f;
            --navy-light: #112240;
            --gold: #ffcc00;
        }

        /* Card Container */
        .portal-card {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(10, 25, 47, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .portal-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(10, 25, 47, 0.1);
            border-color: var(--gold);
        }

        /* Icon Styling */
        .icon-box-portal {
            width: 60px;
            height: 60px;
            background: var(--navy);
            color: var(--gold);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        /* Button Styling */
        .btn-navy-gold {
            background: var(--navy);
            color: var(--gold);
            border: 1px solid var(--navy);
            font-weight: 600;
            padding: 10px;
            border-radius: 10px;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-navy-gold:hover {
            background: var(--gold);
            color: var(--navy);
            border-color: var(--gold);
        }

        .section-title {
            color: var(--navy);
            font-weight: 800;
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 4px;
            background: var(--gold);
            border-radius: 2px;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="content-area">
        <?php include "../../includes/header.php"; ?>

        <main class="p-4">
            
            <div class="welcome-banner mb-5">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <h2 class="fw-bold mb-1">Pusat <span style="color: var(--gold);">Laporan Inventaris</span></h2>
                        <p class="opacity-75 mb-0">Akses pelaporan stok dan distribusi untuk seluruh kategori barang.</p>
                    </div>
                </div>
            </div>

            <h4 class="section-title">Kategori Laporan</h4>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="portal-card shadow-sm p-4">
                        <div>
                            <div class="icon-box-portal">
                                <i class="bi bi-flask"></i>
                            </div>
                            <h4 class="fw-bold text-navy">Bahan Praktek</h4>
                            <p class="text-muted small">Laporan penggunaan material habis pakai per laboratorium atau jurusan.</p>
                        </div>
                        <div class="mt-4">
                            <a href="laporan_bahan.php" class="btn-navy-gold d-block">
                                Buka Laporan <i class="bi bi-chevron-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="portal-card shadow-sm p-4">
                        <div>
                            <div class="icon-box-portal">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <h4 class="fw-bold text-navy">Alat Tulis Kantor</h4>
                            <p class="text-muted small">Laporan ketersediaan stok kertas, alat tulis, dan perlengkapan kantor.</p>
                        </div>
                        <div class="mt-4">
                            <a href="laporan_atk.php" class="btn-navy-gold d-block">
                                Buka Laporan <i class="bi bi-chevron-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="portal-card shadow-sm p-4">
                        <div>
                            <div class="icon-box-portal">
                                <i class="bi bi-trash3"></i>
                            </div>
                            <h4 class="fw-bold text-navy">Kebersihan</h4>
                            <p class="text-muted small">Laporan stok bahan pembersih, alat sanitasi, dan perlengkapan gedung.</p>
                        </div>
                        <div class="mt-4">
                            <a href="laporan_kebersihan.php" class="btn-navy-gold d-block">
                                Buka Laporan <i class="bi bi-chevron-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 p-4 rounded-4 border-start border-4 border-warning bg-white shadow-sm d-flex align-items-center">
                <i class="bi bi-info-circle-fill text-navy fs-3 me-3"></i>
                <div>
                    <h6 class="fw-bold mb-1 text-navy">Informasi Dokumentasi</h6>
                    <p class="text-muted small mb-0">Seluruh laporan yang dihasilkan telah disesuaikan dengan standar format administrasi kampus untuk keperluan audit dan akreditasi.</p>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>