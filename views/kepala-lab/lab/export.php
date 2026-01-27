<?php
session_start();
include "../../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    exit("Akses Ditolak");
}

// 1. Ambil Parameter
$tipe_data = $_GET['tipe_data'] ?? 'pemakaian';
$periode   = $_GET['periode'] ?? 'bulan';
$waktu     = $_GET['waktu'] ?? date('Y-m'); 
$format    = $_GET['format'] ?? 'pdf';
$id_user_session = $_SESSION['id_user']; 

// --- BAGIAN CUSTOM PENANDATANGAN ---
$opsi_nama   = $_GET['opsi_nama'] ?? 'default';
$custom_nama = $_GET['custom_nama'] ?? '';
$custom_nip  = $_GET['custom_nip'] ?? '';

// 2. Ambil Identitas Kepala Lab & Nama Lab
$user_sql = "SELECT k.nama_kepala, k.nip, k.id_lab, l.nama_lab 
             FROM kepala_lab k 
             LEFT JOIN lab l ON k.id_lab = l.id_lab 
             WHERE k.id_kepala = '$id_user_session'"; 
$user_res = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_res);

$id_lab_user = $user_data['id_lab'] ?? '';
$nama_lab    = $user_data['nama_lab'] ?? 'Laboratorium';

// LOGIKA CUSTOM: Jika user memilih custom, gunakan input manual. Jika tidak, pakai database.
if ($opsi_nama === 'custom' && !empty($custom_nama)) {
    $nama_kepala = $custom_nama;
    $nip_kepala  = $custom_nip;
} else {
    $nama_kepala = $user_data['nama_kepala'] ?? '..........................';
    $nip_kepala  = $user_data['nip'] ?? '..........................';
}

// 3. Logika Penentuan Rentang Tanggal & Label Periode
$tanggal_mulai = $waktu . "-01 00:00:00"; 
if ($periode == 'triwulan') {
    $tanggal_selesai = date('Y-m-t 23:59:59', strtotime($waktu . "-01 +2 months"));
    $label_periode = "Triwulan (" . date('M Y', strtotime($tanggal_mulai)) . " - " . date('M Y', strtotime($tanggal_selesai)) . ")";
} elseif ($periode == 'semester') {
    $tanggal_selesai = date('Y-m-t 23:59:59', strtotime($waktu . "-01 +5 months"));
    $label_periode = "Semester (" . date('M Y', strtotime($tanggal_mulai)) . " - " . date('M Y', strtotime($tanggal_selesai)) . ")";
} else {
    $tanggal_selesai = date('Y-m-t 23:59:59', strtotime($waktu . "-01"));
    $label_periode = date('F Y', strtotime($tanggal_mulai));
}

