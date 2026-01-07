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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Masuk | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root { --navy: #001f3f; --bg: #f4f7fa; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .text-navy { color: var(--navy); }
        .table thead th { 
            background-color: #f8f9fa; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 1px;
            color: #6c757d;
            border: none;
        }
        .status-pill { font-size: 0.75rem; padding: 6px 12px; border-radius: 30px; font-weight: 600; }
        .bg-proses { background: #fff3cd; color: #856404; }
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
                    <div class="icon-shape bg-warning-subtle p-3 rounded-3 me-4 text-warning shadow-sm">
                        <i class="bi bi-truck" style="font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--navy);">Konfirmasi Barang Masuk</h4>
                        <p class="text-muted mb-0 small">Verifikasi barang yang telah sampai di laboratorium Anda</p>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold text-uppercase mb-0">Antrian Penerimaan</h6>
                </div>

                <div class="table-responsive">
                    <table id="tabelKonfirmasi" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode Distribusi</th>
                                <th>Nama Material</th>
                                <th class="text-center">Jumlah</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sql = "SELECT d.*, b.nama_bahan, b.satuan 
                                    FROM distribusi_lab d 
                                    JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
                                    WHERE d.id_lab = '$id_lab_user' 
                                    AND d.status != 'diterima'
                                    ORDER BY d.id_distribusi DESC";
                            $query = mysqli_query($conn, $sql);

                            while ($row = mysqli_fetch_assoc($query)) : 
                                // Penanganan Tanggal agar tidak error
                                $tgl_kirim = $row['tgl_distribusi'] ?? $row['tgl_permintaan'] ?? $row['tanggal'] ?? null;
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-navy"><?= $row['kode_distribusi'] ?></span>
                                    <div class="smaller text-muted" style="font-size: 0.7rem;">
                                        Dikirim: <?= $tgl_kirim ? date('d/m/Y', strtotime($tgl_kirim)) : '-'; ?>
                                    </div>
                                </td>
                                <td class="fw-bold"><?= $row['nama_bahan'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-3">
                                        <?= $row['jumlah'] ?> <?= $row['satuan'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill bg-proses">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sedang Dikirim
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold" 
                                            onclick="terimaBarang('<?= $row['id_distribusi'] ?>', '<?= addslashes($row['nama_bahan']) ?>')">
                                        <i class="bi bi-check2-circle me-1"></i> Terima
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#tabelKonfirmasi').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" }
        });
    });

    function terimaBarang(id, nama) {
        Swal.fire({
            title: 'Konfirmasi Terima?',
            html: `Apakah barang <b>${nama}</b> sudah sampai di laboratorium Anda?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#001f3f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Sudah Sampai',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    // LOKASI DIARAHKAN KE FOLDER PROSES/TAMBAH.PHP
                    url: '../proses/tambah.php', 
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.trim() === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Barang telah diterima dan tercatat di sistem.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); 
                            });
                        } else {
                            Swal.fire('Gagal!', 'Pesan Error: ' + response, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Tidak dapat menghubungi server.', 'error');
                    }
                });
            }
        });
    }
</script>

</body>
</html>