<?php
session_start();
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. Ambil Parameter Filter
$scope       = $_GET['scope'] ?? 'semua';
$id_jurusan   = $_GET['id_jurusan'] ?? '';
$id_lab       = $_GET['id_lab'] ?? '';
$tgl_awal     = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir    = $_GET['tgl_akhir'] ?? date('Y-m-d');
$format       = $_GET['format'] ?? 'print';

// 2. Logika Penandatangan (Mengambil dari Session Login yang sudah diperbaiki)
$opsi_nama    = $_GET['opsi_nama'] ?? 'default';
if ($opsi_nama === 'custom' && !empty($_GET['custom_nama'])) {
    $nama_admin = $_GET['custom_nama'];
    $nip_admin  = $_GET['custom_nip'] ?: "..........................";
    $status_verifikasi = "Terverifikasi (Input Manual)";
} else {
    $nama_admin = $_SESSION['nama_lengkap'] ?: "Administrator";
    $nip_admin  = $_SESSION['nip'] ?: "..........................";
    $status_verifikasi = "Terverifikasi secara Sistem (E-Inventory)";
}

$judul_laporan = "LAPORAN DISTRIBUSI BARANG INVENTARIS";

// 3. Query Data Distribusi
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

// 4. Header untuk Format Excel/Word jika dipilih
if ($format == 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Distribusi.xls");
} elseif ($format == 'word') {
    header("Content-type: application/vnd-ms-word");
    header("Content-Disposition: attachment; filename=Laporan_Distribusi.doc");
}
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
        .logo-container { width: 3cm !important; }
        .logo-container img { width: 2.5cm !important; height: auto; }
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
                <img src="../../images/logo.png" onerror="this.src='https://upload.wikimedia.org/wikipedia/id/0/05/Logo_Politeknik_ATI_Makassar.png'">
            </td>
            <td class="teks-kop">
                <h4>KEMENTERIAN PERINDUSTRIAN REPUBLIK INDONESIA</h4>
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
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d/m/y', strtotime($row['tanggal_distribusi'])) ?></td>
                    <td class="text-center"><b><?= $row['kode_distribusi'] ?></b></td>
                    <td><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td><small><?= $row['spesifikasi'] ?: '-' ?></small></td>
                    <td><?= $row['nama_lab'] ?></td>
                    <td class="text-center"><?= $row['jumlah'] ?></td>
                    <td class="text-center"><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="6" class="text-end">TOTAL BARANG DIDISTRIBUSIKAN :</td>
                    <td class="text-center"><?= $total_qty ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-4">Tidak ada data distribusi barang pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center" style="font-size: 9pt;">
            <p class="mb-0">Makassar, <?= date('d F Y') ?></p>
            <p class="mb-0">Petugas Logistik / Gudang,</p>
            <div style="height: 90px; display: flex; align-items: center; justify-content: center;">
                 <div class="verif-box">
                    <small>Ditandatangani secara digital oleh:</small><br>
                    <b><?= strtoupper($nama_admin) ?></b><br>
                    <small style="color: green;"><?= $status_verifikasi ?></small>
                 </div>
            </div>
            <p class="fw-bold mb-0 text-decoration-underline"><?= strtoupper($nama_admin) ?></p>
            <p>NIP. <?= $nip_admin ?></p>
        </div>
    </div>
</div>

</body>
</html>