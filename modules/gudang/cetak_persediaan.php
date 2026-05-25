<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

// --- Ambil Parameter Tanda Tangan dari URL ---
$nama_kiri = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? "Admin Gudang";
$nip_kiri  = $_SESSION['nip'] ?? "..........................";

$nama_kanan = $_GET['nama_kanan'] ?? '...........................................';
$nip_kanan  = $_GET['nip_kanan'] ?? '...........................................';

// --- TANGKAP PARAMETER PENCARIAN & TANGGAL ---
$search    = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$tgl_awal  = isset($_GET['tgl_awal']) ? mysqli_real_escape_string($conn, $_GET['tgl_awal']) : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($conn, $_GET['tgl_akhir']) : '';

// --- LOGIKA FILTER DATABASE ---
$whereClause = "WHERE 1=1"; // Dasar WHERE

if ($search != '') {
    $whereClause .= " AND nama_barang LIKE '%$search%'";
}

// Jika tanggal awal dan akhir diisi dari Pop-up
if ($tgl_awal != '' && $tgl_akhir != '') {
    // Pastikan database memiliki kolom tgl_input atau sesuaikan dengan nama kolom tanggal di tabel Anda
    $whereClause .= " AND DATE(tgl_input) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
    $teks_periode = date('d F Y', strtotime($tgl_awal)) . " s/d " . date('d F Y', strtotime($tgl_akhir));
} else {
    $teks_periode = "Sampai dengan " . date('d F Y');
}

// Ambil SEMUA data yang cocok dengan pencarian dan tanggal
$query = "SELECT * FROM gudang_persediaan $whereClause ORDER BY nama_barang ASC";
$res = mysqli_query($conn, $query);

// 1. Deteksi Protokol & URL Dinamis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_path = $_SERVER['SCRIPT_NAME']; 
$parts = explode('/', trim($current_path, '/'));
$project_folder = $parts[0]; 
$base_url = $protocol . $host . "/" . $project_folder . "/";
$logo_url = $base_url . "images/images.png";

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Stok Persediaan</title>
    <style>
        /* === CSS DASAR UNTUK PRINT === */
        body { 
            background: #525659; /* Latar gelap ala PDF viewer jika dibuka di browser */
            font-family: 'Times New Roman', Times, serif; 
            color: #000; 
            line-height: 1.3; 
            font-size: 11pt; 
            margin: 0;
            padding: 0;
        }

        .container-print { 
            background: white; 
            width: 21cm; /* Kertas A4 Portrait */
            min-height: 29.7cm; 
            margin: 20px auto; 
            padding: 1.5cm; 
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            box-sizing: border-box;
        }

        /* === KOP SURAT RESMI === */
        .kop-table { width: 100%; border-collapse: collapse; border-bottom: 3px solid #000; margin-bottom: 2px; }
        .kop-table td { padding: 0; vertical-align: middle; }
        .garis-tipis { border-top: 1px solid #000; margin-bottom: 20px; }

        /* === TABEL DATA === */
        .table-laporan { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 10pt;}
        .table-laporan th, .table-laporan td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .table-laporan th { background-color: #f1f5f9; font-weight: bold; text-align: center; -webkit-print-color-adjust: exact;}
        
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #b91c1c; }

        /* === TOMBOL FLOATING (TIDAK DICETAK) === */
        .no-print-area { position: fixed; top: 20px; right: 20px; z-index: 999; display: flex; gap: 10px;}
        .btn-action { font-family: Arial, sans-serif; padding: 10px 20px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.3);}
        .btn-print { background: #ffcc00; color: #001f3f; }
        .btn-close-app { background: #dc3545; color: white; }

        /* === PENGATURAN KETIKA DI-PRINT === */
        @media print {
            @page { size: A4 portrait; margin: 1cm; } 
            body { background: white; margin: 0; }
            .container-print { width: 100%; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
            .no-print-area { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print-area">
    <button onclick="window.print()" class="btn-action btn-print">🖨️ Cetak PDF</button>
    <button onclick="window.close()" class="btn-action btn-close-app">✖ Tutup Laporan</button>
</div>

<div class="container-print">
    <!-- BAGIAN KOP SURAT -->
    <table class="kop-table" style="table-layout: fixed;">
        <tr>
            <td class="logo-container" style="width: 150px; text-align: left; vertical-align: middle;">
                <img src="<?= $logo_url ?>" width="110" alt="Logo">
            </td>
            <td class="teks-kop" style="text-align: center; vertical-align: middle;">
                <h5 style="margin:0; font-size: 11pt; font-weight: normal;">KEMENTERIAN PERINDUSTRIAN REPUBLIK INDONESIA</h5>
                <h3 style="margin:0; font-weight: bold; font-size: 18pt; letter-spacing: 1px;">POLITEKNIK ATI MAKASSAR</h3>
                <p style="margin:0; font-size: 10pt;">Jl. Sunu No. 220 Makassar, Telp. (0411) 449609 Fax. (0411) 449867</p>
            </td>
            <td style="width: 150px;"></td>
        </tr>
    </table>
    <div class="garis-tipis"></div>

    <!-- 2. JUDUL LAPORAN -->
    <div style="text-align: center; margin-bottom: 25px;">
        <h3 style="text-decoration: underline; margin: 0 0 5px 0; font-weight: bold; font-size: 14pt;">LAPORAN STOK GUDANG PERSEDIAAN</h3>
        <p style="margin: 0; font-size: 11pt;">Periode: <?= $teks_periode ?></p>
    </div>

    <!-- 3. TABEL DATA -->
    <table class="table-laporan">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">TGL INPUT</th>
                <th width="25%">NAMA BARANG</th>
                <th width="10%">SATUAN</th>
                <th width="10%">AWAL</th>
                <th width="10%">PENGAJUAN</th>
                <th width="10%">PEMAKAIAN</th>
                <th width="15%">SISA STOK</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($res) > 0):
                while($row = mysqli_fetch_assoc($res)): 
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="text-center"><?= isset($row['tgl_input']) ? date('d/m/Y', strtotime($row['tgl_input'])) : '-'; ?></td>
                <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                <td class="text-center"><?= htmlspecialchars($row['satuan']); ?></td>
                <td class="text-center"><?= $row['stok_awal']; ?></td>
                <td class="text-center fw-bold"><?= $row['pengajuan_barang']; ?></td>
                <td class="text-center fw-bold text-danger"><?= $row['pemakaian_barang']; ?></td>
                <td class="text-center fw-bold" style="font-size: 11pt;"><?= $row['stok_akhir']; ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data stok di gudang persediaan pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 4. AREA TANDA TANGAN -->
    <table style="width: 100%; margin-top: 40px; font-size: 11pt; border-collapse: collapse; page-break-inside: avoid;">
        <tr>
            <td style="width: 50%; text-align: left; padding-left: 10px;">
                <p style="margin: 0;">Mengetahui,<br>Admin Gudang Pusat,</p>
                <div style="height: 80px;"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;"><?= strtoupper($nama_kiri) ?></p>
                <p style="margin: 0;">NIP. <?= $nip_kiri ?></p>
            </td>
            
            <td style="width: 50%; text-align: right; padding-right: 10px;">
                <p style="margin: 0; padding-bottom: 5px;">Makassar, <?= date('d F Y') ?></p>
                <p style="margin: 0;">Menyetujui,</p>
                <div style="height: 80px;"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;"><?= strtoupper($nama_kanan) ?></p>
                <p style="margin: 0;">NIP. <?= $nip_kanan ?></p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>