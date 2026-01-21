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

/** * 1. AMBIL DATA KEPALA LAB & NAMA LAB SECARA OTOMATIS
 * Kita hubungkan tabel kepala_lab dengan tabel lab berdasarkan id_lab
 */
$user_sql = "SELECT k.nama_kepala, k.nip, l.nama_lab 
             FROM kepala_lab k 
             LEFT JOIN lab l ON k.id_lab = l.id_lab 
             WHERE k.id_kepala = '$id_kepala_session'";

$user_res = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_res);

// Simpan ke variabel agar mudah digunakan di HTML
$nama_lab    = $user_data['nama_lab'] ?? 'Laboratorium';
$nama_kepala = $user_data['nama_kepala'] ?? 'Nama Tidak Terdaftar';
$nip_kepala  = $user_data['nip'] ?? '...........................';

/**
 * 2. AMBIL DATA PERMINTAAN BARANG
 */
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
    <title>Cetak_Laporan_<?= str_replace(' ', '_', $nama_lab) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: white; font-family: 'Times New Roman', Times, serif; color: black; }
        .kop-surat { border-bottom: 3px solid black; padding-bottom: 10px; margin-bottom: 20px; }
        .table-bordered th, .table-bordered td { border: 1px solid black !important; padding: 6px; font-size: 10pt; }
        .table th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; }
        @media print { .no-print { display: none; } @page { size: portrait; margin: 1cm; } }
    </style>
</head>
<body>

<div class="container mt-3">
    <div class="no-print mb-4 text-end">
        <button onclick="window.print()" class="btn btn-sm btn-primary">Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-sm btn-secondary">Tutup</button>
    </div>

    <div class="kop-surat d-flex align-items-center">
        <img src="../../../images/logo.png" alt="Logo" style="width: 70px;" class="me-3">
        <div class="text-center w-100">
            <h6 class="mb-0 fw-bold">KEMENTERIAN PERINDUSTRIAN REPUBLIK INDONESIA</h6>
            <h5 class="mb-0 fw-bold">POLITEKNIK ATI MAKASSAR</h5>
            <p class="mb-0 fw-bold text-uppercase" style="font-size: 11pt;">UNIT: <?= $nama_lab ?></p>
            <p class="mb-0 small" style="font-size: 8pt;">Jl. Sunu No.221, Makassar, Sulawesi Selatan | www.atim.ac.id</p>
        </div>
    </div>

    <div class="text-center mb-4">
        <h6 class="text-decoration-underline fw-bold mb-1">LAPORAN DAFTAR TUNGGU (PENDING) KEBUTUHAN BAHAN</h6>
        <p class="small">Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="12%">TANGGAL</th>
                <th width="10%">KODE</th>
                <th width="20%">NAMA BAHAN</th>
                <th width="20%">SPESIFIKASI</th>
                <th width="8%">QTY</th>
                <th width="7%">SAT</th>
                <th width="10%">KONDISI</th>
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
                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_permintaan'])) ?></td>
                <td class="text-center small"><?= $row['kode_bahan'] ?: '-' ?></td>
                <td><?= $row['nama_bahan'] ?></td>
                <td style="font-size: 9pt;"><?= $row['spesifikasi'] ?: '-' ?></td>
                <td class="text-center"><?= $row['jumlah_minta'] ?></td>
                <td class="text-center"><?= $row['satuan'] ?></td>
                <td class="text-center"><?= $row['kondisi'] ?></td>
                <td class="text-center text-uppercase fw-bold" style="font-size: 8pt;"><?= $row['status'] ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="9" class="text-center py-4 text-muted">Data permintaan berstatus PENDING tidak ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-4">
        <div class="col-7"></div>
        <div class="col-5 text-center">
            <p class="mb-0 small">Makassar, <?= date('d F Y') ?></p>
            <p class="small mb-0">Kepala <?= $nama_lab ?>,</p>
            <div style="height: 70px;"></div>
            <p class="fw-bold mb-0 text-decoration-underline small"><?= strtoupper($nama_kepala) ?></p>
            <p class="small">NIP. <?= $nip_kepala ?></p>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>