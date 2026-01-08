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
 * QUERY STOK:
 * Mengambil data stok awal dari tabel distribusi dan menghitung pengurangan 
 * secara otomatis dari tabel pemakaian_lab.
 */
$sql_stok = "SELECT 
                d.id_distribusi,
                d.kode_distribusi, 
                d.id_praktek,
                d.jumlah as qty_awal,
                b.nama_bahan, 
                b.satuan, 
                d.tanggal_distribusi, 
                -- Menghitung total yang sudah dipakai
                COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0) as total_terpakai,
                -- Sisa Stok = Stok Awal - Total Pakai
                (d.jumlah - COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0)) as sisa_stok
             FROM distribusi_lab d
             JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
             WHERE d.id_lab = '$id_lab_user' 
             AND d.status = 'diterima'
             ORDER BY d.tanggal_distribusi DESC";

$query_stok = mysqli_query($conn, $sql_stok);

if (!$query_stok) {
    die("Gagal mengambil data stok: " . mysqli_error($conn));
}
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --navy: #001f3f; 
            --bg: #f8f9fc; 
            --accent: #4e73df;
        }
        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #2d3436;
        }
        .card { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
        }
        .text-navy { color: var(--navy); }
        .stok-badge { 
            font-size: 1rem; 
            font-weight: 700; 
            padding: 8px 16px; 
            border-radius: 12px; 
            display: inline-block; 
            min-width: 60px;
        }
        /* Color Logic for stock levels */
        .bg-low { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .bg-empty { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .bg-safe { background: #e0f2f1; color: #00695c; border: 1px solid #b2dfdb; }

        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php if(file_exists("../../../includes/sidebar.php")) include "../../../includes/sidebar.php"; ?>

    <div class="flex-grow-1" style="min-height: 100vh;">
        <?php if(file_exists("../../../includes/header.php")) include "../../../includes/header.php"; ?>

        <main class="p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h1 class="fw-bold text-navy mb-1">Stok Lab Saya</h1>
                    <p class="text-muted">Monitoring real-time ketersediaan bahan berdasarkan pemakaian.</p>
                </div>
                <a href="laporan.php" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-printer me-2"></i> Cetak Laporan
                </a>
            </div>

            <div class="card p-4">
                <div class="table-responsive">
                    <table id="tabelStok" class="table table-hover align-middle" style="width:100%">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th width="5%">No</th>
                                <th>Informasi Bahan</th>
                                <th class="text-center">Stok Awal</th>
                                <th class="text-center">Terpakai</th>
                                <th class="text-center">Sisa Stok</th>
                                <th class="text-center">Satuan</th>
                                <th>Tgl Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_stok) > 0) :
                                while ($row = mysqli_fetch_assoc($query_stok)) : 
                                    $sisa = $row['sisa_stok'];
                                    
                                    // Logic penentuan warna
                                    if ($sisa <= 0) {
                                        $status_class = 'bg-empty';
                                        $label = "Habis";
                                    } elseif ($sisa < 5) {
                                        $status_class = 'bg-low';
                                        $label = "Menipis";
                                    } else {
                                        $status_class = 'bg-safe';
                                        $label = "Aman";
                                    }
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-shape bg-light me-3">
                                            <i class="bi bi-box-seam text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-navy"><?= $row['nama_bahan']; ?></div>
                                            <span class="badge bg-light text-muted fw-normal border" style="font-size: 0.7rem;">
                                                <?= $row['kode_distribusi']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-semibold text-muted">
                                    <?= number_format($row['qty_awal'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center text-danger fw-semibold">
                                    - <?= number_format($row['total_terpakai'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <span class="stok-badge <?= $status_class; ?>">
                                        <?= number_format($sisa, 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td class="text-center fw-bold text-muted"><?= $row['satuan']; ?></td>
                                <td>
                                    <small class="text-muted">
                                        <?= !empty($row['tanggal_distribusi']) ? date('d/m/Y', strtotime($row['tanggal_distribusi'])) : '-'; ?>
                                    </small>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            endif; 
                            ?>
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
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            "pageLength": 10,
            "order": [[6, "desc"]],
            "responsive": true
        });
    });
</script>

</body>
</html>