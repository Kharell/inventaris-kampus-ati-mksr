<?php
session_start();
include "../../../config/database.php";

// --- SECURITY & SESSION CHECK ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../../login.php");
    exit;
}
$id_lab_user = $_SESSION['id_lab'] ?? '';

// Ambil statistik ringkasan
$count_pakai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemakaian_lab WHERE id_lab = '$id_lab_user'"))['total'];
$count_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab WHERE id_lab = '$id_lab_user' AND status = 'diterima'"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kepala Lab | Inventory Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --navy: #001f3f; 
            --bg: #f8f9fc; 
            --glass: rgba(255, 255, 255, 0.9);
            --accent: #4e73df;
        }
        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #2d3436;
        }
        
        .card { 
            border: none; 
            border-radius: 24px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.03); 
            transition: all 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }

        .icon-shape {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
        }

        .report-selector { display: none; }
        .report-card {
            cursor: pointer;
            border: 2px solid #edf2f7;
            border-radius: 20px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: 0.2s;
            background: white;
        }
        .report-selector:checked + .report-card {
            border-color: var(--navy);
            background: #f0f7ff;
            box-shadow: 0 8px 20px rgba(0,31,63,0.1);
        }

        .config-box {
            background: white;
            padding: 30px;
            border-radius: 24px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .btn-generate {
            background: var(--navy);
            color: white;
            border: none;
            border-radius: 18px;
            padding: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: 0.3s;
        }
        .btn-generate:hover {
            background: #003366;
            box-shadow: 0 12px 24px rgba(0,31,63,0.2);
            transform: scale(1.02);
            color: white;
        }

        .format-pill {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 10px;
            border: 2px solid #edf2f7;
            transition: 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        .format-pill i { font-size: 1.2rem; }
        .btn-check:checked + .format-pill {
            background-color: var(--navy) !important;
            border-color: var(--navy) !important;
            color: white !important;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="flex-grow-1" style="min-height: 100vh;">
        <?php include "../../../includes/header.php"; ?>

        <main class="p-4 p-lg-5">
            <div class="d-flex flex-column mb-5">
                <h1 class="fw-bold" style="color: var(--navy);">LAPORAN KEPALA LAB</h1>
                <p class="text-muted">Sistem dokumentasi dan pengarsipan digital laporan pertanggungjawaban laboratorium.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card p-4 text-center">
                        <div class="icon-shape bg-primary-subtle text-primary mx-auto mb-3">
                            <i class="bi bi-stack fs-4"></i>
                        </div>
                        <h4 class="fw-bold mb-0"><?= $count_stok ?></h4>
                        <small class="text-muted">Total Bahan</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 text-center">
                        <div class="icon-shape bg-danger-subtle text-danger mx-auto mb-3">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                        <h4 class="fw-bold mb-0"><?= $count_pakai ?></h4>
                        <small class="text-muted">Riwayat Pakai</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-4 bg-navy text-white h-100 d-flex flex-row align-items-center" style="background: linear-gradient(135deg, #001f3f 0%, #004080 100%);">
                        <div class="me-4">
                            <i class="bi bi-shield-check" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Laporan Terverifikasi</h6>
                            <p class="small mb-0 opacity-75">Dokumen yang dihasilkan telah disesuaikan dengan standar regulasi laboratorium pendidikan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="export.php" method="GET" target="_blank">
    <div class="row g-4">
        <div class="col-lg-7">
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="bi bi-grid-fill me-3 text-primary"></i> Pilih Kategori Dokumen
            </h5>
            
            <input type="radio" class="report-selector" name="tipe_data" id="type1" value="pemakaian" checked>
            <label class="report-card w-100 mb-3" for="type1">
                <div class="icon-shape bg-primary text-white shadow-sm"><i class="bi bi-box-arrow-up-right"></i></div>
                <div>
                    <span class="d-block fw-bold fs-5">Laporan Pemakaian</span>
                    <span class="small text-muted">Data penggunaan bahan berdasarkan rincian tanggal.</span>
                </div>
            </label>

            <input type="radio" class="report-selector" name="tipe_data" id="type2" value="sisa">
            <label class="report-card w-100 mb-3" for="type2">
                <div class="icon-shape bg-success text-white shadow-sm"><i class="bi bi-pie-chart"></i></div>
                <div>
                    <span class="d-block fw-bold fs-5">Laporan Sisa Barang</span>
                    <span class="small text-muted">Rekapitulasi sisa stok/inventaris akhir saja.</span>
                </div>
            </label>

            <input type="radio" class="report-selector" name="tipe_data" id="type3" value="gabungan">
            <label class="report-card w-100 mb-4" for="type3">
                <div class="icon-shape bg-warning text-white shadow-sm"><i class="bi bi-layout-three-columns"></i></div>
                <div>
                    <span class="d-block fw-bold fs-5">Rekapitulasi Stok & Pakai (Gabungan)</span>
                    <span class="small text-muted">Tabel lengkap: Stok Awal, Total Pakai, dan Sisa Akhir.</span>
                </div>
            </label>

            <
        </div>

        <div class="col-lg-5">
            <div class="config-box shadow-sm h-100">
                <h5 class="fw-bold mb-4 d-flex align-items-center">
                    <i class="bi bi-sliders me-3 text-primary"></i> Konfigurasi Laporan
                </h5>

                <div class="mb-4">
                    <label class="small fw-bold text-muted mb-2 text-uppercase d-flex align-items-center">
                        <i class="bi bi-calendar-range me-2 text-primary"></i> Rentang Periode
                    </label>
                    <select name="periode" class="form-select border-0 bg-light py-3 px-4" id="selectPeriode" onchange="updateWaktuLabel()" style="border-radius: 14px;">
                        <option value="bulan">Laporan Bulanan</option>
                        <option value="triwulan">Laporan Triwulan</option>
                        <option value="semester">Laporan Semester</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="small fw-bold text-muted mb-2 text-uppercase d-flex align-items-center" id="labelWaktu">
                        <i class="bi bi-calendar-check me-2 text-primary"></i> Bulan Laporan
                    </label>
                    <input type="month" name="waktu" class="form-control border-0 bg-light py-3 px-4" value="<?= date('Y-m') ?>" style="border-radius: 14px;" required>
                    <div id="hintWaktu" class="mt-2 small text-muted fst-italic">Laporan mencakup data 1 bulan penuh.</div>
                </div>

                <div class="mb-5">
                    <label class="small fw-bold text-muted mb-3 text-uppercase d-flex align-items-center">
                        <i class="bi bi-file-earmark-arrow-down me-2 text-primary"></i> Format Dokumen
                    </label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="format" id="f_pdf" value="pdf" checked>
                            <label class="btn btn-outline-light text-dark w-100 format-pill" for="f_pdf">
                                <i class="bi bi-filetype-pdf text-danger"></i> PDF
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="format" id="f_word" value="word">
                            <label class="btn btn-outline-light text-dark w-100 format-pill" for="f_word">
                                <i class="bi bi-filetype-docx text-primary"></i> WORD
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="format" id="f_excel" value="excel">
                            <label class="btn btn-outline-light text-dark w-100 format-pill" for="f_excel">
                                <i class="bi bi-filetype-xlsx text-success"></i> EXCEL
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-generate w-100 py-3">
                    <i class="bi bi-printer-fill me-2"></i> GENERATE DOKUMEN
                </button>
            </div>
        </div>
    </div>
</form>
        </main>
    </div>
</div>

<script>
function updateWaktuLabel() {
    const periode = document.getElementById('selectPeriode').value;
    const label = document.getElementById('labelWaktu');
    const hint = document.getElementById('hintWaktu');
    
    if (periode === 'bulan') {
        label.innerHTML = '<i class="bi bi-calendar-check me-2 text-primary"></i> Bulan Laporan';
        hint.innerHTML = '<i class="bi bi-info-circle me-1"></i> Data akan diambil selama 1 bulan penuh.';
    } else if (periode === 'triwulan') {
        label.innerHTML = '<i class="bi bi-calendar-plus me-2 text-primary"></i> Mulai Dari Bulan (Awal Triwulan)';
        hint.innerHTML = '<i class="bi bi-info-circle me-1"></i> Data akan diambil selama 3 bulan kedepan.';
    } else if (periode === 'semester') {
        label.innerHTML = '<i class="bi bi-calendar-plus me-2 text-primary"></i> Mulai Dari Bulan (Awal Semester)';
        hint.innerHTML = '<i class="bi bi-info-circle me-1"></i> Data akan diambil selama 6 bulan kedepan.';
    }
}
document.addEventListener('DOMContentLoaded', updateWaktuLabel);
</script>

</body>
</html>