<?php
// laporan_distribusi.php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// Ambil data untuk filter dropdown
$jurusan_query = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
$lab_all_query = mysqli_query($conn, "SELECT l.*, j.nama_jurusan FROM lab l JOIN jurusan j ON l.id_jurusan = j.id_jurusan ORDER BY j.nama_jurusan ASC, l.nama_lab ASC");

$labs = [];
while($l = mysqli_fetch_assoc($lab_all_query)) { $labs[] = $l; }

// Statistik ringkas (opsional untuk header)
$total_distribusi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Distribusi - Inventaris Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* SINKRONISASI DENGAN SIDEBAR ANDA */
        :root { 
            --navy: #001f3f; 
            --navy-light: #112240; 
            --gold: #ffcc00; 
        }
        
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }

        /* Mengikuti margin sidebar fixed anda */
        @media (min-width: 992px) {
            .main-content { margin-left: 260px !important; }
        }

        .header-card { 
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); 
            color: white; border-radius: 15px; padding: 30px; margin-bottom: 25px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); 
            border-bottom: 4px solid var(--gold);
        }

        .report-selector { display: none; }
        .report-card {
            cursor: pointer; border: 2px solid #e9ecef; border-radius: 15px;
            padding: 15px 20px; display: flex; align-items: center; gap: 15px;
            transition: 0.3s; background: white; margin-bottom: 12px;
        }
        .report-selector:checked + .report-card {
            border-color: var(--gold); background: #fffdf2;
            transform: translateX(10px);
        }
        .report-selector:checked + .report-card .icon-shape {
            background-color: var(--gold); color: var(--navy);
        }

        .icon-shape { 
            width: 45px; height: 45px; border-radius: 10px; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.2rem; background-color: #f8f9fa; color: var(--navy);
        }

        .config-card { border-radius: 15px; border: none; }
        
        .format-pill {
            border-radius: 10px; font-weight: 600; padding: 10px;
            border: 2px solid #e9ecef; cursor: pointer; text-align: center; display: block;
            transition: 0.2s; font-size: 0.75rem;
        }
        .btn-check:checked + .format-pill {
            background-color: var(--navy) !important; color: white !important; border-color: var(--navy) !important;
        }

        .btn-gold { 
            background-color: var(--gold); color: var(--navy); 
            font-weight: 700; border: none; border-radius: 12px; 
            padding: 15px; transition: 0.3s;
        }
        .btn-gold:hover { background-color: #e6b800; transform: translateY(-2px); }

        .info-badge {
            background-color: #e7f1ff; color: #084298; border-radius: 8px;
            padding: 12px; font-size: 0.85rem; border: 1px solid #b8daff;
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
                        <h2 class="fw-bold mb-1">Cetak Laporan Distribusi Bahan Praktek</h2>
                        <p class="mb-0 text-white-50">Filter distribusi barang antar Jurusan dan Lab</p>
                    </div>
                </div>
            </div>

            <form action="proses_cetak_distribusi.php" method="GET" target="_blank">
                <div class="row g-4">
                    
                    <div class="col-lg-7">
                        
                        <input type="radio" class="report-selector" name="scope" id="scope_semua" value="semua" checked onclick="toggleScope('semua')">
                        <label class="report-card w-100" for="scope_semua">
                            <div class="icon-shape"><i class="bi bi-globe"></i></div>
                            <div>
                                <span class="d-block fw-bold">Semua Data Distribusi</span>
                                <span class="small text-muted">Seluruh laporan dari semua jurusan & lab</span>
                            </div>
                        </label>

                        <input type="radio" class="report-selector" name="scope" id="scope_jurusan" value="jurusan" onclick="toggleScope('jurusan')">
                        <label class="report-card w-100" for="scope_jurusan">
                            <div class="icon-shape"><i class="bi bi-mortarboard"></i></div>
                            <div>
                                <span class="d-block fw-bold">Berdasarkan Jurusan</span>
                                <span class="small text-muted">Distribusi untuk satu jurusan tertentu</span>
                            </div>
                        </label>

                        <input type="radio" class="report-selector" name="scope" id="scope_lab" value="lab" onclick="toggleScope('lab')">
                        <label class="report-card w-100" for="scope_lab">
                            <div class="icon-shape"><i class="bi bi-buildings"></i></div>
                            <div>
                                <span class="d-block fw-bold"> Berdasarkan Laboratorium</span>
                                <span class="small text-muted">Detail distribusi per laboratorium spesifik</span>
                            </div>
                        </label>

                        <div id="panel_wilayah" class="card p-4 border-0 shadow-sm mt-3 d-none" style="border-radius: 15px;">
                            <div id="div_jurusan" class="mb-3 d-none">
                                <label class="form-label small fw-bold">PILIH JURUSAN</label>
                                <select name="id_jurusan" id="id_jurusan" class="form-select border-2" onchange="filterLabByJurusan(this.value)">
                                    <option value="">-- Pilih Jurusan --</option>
                                    <?php mysqli_data_seek($jurusan_query, 0); while($j = mysqli_fetch_assoc($jurusan_query)): ?>
                                        <option value="<?= $j['id_jurusan'] ?>"><?= $j['nama_jurusan'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div id="div_lab" class="mb-0 d-none">
                                <label class="form-label small fw-bold">PILIH LABORATORIUM</label>
                                <select name="id_lab" id="id_lab" class="form-select border-2">
                                    <option value="">-- Pilih Lab --</option>
                                    <?php foreach($labs as $l): ?>
                                        <option value="<?= $l['id_lab'] ?>" data-jurusan="<?= $l['id_jurusan'] ?>"><?= $l['nama_lab'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card config-card shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4 text-navy text-uppercase small"><i class="bi bi-gear-fill me-2"></i>Konfigurasi Laporan</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">PERIODE WAKTU</label>
                                    <select name="periode_tipe" id="periode_tipe" class="form-select border-2" onchange="handlePeriode(this.value)" style="border-radius: 10px;">
                                        <option value="custom">Rentang Tanggal Bebas</option>
                                        <option value="bulan">Perbulan (Otomatis)</option>
                                        <option value="triwulan">Triwulan (3 Bulan)</option>
                                        <option value="semester">Semester (6 Bulan)</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12">
                                        <label class="form-label small text-muted fw-bold" id="lbl_awal">TANGGAL AWAL</label>
                                        <input type="date" name="tgl_awal" id="tgl_awal" class="form-control border-2" value="<?= date('Y-m-d') ?>" onchange="hitungOtomatis()">
                                    </div>
                                    <div class="col-12" id="box_akhir">
                                        <label class="form-label small text-muted fw-bold">TANGGAL AKHIR</label>
                                        <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control border-2" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div id="info_box" class="info-badge mb-4 d-none">
                                    <i class="bi bi-info-circle-fill me-2"></i> <span id="info_txt"></span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted d-block">FORMAT DOKUMEN</label>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <input type="radio" class="btn-check" name="format" id="f_pdf" value="pdf" checked>
                                            <label class="format-pill" for="f_pdf"><i class="bi bi-file-pdf"></i> PDF</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check" name="format" id="f_excel" value="excel">
                                            <label class="format-pill" for="f_excel"><i class="bi bi-file-excel"></i> EXCEL</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="radio" class="btn-check" name="format" id="f_word" value="word">
                                            <label class="format-pill" for="f_word"><i class="bi bi-file-word"></i> WORD</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-gold w-100 shadow-sm py-3">
                                    <i class="bi bi-printer-fill me-2"></i> PROSES DOKUMEN
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
    // Toggle scope Wilayah
    function toggleScope(val) {
        const panel = document.getElementById('panel_wilayah');
        const dJurusan = document.getElementById('div_jurusan');
        const dLab = document.getElementById('div_lab');

        panel.classList.remove('d-none');
        dJurusan.classList.add('d-none');
        dLab.classList.add('d-none');

        if (val === 'jurusan') {
            dJurusan.classList.remove('d-none');
        } else if (val === 'lab') {
            dJurusan.classList.remove('d-none');
            dLab.classList.remove('d-none');
        } else {
            panel.classList.add('d-none');
        }
    }

    // Filter Lab berdasarkan Jurusan
    function filterLabByJurusan(id) {
        const labSelect = document.getElementById('id_lab');
        const opts = labSelect.options;
        labSelect.value = "";
        for (let i = 0; i < opts.length; i++) {
            const jurId = opts[i].getAttribute('data-jurusan');
            opts[i].style.display = (id === "" || jurId === id || opts[i].value === "") ? "block" : "none";
        }
    }

    // Handle Periode
    function handlePeriode(val) {
        const box = document.getElementById('box_akhir');
        const info = document.getElementById('info_box');
        if (val === 'custom') {
            box.classList.remove('d-none');
            info.classList.add('d-none');
        } else {
            box.classList.add('d-none');
            info.classList.remove('d-none');
            hitungOtomatis();
        }
    }

    function hitungOtomatis() {
        const tipe = document.getElementById('periode_tipe').value;
        const awal = document.getElementById('tgl_awal').value;
        const akhirInput = document.getElementById('tgl_akhir');
        const infoTxt = document.getElementById('info_txt');

        if (!awal || tipe === 'custom') return;

        let dStart = new Date(awal);
        let dEnd = new Date(awal);
        
        if (tipe === 'bulan') dEnd.setMonth(dStart.getMonth() + 1);
        else if (tipe === 'triwulan') dEnd.setMonth(dStart.getMonth() + 3);
        else if (tipe === 'semester') dEnd.setMonth(dStart.getMonth() + 6);

        dEnd.setDate(dEnd.getDate() - 1);
        const hasil = dEnd.toISOString().split('T')[0];
        akhirInput.value = hasil;
        infoTxt.innerText = "Sistem akan menarik data hingga: " + hasil;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>