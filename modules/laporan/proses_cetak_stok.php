<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. Deteksi URL Dinamis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_path = $_SERVER['SCRIPT_NAME']; 
$parts = explode('/', $current_path);
$project_folder = $parts[1]; 
$base_url = $protocol . $host . "/" . $project_folder . "/";
$logo_url = $base_url . "images/images.png";

// 2. TANGKAP PARAMETER
$kategori   = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';
$tgl_awal   = isset($_GET['tgl_awal']) ? mysqli_real_escape_string($conn, $_GET['tgl_awal']) : date('Y-m-01');
$tgl_akhir  = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($conn, $_GET['tgl_akhir']) : date('Y-m-d');
$format     = isset($_GET['format']) ? $_GET['format'] : 'print';

// 3. LOGIKA DOWNLOAD FILE
$filename = "Laporan_Stok_" . date('Ymd');
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
    $status_verifikasi = "Terverifikasi (Input Manual oleh Petugas)";
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
    $status_verifikasi = "Terverifikasi secara Sistem (E-Inventory)";
}

// 5. QUERY DATA
$q_barang = "SELECT b.id_barang as id, b.kode_barang as kode, b.nama_barang as nama, b.kategori as kat, b.satuan, b.stok as stok_akhir, b.tgl_masuk, 
            b.spesifikasi, b.kondisi,
            IFNULL((SELECT SUM(jumlah) FROM distribusi_lab WHERE id_praktek = b.id_barang AND tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0) as keluar,
            IF(b.tgl_masuk BETWEEN '$tgl_awal' AND '$tgl_akhir', b.stok + IFNULL((SELECT SUM(jumlah) FROM distribusi_lab WHERE id_praktek = b.id_barang AND tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0), 0) as masuk
            FROM barang b 
            HAVING (masuk > 0 OR keluar > 0)";

$q_praktek = "SELECT p.id_praktek as id, p.kode_bahan as kode, p.nama_bahan as nama, 'Bahan Praktek' as kat, p.satuan, p.stok as stok_akhir, p.tgl_masuk, 
             p.spesifikasi, p.kondisi,
             IFNULL((SELECT SUM(jumlah) FROM distribusi_lab WHERE id_praktek = p.id_praktek AND tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0) as keluar,
             IF(p.tgl_masuk BETWEEN '$tgl_awal' AND '$tgl_akhir', p.stok + IFNULL((SELECT SUM(jumlah) FROM distribusi_lab WHERE id_praktek = p.id_praktek AND tanggal_distribusi BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0), 0) as masuk
             FROM bahan_praktek p 
             HAVING (masuk > 0 OR keluar > 0)";

if ($kategori === 'semua') {
    $judul_laporan = "LAPORAN REKAPITULASI STOK GABUNGAN";
    $sql = "($q_barang) UNION ALL ($q_praktek) ORDER BY kat ASC, nama ASC";
} else {
    $judul_laporan = "LAPORAN STOK " . strtoupper($kategori);
    $sql = ($kategori === 'praktek') ? $q_praktek . " ORDER BY nama ASC" : $q_barang . " AND b.kategori = '$kategori' ORDER BY nama ASC";
}
$result = mysqli_query($conn, $sql);
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
        
        /* Kop Surat disamakan */
        .kop-table { width: 100%; border: none !important; border-bottom: 3px solid black !important; margin-bottom: 20px; }
        .logo-container { width: 100px; text-align: left; }
        .teks-kop { text-align: center; }
        
        /* Tabel Laporan disamakan */
        .table-laporan { width: 100%; border-collapse: collapse; border: 1px solid black; }
        .table-laporan th, .table-laporan td { border: 1px solid black !important; padding: 5px; vertical-align: middle; }
        .table-laporan th { background-color: #f2f2f2 !important; font-weight: bold; text-align: center; }
        
        .verif-box { border: 1px solid #ddd; padding: 5px; font-size: 7pt; color: #666; font-style: italic; display: inline-block; }
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        
        @media print {
            @page { size: portrait; margin: 0.5cm; } 
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
        <h6 style="text-decoration: underline; margin-bottom: 5px;"><?= $judul_laporan ?></h6>
        <p>Periode: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th rowspan="2" width="30">NO</th>
                <th rowspan="2" width="80">TGL INPUT</th>
                <th rowspan="2" width="90">KODE</th>
                <th rowspan="2">NAMA BARANG</th>
                <th rowspan="2">SPESIFIKASI</th>
                <th rowspan="2" width="70">KONDISI</th>
                <th colspan="2">MUTASI</th>
                <th rowspan="2" width="60">STOK AKHIR</th>
            </tr>
            <tr>
                <th width="60">MASUK</th>
                <th width="60">KELUAR</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; $current_kat = null;
            $sub_m = 0; $sub_k = 0; $sub_a = 0;
            $g_m = 0; $g_k = 0; $g_a = 0;

            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    if ($kategori === 'semua' && $current_kat !== null && $current_kat !== $row['kat']): ?>
                        <tr style="background-color: #f9f9f9; font-weight: bold;">
                            <td colspan="6" align="right">JUMLAH <?= strtoupper($current_kat) ?> :</td>
                            <td align="center"><?= $sub_m ?></td>
                            <td align="center"><?= $sub_k ?></td>
                            <td align="center"><?= $sub_a ?></td>
                        </tr>
                    <?php 
                        $sub_m = 0; $sub_k = 0; $sub_a = 0;
                    endif;

                    $current_kat = $row['kat'];
                    $sub_m += (int)$row['masuk']; $sub_k += (int)$row['keluar']; $sub_a += (int)$row['stok_akhir'];
                    $g_m += (int)$row['masuk']; $g_k += (int)$row['keluar']; $g_a += (int)$row['stok_akhir'];
            ?>
                <tr>
                    <td align="center"><?= $no++ ?></td>
                    <td align="center"><?= date('d/m/y', strtotime($row['tgl_masuk'])) ?></td>
                    <td align="center"><b><?= $row['kode'] ?></b></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?: '-' ?></small></td>
                    <td align="center"><?= $row['kondisi'] ?: 'Baik' ?></td>
                    <td align="center"><?= $row['masuk'] ?></td>
                    <td align="center"><?= $row['keluar'] ?></td>
                    <td align="center"><b><?= $row['stok_akhir'] ?></b></td>
                </tr>
            <?php endwhile; 
                if ($kategori === 'semua'): ?>
                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                        <td colspan="6" align="right">JUMLAH <?= strtoupper($current_kat) ?> :</td>
                        <td align="center"><?= $sub_m ?></td>
                        <td align="center"><?= $sub_k ?></td>
                        <td align="center"><?= $sub_a ?></td>
                    </tr>
                <?php endif; ?>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="6" align="right">TOTAL KESELURUHAN :</td>
                    <td align="center" bgcolor="#e3f2fd"><?= $g_m ?></td>
                    <td align="center" bgcolor="#fffde7"><?= $g_k ?></td>
                    <td align="center" bgcolor="#f1f8e9"><?= $g_a ?></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="9" align="center">Data tidak tersedia pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table width="100%" style="margin-top: 30px; border: none;">
        <tr>
            <td colspan="2" align="right" style="padding-bottom: 10px;">Makassar, <?= date('d F Y') ?></td>
        </tr>
        <tr valign="top">
            <td width="60%"></td>
            <td width="40%" align="center">
                <p>Mengetahui,<br>Petugas Logistik / Gudang,</p>
          
                <div class="verif-box">
                    Ditandatangani secara digital oleh:<br>
                    <b><?= strtoupper($nama_admin) ?></b><br>
                    <?= $status_verifikasi ?>
                </div>
                <p style="margin-top:15px;">
                    <b><u><?= strtoupper($nama_admin) ?></u></b><br>
                    NIP. <?= $nip_admin ?>
                </p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>