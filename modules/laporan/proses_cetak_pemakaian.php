<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// 1. TANGKAP PARAMETER
$scope        = $_GET['scope'] ?? 'semua';
$id_jurusan   = $_GET['id_jurusan'] ?? '';
$id_lab       = $_GET['id_lab'] ?? '';
$tgl_awal     = isset($_GET['tgl_awal']) ? mysqli_real_escape_string($conn, $_GET['tgl_awal']) : date('Y-m-01');
$tgl_akhir    = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($conn, $_GET['tgl_akhir']) : date('Y-m-d');
$format       = $_GET['format'] ?? 'pdf';

// 2. LOGIKA DOWNLOAD FILE
$filename = "Laporan_Pemakaian_" . date('Ymd');
if ($format == 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
}

// 3. DATA ADMIN DINAMIS
$id_user_session = $_SESSION['id_user'] ?? $_SESSION['id_admin'] ?? 1;
$admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_user_session'");
$admin_data  = mysqli_fetch_assoc($admin_query);
$nama_admin = $admin_data['nama_lengkap'] ?? "Administrator";
$nip_admin  = $admin_data['nip'] ?? "..........................";

// 4. LOGIKA QUERY (Menambahkan d.jumlah sebagai Stok Awal)
$query = "SELECT p.*, 
                 b.nama_bahan, b.satuan, 
                 l.nama_lab, j.nama_jurusan,
                 d.kode_distribusi, d.jumlah as stok_awal, 
                 IFNULL(d.spesifikasi, '-') as spesifikasi, 
                 IFNULL(d.kondisi, 'Baik') as kondisi,
                 (d.jumlah - (SELECT COALESCE(SUM(jumlah_pakai),0) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi)) as sisa_stok
          FROM pemakaian_lab p
          JOIN bahan_praktek b ON p.id_praktek = b.id_praktek
          JOIN lab l ON p.id_lab = l.id_lab
          JOIN jurusan j ON l.id_jurusan = j.id_jurusan
          LEFT JOIN distribusi_lab d ON p.id_distribusi = d.id_distribusi
          WHERE p.tgl_pakai BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";

$title_suffix = "SEMUA UNIT / JURUSAN";
$show_double_ttd = false;

// Filter Scope (Jurusan/Lab)
if ($scope == 'jurusan' && !empty($id_jurusan)) {
    $query .= " AND j.id_jurusan = '$id_jurusan'";
    $res_j = mysqli_query($conn, "SELECT nama_jurusan FROM jurusan WHERE id_jurusan = '$id_jurusan'");
    $title_suffix = "JURUSAN " . strtoupper(mysqli_fetch_assoc($res_j)['nama_jurusan']);
} elseif ($scope == 'lab' && !empty($id_lab)) {
    $query .= " AND l.id_lab = '$id_lab'";
    $res_l = mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab = '$id_lab'");
    $title_suffix = "LABORATORIUM " . strtoupper(mysqli_fetch_assoc($res_l)['nama_lab']);
    
    $kepala_query = mysqli_query($conn, "SELECT k.nama_kepala, k.nip, l.nama_lab FROM kepala_lab k JOIN lab l ON k.id_lab = l.id_lab WHERE k.id_lab = '$id_lab'");
    $k_data = mysqli_fetch_assoc($kepala_query);
    if ($k_data) {
        $show_double_ttd = true;
        $nama_kepala = $k_data['nama_kepala']; $nip_kepala = $k_data['nip']; $jabatan_kepala = "Kepala " . $k_data['nama_lab'];
    }
}

