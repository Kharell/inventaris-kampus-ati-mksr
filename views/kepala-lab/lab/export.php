<?php
session_start();
include "../../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    exit("Akses Ditolak");
}

// 1. Ambil Parameter
$tipe_data = $_GET['tipe_data'] ?? 'pemakaian';
$periode   = $_GET['periode'] ?? 'bulan';
$waktu     = $_GET['waktu'] ?? date('Y-m'); 
$format    = $_GET['format'] ?? 'pdf';
$id_lab_user = $_SESSION['id_lab'];

// 2. Logika Tanggal
$tanggal_mulai = $waktu . "-01 00:00:00"; 
if ($periode == 'triwulan') {
    $tanggal_selesai = date('Y-m-t 23:59:59', strtotime($waktu . "-01 +2 months"));
    $label_periode = "Triwulan (" . date('M Y', strtotime($tanggal_mulai)) . " - " . date('M Y', strtotime($tanggal_selesai)) . ")";
} elseif ($periode == 'semester') {
    $tanggal_selesai = date('Y-m-t 23:59:59', strtotime($waktu . "-01 +5 months"));
    $label_periode = "Semester (" . date('M Y', strtotime($tanggal_mulai)) . " - " . date('M Y', strtotime($tanggal_selesai)) . ")";
} else {
    $tanggal_selesai = date('Y-m-t 23:59:59', strtotime($waktu . "-01"));
    $label_periode = date('F Y', strtotime($tanggal_mulai));
}

// 3. Query SQL
if ($tipe_data == 'pemakaian') {
    $query = "SELECT p.*, b.nama_bahan, b.satuan, d.kode_distribusi 
              FROM pemakaian_lab p
              JOIN bahan_praktek b ON p.id_praktek = b.id_praktek
              JOIN distribusi_lab d ON p.id_distribusi = d.id_distribusi
              WHERE p.id_lab = '$id_lab_user' 
              AND p.tgl_pakai BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
              ORDER BY p.tgl_pakai ASC";
    $nama_file = "Laporan_Pemakaian_" . str_replace(' ', '_', $label_periode);
} else {
    // Digunakan untuk 'sisa' dan 'gabungan'
    $query = "SELECT 
                d.kode_distribusi, b.nama_bahan, b.satuan, d.jumlah as stok_awal,
                COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0) as total_pakai,
                (d.jumlah - COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0)) as sisa_stok
              FROM distribusi_lab d
              JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
              WHERE d.id_lab = '$id_lab_user' AND d.status = 'diterima'
              ORDER BY d.kode_distribusi ASC";
    $nama_file = ($tipe_data == 'gabungan' ? "Laporan_Gabungan_" : "Laporan_Sisa_Stok_") . date('d_M_Y');
}

$result = mysqli_query($conn, $query);

if ($format == 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$nama_file.xls");
} elseif ($format == 'word') {
    header("Content-type: application/vnd-ms-word");
    header("Content-Disposition: attachment; filename=$nama_file.doc");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; padding: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        .footer-sign { margin-top: 40px; float: right; width: 250px; text-align: center; }
        .space { height: 70px; }
        .text-left { text-align: left; }
        .bg-gray { background-color: #f9f9f9; font-weight: bold; }
    </style>
</head>
<body <?= ($format == 'pdf') ? 'onload="window.print()"' : '' ?>>

    <div class="header">
        <h2>LAPORAN PERTANGGUNGJAWABAN KEPALA LAB</h2>
        <p>Kategori: <strong>
            <?php 
                if($tipe_data == 'pemakaian') echo 'Riwayat Pemakaian Bahan';
                elseif($tipe_data == 'gabungan') echo 'Rekapitulasi Stok & Pakai (Gabungan)';
                else echo 'Sisa Stok Barang';
            ?>
        </strong></p>
        <p>Periode: <strong><?= $label_periode ?></strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <?php if ($tipe_data == 'pemakaian'): ?>
                    <th>Tanggal Pakai</th>
                    <th>Kode Distribusi</th>
                    <th>Nama Bahan</th>
                    <th>Jumlah Pakai</th>
                <?php else: ?>
                    <th>Kode Distribusi</th>
                    <th>Nama Bahan</th>
                    <th>Stok Awal</th>
                    <?php if($tipe_data == 'gabungan'): ?> <th>Total Pakai</th> <?php endif; ?>
                    <th>Sisa Stok</th>
                <?php endif; ?>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $g_awal = 0; $g_pakai = 0; $g_sisa = 0;
            while ($row = mysqli_fetch_assoc($result)): 
                $g_awal += $row['stok_awal'] ?? 0;
                $p_skrg = ($tipe_data == 'pemakaian') ? $row['jumlah_pakai'] : $row['total_pakai'];
                $g_pakai += $p_skrg;
                $g_sisa += $row['sisa_stok'] ?? 0;
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <?php if ($tipe_data == 'pemakaian'): ?>
                        <td><?= date('d/m/Y', strtotime($row['tgl_pakai'])) ?></td>
                        <td><?= $row['kode_distribusi'] ?></td>
                        <td class="text-left"><?= $row['nama_bahan'] ?></td>
                        <td><?= number_format($p_skrg) ?></td>
                    <?php else: ?>
                        <td><?= $row['kode_distribusi'] ?></td>
                        <td class="text-left"><?= $row['nama_bahan'] ?></td>
                        <td><?= number_format($row['stok_awal']) ?></td>
                        <?php if($tipe_data == 'gabungan'): ?>
                            <td style="color: red;"><?= number_format($row['total_pakai']) ?></td>
                        <?php endif; ?>
                        <td><strong><?= number_format($row['sisa_stok']) ?></strong></td>
                    <?php endif; ?>
                    <td><?= $row['satuan'] ?></td>
                </tr>
            <?php endwhile; ?>
            
            <tr class="bg-gray">
                <?php if ($tipe_data == 'pemakaian'): ?>
                    <td colspan="4">TOTAL KESELURUHAN</td>
                    <td><?= number_format($g_pakai) ?></td>
                <?php else: ?>
                    <td colspan="3">TOTAL KESELURUHAN</td>
                    <td><?= number_format($g_awal) ?></td>
                    <?php if($tipe_data == 'gabungan'): ?> <td><?= number_format($g_pakai) ?></td> <?php endif; ?>
                    <td><?= number_format($g_sisa) ?></td>
                <?php endif; ?>
                <td>-</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-sign">
        <p>Makassar, <?= date('d F Y') ?></p>
        <p>Kepala Laboratorium,</p>
        <div class="space"></div>
        <p><strong>( ________________________ )</strong></p>
        <p>NIP. ........................................</p>
    </div>
</body>
</html>