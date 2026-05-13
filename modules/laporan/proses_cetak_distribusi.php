<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

// 1. Deteksi Protokol & URL Dinamis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_path = $_SERVER['SCRIPT_NAME']; 
$parts = explode('/', trim($current_path, '/'));
$project_folder = $parts[0]; 
$base_url = $protocol . $host . "/" . $project_folder . "/";
$logo_url = $base_url . "images/images.png";

// 2. Ambil Parameter Filter & Sanitasi Dasar
$scope       = $_GET['scope'] ?? 'semua';
$id_jurusan  = mysqli_real_escape_string($conn, $_GET['id_jurusan'] ?? '');
$id_lab      = mysqli_real_escape_string($conn, $_GET['id_lab'] ?? '');
$tgl_awal    = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir   = $_GET['tgl_akhir'] ?? date('Y-m-d');
$format      = $_GET['format'] ?? 'print';

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
    $nama_admin = $_SESSION['nama_lengkap'] ?? ($_SESSION['nama'] ?? "Administrator");
    $nip_admin  = $_SESSION['nip'] ?? "..........................";
    $status_verifikasi = "Terverifikasi secara Sistem (E-Inventory)";
}

$judul_laporan = "LAPORAN DISTRIBUSI BARANG INVENTARIS";

// 5. Query Data Distribusi (Disesuaikan dengan Sistem Satu Gudang)
// PERBAIKAN: Menambahkan `AND d.jumlah_diterima > 0` agar yang ditolak/0 tidak masuk tabel
$query = "SELECT 
            d.kode_distribusi, 
            COALESCE(d.tanggal_diterima, d.tanggal_distribusi) as tgl_valid,
            d.jumlah_diterima as jumlah,
            b.nama_bahan, 
            b.satuan, 
            b.spesifikasi, 
            l.nama_lab, 
            j.nama_jurusan 
          FROM distribusi_lab d
          JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
          JOIN lab l ON d.id_lab = l.id_lab
          JOIN jurusan j ON l.id_jurusan = j.id_jurusan
          WHERE d.status = 'diterima' 
          AND d.jumlah_diterima > 0 
          AND DATE(COALESCE(d.tanggal_diterima, d.tanggal_distribusi)) BETWEEN '$tgl_awal' AND '$tgl_akhir'";

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

