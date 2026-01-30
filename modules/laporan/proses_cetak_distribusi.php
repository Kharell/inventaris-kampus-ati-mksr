<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. TANGKAP PARAMETER
$scope        = $_GET['scope'] ?? 'semua';
$id_jurusan   = $_GET['id_jurusan'] ?? '';
$id_lab       = $_GET['id_lab'] ?? '';
$tgl_awal     = isset($_GET['tgl_awal']) ? mysqli_real_escape_string($conn, $_GET['tgl_awal']) : date('Y-m-01');
$tgl_akhir    = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($conn, $_GET['tgl_akhir']) : date('Y-m-d');
$format       = $_GET['format'] ?? 'pdf';

// 2. DATA ADMIN DINAMIS
$id_user_session = $_SESSION['id_user'] ?? ($_SESSION['id_admin'] ?? null);
$admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_user_session'");
$admin_data  = mysqli_fetch_assoc($admin_query);

if (!$admin_data) {
    $admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users LIMIT 1");
    $admin_data  = mysqli_fetch_assoc($admin_query);
}

$nama_admin  = $admin_data['nama_lengkap'] ?? "Administrator";
$nip_admin   = $admin_data['nip'] ?? "..........................";

// 3. LOGIKA FILTER QUERY
// Base Query
$query = "SELECT d.*, b.nama_bahan, b.satuan, l.nama_lab, j.nama_jurusan 
          FROM distribusi_lab d
          JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
          JOIN lab l ON d.id_lab = l.id_lab
          JOIN jurusan j ON l.id_jurusan = j.id_jurusan
          WHERE d.tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'";

$title_suffix = "SEMUA UNIT / JURUSAN";

// Kondisi Filter
if ($scope == 'jurusan' && !empty($id_jurusan)) {
    $query .= " AND j.id_jurusan = '$id_jurusan'";
    $res_j = mysqli_query($conn, "SELECT nama_jurusan FROM jurusan WHERE id_jurusan = '$id_jurusan'");
    $data_j = mysqli_fetch_assoc($res_j);
    $title_suffix = "JURUSAN " . strtoupper($data_j['nama_jurusan']);
} elseif ($scope == 'lab' && !empty($id_lab)) {
    $query .= " AND l.id_lab = '$id_lab'";
    $res_l = mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab = '$id_lab'");
    $data_l = mysqli_fetch_assoc($res_l);
    $title_suffix = " " . strtoupper($data_l['nama_lab']);
}

$query .= " ORDER BY d.tanggal_distribusi ASC";
$result = mysqli_query($conn, $query);

// 4. HANDLING DOWNLOAD
$filename = "Laporan_Distribusi_" . date('Ymd');
if ($format == 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Distribusi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: Arial, sans-serif; color: black; line-height: 1.2; }
        .container-print { 
            background: white; padding: 1cm; width: 98%; max-width: 1200px;
            margin: 20px auto; min-height: 297mm; box-shadow: 0 0 15px rgba(0,0,0,0.1); 
        }
        .kop-table { width: 100%; border: none !important; }
        .kop-table td { border: none !important; vertical-align: middle; }
        .logo-container { width: 3.5cm !important; }
        .logo-container img { width: 3.5cm !important; height: auto; }
        .teks-kop { text-align: center; padding-right: 1.5cm; }
        .teks-kop h4 { font-size: 10pt; margin: 0; }
        .teks-kop h2 { font-size: 16pt; margin: 2px 0; font-weight: bold; }
        .garis-kop { border-top: 1px solid black; border-bottom: 3.5px solid black; height: 3px; margin: 5px 0 20px 0; }
        
        .table-laporan { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table-laporan th, .table-laporan td { 
            border: 1px solid black !important; 
            padding: 5px; 
            font-size: 8.5pt; 
            word-wrap: break-word;
            vertical-align: middle;
        }
        .table-laporan th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; }
        
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; gap: 10px; }
        
        @media print {
            @page { size: portrait; margin: 0.5cm; } 
            .no-print { display: none !important; } 
            body { background: white; margin: 0; padding: 0; }
            .container-print { width: 100%; box-shadow: none; margin: 0; padding: 0.5cm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">🖨️ CETAK PDF</button>
    <button onclick="window.close()" class="btn btn-danger">✖ TUTUP</button>
</div>

<div class="container-print">
    <table class="kop-table">
        <tr>
            <td class="logo-container">
                <img src="../../images/images.png">
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
        <h4 class="text-decoration-underline fw-bold mb-1">LAPORAN DISTRIBUSI BARANG INVENTARIS</h4>
        <h5 class="fw-bold mb-1"><?= $title_suffix ?></h5>
        <p>Periode: <b><?= date('d/m/Y', strtotime($tgl_awal)) ?></b> s/d <b><?= date('d/m/Y', strtotime($tgl_akhir)) ?></b></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th width="70">TANGGAL</th>
                <th width="85">KODE</th>
                <th>NAMA BARANG</th>
                <th>SPESIFIKASI</th>
                <th width="65">KONDISI</th>
                <th width="140">TUJUAN (LAB)</th>
                <th width="40">QTY</th>
                <th width="60">SAT</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; $total_qty = 0;
            if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    $total_qty += $row['jumlah'];
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d/m/y', strtotime($row['tanggal_distribusi'])) ?></td>
                    <td class="text-center"><b><?= $row['kode_distribusi'] ?: '-' ?></b></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?: '-' ?></small></td>
                    <td class="text-center"><?= $row['kondisi'] ?: 'Baik' ?></td>
                    <td>
                        <b><?= $row['nama_jurusan'] ?></b><br>
                        <small><?= $row['nama_lab'] ?></small>
                    </td>
                    <td class="text-center fw-bold"><?= $row['jumlah'] ?></td>
                    <td class="text-center"><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="7" class="text-end">TOTAL BARANG DIDISTRIBUSIKAN :</td>
                    <td class="text-center"><?= $total_qty ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="9" class="text-center py-4">Data distribusi tidak ditemukan untuk <?= strtolower($title_suffix) ?>.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center">
            <p class="mb-0">Makassar, <?= date('d F Y') ?></p>
            <p class="mb-0">Petugas Gudang,</p>
            <div style="height: 80px;"></div>
            <p class="fw-bold mb-0 text-decoration-underline"><?= strtoupper($nama_admin) ?></p>
            <p>NIP. <?= $nip_admin ?></p>
        </div>
    </div>
</div>

</body>
</html>