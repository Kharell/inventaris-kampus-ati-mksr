<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. Deteksi Protokol & URL Dinamis (Penting agar Logo Muncul di Word/Excel)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_path = $_SERVER['SCRIPT_NAME']; 
$parts = explode('/', $current_path);
$project_folder = $parts[1]; 
$base_url = $protocol . $host . "/" . $project_folder . "/";
$logo_url = $base_url . "images/images.png";

// 1. Ambil Parameter Filter
$scope       = $_GET['scope'] ?? 'semua';
$id_jurusan   = $_GET['id_jurusan'] ?? '';
$id_lab       = $_GET['id_lab'] ?? '';
$tgl_awal     = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir    = $_GET['tgl_akhir'] ?? date('Y-m-d');
$format       = $_GET['format'] ?? 'print';

// 2. Logika Download File dengan XML Office Namespace
$filename = "Laporan_Distribusi_" . date('Ymd');
if ($format === 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>";
} elseif ($format === 'word') {
    header("Content-type: application/vnd-ms-word");
    header("Content-Disposition: attachment; filename=$filename.doc");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "";
}

// 3. Logika Penandatangan
$opsi_nama    = $_GET['opsi_nama'] ?? 'default';
if ($opsi_nama === 'custom' && !empty($_GET['custom_nama'])) {
    $nama_admin = $_GET['custom_nama'];
    $nip_admin  = $_GET['custom_nip'] ?: "..........................";
    $status_verifikasi = "Terverifikasi (Input Manual)";
} else {
    $nama_admin = $_SESSION['nama_lengkap'] ?: $_SESSION['nama'] ?: "Administrator";
    $nip_admin  = $_SESSION['nip'] ?: "..........................";
    $status_verifikasi = "Terverifikasi secara Sistem (E-Inventory)";
}

$judul_laporan = "LAPORAN DISTRIBUSI BARANG INVENTARIS";

// 4. Query Data Distribusi
$query = "SELECT d.*, b.nama_bahan, b.satuan, b.spesifikasi, l.nama_lab, j.nama_jurusan 
          FROM distribusi_lab d
          JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
          JOIN lab l ON d.id_lab = l.id_lab
          JOIN jurusan j ON l.id_jurusan = j.id_jurusan
          WHERE d.tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'";

if ($scope == 'jurusan' && !empty($id_jurusan)) {
    $query .= " AND j.id_jurusan = '$id_jurusan'";
} elseif ($scope == 'lab' && !empty($id_lab)) {
    $query .= " AND l.id_lab = '$id_lab'";
}

$result = mysqli_query($conn, $query . " ORDER BY d.tanggal_distribusi ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - <?= $judul_laporan ?></title>
    <?php if ($format === 'pdf' || $format === 'print'): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    <style>
        body { background: <?= ($format === 'pdf' || $format === 'print') ? '#f4f7f6' : '#fff' ?>; font-family: Arial, sans-serif; color: black; line-height: 1.2; font-size: 9pt; }
        .container-print { background: white; padding: 1cm; width: 99%; margin: auto; }
        
        /* Kop Table Style agar tidak bergeser di Word/Excel */
        .kop-table { 
        width: 100%; 
        border: none !important; 
        border-bottom: 3px solid black !important; /* INI GARISNYA */
        margin-bottom: 20px; 
}
        .kop-table td { border: none !important; }
        .logo-container { width: 100px; text-align: left; }
        .teks-kop { text-align: center; }
        
        /* Table Data Style - Menggunakan mso-border agar garis muncul di Office */
        .table-laporan { width: 100%; border-collapse: collapse; }
        .table-laporan th, .table-laporan td { 
            border: .5pt solid black !important; 
            padding: 5px; 
            vertical-align: middle; 
            mso-number-format:"\@"; /* Mencegah angka nol di depan NIP/Kode hilang */
        }
        .table-laporan th { background-color: #f2f2f2 !important; font-weight: bold; text-align: center; }
        
        .verif-box { border: 1px solid #ddd; padding: 5px; font-size: 7pt; color: #666; font-style: italic; }
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        
        @media print {
            @page { size: landscape; margin: 0.5cm; } 
            .no-print { display: none !important; } 
            .container-print { padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

<?php if ($format === 'print' || $format === 'pdf') : ?>
<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">🖨️ CETAK</button>
    <button onclick="window.close()" class="btn btn-danger">✖ TUTUP</button>
</div>
<?php endif; ?>

<div class="container-print">
    <table class="kop-table" style="width: 100%; border: none; table-layout: fixed;">
    <tr>
        <td class="logo-container" style="width: 150px; text-align: left; vertical-align: middle;">
            <img src="<?= $logo_url ?>" width="130" alt="Logo">
        </td>

        <td class="teks-kop" style="text-align: center; vertical-align: middle;">
            <h5 style="margin:0; font-size: 11pt;">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA INDUSTRI</h5>
            <h3 style="margin:0; font-weight: bold;">POLITEKNIK ATI MAKASSAR</h3>
            <p style="margin:0; font-size: 10pt;">Jl. Sunu No. 220 Makassar, Telp. (0411) 449609 Fax. (0411) 449867</p>
        </td>

        <td style="width: 150px;"></td>
    </tr>
</table>


    <div style="text-align: center; margin-bottom: 20px;">
        <h6 style="text-decoration: underline; margin-bottom: 5px;"><?= $judul_laporan ?></h6>
        <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <table class="table-laporan" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th width="35">NO</th>
                <th width="80">TGL DISTRIBUSI</th>
                <th width="100">KODE DISTRIBUSI</th>
                <th>NAMA BARANG / BAHAN</th>
                <th width="150">SPESIFIKASI</th>
                <th>LAB TUJUAN</th>
                <th width="50">QTY</th>
                <th width="60">SATUAN</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $total_qty = 0;
            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    $total_qty += (int)$row['jumlah'];
            ?>
                <tr>
                    <td align="center"><?= $no++ ?></td>
                    <td align="center"><?= date('d/m/y', strtotime($row['tanggal_distribusi'])) ?></td>
                    <td align="center"><b><?= $row['kode_distribusi'] ?></b></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?: '-' ?></small></td>
                    <td><?= $row['nama_lab'] ?></td>
                    <td align="center"><?= $row['jumlah'] ?></td>
                    <td align="center"><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="6" align="right">TOTAL BARANG DIDISTRIBUSIKAN :</td>
                    <td align="center"><?= $total_qty ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="8" align="center">Tidak ada data distribusi barang pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table width="100%" border="0" style="margin-top: 30px;">
        <tr>
            <td colspan="2" align="right" style="padding-bottom: 10px;">Makassar, <?= date('d F Y') ?></td>
        </tr>
        <tr>
            <td width="60%"></td>
            <td width="40%" align="center">
                <p>Mengetahui,<br>Petugas Logistik / Gudang,</p>
                <div class="verif-box">
                    <small>Ditandatangani secara digital oleh:</small><br>
                    <b><?= strtoupper($nama_admin) ?></b><br>
                    <small style="color: green;"><?= $status_verifikasi ?></small>
                </div>
                <p style="margin-top:15px;"><b><u><?= strtoupper($nama_admin) ?></u></b><br>NIP. <?= $nip_admin ?></p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>