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
    <title>Manajemen Laporan Bahan | Navy Gold</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    
    <style>
        :root {
            --navy: #0a192f;
            --navy-light: #112240;
            --gold: #ffcc00;
            --gold-glow: rgba(255, 204, 0, 0.2);
        }

        /* Tombol Kembali Floating Style */
        .btn-back-custom {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--navy);
            font-weight: 700;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 50px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: 0.4s;
            border: 1px solid #eee;
        }
        .btn-back-custom i {
            width: 30px;
            height: 30px;
            background: var(--navy);
            color: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .btn-back-custom:hover {
            background: var(--gold);
            transform: translateX(-5px);
        }

        /* Jenis Laporan Card Selector */
        .type-selector {
            border: 2px solid #f0f0f0;
            border-radius: 15px;
            padding: 15px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
            background: #fdfdfd;
        }
        .type-selector:hover {
            border-color: var(--gold);
            background: white;
        }
        .form-check-input:checked + .type-selector {
            border-color: var(--navy);
            background: var(--navy);
            color: white;
        }
        .form-check-input:checked + .type-selector .text-muted {
            color: rgba(255,255,255,0.6) !important;
        }

        .report-glass-card {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            padding: 40px;
            border-bottom: 5px solid var(--gold);
        }

        .btn-generate {
            border-radius: 12px;
            padding: 15px;
            font-weight: 800;
            transition: 0.3s;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="content-area">
        <?php include "../../includes/header.php"; ?>

        <main class="p-4">
            
            <div class="mb-5 mt-2">
                <a href="index.php" class="btn-back-custom">
                    <i class="bi bi-arrow-left"></i>
                    <span>KEMBALI KE PORTAL</span>
                </a>
            </div>

            <div class="report-glass-card">
                <div class="header-gradient text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold mb-1">Konfigurasi Laporan <span style="color: var(--gold);">Bahan Praktek</span></h2>
                            <p class="opacity-75 mb-0">Sesuaikan jenis data, cakupan unit, dan format dokumen.</p>
                        </div>
                        <i class="bi bi-gear-wide-connected d-none d-md-block fs-1 text-gold opacity-50"></i>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form action="proses_cetak.php" method="POST" target="_blank">
                        
                        <h6 class="fw-bold text-navy mb-3 text-uppercase" style="letter-spacing: 1px;">1. Pilih Jenis Data</h6>
                        <div class="row g-3 mb-5">
                            <div class="col-md-4">
                                <input type="radio" class="form-check-input d-none" name="jenis_data" id="data1" value="distribusi" checked>
                                <label class="type-selector w-100" for="data1">
                                    <i class="bi bi-truck fs-3"></i>
                                    <div>
                                        <div class="fw-bold">Laporan Distribusi</div>
                                        <small class="text-muted">Barang keluar dari gudang ke Lab</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="form-check-input d-none" name="jenis_data" id="data2" value="pemakaian">
                                <label class="type-selector w-100" for="data2">
                                    <i class="bi bi-person-check fs-3"></i>
                                    <div>
                                        <div class="fw-bold">Laporan Pemakaian</div>
                                        <small class="text-muted">Data penggunaan oleh Kepala Lab</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="form-check-input d-none" name="jenis_data" id="data3" value="stok_bulanan">
                                <label class="type-selector w-100" for="data3">
                                    <i class="bi bi-calendar-range fs-3"></i>
                                    <div>
                                        <div class="fw-bold">Laporan Bulanan</div>
                                        <small class="text-muted">Rekapitulasi stok sisa & masuk</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <h6 class="fw-bold text-navy mb-3 text-uppercase" style="letter-spacing: 1px;">2. Filter Unit & Periode</h6>
                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="small fw-bold mb-2">Pilih Cakupan</label>
                                <select name="scope" class="form-select p-3 border-2 shadow-none rounded-3" id="scopeSelect">
                                    <option value="global">Seluruh Kampus (Global)</option>
                                    <option value="jurusan">Per Jurusan</option>
                                    <option value="lab">Per Laboratorium Spesifik</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold mb-2">Pilih Nama Unit (Jika Per Lab/Jurusan)</label>
                                <select name="target_id" class="form-select p-3 border-2 shadow-none rounded-3">
                                    <option value="all">-- Semua Unit --</option>
                                    </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold mb-2">Periode Bulan</label>
                                <input type="month" name="periode" class="form-select p-3 border-2 shadow-none rounded-3" required>
                            </div>
                        </div>

                        <h6 class="fw-bold text-navy mb-3 text-uppercase" style="letter-spacing: 1px;">3. Ekspor Dokumen</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <button type="submit" name="format" value="pdf" class="btn btn-danger btn-generate w-100 py-3 shadow-sm">
                                    <i class="bi bi-file-earmark-pdf-fill me-2"></i> GENERATE PDF DOCUMENT
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="format" value="excel" class="btn btn-success btn-generate w-100 py-3 shadow-sm">
                                    <i class="bi bi-file-earmark-excel-fill me-2"></i> EXPORT TO EXCEL SPREADSHEET
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>