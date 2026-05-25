<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

// 1. Ambil data untuk filter dropdown (Jurusan & Lab)
$jurusan_query = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
$lab_all_query = mysqli_query($conn, "SELECT l.*, j.nama_jurusan FROM lab l JOIN jurusan j ON l.id_jurusan = j.id_jurusan ORDER BY j.nama_jurusan ASC, l.nama_lab ASC");

$labs = [];
while($l = mysqli_fetch_assoc($lab_all_query)) { $labs[] = $l; }

// // 2. Ambil statistik ringkas untuk card
// $count_atk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE kategori = 'ATK'"))['total'];
// $count_kebersihan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE kategori = 'Kebersihan'"))['total'];
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
        :root { 
            --navy: #0a192f; 
            --navy-light: #112240; 
            --gold: #ffcc00; 
        }
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

        .custom-option-card {
            border: 2px solid #e9ecef !important;
            border-radius: 12px !important; color: #666 !important; transition: 0.3s; cursor: pointer;
        }
        .btn-check:checked + .custom-option-card {
            border-color: var(--navy) !important; background-color: #f8f9fa !important;
        }
        .animate-fade-in { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
                        <h2 class="fw-bold mb-1">Cetak Laporan Stok Bahan</h2>
                        <p class="mb-0 text-white-50">Filter sisa stok berdasarkan Jurusan dan Laboratorium</p>
                    </div>
                </div>
            </div>

            <!-- PERHATIAN: Pastikan action form mengarah ke file cetak STOK yang benar -->
            <form action="proses_cetak_stok.php" method="GET" target="_blank">
                <div class="row g-4">
                    
                    <!-- KIRI: Pemilihan Scope (Semua / Jurusan / Lab) -->
                    <div class="col-lg-7">
                        <h5 class="fw-bold mb-4"><i class="bi bi-grid-fill me-2 text-primary"></i>Kategori Dokumen</h5>
                        <input type="radio" class="report-selector" name="scope" id="scope_semua" value="semua" checked onclick="toggleScope('semua')">
                        <label class="report-card w-100" for="scope_semua">
                            <div class="icon-shape"><i class="bi bi-collection"></i></div>
                            <div>
                                <span class="d-block fw-bold">Seluruh Kampus</span>
                                <span class="small text-muted">Akumulasi stok dari Gudang Pusat & Seluruh Laboratorium</span>
                            </div>
                        </label>

                        <input type="radio" class="report-selector" name="scope" id="scope_jurusan" value="jurusan" onclick="toggleScope('jurusan')">
                        <label class="report-card w-100" for="scope_jurusan">
                            <div class="icon-shape"><i class="bi bi-mortarboard"></i></div>
                            <div>
                                <span class="d-block fw-bold">Berdasarkan Jurusan</span>
                                <span class="small text-muted">Total stok pada laboratorium di satu jurusan</span>
                            </div>
                        </label>

                        <input type="radio" class="report-selector" name="scope" id="scope_lab" value="lab" onclick="toggleScope('lab')">
                        <label class="report-card w-100" for="scope_lab">
                            <div class="icon-shape"><i class="bi bi-buildings"></i></div>
                            <div>
                                <span class="d-block fw-bold">Berdasarkan Laboratorium</span>
                                <span class="small text-muted">Sisa stok spesifik di satu laboratorium</span>
                            </div>
                        </label>

                        <!-- Panel Pemilihan Jurusan & Lab -->
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

                    <!-- KANAN: Konfigurasi Cetak (Periode, TTD, Format) -->
                    <div class="col-lg-5">
                        <div class="card config-card shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-4 text-navy text-uppercase small"><i class="bi bi-calendar-check-fill me-2"></i>Rentang Waktu</h6>
                                
                                <div class="mb-3">
                                    <select name="periode_tipe" id="periode_tipe" class="form-select border-2" onchange="handlePeriode(this.value)" style="border-radius: 10px;">
                                        <option value="custom">Rentang Tanggal Bebas</option>
                                        <option value="bulan">Per Bulan</option>
                                        <option value="triwulan">Triwulan</option>
                                        <option value="semester">Semester (6 Bulan)</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12">
                                        <label class="form-label small text-muted fw-bold">MULAI TANGGAL</label>
                                        <input type="date" name="tgl_awal" id="tgl_awal" class="form-control border-2" value="<?= date('Y-m-d') ?>" onchange="hitungOtomatis()">
                                    </div>
                                    <div class="col-12" id="box_akhir">
                                        <label class="form-label small text-muted fw-bold">SAMPAI TANGGAL</label>
                                        <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control border-2" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div id="info_box" class="info-badge mb-4 d-none">
                                    <i class="bi bi-info-circle-fill me-2"></i> <span id="info_txt"></span>
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
                                    <label class="form-label small fw-bold text-muted d-block">FORMAT OUTPUT</label>
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
                                     <i class="bi bi-printer-fill me-2"></i> GENERATE DOKUMEN STOK
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
    // Tampilkan / Sembunyikan Input TTD Manual
    function toggleInputNama(show) {
        const wrapper = document.getElementById('wrapper_custom_nama');
        const inputNama = document.getElementById('input_custom_nama');
        const inputNip = document.getElementById('input_custom_nip');
        
        if (show) {
            wrapper.style.display = 'block';
            inputNama.setAttribute('required', 'required');
            inputNip.setAttribute('required', 'required');
        } else {
            wrapper.style.display = 'none';
            inputNama.removeAttribute('required');
            inputNip.removeAttribute('required');
            inputNama.value = '';
            inputNip.value = '';
        }
    }

    // Tampilkan Dropdown Jurusan / Lab Berdasarkan Pilihan
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

    // Filter Lab jika Jurusan dipilih
    function filterLabByJurusan(id) {
        const labSelect = document.getElementById('id_lab');
        const opts = labSelect.options;
        labSelect.value = "";
        for (let i = 0; i < opts.length; i++) {
            const jurId = opts[i].getAttribute('data-jurusan');
            opts[i].style.display = (id === "" || jurId === id || opts[i].value === "") ? "block" : "none";
        }
    }

    // Logika Pengaturan Waktu
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

        let dEnd = new Date(awal);
        if (tipe === 'bulan') dEnd.setMonth(dEnd.getMonth() + 1);
        else if (tipe === 'triwulan') dEnd.setMonth(dEnd.getMonth() + 3);
        else if (tipe === 'semester') dEnd.setMonth(dEnd.getMonth() + 6);

        dEnd.setDate(dEnd.getDate() - 1);
        const hasil = dEnd.toISOString().split('T')[0];
        akhirInput.value = hasil;
        infoTxt.innerText = "Mencakup data stok hingga: " + hasil;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>