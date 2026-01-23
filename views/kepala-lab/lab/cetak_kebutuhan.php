<?php
session_start();
include "../../../config/database.php"; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    exit('Akses ditolak.');
}

// Mengambil ID dari session login
$id_kepala_session = $_SESSION['id_user']; 

// Menangkap rentang tanggal dari URL
$tgl_awal  = mysqli_real_escape_string($conn, $_GET['tgl_awal']);
$tgl_akhir = mysqli_real_escape_string($conn, $_GET['tgl_akhir']);

/** * 1. AMBIL DATA KEPALA LAB & NAMA LAB SECARA OTOMATIS */
$user_sql = "SELECT k.nama_kepala, k.nip, l.nama_lab 
             FROM kepala_lab k 
             LEFT JOIN lab l ON k.id_lab = l.id_lab 
             WHERE k.id_kepala = '$id_kepala_session'";

$user_res = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_res);

$nama_lab    = $user_data['nama_lab'] ?? 'Laboratorium';
$nama_kepala = $user_data['nama_kepala'] ?? 'Nama Tidak Terdaftar';
$nip_kepala  = $user_data['nip'] ?? '...........................';

/** * 2. AMBIL DATA PERMINTAAN BARANG */
$sql = "SELECT p.*, b.nama_bahan, b.satuan, b.kode_bahan 
        FROM permintaan_barang p 
        LEFT JOIN bahan_praktek b ON p.id_barang = b.id_praktek 
        WHERE p.id_kepala = '$id_kepala_session' 
        AND p.status = 'pending' 
        AND DATE(p.tgl_permintaan) BETWEEN '$tgl_awal' AND '$tgl_akhir'
        ORDER BY p.tgl_permintaan ASC";

$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_<?= str_replace(' ', '_', $nama_lab) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: white; font-family: Arial, Helvetica, sans-serif; color: black; line-height: 1.2; }
        
        /* --- STYLING KOP SURAT STABIL (FIXED SIZE) --- */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
            margin-bottom: 0px;
        }
        .kop-table td { border: none !important; vertical-align: middle; padding: 0; }
        
        /* KUNCI UKURAN LOGO DALAM CM */
        .logo-container { 
            width: 5.5cm !important; 
            text-align: left; 
        }
        .logo-container img {
            width: 5.5cm !important; /* Ukuran menyesuaikan logo panjang Kemenperin */
            height: auto;
            display: block;
        }
        
        .teks-kop { 
            text-align: center; 
            padding-right: 1.5cm; /* Menyeimbangkan posisi teks agar ke tengah kertas */
        }
        .teks-kop h4 { font-size: 11pt; margin: 0; font-weight: normal; text-transform: uppercase; }
        .teks-kop h2 { font-size: 16pt; margin: 2px 0; font-weight: bold; }
        .teks-kop p { font-size: 8.5pt; margin: 0; }

        .garis-kop {
            border-top: 1px solid black;
            border-bottom: 3.5px solid black;
            height: 3px;
            margin-top: 5px;
            margin-bottom: 25px;
        }

        /* --- STYLING TABEL --- */
        .table-bordered th, .table-bordered td { border: 1px solid black !important; padding: 6px; font-size: 9pt; }
        .table th { background-color: #f2f2f2 !important; text-align: center; vertical-align: middle; font-weight: bold; }
        
        @media print { 
            .no-print { display: none; } 
            @page { size: portrait; margin: 1cm; } 
            body { -webkit-print-color-adjust: exact; }
            .logo-container { width: 5.5cm !important; }
            .logo-container img { width: 5.5cm !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid px-4 mt-3">
    <div class="no-print mb-4 text-end">
        <button onclick="window.print()" class="btn btn-sm btn-primary">Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-sm btn-secondary">Tutup</button>
    </div>

    <table class="kop-table">
        <tr>
            <td class="logo-container">
                <img src="../../../images/imaages.png" alt="Logo Kemenperin">
            </td>
            <td class="teks-kop">
                <h4>BADAN PENGEMBANGAN SUMBER DAYA MANUSIA INDUSTRI</h4>
                <h2>POLITEKNIK ATI MAKASSAR</h2>
                <p>Jl. Sunu No. 220 Makassar, Telp. (0411) 449609 Fax. (0411) 449867</p>
            </td>
        </tr>
    </table>
    <div class="garis-kop"></div>

    <div class="text-center mb-4">
        <h5 class="text-decoration-underline fw-bold mb-1">LAPORAN DAFTAR TUNGGU (PENDING) KEBUTUHAN BAHAN</h5>
        <p class="mb-0">Unit: <strong><?= strtoupper($nama_lab) ?></strong></p>
        <p class="small">Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="12%">TANGGAL</th>
                <th width="10%">KODE</th>
                <th width="18%">NAMA BAHAN</th>
                <th>SPESIFIKASI</th>
                <th width="10%">KONDISI</th>
                <th width="6%">QTY</th>
                <th width="6%">SAT</th>
                <th width="10%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($query) > 0):
                while($row = mysqli_fetch_assoc($query)): 
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="text-center"><?= date('d/m/y', strtotime($row['tgl_permintaan'])) ?></td>
                <td class="text-center small"><?= $row['kode_bahan'] ?: '-' ?></td>
                <td><?= $row['nama_bahan'] ?></td>
                <td style="font-size: 8.5pt;"><?= $row['spesifikasi'] ?: '-' ?></td>
                <td class="text-center"><?= $row['kondisi'] ?></td>
                <td class="text-center"><?= $row['jumlah_minta'] ?></td>
                <td class="text-center"><?= $row['satuan'] ?></td>
                <td class="text-center text-uppercase fw-bold" style="font-size: 8pt; color: red;"><?= $row['status'] ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="9" class="text-center py-4 text-muted">Data permintaan berstatus PENDING tidak ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-5">
        <div class="col-7"></div>
        <div class="col-5 text-center" style="font-size: 10pt;">
            <p class="mb-0">Makassar, <?= date('d F Y') ?></p>
            <p class="mb-0">Kepala <?= $nama_lab ?>,</p>
            <div style="height: 70px;"></div>
            <p class="fw-bold mb-0 text-decoration-underline"><?= strtoupper($nama_kepala) ?></p>
            <p>NIP. <?= $nip_kepala ?></p>
        </div>
    </div>
</div>

<script>
    // Jalankan perintah print otomatis saat halaman dimuat
    window.onload = function() {
        // window.print(); // Aktifkan jika ingin langsung cetak
    }
</script>

</body>
</html>