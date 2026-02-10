<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. Deteksi Protokol (http atau https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

// 2. Deteksi Host (misal: localhost atau domain.com)
$host = $_SERVER['HTTP_HOST'];

// 3. Deteksi Folder Proyek secara dinamis
// Kita mengambil path script saat ini dan membuang nama filenya
$current_path = $_SERVER['SCRIPT_NAME']; // Hasil: /folder_proyek/modules/laporan/export.php
$parts = explode('/', $current_path);
$project_folder = $parts[1]; // Mengambil bagian pertama setelah slash pertama (nama folder proyek)

// 4. Gabungkan menjadi Base URL
$base_url = $protocol . $host . "/" . $project_folder . "/";

// 5. Link Logo Final (Sesuaikan dengan folder image Anda dari root proyek)
$logo_url = $base_url . "images/images.png";



// 1. TANGKAP PARAMETER
$scope         = $_GET['scope'] ?? 'semua';
$id_jurusan    = $_GET['id_jurusan'] ?? '';
$id_lab        = $_GET['id_lab'] ?? '';
$tgl_awal      = isset($_GET['tgl_awal']) ? mysqli_real_escape_string($conn, $_GET['tgl_awal']) : date('Y-m-01');
$tgl_akhir     = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($conn, $_GET['tgl_akhir']) : date('Y-m-d');
$format        = $_GET['format'] ?? 'pdf';

// 2. LOGIKA DOWNLOAD FILE
$filename = "Laporan_Pemakaian_" . date('Ymd');
if ($format === 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$filename.xls");
} elseif ($format === 'word') {
    header("Content-type: application/vnd-ms-word");
    header("Content-Disposition: attachment; filename=$filename.doc");
}

// 3. LOGIKA PENANDATANGAN (DISEMPURNAKAN)
$opsi_nama    = $_GET['opsi_nama'] ?? 'default';
$custom_nama  = $_GET['custom_nama'] ?? '';
$custom_nip   = $_GET['custom_nip'] ?? '';

if ($opsi_nama === 'custom' && !empty($custom_nama)) {
    $nama_admin = $custom_nama;
    $nip_admin  = $custom_nip ?: "..........................";
    $status_verifikasi = "Terverifikasi (Input Manual oleh Petugas)";
} else {
    // Mengambil data dari session yang di-set saat login
    // Jika nama_lengkap di session kosong, baru ambil dari kolom 'nama' atau 'username'
    $nama_admin = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? "Administrator";
    $nip_admin  = $_SESSION['nip'] ?? "..........................";
    
    // Jika data di session ternyata belum ada (karena belum relogin), ambil paksa dari DB berdasarkan ID session
    if ($nama_admin == "Administrator" || $nip_admin == "..........................") {
        $id_user_session = $_SESSION['id_user'] ?? 1;
        $admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id_admin = '$id_user_session' OR id_user = '$id_user_session'");
        $admin_data  = mysqli_fetch_assoc($admin_query);
        if ($admin_data) {
            $nama_admin = $admin_data['nama_lengkap'] ?: $nama_admin;
            $nip_admin  = $admin_data['nip'] ?: $nip_admin;
        }
    }
    $status_verifikasi = "Terverifikasi secara Sistem (E-Inventory)";
}

// 4. QUERY DATA
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

if ($scope == 'jurusan' && !empty($id_jurusan)) {
    $query .= " AND j.id_jurusan = '$id_jurusan'";
    $res_j = mysqli_query($conn, "SELECT nama_jurusan FROM jurusan WHERE id_jurusan = '$id_jurusan'");
    $title_suffix = "JURUSAN " . strtoupper(mysqli_fetch_assoc($res_j)['nama_jurusan']);
} elseif ($scope == 'lab' && !empty($id_lab)) {
    $query .= " AND l.id_lab = '$id_lab'";
    $res_l = mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab = '$id_lab'");
    $title_suffix = " " . strtoupper(mysqli_fetch_assoc($res_l)['nama_lab']);
    
    $kepala_query = mysqli_query($conn, "SELECT k.nama_kepala, k.nip, l.nama_lab FROM kepala_lab k JOIN lab l ON k.id_lab = l.id_lab WHERE k.id_lab = '$id_lab'");
    $k_data = mysqli_fetch_assoc($kepala_query);
    if ($k_data) {
        $show_double_ttd = true;
        $nama_kepala = $k_data['nama_kepala']; 
        $nip_kepala = $k_data['nip']; 
        $jabatan_kepala = "Kepala " . $k_data['nama_lab'];
    }
}

$query .= " ORDER BY p.tgl_pakai ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemakaian Bahan</title>
    <?php if ($format === 'pdf' || $format === 'print'): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    <style>
        body { background: <?= ($format === 'pdf' || $format === 'print') ? '#f4f7f6' : '#fff' ?>; font-family: Arial, sans-serif; color: black; line-height: 1.2; font-size: 9pt; }
        .container-print { background: white; padding: 1cm; width: 99%; margin: auto; }
        
        .kop-table { width: 100%; border: none !important; border-bottom: 3px solid black !important; margin-bottom: 20px; }
        .logo-container { width: 100px; text-align: left; }
        .teks-kop { text-align: center; }
        
        .table-laporan { width: 100%; border-collapse: collapse; border: 1px solid black; }
        .table-laporan th, .table-laporan td { border: 1px solid black !important; padding: 5px; vertical-align: middle; }
        .table-laporan th { background-color: #f2f2f2 !important; font-weight: bold; text-align: center; }
        
        .verif-box { border: 1px solid #ddd; padding: 5px; font-size: 7pt; color: #666; font-style: italic; display: inline-block; }
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
        <h6 style="text-decoration: underline; margin-bottom: 5px;">LAPORAN PEMAKAIAN BAHAN PRAKTIKUM</h6>
        <h6 style="margin:0;"><?= $title_suffix ?></h6>
        <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>TGL PAKAI</th>
                <th>KODE</th>
                <th width="200">NAMA BAHAN</th>
                <th>SPESIFIKASI</th>
                <th>KONDISI</th>
                <th>AWAL</th>
                <th>PAKAI</th>
                <th>SISA</th>
                <th>SATUAN</th>
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
                    <td align="center"><?= $no++ ?></td>
                    <td align="center"><?= date('d/m/y', strtotime($row['tgl_pakai'])) ?></td>
                    <td align="center"><b><?= $row['kode_distribusi'] ?? '-' ?></b></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?></small></td>
                    <td align="center"><?= $row['kondisi'] ?></td>
                    <td align="center" bgcolor="#e3f2fd"><b><?= $row['stok_awal'] ?></b></td>
                    <td align="center" bgcolor="#fffde7"><b><?= $row['jumlah_pakai'] ?></b></td>
                    <td align="center" bgcolor="#f1f8e9"><b><?= $row['sisa_stok'] ?></b></td>
                    <td align="center"><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="6" align="right">TOTAL REKAPITULASI :</td>
                    <td align="center" bgcolor="#e3f2fd"><?= $t_awal ?></td>
                    <td align="center" bgcolor="#fffde7"><?= $t_pakai ?></td>
                    <td align="center" bgcolor="#f1f8e9"><?= $t_sisa ?></td>
                    <td align="center">UNIT</td>
                </tr>
            <?php else: ?>
                <tr><td colspan="10" align="center">Data tidak tersedia pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table width="100%" style="margin-top: 30px; border: none;">
        <tr>
            <td colspan="2" align="right" style="padding-bottom: 10px;">Makassar, <?= date('d F Y') ?></td>
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
                    <div style="height: 60px;"></div>
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

</body>
</html>