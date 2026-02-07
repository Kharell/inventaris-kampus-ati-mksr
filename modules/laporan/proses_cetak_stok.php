<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE); 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. TANGKAP PARAMETER
$kategori   = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';
$tgl_awal   = isset($_GET['tgl_awal']) ? mysqli_real_escape_string($conn, $_GET['tgl_awal']) : date('Y-m-01');
$tgl_akhir  = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($conn, $_GET['tgl_akhir']) : date('Y-m-d');
$format     = isset($_GET['format']) ? $_GET['format'] : 'print';

// --- TAMBAHAN FITUR CUSTOM NAMA & NIP ---
$opsi_nama   = isset($_GET['opsi_nama']) ? $_GET['opsi_nama'] : 'default';
$custom_nama = isset($_GET['custom_nama']) ? $_GET['custom_nama'] : '';
$custom_nip  = isset($_GET['custom_nip']) ? $_GET['custom_nip'] : '';
// ----------------------------------------

// 2. LOGIKA EXCEL & WORD
if ($format === 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Stok_" . date('d-m-Y') . ".xls");
} elseif ($format === 'word') {
    header("Content-type: application/vnd-ms-word");
    header("Content-Disposition: attachment; filename=Laporan_Stok_" . date('d-m-Y') . ".doc");
}

// 2. DATA ADMIN
$id_user_session = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : (isset($_SESSION['id_admin']) ? $_SESSION['id_admin'] : null);
$admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id_user = '$id_user_session'");
$admin_data  = mysqli_fetch_assoc($admin_query);

if (!$admin_data) {
    $admin_query = mysqli_query($conn, "SELECT nama_lengkap, nip FROM users LIMIT 1");
    $admin_data  = mysqli_fetch_assoc($admin_query);
}

// --- LOGIKA PENENTUAN NAMA PENANDATANGAN & STATUS VERIFIKASI ---
if ($opsi_nama === 'custom' && !empty($custom_nama)) {
    $nama_admin = $custom_nama;
    $nip_admin  = !empty($custom_nip) ? $custom_nip : "..........................";
    $status_verifikasi = "Terverifikasi (Input Manual oleh Kepala Lab)";
} else {
    $nama_admin  = !empty($admin_data['nama_lengkap']) ? $admin_data['nama_lengkap'] : "Administrator";
    $nip_admin   = !empty($admin_data['nip']) ? $admin_data['nip'] : "..........................";
    $status_verifikasi = "Terverifikasi secara Sistem (Admin)";
}
// --------------------------------------------

// 3. LOGIKA QUERY (Menambahkan spesifikasi dan kondisi)
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
    if ($kategori === 'praktek') {
        $sql = $q_praktek . " ORDER BY nama ASC";
    } else {
        $sql = $q_barang . " AND b.kategori = '$kategori' ORDER BY nama ASC";
    }
}

$result = mysqli_query($conn, $sql);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - <?= $judul_laporan ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: Arial, sans-serif; color: black; line-height: 1.2; }
        .container-print { 
            background: white; padding: 1cm; width: 98%; max-width: 1200px;
            margin: 20px auto; min-height: 297mm; box-shadow: 0 0 15px rgba(0,0,0,0.1); 
        }
        .kop-table { width: 100%; border: none !important; }
        .logo-container { width: 4cm !important; }
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
        .row-subtotal { background-color: #f9f9f9 !important; font-weight: bold; }
        .row-grandtotal { background-color: #000 !important; color: #fff !important; font-weight: bold; }

        /* Style Verifikasi Digital */
        .verif-box {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 7pt;
            color: #666;
            display: inline-block;
            margin-top: 5px;
            font-style: italic;
        }
        
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; gap: 10px; }
        @media print {
            @page { size: portrait; margin: 0.5cm; } 
            .no-print { display: none !important; } 
            body { background: white; margin: 0; padding: 0.5cm; }
            .container-print { width: 100%; box-shadow: none; margin: 0; padding: 0; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
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
        <h5 class="text-decoration-underline fw-bold mb-1"><?= $judul_laporan ?></h5>
        <p style="font-size: 9pt;">Periode: <b><?= date('d/m/Y', strtotime($tgl_awal)) ?></b> s/d <b><?= date('d/m/Y', strtotime($tgl_akhir)) ?></b></p>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th rowspan="2" width="30">NO</th>
                <th rowspan="2" width="60">TGL INPUT</th>
                <th rowspan="2" width="70">KODE</th>
                <th rowspan="2">NAMA BARANG</th>
                <th rowspan="2" width="">SPESIFIKASI</th>
                <th rowspan="2" width="60">KONDISI</th>
                <th colspan="2" width="150">MUTASI</th>
                <th rowspan="2" width="50">STOK AKHIR</th>
            </tr>
            <tr>
                <th>MASUK</th>
                <th>KELUAR</th>
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
                        <tr class="row-subtotal">
                            <td colspan="6" class="text-end">JUMLAH <?= strtoupper($current_kat) ?> :</td>
                            <td class="text-center"><?= $sub_m ?></td>
                            <td class="text-center"><?= $sub_k ?></td>
                            <td class="text-center"><?= $sub_a ?></td>
                        </tr>
                    <?php 
                        $sub_m = 0; $sub_k = 0; $sub_a = 0;
                    endif;

                    $current_kat = $row['kat'];
                    $sub_m += (int)$row['masuk']; $sub_k += (int)$row['keluar']; $sub_a += (int)$row['stok_akhir'];
                    $g_m += (int)$row['masuk']; $g_k += (int)$row['keluar']; $g_a += (int)$row['stok_akhir'];
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d/m/y', strtotime($row['tgl_masuk'])) ?></td>
                    <td class="text-center"><b><?= $row['kode'] ?></b></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?: '-' ?></small></td>
                    <td class="text-center"><?= $row['kondisi'] ?: 'Baik' ?></td>
                    <td class="text-center"><?= $row['masuk'] ?></td>
                    <td class="text-center"><?= $row['keluar'] ?></td>
                    <td class="text-center"><b><?= $row['stok_akhir'] ?></b></td>
                </tr>
            <?php endwhile; 
                if ($kategori === 'semua'): ?>
                    <tr class="row-subtotal">
                        <td colspan="6" class="text-end">JUMLAH <?= strtoupper($current_kat) ?> :</td>
                        <td class="text-center"><?= $sub_m ?></td>
                        <td class="text-center"><?= $sub_k ?></td>
                        <td class="text-center"><?= $sub_a ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="row-grandtotal">
                    <td colspan="6" class="text-end">TOTAL KESELURUHAN :</td>
                    <td class="text-center"><?= $g_m ?></td>
                    <td class="text-center"><?= $g_k ?></td>
                    <td class="text-center"><?= $g_a ?></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="9" class="text-center py-4">Tidak ada mutasi barang pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center" style="font-size: 9pt;">
            <p class="mb-0">Makassar, <?= date('d F Y') ?></p>
            <p class="mb-0">Petugas Logistik / Gudang,</p>
            <div style="height: 80px; display: flex; align-items: center; justify-content: center;">
                 <div class="verif-box">
                    <small>Ditandatangani secara digital oleh:</small><br>
                    <b><?= $nama_admin ?></b><br>
                    <small><?= $status_verifikasi ?></small>
                 </div>
            </div>
            <p class="fw-bold mb-0 text-decoration-underline"><?= strtoupper($nama_admin) ?></p>
            <p>NIP. <?= $nip_admin ?></p>
        </div>
    </div>
</div>

</body>
</html>