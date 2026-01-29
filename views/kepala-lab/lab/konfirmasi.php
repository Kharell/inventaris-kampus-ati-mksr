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
        .bg-ditolak { background: #f8d7da; color: #842029; } /* Style Baru untuk Ditolak */
        .row-rejected { background-color: #fff5f5 !important; } /* Highlight baris yang ditolak */
        .text-spek { font-size: 0.8rem; color: #6c757d; display: block; margin-top: 2px; }
        .badge-kondisi { font-size: 0.7rem; padding: 3px 8px; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../../includes/header.php"; ?>
        <main class="p-4" style="margin-top: 20px;">
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
                                <th>Spek & Kondisi</th> 
                                <th class="text-center">Jumlah</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Query: Tetap panggil yang statusnya 'ditolak', tapi sembunyikan yang sudah 'diterima'
                            $sql = "SELECT d.*, b.nama_bahan, b.satuan 
                                    FROM distribusi_lab d 
                                    JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
                                    WHERE d.id_lab = '$id_lab_user' 
                                    AND d.status != 'diterima'
                                    ORDER BY d.id_distribusi DESC";
                            $query = mysqli_query($conn, $sql);

                            while ($row = mysqli_fetch_assoc($query)) : 
                                $is_rejected = ($row['status'] == 'ditolak');
                                $tgl_kirim = $row['tanggal_distribusi'] ?? null;
                                $kon = $row['kondisi'] ?? 'Baik';
                                $kon_class = ($kon == 'Baik') ? 'bg-success' : (($kon == 'Rusak') ? 'bg-danger' : 'bg-warning text-dark');
                            ?>
                            <tr class="<?= $is_rejected ? 'row-rejected' : '' ?>">
                                <td>
                                    <span class="fw-bold text-navy"><?= htmlspecialchars($row['kode_distribusi']) ?></span>
                                    <div class="smaller text-muted" style="font-size: 0.7rem;">
                                        Dikirim: <?= $tgl_kirim ? date('d/m/Y', strtotime($tgl_kirim)) : '-'; ?>
                                    </div>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_bahan']) ?></td>
                                <td>
                                    <span class="text-spek"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($row['spesifikasi'] ?? '-') ?></span>
                                    <span class="badge badge-kondisi <?= $kon_class ?> mt-1">
                                        <?= htmlspecialchars($kon) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-3">
                                        <?= $row['jumlah'] ?> <?= htmlspecialchars($row['satuan']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($is_rejected): ?>
                                        <span class="status-pill bg-ditolak" title="<?= htmlspecialchars($row['keterangan'] ?? '') ?>">
                                            <i class="bi bi-x-circle me-1"></i> Penolakan Dikirim
                                        </span>
                                        <small class="d-block text-danger mt-1" style="font-size: 0.65rem;">
                                            Alasan: <?= htmlspecialchars($row['keterangan'] ?? '-') ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="status-pill bg-proses">
                                            <i class="bi bi-arrow-repeat me-1"></i> Sedang Dikirim
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if (!$is_rejected): ?>
                                            <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold" 
                                                    onclick="terimaBarang('<?= $row['id_distribusi'] ?>', '<?= addslashes($row['nama_bahan']) ?>')">
                                                <i class="bi bi-check2-circle me-1"></i> Terima
                                            </button>
                                            <button class="btn btn-sm btn-danger rounded-pill px-3 fw-bold" 
                                                    onclick="tolakBarang('<?= $row['id_distribusi'] ?>', '<?= addslashes($row['nama_bahan']) ?>')">
                                                <i class="bi bi-x-circle me-1"></i> Tolak
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small italic">Sudah Diproses</span>
                                        <?php endif; ?>
                                    </div>
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
                    url: '../proses/tambah.php', 
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.trim() === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Diterima!',
                                text: 'Barang otomatis masuk ke inventaris lab.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => { location.reload(); });
                        } else {
                            Swal.fire('Gagal!', response, 'error');
                        }
                    }
                });
            }
        });
    }

    function tolakBarang(id, nama) {
        Swal.fire({
            title: 'Penolakan Barang',
            html: `
                <div class="text-start">
                    <p class="small text-muted mb-3">Mengapa barang <b>${nama}</b> ditolak?</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="alasanTolak" id="alas1" value="Spesifikasi Tidak Sesuai" checked>
                        <label class="form-check-label" for="alas1">Spesifikasi Tidak Sesuai</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="alasanTolak" id="alas2" value="Kondisi Barang Rusak/Cacat">
                        <label class="form-check-label" for="alas2">Kondisi Barang Rusak/Cacat</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="alasanTolak" id="alas3" value="Jumlah Tidak Sesuai">
                        <label class="form-check-label" for="alas3">Jumlah Tidak Sesuai</label>
                    </div>
                    <textarea id="catatanTambahan" class="form-control mt-2" placeholder="Catatan tambahan (opsional)..." rows="2"></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Konfirmasi Tolak',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const alasanPilihan = document.querySelector('input[name="alasanTolak"]:checked').value;
                const catatan = document.getElementById('catatanTambahan').value;
                return { alasan: alasanPilihan, catatan: catatan };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const dataFinal = result.value.alasan + (result.value.catatan ? " - " + result.value.catatan : "");
                $.ajax({
                    url: '../proses/tambah.php',
                    type: 'POST',
                    data: { aksi: 'tolak', id: id, keterangan: dataFinal },
                    success: function(response) {
                        if (response.trim() === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Penolakan Dikirim',
                                text: 'Status penolakan telah tercatat.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => { location.reload(); });
                        } else {
                            Swal.fire('Gagal!', response, 'error');
                        }
                    }
                });
            }
        });
    }
</script>

</body>
</html>