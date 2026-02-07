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

// --- LOGIKA PENANDATANGAN (OTOMATIS BERDASARKAN LOGIN) ---
$opsi_nama    = $_GET['opsi_nama'] ?? 'default';
$custom_nama  = $_GET['custom_nama'] ?? '';
$custom_nip   = $_GET['custom_nip'] ?? '';

// Mengambil ID User dari session (pastikan session ini ada di login.php anda)
$id_user_session = $_SESSION['id_user'] ?? ($_SESSION['id_admin'] ?? null);

if ($opsi_nama === 'custom' && !empty($custom_nama)) {
    // Jika memilih input manual
    $nama_admin = $custom_nama;
    $nip_admin  = $custom_nip ?: "..........................";
    $status_verifikasi = "Terverifikasi (Input Manual oleh Petugas)";
} else {
    // DEFAULT: Mengambil data dari database berdasarkan siapa yang login
    $admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_user_session'");
    $admin_data  = mysqli_fetch_assoc($admin_query);

    if ($admin_data) {
        $nama_admin = $admin_data['nama_lengkap'];
        $nip_admin  = $admin_data['nip'] ?: "..........................";
    } else {
        // Fallback jika session tidak ditemukan
        $nama_admin = "Administrator";
        $nip_admin  = "..........................";
    }
    $status_verifikasi = "Terverifikasi secara Sistem (E-Inventory)";
}

// 2. QUERY DATA
$query = "SELECT d.*, b.nama_bahan, b.satuan, b.spesifikasi, l.nama_lab, j.nama_jurusan 
          FROM distribusi_lab d
          JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
          JOIN lab l ON d.id_lab = l.id_lab
          JOIN jurusan j ON l.id_jurusan = j.id_jurusan
          WHERE d.tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'";

$title_suffix = "SEMUA UNIT / JURUSAN";

if ($scope == 'jurusan' && !empty($id_jurusan)) {
    $query .= " AND j.id_jurusan = '$id_jurusan'";
    $res_j = mysqli_query($conn, "SELECT nama_jurusan FROM jurusan WHERE id_jurusan = '$id_jurusan'");
    $data_j = mysqli_fetch_assoc($res_j);
    $title_suffix = "JURUSAN " . strtoupper($data_j['nama_jurusan']);
} elseif ($scope == 'lab' && !empty($id_lab)) {
    $query .= " AND l.id_lab = '$id_lab'";
    $res_l = mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab = '$id_lab'");
    $data_l = mysqli_fetch_assoc($res_l);
    $title_suffix = strtoupper($data_l['nama_lab']);
}

$query .= " ORDER BY d.tanggal_distribusi ASC";
$result = mysqli_query($conn, $query);

// 3. HANDLING DOWNLOAD HEADERS
$filename = "Laporan_Distribusi_" . date('Ymd');
if ($format == 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
} elseif ($format == 'word') {
    header("Content-type: application/vnd-ms-word");
    header("Content-Disposition: attachment; filename=$filename.doc");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Distribusi</title>
    <?php if ($format == 'pdf'): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    <style>
        body { background: <?= ($format == 'pdf') ? '#f4f7f6' : 'white' ?>; font-family: Arial, sans-serif; color: black; line-height: 1.2; }
        .container-print { 
            background: white; padding: 1cm; width: 98%; max-width: 1200px;
            margin: <?= ($format == 'pdf') ? '20px auto' : '0' ?>; 
            min-height: 297mm; 
            box-shadow: <?= ($format == 'pdf') ? '0 0 15px rgba(0,0,0,0.1)' : 'none' ?>; 
        }
        .kop-table { width: 100%; border: none !important; border-bottom: 3px solid black !important; margin-bottom: 15px; }
        .kop-table td { border: none !important; vertical-align: middle; padding: 5px; }
        .logo-container { width: 80px !important; }
        .logo-container img { width: 80px !important; height: auto; }
        .teks-kop { text-align: center; }
        .teks-kop h4 { font-size: 10pt; margin: 0; }
        .teks-kop h2 { font-size: 16pt; margin: 2px 0; font-weight: bold; }
        
        .table-laporan { width: 100%; border-collapse: collapse; }
        .table-laporan th, .table-laporan td { 
            border: 1px solid black !important; 
            padding: 5px; 
            font-size: 9pt; 
            vertical-align: middle;
        }
        .table-laporan th { background-color: #f2f2f2 !important; text-align: center; font-weight: bold; }
        
        .verif-box {
            border: 1px solid #ddd; padding: 5px; font-size: 7pt;
            color: #666; display: inline-block; margin-top: 5px; font-style: italic;
        }

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

<?php if ($format == 'pdf'): ?>
<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">🖨️ CETAK / PDF</button>
    <button onclick="window.close()" class="btn btn-danger">✖ TUTUP</button>
</div>
<?php endif; ?>

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

    <div style="text-align: center; margin-bottom: 20px;">
        <h4 style="text-decoration: underline; font-weight: bold; margin-bottom: 5px;">LAPORAN DISTRIBUSI BARANG INVENTARIS</h4>
        <h5 style="font-weight: bold; margin-bottom: 5px;"><?= $title_suffix ?></h5>
        <p>Periode: <b><?= date('d/m/Y', strtotime($tgl_awal)) ?></b> s/d <b><?= date('d/m/Y', strtotime($tgl_akhir)) ?></b></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th width="80">TANGGAL</th>
                <th width="100">KODE</th>
                <th>NAMA BARANG</th>
                <th>SPESIFIKASI</th>
                <th width="150">TUJUAN (LAB)</th>
                <th width="50">QTY</th>
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
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td style="text-align: center;"><?= date('d/m/y', strtotime($row['tanggal_distribusi'])) ?></td>
                    <td style="text-align: center;"><b><?= $row['kode_distribusi'] ?: '-' ?></b></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?: '-' ?></small></td>
                    <td>
                        <b><?= $row['nama_jurusan'] ?></b><br>
                        <small><?= $row['nama_lab'] ?></small>
                    </td>
                    <td style="text-align: center; font-weight: bold;"><?= $row['jumlah'] ?></td>
                    <td style="text-align: center;"><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="6" style="text-align: right;">TOTAL BARANG DIDISTRIBUSIKAN :</td>
                    <td style="text-align: center;"><?= $total_qty ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="8" style="text-align: center; padding: 20px;">Data distribusi tidak ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table width="100%" style="margin-top: 40px; border: none;">
        <tr>
            <td width="60%"></td>
            <td style="text-align: center;">
                <p>Makassar, <?= date('d F Y') ?></p>
                <p>Petugas Gudang,</p>
                <div class="verif-box">
                    Ditandatangani secara digital oleh:<br>
                    <b><?= $nama_admin ?></b><br>
                    <?= $status_verifikasi ?>
                </div>
                <p style="margin-top: 15px;"><b><u><?= strtoupper($nama_admin) ?></u></b><br>NIP. <?= $nip_admin ?></p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>