// 4. Query SQL
if ($tipe_data == 'pemakaian') {
    $query = "SELECT p.*, b.nama_bahan, b.satuan, d.kode_distribusi, d.spesifikasi, d.kondisi
              FROM pemakaian_lab p
              JOIN bahan_praktek b ON p.id_praktek = b.id_praktek
              JOIN distribusi_lab d ON p.id_distribusi = d.id_distribusi
              WHERE p.id_lab = '$id_lab_user' 
              AND p.tgl_pakai BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
              ORDER BY p.tgl_pakai ASC";
    $judul_laporan = "LAPORAN PERTANGGUNGJAWABAN PEMAKAIAN BAHAN";
} else {
    $query = "SELECT 
                d.kode_distribusi, d.spesifikasi, d.kondisi, b.nama_bahan, b.satuan, d.jumlah as stok_awal,
                COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0) as total_pakai,
                (d.jumlah - COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0)) as sisa_stok
              FROM distribusi_lab d
              JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
              WHERE d.id_lab = '$id_lab_user' AND d.status = 'diterima'
              ORDER BY d.kode_distribusi ASC";
    $judul_laporan = ($tipe_data == 'gabungan') ? "LAPORAN REKAPITULASI STOK & PEMAKAIAN" : "LAPORAN SISA STOK BAHAN";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_<?= str_replace(' ', '_', $nama_lab) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: white; font-family: Arial, sans-serif; color: black; line-height: 1.2; }
        
        /* --- STYLING KOP SURAT --- */
        .kop-table { width: 100%; border-collapse: collapse; border: none !important; margin-bottom: 0px; }
        .kop-table td { border: none !important; vertical-align: middle; padding: 0; }
        
        .logo-container { width: 4.5cm !important; text-align: left; }
        .logo-container img { width: 4.5cm !important; height: auto; display: block; }
        
        .teks-kop { text-align: center; padding-right: 2cm; }
        .teks-kop h4 { font-size: 11pt; margin: 0; font-weight: normal; text-transform: uppercase; }
        .teks-kop h2 { font-size: 16pt; margin: 2px 0; font-weight: bold; font-family: Arial, sans-serif; }
        .teks-kop p { font-size: 8pt; margin: 0; }

        .garis-kop { border-top: 1px solid black; border-bottom: 3px solid black; height: 2px; margin-top: 5px; margin-bottom: 20px; }
        
        /* --- TABEL DATA --- */
        .table-laporan { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-laporan th, .table-laporan td { border: 1px solid black !important; padding: 5px 4px; font-size: 8.5pt; }
        .table-laporan th { background-color: #f2f2f2 !important; font-weight: bold; text-align: center; text-transform: uppercase; }
        
        @media print { 
            .no-print { display: none; } 
            @page { size: portrait; margin: 1cm; }
            body { -webkit-print-color-adjust: exact; }
            .logo-container { width: 4.5cm !important; }
            .logo-container img { width: 4.5cm !important; }
        }
    </style>
</head>
<body <?= ($format == 'pdf') ? 'onload="window.print()"' : '' ?>>

<div class="container-fluid px-4 mt-3">
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-sm btn-primary">Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-sm btn-secondary">Tutup</button>
    </div>

    <table class="kop-table">
        <tr>
            <td class="logo-container">
                <img src="../../../images/images.png" alt="Logo">
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
        <h5 style="text-decoration: underline; font-weight: bold; margin-bottom: 5px;"><?= $judul_laporan ?></h5>
        <p class="mb-0">Unit: <strong><?= strtoupper($nama_lab) ?></strong></p>
        <p class="small">Periode: <?= $label_periode ?></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="3%">NO</th>
                <?php if ($tipe_data == 'pemakaian'): ?>
                    <th width="10%">TGL PAKAI</th>
                    <th width="15%">KODE</th>
                    <th width="20%">NAMA BAHAN</th>
                    <th>SPESIFIKASI</th>
                    <th width="10%">KONDISI</th>
                    <th width="6%">QTY</th>
                <?php else: ?>
                    <th width="15%">KODE</th>
                    <th width="20%">NAMA BAHAN</th>
                    <th>SPESIFIKASI</th>
                    <th width="10%">KONDISI</th>
                    <th width="7%">AWAL</th>
                    <?php if($tipe_data == 'gabungan'): ?> <th width="7%">PAKAI</th> <?php endif; ?>
                    <th width="7%">SISA</th>
                <?php endif; ?>
                <th width="6%">SAT</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $t_qty = 0; $t_awal = 0; $t_sisa = 0;
            if(mysqli_num_rows($result) > 0):
                while($row = mysqli_fetch_assoc($result)): 
                    $p_skrg = ($tipe_data == 'pemakaian') ? $row['jumlah_pakai'] : $row['total_pakai'];
                    $t_qty += $p_skrg;
                    $t_awal += ($row['stok_awal'] ?? 0);
                    $t_sisa += ($row['sisa_stok'] ?? 0);
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <?php if ($tipe_data == 'pemakaian'): ?>
                    <td class="text-center"><?= date('d/m/y', strtotime($row['tgl_pakai'])) ?></td>
                    <td class="text-center"><?= $row['kode_distribusi'] ?></td>
                    <td><?= $row['nama_bahan'] ?></td>
                    <td><?= $row['spesifikasi'] ?: '-' ?></td>
                    <td class="text-center"><?= $row['kondisi'] ?></td>
                    <td class="text-center"><?= number_format($p_skrg) ?></td>
                <?php else: ?>
                    <td class="text-center"><?= $row['kode_distribusi'] ?></td>
                    <td><?= $row['nama_bahan'] ?></td>
                    <td><?= $row['spesifikasi'] ?: '-' ?></td>
                    <td class="text-center"><?= $row['kondisi'] ?></td>
                    <td class="text-center"><?= number_format($row['stok_awal']) ?></td>
                    <?php if($tipe_data == 'gabungan'): ?>
                        <td class="text-center" style="color: red;"><?= number_format($p_skrg) ?></td>
                    <?php endif; ?>
                    <td class="text-center fw-bold"><?= number_format($row['sisa_stok']) ?></td>
                <?php endif; ?>
                <td class="text-center"><?= $row['satuan'] ?></td>
            </tr>
            <?php endwhile; ?>

            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <?php if ($tipe_data == 'pemakaian'): ?>
                    <td colspan="6" class="text-center">TOTAL KESELURUHAN</td>
                    <td class="text-center"><?= number_format($t_qty) ?></td>
                <?php elseif ($tipe_data == 'gabungan'): ?>
                    <td colspan="5" class="text-center">TOTAL KESELURUHAN</td>
                    <td class="text-center"><?= number_format($t_awal) ?></td>
                    <td class="text-center"><?= number_format($t_qty) ?></td>
                    <td class="text-center"><?= number_format($t_sisa) ?></td>
                <?php else: ?>
                    <td colspan="5" class="text-center">TOTAL KESELURUHAN</td>
                    <td class="text-center"><?= number_format($t_awal) ?></td>
                    <td class="text-center"><?= number_format($t_sisa) ?></td>
                <?php endif; ?>
                <td class="text-center">-</td>
            </tr>

            <?php else: ?>
                <tr><td colspan="10" class="text-center py-3">Tidak ada data.</td></tr>
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

</body>
</html>