$query .= " ORDER BY p.tgl_pakai ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Pemakaian Bahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: Arial, sans-serif; color: black; line-height: 1.2; font-size: 8.5pt; }
        .container-print { background: white; padding: 1.5cm; width: 99%; max-width: 1600px; margin: 20px auto; min-height: 210mm; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        
        /* Kop Resmi */
        .kop-table { width: 100%; border: none !important; margin-bottom: 0; }
        .logo-container { width: 120px; text-align: left; vertical-align: middle; }
        .teks-kop { text-align: center; vertical-align: middle; padding-right: 120px; }
        .teks-kop h4 { font-size: 11pt; margin: 0; font-weight: normal; letter-spacing: 0.5px; }
        .teks-kop h2 { font-size: 17pt; margin: 2px 0; font-weight: bold; }
        .teks-kop p { font-size: 8.5pt; margin: 0; font-style: italic; }
        .garis-kop { border-top: 2px solid black; border-bottom: 1px solid black; height: 5px; margin: 10px 0 20px 0; }

        /* Tabel */
        .table-laporan { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table-laporan th, .table-laporan td { border: 1px solid black !important; padding: 5px 3px; vertical-align: middle; word-wrap: break-word; }
        .table-laporan th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 8pt; }
        
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; gap: 10px; }
        @media print {
            @page { size: landscape; margin: 0.5cm; } 
            .no-print { display: none !important; } 
            body { background: white; margin: 0; }
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
            <td class="logo-container"><img src="../../images/images.png" style="width:105px;"></td>
            <td class="teks-kop">
                <h4>BADAN PENGEMBANGAN SUMBER DAYA MANUSIA INDUSTRI</h4>
                <h2>POLITEKNIK ATI MAKASSAR</h2>
                <p>Jl. Sunu No. 220 Makassar, Telp. (0411) 449609 Fax. (0411) 449867</p>
            </td>
        </tr>
    </table>
    <div class="garis-kop"></div>

    <div class="text-center mb-4">
        <h4 class="text-decoration-underline fw-bold mb-1">LAPORAN PEMAKAIAN BAHAN PRAKTIKUM</h4>
        <h5 class="fw-bold mb-1"><?= $title_suffix ?></h5>
        <p>Periode: <b><?= date('d/m/Y', strtotime($tgl_awal)) ?></b> s/d <b><?= date('d/m/Y', strtotime($tgl_akhir)) ?></b></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 70px;">KODE</th>
                <th style="width: 75px;">TGL PAKAI</th>
                <th style="width: 18%;">NAMA BAHAN</th>
                <th>SPESIFIKASI</th>
                <th style="width: 60px;">KONDISI</th>
                <th style="width: 14%;">UNIT / LAB</th>
                <th style="width: 55px; background-color: #e3f2fd !important;">AWAL</th>
                <th style="width: 55px; background-color: #fffde7 !important;">PAKAI</th>
                <th style="width: 55px; background-color: #f1f8e9 !important;">SISA</th>
                <th style="width: 60px;">SATUAN</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; $t_awal = 0; $t_pakai = 0; $t_sisa = 0;
            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    $t_awal  += $row['stok_awal'];
                    $t_pakai += $row['jumlah_pakai'];
                    $t_sisa  += $row['sisa_stok'];
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><b><?= $row['kode_distribusi'] ?? '-' ?></b></td>
                    <td class="text-center"><?= date('d/m/y', strtotime($row['tgl_pakai'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?></small></td>
                    <td class="text-center"><?= $row['kondisi'] ?></td>
                    <td><small><b><?= $row['nama_jurusan'] ?></b><br><?= $row['nama_lab'] ?></small></td>
                    <td class="text-center fw-bold" style="background-color: #e3f2fd;"><?= $row['stok_awal'] ?></td>
                    <td class="text-center fw-bold" style="background-color: #fffde7;"><?= $row['jumlah_pakai'] ?></td>
                    <td class="text-center fw-bold" style="background-color: #f1f8e9;"><?= $row['sisa_stok'] ?></td>
                    <td class="text-center"><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="7" class="text-end">TOTAL REKAPITULASI :</td>
                    <td class="text-center" style="background-color: #e3f2fd;"><?= $t_awal ?></td>
                    <td class="text-center" style="background-color: #fffde7;"><?= $t_pakai ?></td>
                    <td class="text-center" style="background-color: #f1f8e9;"><?= $t_sisa ?></td>
                    <td class="text-center">ITEM</td>
                </tr>
            <?php else: ?>
                <tr><td colspan="11" class="text-center py-4">Data tidak ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 30px; border: none;">
        <tr>
            <td colspan="2" style="text-align: right; padding-right: 50px; padding-bottom: 15px;">Makassar, <?= date('d F Y') ?></td>
        </tr>
        <tr style="text-align: center; vertical-align: top;">
            <?php if ($show_double_ttd): ?>
                <td style="width: 50%;">
                    <p class="mb-0">Mengetahui,</p><p class="mb-0">Petugas Logistik,</p>
                    <div style="height: 70px;"></div>
                    <p class="fw-bold mb-0 text-decoration-underline"><?= strtoupper($nama_admin) ?></p><p>NIP. <?= $nip_admin ?></p>
                </td>
                <td style="width: 50%;">
                    <p class="mb-0">Menyetujui,</p><p class="mb-0"><?= $jabatan_kepala ?>,</p>
                    <div style="height: 70px;"></div>
                    <p class="fw-bold mb-0 text-decoration-underline"><?= strtoupper($nama_kepala) ?></p><p>NIP. <?= $nip_kepala ?></p>
                </td>
            <?php else: ?>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    <p class="mb-0">Mengetahui,</p><p class="mb-0">Petugas Logistik,</p>
                    <div style="height: 70px;"></div>
                    <p class="fw-bold mb-0 text-decoration-underline"><?= strtoupper($nama_admin) ?></p><p>NIP. <?= $nip_admin ?></p>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>
</body>
</html>