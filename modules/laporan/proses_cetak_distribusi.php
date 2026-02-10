<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. Deteksi Protokol & URL Dinamis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_path = $_SERVER['SCRIPT_NAME']; 
$parts = explode('/', trim($current_path, '/'));
$project_folder = $parts[0]; // Mengambil folder pertama setelah domain
$base_url = $protocol . $host . "/" . $project_folder . "/";
$logo_url = $base_url . "images/images.png";

// 2. Ambil Parameter Filter & Sanitasi Dasar
$scope       = $_GET['scope'] ?? 'semua';
$id_jurusan   = mysqli_real_escape_string($conn, $_GET['id_jurusan'] ?? '');
$id_lab       = mysqli_real_escape_string($conn, $_GET['id_lab'] ?? '');
$tgl_awal     = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir    = $_GET['tgl_akhir'] ?? date('Y-m-d');
$format       = $_GET['format'] ?? 'print';

// Inisialisasi variabel agar tidak error logic
$title_suffix = "";
$show_double_ttd = false;

// 3. Logika Download File
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
    echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
}

// 4. Logika Penandatangan
$opsi_nama = $_GET['opsi_nama'] ?? 'default';
if ($opsi_nama === 'custom' && !empty($_GET['custom_nama'])) {
    $nama_admin = $_GET['custom_nama'];
    $nip_admin  = $_GET['custom_nip'] ?: "..........................";
    $status_verifikasi = "Terverifikasi (Input Manual)";
} else {
    $nama_admin = $_SESSION['nama_lengkap'] ?: ($_SESSION['nama'] ?: "Administrator");
    $nip_admin  = $_SESSION['nip'] ?: "..........................";
    $status_verifikasi = "Terverifikasi secara Sistem (E-Inventory)";
}

$judul_laporan = "LAPORAN DISTRIBUSI BARANG INVENTARIS";

// 5. Query Data Distribusi
$query = "SELECT d.*, b.nama_bahan, b.satuan, b.spesifikasi, l.nama_lab, j.nama_jurusan 
          FROM distribusi_lab d
          JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
          JOIN lab l ON d.id_lab = l.id_lab
          JOIN jurusan j ON l.id_jurusan = j.id_jurusan
          WHERE d.tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'";

if ($scope == 'jurusan' && !empty($id_jurusan)) {
    $query .= " AND j.id_jurusan = '$id_jurusan'";
    $res_j = mysqli_query($conn, "SELECT nama_jurusan FROM jurusan WHERE id_jurusan = '$id_jurusan'");
    $data_j = mysqli_fetch_assoc($res_j);
    $title_suffix = "JURUSAN " . strtoupper($data_j['nama_jurusan'] ?? '');
} elseif ($scope == 'lab' && !empty($id_lab)) {
    $query .= " AND l.id_lab = '$id_lab'";
    $res_l = mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab = '$id_lab'");
    $data_l = mysqli_fetch_assoc($res_l);
    $title_suffix = " " . strtoupper($data_l['nama_lab'] ?? '');
    
    // Ambil data Kepala Lab untuk TTD ganda
    $kepala_query = mysqli_query($conn, "SELECT k.nama_kepala, k.nip, l.nama_lab FROM kepala_lab k JOIN lab l ON k.id_lab = l.id_lab WHERE k.id_lab = '$id_lab'");
    $k_data = mysqli_fetch_assoc($kepala_query);
    if ($k_data) {
        $show_double_ttd = true;
        $nama_kepala = $k_data['nama_kepala']; 
        $nip_kepala = $k_data['nip']; 
        $jabatan_kepala = "Kepala " . $k_data['nama_lab'];
    }
}

