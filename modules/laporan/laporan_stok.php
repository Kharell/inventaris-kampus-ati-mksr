<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// Ambil statistik ringkas untuk card
$count_atk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE kategori = 'ATK'"))['total'];
$count_kebersihan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE kategori = 'Kebersihan'"))['total'];
$count_praktek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_praktek"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok - Gudang Pusat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root { --navy: #0a192f; --navy-light: #112240; --gold: #ffcc00; }
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
        
        .header-card { 
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); 
            color: white; border-radius: 15px; padding: 30px; margin-bottom: 25px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); 
        }

        .report-selector { display: none; }
        .report-card {
            cursor: pointer; border: 2px solid #e9ecef; border-radius: 15px;
            padding: 15px 20px; display: flex; align-items: center; gap: 15px;
            transition: 0.3s; background: white; margin-bottom: 12px;
        }
        .report-selector:checked + .report-card {
            border-color: var(--gold); background: #fffdf2;
            transform: translateX(10px); box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .report-selector:checked + .report-card .icon-shape {
            background-color: var(--gold); color: var(--navy);
        }

        .icon-shape { 
            width: 45px; height: 45px; border-radius: 10px; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.2rem; background-color: #f8f9fa; color: var(--navy);
            transition: 0.3s;
        }

        .config-card { border-radius: 15px; border: none; position: sticky; top: 90px; }
        
        .format-pill {
            border-radius: 10px; font-weight: 600; padding: 10px;
            border: 2px solid #e9ecef; cursor: pointer; text-align: center; display: block;
            transition: 0.2s; font-size: 0.85rem;
        }
        .btn-check:checked + .format-pill {
            background-color: var(--navy) !important; color: white !important; border-color: var(--navy) !important;
        }

        .btn-gold { 
            background-color: var(--gold); color: var(--navy); 
            font-weight: 700; border: none; border-radius: 12px; 
            padding: 15px; transition: 0.3s; 
        }
        .btn-gold:hover { 
            background-color: #e6b800; transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(255,204,0,0.3); 
        }

        .info-badge {
            background-color: #e7f1ff; color: #084298; border-radius: 8px;
            padding: 12px; font-size: 0.85rem; border: 1px solid #b8daff;
        }

        /* Style tambahan untuk opsi custom nama */
        .custom-option-card {
            border: 2px solid #e9ecef; transition: 0.3s; cursor: pointer;
        }
        .btn-check:checked + .custom-option-card {
            border-color: var(--navy) !important; background-color: #f8faff !important;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../includes/header.php"; ?>

        <main class="p-3 p-md-4" style="margin-top: 70px;">
            <div class="header-card shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-printer-fill text-warning fs-1"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1">Cetak Laporan Inventaris</h2>
                        <p class="mb-0 text-white-50">Filter data berdasarkan kategori dan rentang waktu</p>
                    </div>
                </div>
            </div>

            <form action="proses_cetak_stok.php" method="GET" target="_blank">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <input type="radio" class="report-selector" name="kategori" id="kat_praktek" value="praktek" checked>
                        <label class="report-card w-100" for="kat_praktek">
                            <div class="icon-shape"><i class="bi bi-tools"></i></div>
                            <div>
                                <span class="d-block fw-bold">Bahan Praktek / Workshop</span>
                                <span class="small text-muted"><?= $count_praktek ?> Item aktif</span>
                            </div>
                        </label>
                    </div>

                    <div class="col-lg-5">
                        <div class="card config-card shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4 text-navy text-uppercase small"><i class="bi bi-calendar-check me-2"></i>Pengaturan Periode</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">TIPE PERIODE</label>
                                    <select name="periode_tipe" id="periode_tipe" class="form-select border-2 py-2" onchange="handlePeriodeChange(this.value)" style="border-radius: 10px;">
                                        <option value="custom">Rentang Tanggal Custom</option>
                                        <option value="bulan">Perbulan (Otomatis)</option>
                                        <option value="triwulan">Triwulan (Otomatis 3 Bulan)</option>
                                        <option value="semester">Semester (Otomatis 6 Bulan)</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12" id="col_awal">
                                        <label class="form-label small text-muted fw-bold" id="label_awal">DARI TANGGAL</label>
                                        <input type="date" name="tgl_awal" id="tgl_awal" class="form-control border-2" value="<?= date('Y-m-d') ?>" onchange="hitungOtomatis()" style="border-radius: 10px;">
                                    </div>
                                    <div class="col-12" id="col_akhir">
                                        <label class="form-label small text-muted fw-bold">SAMPAI TANGGAL</label>
                                        <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control border-2" value="<?= date('Y-m-d') ?>" style="border-radius: 10px;">
                                    </div>
                                </div>

                                <div id="auto_info" class="info-badge mb-4 d-none">
                                    <i class="bi bi-info-circle-fill me-2"></i> <span id="info_text"></span>
                                </div>

                                
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 text-navy text-uppercase small">
                                        <i class="bi bi-pen-fill me-2"></i>Verifikasi Penandatanganan
                                    </h6>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="opsi_nama" id="nama_asli" value="default" checked onchange="toggleInputNama(false)">
                                            <label class="btn btn-outline-light w-100 p-2 custom-option-card text-start" for="nama_asli">
                                                <i class="bi bi-patch-check-fill mb-1 d-block" style="color: var(--navy);"></i>
                                                <span class="d-block fw-bold text-navy small">Default</span>
                                                <small class="text-muted" style="font-size: 0.6rem;"><?= $_SESSION['nama_user'] ?? 'User Aktif' ?></small>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <input type="radio" class="btn-check" name="opsi_nama" id="nama_custom" value="custom" onchange="toggleInputNama(true)">
                                            <label class="btn btn-outline-light w-100 p-2 custom-option-card text-start" for="nama_custom">
                                                <i class="bi bi-pencil-square mb-1 d-block" style="color: var(--navy);"></i>
                                                <span class="d-block fw-bold text-navy small">Ganti Nama</span>
                                                <small class="text-muted" style="font-size: 0.6rem;">Manual NIP</small>
                                            </label>
                                        </div>
                                    </div>


                                    <div id="wrapper_custom_nama" class="animate-fade-in" style="display: none;">
                                        <div class="row g-2">
                                            <div class="col-12 mb-2">
                                                <input type="text" name="custom_nama" id="input_custom_nama" class="form-control form-control-sm bg-light border-0" placeholder="Nama & Gelar Penandatangan">
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="custom_nip" id="input_custom_nip" class="form-control form-control-sm bg-light border-0" placeholder="NIP Penandatangan">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted d-block">FORMAT DOKUMEN</label>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="radio" class="btn-check" name="format" id="pdf" value="pdf" checked>
                                            <label class="format-pill" for="pdf"><i class="bi bi-file-pdf"></i> PDF</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check" name="format" id="excel" value="excel">
                                            <label class="format-pill" for="excel"><i class="bi bi-file-excel"></i> EXCEL</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check" name="format" id="word" value="word">
                                            <label class="format-pill" for="word"><i class="bi bi-file-word"></i> WORD</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-gold w-100 shadow-sm py-3">
                                    <i class="bi bi-printer-fill me-2"></i> GENERATE DOKUMEN
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    // Fungsi tambahan untuk toggle input nama custom
    function toggleInputNama(show) {
        const wrapper = document.getElementById('wrapper_custom_nama');
        wrapper.style.display = show ? 'block' : 'none';
    }

    function handlePeriodeChange(val) {
        const colAkhir = document.getElementById('col_akhir');
        const autoInfo = document.getElementById('auto_info');
        const labelAwal = document.getElementById('label_awal');
        const tglAwal = document.getElementById('tgl_awal');
        const tglAkhir = document.getElementById('tgl_akhir');

        if (val === 'custom') {
            colAkhir.classList.remove('d-none');
            autoInfo.classList.add('d-none');
            labelAwal.innerText = "DARI TANGGAL";
        } else {
            colAkhir.classList.add('d-none');
            autoInfo.classList.remove('d-none');
            labelAwal.innerText = "TANGGAL PATOKAN (MULAI)";
            hitungOtomatis();
        }
    }

    function hitungOtomatis() {
        const tipe = document.getElementById('periode_tipe').value;
        const tglAwalVal = document.getElementById('tgl_awal').value;
        const tglAkhir = document.getElementById('tgl_akhir');
        const infoText = document.getElementById('info_text');

        if (!tglAwalVal || tipe === 'custom') return;

        let dStart = new Date(tglAwalVal);
        let dEnd = new Date(tglAwalVal);
        
        if (tipe === 'bulan') {
            dEnd = new Date(dStart.getFullYear(), dStart.getMonth() + 1, 0);
        } else if (tipe === 'triwulan') {
            dEnd.setMonth(dStart.getMonth() + 3);
        } else if (tipe === 'semester') {
            dEnd.setMonth(dStart.getMonth() + 6);
        }

        const formattedEnd = dEnd.toISOString().split('T')[0];
        tglAkhir.value = formattedEnd;
        infoText.innerText = "Laporan akan ditarik dari " + tglAwalVal + " sampai " + formattedEnd;
    }

    window.onload = function() {
        handlePeriodeChange(document.getElementById('periode_tipe').value);
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>