// Urutkan berdasarkan Nama Bahan terlebih dahulu, baru Tanggal
$query .= " ORDER BY b.nama_bahan ASC, tgl_valid ASC";
$result = mysqli_query($conn, $query);
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
        body { background: <?= ($format === 'print' || $format === 'pdf') ? '#f4f7f6' : '#fff' ?>; font-family: Arial, sans-serif; color: black; line-height: 1.2; font-size: 9pt; }
        .container-print { background: white; padding: 1cm; width: 99%; margin: auto; }
        .kop-table { width: 100%; border: none !important; border-bottom: 3px solid black !important; margin-bottom: 20px; }
        .kop-table td { border: none !important; }
        .table-laporan { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-laporan th, .table-laporan td { 
            border: 1px solid black !important; 
            padding: 6px; 
            vertical-align: middle; 
            mso-number-format:"\@"; 
        }
        .table-laporan th { background-color: #f2f2f2 !important; font-weight: bold; text-align: center; }
        .bahan-header { background-color: #e9ecef !important; font-weight: bold; text-align: left !important; }
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        @media print {
            @page { size: landscape; margin: 0.5cm; } 
            .no-print { display: none !important; } 
            .container-print { padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body <?= ($format === 'pdf' || $format === 'print') ? 'onload="window.print()"' : '' ?>>

<?php if ($format === 'pdf' || $format === 'print'): ?>
<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary shadow">🖨️ CETAK SEKARANG</button>
    <button onclick="window.close()" class="btn btn-danger shadow">✖ TUTUP</button>
</div>
<?php endif; ?>

<div class="container-print">
    <!-- BAGIAN KOP SURAT -->
    <table class="kop-table" style="table-layout: fixed;">
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
        <h6 style="text-decoration: underline; margin-bottom: 5px; font-weight: bold;"><?= $judul_laporan ?></h6>
        <?php if(!empty($title_suffix)): ?>
            <h6 style="margin:0; font-weight: bold;"><?= $title_suffix ?></h6>
        <?php endif; ?>
        <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <!-- TABEL DATA DISTRIBUSI -->
    <table class="table-laporan">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th width="100">TGL DITERIMA</th>
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
            $sub_qty = 0;
            $current_bahan = null;
            $current_satuan = null;

            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    
                    // 1. Jika Nama Bahan berubah dan bukan iterasi pertama, cetak Sub-Total
                    if ($current_bahan !== null && $current_bahan !== $row['nama_bahan']): ?>
                        <tr style="background-color: #fcfcfc; font-weight: bold;">
                            <td colspan="6" align="right">SUB TOTAL <?= strtoupper($current_bahan) ?> :</td>
                            <td align="center" style="color: #084298;"><?= number_format($sub_qty) ?></td>
                            <td align="center"><small><?= $current_satuan ?></small></td>
                        </tr>
                    <?php 
                        $sub_qty = 0; // Reset sub-total untuk barang baru
                    endif;

                    // 2. Jika Nama Bahan berubah, cetak Header/Label Bahan Baru
                    if ($current_bahan !== $row['nama_bahan']): 
                        $current_bahan = $row['nama_bahan'];
                        $current_satuan = $row['satuan'];
                    ?>
                        <tr>
                            <td colspan="8" class="bahan-header">
                                 BARANG/BAHAN: <?= strtoupper($current_bahan) ?>
                            </td>
                        </tr>
                    <?php 
                    endif;

                    // Kalkulasi jumlah
                    $qty = (int)$row['jumlah'];
                    $sub_qty += $qty;
                    $total_qty += $qty;
            ?>
                <!-- Cetak Baris Data Distribusi -->
                <tr>
                    <td align="center"><?= $no++ ?></td>
                    <td align="center"><?= date('d/m/Y', strtotime($row['tgl_valid'])) ?></td>
                    <td align="center"><b><?= $row['kode_distribusi'] ?></b></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= htmlspecialchars($row['spesifikasi'] ?: '-') ?></small></td>
                    <td><?= htmlspecialchars($row['nama_lab']) ?></td>
                    <td align="center" style="font-weight: bold;"><?= $qty ?></td>
                    <td align="center"><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
                
                <!-- 3. Cetak Sub-Total untuk barang yang ada di iterasi terakhir -->
                <tr style="background-color: #fcfcfc; font-weight: bold;">
                    <td colspan="6" align="right">SUB TOTAL <?= strtoupper($current_bahan) ?> :</td>
                    <td align="center" style="color: #084298;"><?= number_format($sub_qty) ?></td>
                    <td align="center"><small><?= $current_satuan ?></small></td>
                </tr>

                <!-- 4. Cetak Grand Total Keseluruhan Distribusi -->
                <tr style="background-color: #e3f2fd; font-weight: bold; font-size: 10pt;">
                    <td colspan="6" align="right">TOTAL KESELURUHAN (GRAND TOTAL) DISTRIBUSI :</td>
                    <td align="center" style="color: green;">+ <?= number_format($total_qty) ?></td>
                    <td></td>
                </tr>

            <?php else: ?>
                <tr><td colspan="8" align="center" style="padding: 20px;">Tidak ada data distribusi barang pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- BAGIAN TANDA TANGAN -->
    <table width="100%" border="0" style="margin-top: 40px; font-size: 10pt;">
        <tr>
            <td colspan="2" align="right" style="padding-bottom: 15px; padding-right: 20px;">Makassar, <?= date('d F Y') ?></td>
        </tr>
        <tr valign="top">
            <?php if ($show_double_ttd): ?>
                <td width="50%" align="center">
                    <p>Mengetahui,<br>Admin Gudang Pusat,</p>
                    <div style="height: 75px;"></div>
                    <p style="margin-top:15px; margin-bottom: 0;"><b><u><?= strtoupper($nama_admin) ?></u></b></p>
                    <p>NIP. <?= $nip_admin ?></p>
                </td>
                <td width="50%" align="center">
                    <p>Menyetujui,<br><?= $jabatan_kepala ?>,</p>
                    <div style="height: 75px;"></div>
                    <p style="margin-top:15px; margin-bottom: 0;"><b><u><?= strtoupper($nama_kepala) ?></u></b></p>
                    <p>NIP. <?= $nip_kepala ?></p>
                </td>
            <?php else: ?>
                <td width="50%"></td>
                <td width="50%" align="center">
                    <p>Mengetahui,<br>Admin Gudang Pusat,</p>
                    <div style="height: 75px;"></div>
                    <p style="margin-top:15px; margin-bottom: 0;"><b><u><?= strtoupper($nama_admin) ?></u></b></p>
                    <p>NIP. <?= $nip_admin ?></p>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>

<?php if ($format === 'excel' || $format === 'word') echo "</body></html>"; ?>
</body>
</html>