$result = mysqli_query($conn, $query . " ORDER BY d.tanggal_distribusi ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - <?= $judul_laporan ?></title>
    <?php if ($format === 'print' || $format === 'pdf'): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    <style>
        body { background: <?= ($format === 'print') ? '#f4f7f6' : '#fff' ?>; font-family: Arial, sans-serif; color: black; line-height: 1.2; font-size: 9pt; }
        .container-print { background: white; padding: 1cm; width: 99%; margin: auto; }
        .kop-table { width: 100%; border: none !important; border-bottom: 3px solid black !important; margin-bottom: 20px; }
        .kop-table td { border: none !important; }
        .table-laporan { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-laporan th, .table-laporan td { 
            border: 1px solid black !important; 
            padding: 5px; 
            vertical-align: middle; 
            mso-number-format:"\@"; 
        }
        .table-laporan th { background-color: #f2f2f2 !important; font-weight: bold; text-align: center; }
        .verif-box { border: 1px solid #ddd; padding: 5px; font-size: 7pt; color: #666; font-style: italic; display: inline-block; width: 220px; }
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        @media print {
            @page { size: landscape; margin: 0.5cm; } 
            .no-print { display: none !important; } 
            .container-print { padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

<?php if ($format === 'pdf' || $format === 'print'): ?>
<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">🖨️ CETAK SEKARANG</button>
    <button onclick="window.close()" class="btn btn-danger">✖ TUTUP</button>
</div>
<?php endif; ?>

<div class="container-print">
    <table class="kop-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                <img src="<?= $logo_url ?>" width="110" alt="Logo">
            </td>
            <td style="text-align: center; width: 70%;">
                <h5 style="margin:0; font-size: 11pt;">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA INDUSTRI</h5>
                <h3 style="margin:0; font-weight: bold;">POLITEKNIK ATI MAKASSAR</h3>
                <p style="margin:0; font-size: 10pt;">Jl. Sunu No. 220 Makassar, Telp. (0411) 449609 Fax. (0411) 449867</p>
            </td>
            <td style="width: 15%;"></td>
        </tr>
    </table>

    <div style="text-align: center; margin-bottom: 20px;">
        <h6 style="text-decoration: underline; margin-bottom: 5px;"><?= $judul_laporan ?></h6>
        <h6 style="margin:0;"><?= $title_suffix ?></h6>
        <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th width="100">TGL DISTRIBUSI</th>
                <th width="120">KODE DISTRIBUSI</th>
                <th>NAMA BARANG / BAHAN</th>
                <th>SPESIFIKASI</th>
                <th>LAB TUJUAN</th>
                <th width="50">QTY</th>
                <th width="80">SATUAN</th>
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
                    <td align="center"><?= date('d/m/Y', strtotime($row['tanggal_distribusi'])) ?></td>
                    <td align="center"><b><?= $row['kode_distribusi'] ?></b></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= htmlspecialchars($row['spesifikasi'] ?: '-') ?></small></td>
                    <td><?= htmlspecialchars($row['nama_lab']) ?></td>
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
            <td colspan="2" align="right" style="padding-bottom: 15px;">Makassar, <?= date('d F Y') ?></td>
        </tr>
        <tr valign="top">
            <?php if ($show_double_ttd): ?>
                <td width="50%" align="center">
                    <p>Mengetahui,<br>Petugas Logistik,</p>
                    <div class="verif-box">
                        Ditandatangani secara digital oleh:<br>
                        <b><?= strtoupper($nama_admin) ?></b><br>
                        <?= $status_verifikasi ?>
                    </div>
                    <p style="margin-top:15px;"><b><u><?= strtoupper($nama_admin) ?></u></b><br>NIP. <?= $nip_admin ?></p>
                </td>
                <td width="50%" align="center">
                    <p>Menyetujui,<br><?= $jabatan_kepala ?>,</p>
                    <div style="height: 75px;"></div>
                    <p><b><u><?= strtoupper($nama_kepala) ?></u></b><br>NIP. <?= $nip_kepala ?></p>
                </td>
            <?php else: ?>
                <td width="50%"></td>
                <td width="50%" align="center">
                    <p>Mengetahui,<br>Petugas Logistik,</p>
                    <div class="verif-box">
                        Ditandatangani secara digital oleh:<br>
                        <b><?= strtoupper($nama_admin) ?></b><br>
                        <?= $status_verifikasi ?>
                    </div>
                    <p style="margin-top:15px;"><b><u><?= strtoupper($nama_admin) ?></u></b><br>NIP. <?= $nip_admin ?></p>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>

<?php if ($format === 'excel' || $format === 'word') echo "</body></html>"; ?>
</body>
</html>