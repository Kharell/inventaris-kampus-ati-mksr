<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

// 1. Deteksi URL Dinamis untuk Logo
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_path = $_SERVER['SCRIPT_NAME']; 
$parts = explode('/', $current_path);
$project_folder = $parts[1]; 
$base_url = $protocol . $host . "/" . $project_folder . "/";
$logo_url = $base_url . "images/images.png";

// 2. TANGKAP PARAMETER DARI FORM
$tgl_awal   = isset($_GET['tgl_awal']) ? mysqli_real_escape_string($conn, $_GET['tgl_awal']) : date('Y-m-01');
$tgl_akhir  = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($conn, $_GET['tgl_akhir']) : date('Y-m-d');
$format     = isset($_GET['format']) ? $_GET['format'] : 'pdf';
$scope      = isset($_GET['scope']) ? $_GET['scope'] : 'semua';
$id_jurusan = isset($_GET['id_jurusan']) ? mysqli_real_escape_string($conn, $_GET['id_jurusan']) : '';
$id_lab     = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';

// 3. LOGIKA DOWNLOAD FILE (Word/Excel)
$filename = "Laporan_Stok_Bahan_Praktek_" . date('Ymd');
if ($format === 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
} elseif ($format === 'word') {
    header("Content-type: application/vnd-ms-word");
    header("Content-Disposition: attachment; filename=$filename.doc");
}

// 4. LOGIKA PENANDATANGAN (Disamakan dengan Laporan Pemakaian)
$opsi_nama   = $_GET['opsi_nama'] ?? 'default';
$custom_nama = $_GET['custom_nama'] ?? '';
$custom_nip  = $_GET['custom_nip'] ?? '';

if ($opsi_nama === 'custom' && !empty($custom_nama)) {
    $nama_admin = $custom_nama;
    $nip_admin  = $custom_nip ?: "..........................";
} else {
    $nama_admin = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? "Administrator";
    $nip_admin  = $_SESSION['nip'] ?? "..........................";
    
    if ($nama_admin == "Administrator" || $nip_admin == "..........................") {
        $id_user_session = $_SESSION['id_user'] ?? 1;
        $admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id_admin = '$id_user_session' OR id_user = '$id_user_session'");
        $admin_data  = mysqli_fetch_assoc($admin_query);
        if ($admin_data) {
            $nama_admin = $admin_data['nama_lengkap'] ?: $nama_admin;
            $nip_admin  = $admin_data['nip'] ?: $nip_admin;
        }
    }
}

// 5. FILTER CAKUPAN WILAYAH (SCOPE)
$where_clause = "1=1";
$label_cakupan = "SELURUH LABORATORIUM KAMPUS";

if ($scope === 'jurusan' && !empty($id_jurusan)) {
    $where_clause = "l.id_jurusan = '$id_jurusan'";
    $q_jur = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_jurusan FROM jurusan WHERE id_jurusan='$id_jurusan'"));
    $label_cakupan = "JURUSAN: " . strtoupper($q_jur['nama_jurusan'] ?? '');
} elseif ($scope === 'lab' && !empty($id_lab)) {
    $where_clause = "p.id_lab = '$id_lab'";
    $q_lab = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab='$id_lab'"));
    $label_cakupan = "LABORATORIUM: " . strtoupper($q_lab['nama_lab'] ?? '');
}

// 6. QUERY DATA BAHAN PRAKTEK (Konsep Satu Gudang)
$sql = "SELECT 
            p.kode_bahan as kode, 
            p.nama_bahan as nama, 
            p.spesifikasi, 
            p.kondisi, 
            p.stok as stok_akhir,
            p.satuan,
            l.nama_lab,
            COALESCE((
                SELECT SUM(jumlah_diterima) 
                FROM distribusi_lab 
                WHERE id_praktek = p.id_praktek 
                AND status = 'diterima' 
                AND DATE(COALESCE(tanggal_diterima, tanggal_distribusi)) BETWEEN '$tgl_awal' AND '$tgl_akhir'
            ), 0) as masuk,
            COALESCE((
                SELECT SUM(jumlah_pakai) 
                FROM pemakaian_lab 
                WHERE id_praktek = p.id_praktek 
                AND DATE(tgl_pakai) BETWEEN '$tgl_awal' AND '$tgl_akhir'
            ), 0) as keluar
        FROM bahan_praktek p
        JOIN lab l ON p.id_lab = l.id_lab
        WHERE $where_clause
        ORDER BY l.nama_lab ASC, p.nama_bahan ASC";

