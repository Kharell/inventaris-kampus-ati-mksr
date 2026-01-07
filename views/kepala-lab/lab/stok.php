<?php
session_start();
include "../../../config/database.php";

// 1. Proteksi Akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_lab_user = $_SESSION['id_lab'] ?? ''; 

/**
 * QUERY UTAMA:
 * Mengambil data barang dari tabel distribusi_lab yang sudah DITERIMA.
 * Dijumlahkan (SUM) berdasarkan id_praktek agar barang yang sama tidak muncul berkali-kali.
 */
$sql_stok = "SELECT 
                b.nama_bahan, 
                b.satuan, 
                d.id_praktek,
                SUM(d.jumlah) as total_masuk,
                MAX(d.tanggal_distribusi) as update_terakhir
             FROM distribusi_lab d
             JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
             WHERE d.id_lab = '$id_lab_user' 
             AND d.status = 'diterima'
             GROUP BY d.id_praktek, b.nama_bahan, b.satuan";

$query_stok = mysqli_query($conn, $sql_stok);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Lab Saya | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root { --navy: #001f3f; --bg: #f4f7fa; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .text-navy { color: var(--navy); }
        .stok-badge { font-size: 1.1rem; font-weight: 700; background: #eef2f7; color: var(--navy); padding: 5px 15px; border-radius: 8px; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php if(file_exists("../../../includes/sidebar.php")) include "../../../includes/sidebar.php"; ?>

    <div class="flex-grow-1" style="min-height: 100vh;">
        <?php if(file_exists("../../../includes/header.php")) include "../../../includes/header.php"; ?>

        <main class="p-4">
            <div class="page-header d-flex justify-content-between align-items-center bg-white p-4 shadow-sm rounded-4 border-start border-5 mb-4" style="border-color: var(--navy) !important;">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-primary-subtle p-3 rounded-3 me-4 text-primary shadow-sm">
                        <i class="bi bi-box-seam-fill" style="font-size: 1.8rem; color: var(--navy);"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--navy);">Stok Lab Saya</h4>
                        <p class="text-muted mb-0 small">Total akumulasi barang yang telah Anda konfirmasi diterima</p>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <div class="table-responsive">
                    <table id="tabelStok" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Material / Barang</th>
                                <th class="text-center">Total Stok</th>
                                <th class="text-center">Satuan</th>
                                <th>Update Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_stok) > 0) :
                                while ($row = mysqli_fetch_assoc($query_stok)) : 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?= $row['nama_bahan']; ?></div>
                                    <small class="text-muted text-uppercase">ID Barang: <?= $row['id_praktek']; ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="stok-badge"><?= number_format($row['total_masuk'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="text-center fw-bold text-muted"><?= $row['satuan']; ?></td>
                                <td>
                                    <small><i class="bi bi-clock-history me-1"></i> 
                                    <?= date('d M Y, H:i', strtotime($row['update_terakhir'])); ?>
                                    </small>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/flat/searching-for-data.svg" style="height: 150px;" class="mb-3">
                                    <p class="text-muted">Belum ada stok barang yang tercatat.<br>Silakan lakukan konfirmasi pada menu <b>Konfirmasi Masuk</b>.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelStok').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" }
        });
    });
</script>

</body>
</html>