<?php
session_start();
include "../../config/database.php";

// 1. Proteksi Akses & Ambil ID User dari Session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION['id_user']; // Pastikan session ini berisi ID dari tabel users

// 2. Logika Proses Tombol Terima
if (isset($_POST['proses_konfirmasi'])) {
    $id_dist = $_POST['id_distribusi'];
    $sql_update = "UPDATE distribusi_lab SET status = 'diterima' WHERE id_distribusi = '$id_dist'";
    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Bahan praktek berhasil diterima!'); window.location='konfirmasi.php';</script>";
    }
}

// 3. Query Ambil Data (Sesuai Gambar: distribusi_lab -> bahan_praktek -> kepala_lab)
$sql_tabel = "SELECT d.*, bp.nama_bahan, bp.satuan 
              FROM distribusi_lab d
              JOIN bahan_praktek bp ON d.id_praktek = bp.id_praktek
              JOIN kepala_lab k ON d.id_lab = k.id_lab
              WHERE k.id_kepala = '$id_user' AND d.status = 'dikirim'";

$query = mysqli_query($conn, $sql_tabel);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Masuk | Inventory Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --navy-primary: #001f3f;
            --bg-body: #f4f7fa;
        }

        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; }
        .wrapper { display: flex; width: 100%; }

        /* Sidebar Style */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--navy-primary);
            min-height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 1000;
        }

        .content-area {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Banner Tanpa Kuning */
        .page-banner {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-left: 5px solid var(--navy-primary);
            margin-bottom: 25px;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .btn-navy {
            background: var(--navy-primary);
            color: white; border: none; font-weight: 600;
        }
        .btn-navy:hover { background: #003366; color: white; }

        @media (max-width: 992px) {
            .sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            .content-area { width: 100%; margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="content-area">
        <?php include "../../includes/header.php"; ?>

        <main class="p-4">
            <div class="page-banner">
                <h3 class="fw-bold mb-1" style="color: var(--navy-primary);">Konfirmasi Pengiriman</h3>
                <p class="text-muted mb-0">Daftar bahan praktikum yang sedang dikirim oleh Admin ke Lab Anda.</p>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Distribusi</th>
                                <th>Nama Bahan</th>
                                <th class="text-center">Jumlah</th>
                                <th>Satuan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?= $row['kode_distribusi']; ?></td>
                                    <td class="fw-bold fs-5"><?= $row['nama_bahan']; ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-navy border px-3 py-2" style="color: var(--navy-primary);">
                                            <?= $row['jumlah']; ?>
                                        </span>
                                    </td>
                                    <td><?= $row['satuan']; ?></td>
                                    <td class="text-center">
                                        <form method="POST">
                                            <input type="hidden" name="id_distribusi" value="<?= $row['id_distribusi']; ?>">
                                            <button type="submit" name="proses_konfirmasi" class="btn btn-navy btn-sm px-4" onclick="return confirm('Konfirmasi bahwa bahan telah sampai di Lab?')">
                                                <i class="bi bi-check2-circle me-1"></i> Terima
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-muted"></i>
                                        <p class="text-muted">Data kosong. Pastikan Admin sudah mengirim bahan dan statusnya 'dikirim'.</p>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>