$result = mysqli_query($conn, $sql);
$judul_laporan = "LAPORAN STOK BAHAN PRAKTEK / WORKSHOP";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul_laporan ?></title>
    <?php if ($format === 'pdf' || $format === 'print'): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    <style>
        body { background: <?= ($format === 'pdf' || $format === 'print') ? '#f4f7f6' : '#fff' ?>; font-family: Arial, sans-serif; color: black; line-height: 1.2; font-size: 9pt; }
        .container-print { background: white; padding: 1cm; width: 99%; margin: auto; }
        
        /* Kop Surat */
        .kop-table { width: 100%; border: none !important; border-bottom: 3px solid black !important; margin-bottom: 20px; }
        .logo-container { width: 100px; text-align: left; }
        .teks-kop { text-align: center; }
        
        /* Tabel Laporan */
        .table-laporan { width: 100%; border-collapse: collapse; border: 1px solid black; }
        .table-laporan th, .table-laporan td { border: 1px solid black !important; padding: 6px; vertical-align: middle; }
        .table-laporan th { background-color: #f2f2f2 !important; font-weight: bold; text-align: center; }
        
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .lab-header { background-color: #e9ecef !important; font-weight: bold; text-align: left !important; }
        
        @media print {
            @page { size: portrait; margin: 0.5cm; } 
            .no-print { display: none !important; } 
            .container-print { padding: 0; box-shadow: none; }
            body { -webkit-print-color-adjust: exact; }
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
    <!-- KOP SURAT -->
    <table class="kop-table" style="width: 100%; border: none; table-layout: fixed;">
        <tr>
            <td class="logo-container" style="width: 150px; text-align: left; vertical-align: middle;">
                <img src="<?= $logo_url ?>" width="100" alt="Logo">
            </td>
            <td class="teks-kop" style="text-align: center; vertical-align: middle;">
                <h5 style="margin:0; font-size: 11pt;">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA INDUSTRI</h5>
                <h3 style="margin:0; font-weight: bold;">POLITEKNIK ATI MAKASSAR</h3>
                <p style="margin:0; font-size: 10pt;">Jl. Sunu No. 220 Makassar, Telp. (0411) 449609 Fax. (0411) 449867</p>
            </td>
            <td style="width: 150px;"></td>
        </tr>
    </table>

    <!-- JUDUL LAPORAN -->
    <div style="text-align: center; margin-bottom: 20px;">
        <h6 style="text-decoration: underline; margin-bottom: 5px; font-weight: bold; font-size: 11pt;"><?= $judul_laporan ?></h6>
        <p style="margin: 2px 0;">Cakupan: <b><?= $label_cakupan ?></b></p>
        <p style="margin: 0;">Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <!-- TABEL DATA -->
    <table class="table-laporan">
        <thead>
            <tr>
                <th rowspan="2" width="30">NO</th>
                <th rowspan="2" width="90">KODE</th>
                <th rowspan="2">NAMA BAHAN</th>
                <th rowspan="2">SPESIFIKASI</th>
                <th rowspan="2" width="70">KONDISI</th>
                <th colspan="2">MUTASI PERIODE INI</th>
                <th rowspan="2" width="70">STOK AKHIR</th>
                <th rowspan="2" width="50">SAT</th>
            </tr>
            <tr>
                <th width="60">MASUK</th>
                <th width="60">KELUAR</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $current_lab = null;
            $sub_m = 0; $sub_k = 0; $sub_a = 0;
            $g_m = 0; $g_k = 0; $g_a = 0;

            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    
                    // --- LOGIKA GROUPING BERDASARKAN LAB ---
                    if ($current_lab !== null && $current_lab !== $row['nama_lab']): ?>
                        <tr style="background-color: #fcfcfc; font-weight: bold;">
                            <td colspan="5" align="right">SUB TOTAL <?= strtoupper($current_lab) ?> :</td>
                            <td align="center"><?= number_format($sub_m) ?></td>
                            <td align="center"><?= number_format($sub_k) ?></td>
                            <td align="center"><?= number_format($sub_a) ?></td>
                            <td></td>
                        </tr>
                    <?php 
                        $sub_m = 0; $sub_k = 0; $sub_a = 0;
                    endif;

                    if ($current_lab !== $row['nama_lab']): 
                        $current_lab = $row['nama_lab'];
                    ?>
                        <tr>
                            <td colspan="9" class="lab-header"> LAB: <?= strtoupper($current_lab) ?></td>
                        </tr>
                    <?php 
                    endif; 
                    // --- END LOGIKA GROUPING ---

                    $sub_m += (int)$row['masuk']; $sub_k += (int)$row['keluar']; $sub_a += (int)$row['stok_akhir'];
                    $g_m += (int)$row['masuk']; $g_k += (int)$row['keluar']; $g_a += (int)$row['stok_akhir'];
            ?>
                <tr>
                    <td align="center"><?= $no++ ?></td>
                    <td align="center"><b><?= $row['kode'] ?></b></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?: '-' ?></small></td>
                    <td align="center"><?= $row['kondisi'] ?: 'Baik' ?></td>
                    <td align="center" style="color: green;"><?= $row['masuk'] > 0 ? '+'.$row['masuk'] : '0' ?></td>
                    <td align="center" style="color: red;"><?= $row['keluar'] > 0 ? '-'.$row['keluar'] : '0' ?></td>
                    <td align="center" style="font-size: 10pt;"><b><?= $row['stok_akhir'] ?></b></td>
                    <td align="center"><small><?= $row['satuan'] ?></small></td>
                </tr>
            <?php endwhile; ?>
                
                <!-- Cetak Sub Total untuk Lab Terakhir -->
                <tr style="background-color: #fcfcfc; font-weight: bold;">
                    <td colspan="5" align="right">SUB TOTAL <?= strtoupper($current_lab) ?> :</td>
                    <td align="center"><?= number_format($sub_m) ?></td>
                    <td align="center"><?= number_format($sub_k) ?></td>
                    <td align="center"><?= number_format($sub_a) ?></td>
                    <td></td>
                </tr>
                
                <!-- Cetak Grand Total -->
                <tr style="background-color: #e3f2fd; font-weight: bold; font-size: 10pt;">
                    <td colspan="5" align="right">TOTAL KESELURUHAN (GRAND TOTAL) :</td>
                    <td align="center" style="color: green;">+ <?= number_format($g_m) ?></td>
                    <td align="center" style="color: red;">- <?= number_format($g_k) ?></td>
                    <td align="center"><?= number_format($g_a) ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="9" align="center" style="padding: 20px;">Data tidak ditemukan pada filter periode/cakupan ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <table width="100%" style="margin-top: 40px; border: none; font-size: 10pt;">
        <tr>
            <td colspan="2" align="right" style="padding-bottom: 10px; padding-right: 20px;">Makassar, <?= date('d F Y') ?></td>
        </tr>
        <tr valign="top">
            <td width="60%"></td>
            <td width="40%" align="center">
                <p>Mengetahui,<br>Admin Gudang Pusat,</p>
                <div style="height: 70px;"></div>
                <p style="margin-top:15px; margin-bottom: 0;">
                    <b><u><?= strtoupper($nama_admin) ?></u></b>
                </p>
                <p>NIP. <?= $nip_admin ?></p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>