<?php
session_start();
include "../../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../../login.php");
    exit;
}
$id_lab_user = $_SESSION['id_lab'] ?? '';

// Ambil statistik
$count_pakai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemakaian_lab WHERE id_lab = '$id_lab_user'"))['total'];
$count_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab WHERE id_lab = '$id_lab_user' AND status = 'diterima'"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kepala Lab | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --navy: #001f3f; 
            --gold: #D4AF37; 
            --bg: #f8f9fc; 
            --sidebar-width: 260px;
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #2d3436; 
            overflow-x: hidden;
        }
        
        /* Layout Structure Fix */
        .wrapper { 
            display: flex; 
            width: 100%; 
            align-items: stretch; 
        }

        .main-content { 
            flex: 1; 
            min-height: 100vh; 
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            background-color: var(--bg);
        }

        /* Responsive Adjustments */
        @media (min-width: 992px) {
            .main-content { margin-left: var(--sidebar-width); }
        }

        /* UI Components */
        .card { border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }

        .report-selector { display: none; }
        .report-card {
            cursor: pointer; border: 2px solid #edf2f7; border-radius: 20px;
            padding: 20px; display: flex; align-items: center; gap: 15px;
            transition: 0.2s; background: white;
        }
        .report-selector:checked + .report-card {
            border-color: var(--navy); background: #f0f7ff;
            box-shadow: 0 8px 20px rgba(0,31,63,0.05);
        }

        .custom-option-card {
            border-radius: 18px !important; border: 2px solid #f1f5f9 !important;
            background: #fff !important; transition: all 0.3s ease;
        }
        .btn-check:checked + .custom-option-card {
            border-color: var(--gold) !important;
            background: rgba(212, 175, 55, 0.05) !important;
        }
        .gold-icon { color: #dee2e6; }
        .btn-check:checked + .custom-option-card .gold-icon { color: var(--gold); }

        .format-pill {
            border-radius: 12px; font-weight: 600; padding: 12px;
            border: 2px solid #edf2f7; cursor: pointer; text-align: center;
        }
        .btn-check:checked + .format-pill {
            background-color: var(--navy) !important; color: white !important; border-color: var(--navy) !important;
        }
        
        .btn-generate {
            background: var(--navy); color: white; border: none;
            border-radius: 15px; padding: 15px; font-weight: 700; transition: 0.3s;
        }
        .btn-generate:hover { background: #003366; transform: scale(1.02); color: white; }
        
        .icon-shape { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        
        .animate-fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "../../../includes/sidebar.php"; ?>
   
    <div class="main-content">
        <?php include "../../../includes/header.php"; ?>

        <main class="p-3 p-md-4"style="margin-top: 70px;">
            
            <div class="card border-start border-5 mb-4 mb-md-5" style="border-color: var(--navy) !important;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-light p-3 rounded-4 me-3 text-navy d-none d-sm-block">
                            <i class="bi bi-file-earmark-bar-graph-fill fs-2"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">Panel Laporan Laboratorium</h4>
                            <p class="text-muted small mb-0">Kelola dan ekspor data stok serta pemakaian bahan.</p>
                        </div>
                    </div>
                </div>
            </div>
                <form action="export.php" method="GET" target="_blank">
                    <div class="row g-4 mb-4 mb-md-5">
                        
                        <div class="col-6 col-md-3">
                            <div class="card h-100 text-center border-0" style="background: var(--navy); border-radius: 20px;">
                                <div class="card-body p-3 p-md-4">
                                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" 
                                        style="width: 45px; height: 45px; background: rgba(255,255,255,0.1); border-radius: 12px;">
                                        <i class="bi bi-box-seam-fill text-white"></i>
                                    </div>
                                    <h3 class="fw-bold mb-0 text-white"><?= $count_stok ?></h3>
                                    <small class="text-white-50 fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Total Stok</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="card h-100 text-center border-0" style="background: var(--navy); border-radius: 20px;">
                                <div class="card-body p-3 p-md-4">
                                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" 
                                        style="width: 45px; height: 45px; background: rgba(255,255,255,0.1); border-radius: 12px;">
                                        <i class="bi bi-clock-history text-white"></i>
                                    </div>
                                    <h3 class="fw-bold mb-0 text-white"><?= $count_pakai ?></h3>
                                    <small class="text-white-50 fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Penggunaan</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm" style="border-radius: 20px; background: white;">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-navy">
                                        <i class="bi bi-pen-fill me-2" style="color: var(--navy);"></i>Verifikasi Penandatangan
                                    </h6>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="opsi_nama" id="nama_asli" value="default" checked onchange="toggleInputNama(false)">
                                            <label class="btn btn-outline-light w-100 p-2 custom-option-card text-start" for="nama_asli">
                                                <i class="bi bi-patch-check-fill mb-1 d-block" style="color: var(--navy);"></i>
                                                <span class="d-block fw-bold text-navy small">Nama Default</span>
                                                <small class="text-muted" style="font-size: 0.6rem;"><?= $_SESSION['nama_user'] ?? 'User' ?></small>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="opsi_nama" id="nama_custom" value="custom" onchange="toggleInputNama(true)">
                                            <label class="btn btn-outline-light w-100 p-2 custom-option-card text-start" for="nama_custom">
                                                <i class="bi bi-pencil-square mb-1 d-block" style="color: var(--navy);"></i>
                                                <span class="d-block fw-bold text-navy small">Ganti Identitas</span>
                                                <small class="text-muted" style="font-size: 0.6rem;">Custom Nama/NIP</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div id="wrapper_custom_nama" class="animate-fade-in" style="display: none;">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="text" name="custom_nama" id="input_custom_nama" class="form-control form-control-sm bg-light border-0" placeholder="Nama & Gelar">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" name="custom_nip" id="input_custom_nip" class="form-control form-control-sm bg-light border-0" placeholder="NIP">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
               

                <div class="row g-4">
                    <div class="col-lg-7">
                        <h5 class="fw-bold mb-4"><i class="bi bi-grid-fill me-2 text-primary"></i>Kategori Dokumen</h5>
                        
                        <input type="radio" class="report-selector" name="tipe_data" id="type1" value="pemakaian" checked>
                        <label class="report-card w-100 mb-3" for="type1">
                            <div class="icon-shape bg-primary text-white"><i class="bi bi-box-arrow-up-right"></i></div>
                            <div>
                                <span class="d-block fw-bold fs-5">Laporan Pemakaian</span>
                                <span class="small text-muted">Data penggunaan bahan berdasarkan rincian tanggal.</span>
                            </div>
                        </label>

                        <input type="radio" class="report-selector" name="tipe_data" id="type2" value="sisa">
                        <label class="report-card w-100 mb-3" for="type2">
                            <div class="icon-shape bg-success text-white"><i class="bi bi-pie-chart"></i></div>
                            <div>
                                <span class="d-block fw-bold fs-5">Laporan Sisa Barang</span>
                                <span class="small text-muted">Rekapitulasi sisa stok/inventaris akhir saja.</span>
                            </div>
                        </label>

                        <input type="radio" class="report-selector" name="tipe_data" id="type3" value="gabungan">
                        <label class="report-card w-100 mb-3" for="type3">
                            <div class="icon-shape bg-warning text-white"><i class="bi bi-layout-three-columns"></i></div>
                            <div>
                                <span class="d-block fw-bold fs-5">Rekapitulasi Gabungan</span>
                                <span class="small text-muted">Tabel lengkap: Stok Awal, Total Pakai, dan Sisa Akhir.</span>
                            </div>
                        </label>
                    </div>

                    <div class="col-lg-5">
                        <div class="card p-4 h-100 shadow-sm border-0">
                            <h5 class="fw-bold mb-4"><i class="bi bi-sliders me-2 text-primary"></i>Konfigurasi</h5>
                            
                            <div class="mb-3">
                                <label class="small fw-bold text-muted text-uppercase">Rentang Periode</label>
                                <select name="periode" class="form-select border-0 bg-light py-2" id="selectPeriode" onchange="updateWaktuLabel()" style="border-radius: 12px;">
                                    <option value="bulan">Bulanan</option>
                                    <option value="triwulan">Triwulan</option>
                                    <option value="semester">Semester</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="small fw-bold text-muted text-uppercase" id="labelWaktu">Bulan Laporan</label>
                                <input type="month" name="waktu" class="form-control border-0 bg-light py-2" value="<?= date('Y-m') ?>" style="border-radius: 12px;" required>
                            </div>

                            <div class="mb-4">
                                <label class="small fw-bold text-muted text-uppercase mb-2 d-block">Format Output</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="format" id="f_pdf" value="pdf" checked>
                                        <label class="format-pill w-100 small" for="f_pdf"><i class="bi bi-file-pdf text-danger"></i> PDF</label>
                                    </div>
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="format" id="f_word" value="word">
                                        <label class="format-pill w-100 small" for="f_word"><i class="bi bi-file-word text-primary"></i> WORD</label>
                                    </div>
                                    <div class="col-4">
                                        <input type="radio" class="btn-check" name="format" id="f_excel" value="excel">
                                        <label class="format-pill w-100 small" for="f_excel"><i class="bi bi-file-excel text-success"></i> EXCEL</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-generate w-100">
                                <i class="bi bi-printer-fill me-2"></i> GENERATE DOKUMEN
                            </button>
                        </div>
                    </div>
                </div>
            </form>
             </form>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleInputNama(isCustom) {
    const wrapper = document.getElementById('wrapper_custom_nama');
    const inputNama = document.getElementById('input_custom_nama');
    const inputNip = document.getElementById('input_custom_nip');
    
    wrapper.style.display = isCustom ? 'block' : 'none';
    if (!isCustom) {
        inputNama.value = '';
        inputNip.value = '';
    } else {
        inputNama.focus();
    }
}

function updateWaktuLabel() {
    const periode = document.getElementById('selectPeriode').value;
    const label = document.getElementById('labelWaktu');
    if (periode === 'bulan') label.innerText = 'Bulan Laporan';
    else if (periode === 'triwulan') label.innerText = 'Mulai Triwulan (Bulan Awal)';
    else label.innerText = 'Mulai Semester (Bulan Awal)';
}
</script>

</body>
</html>