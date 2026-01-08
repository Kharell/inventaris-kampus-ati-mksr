<?php
session_start();
include "../../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../../login.php");
    exit;
}

$id_lab_user = $_SESSION['id_lab'] ?? '';

// 1. Query Opsi Barang (Hanya yang sisa stoknya > 0)
$sql_opsi = "SELECT 
                d.id_distribusi, 
                d.kode_distribusi, 
                d.id_praktek, 
                d.jumlah as qty_awal,
                b.nama_bahan, 
                b.satuan,
                (d.jumlah - COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0)) as sisa_stok
             FROM distribusi_lab d
             JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
             WHERE d.id_lab = '$id_lab_user' AND d.status = 'diterima'
             HAVING sisa_stok > 0";
$query_opsi = mysqli_query($conn, $sql_opsi);

// 2. Query Riwayat Pemakaian
$sql_history = "SELECT p.*, b.nama_bahan, b.satuan, d.kode_distribusi 
                FROM pemakaian_lab p 
                JOIN bahan_praktek b ON p.id_praktek = b.id_praktek 
                JOIN distribusi_lab d ON p.id_distribusi = d.id_distribusi
                WHERE p.id_lab = '$id_lab_user' 
                ORDER BY p.tgl_pakai DESC";
$query_history = mysqli_query($conn, $sql_history);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lapor Pemakaian | Inventory Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        :root { --navy: #001f3f; --bg: #f4f7fa; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .btn-navy { background: var(--navy); color: white; border-radius: 10px; transition: 0.3s; }
        .btn-navy:hover { background: #003366; color: white; transform: translateY(-2px); }
        .badge-kode { background: #e9ecef; color: #495057; border: 1px solid #dee2e6; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="flex-grow-1" style="min-height: 100vh;">
        <?php include "../../../includes/header.php"; ?>

        <main class="p-4">
            <div class="page-header d-flex justify-content-between align-items-center bg-white p-4 shadow-sm rounded-4 border-start border-5 mb-4" style="border-color: var(--navy) !important;">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-danger-subtle p-3 rounded-3 me-4 text-danger">
                        <i class="bi bi-box-arrow-right" style="font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--navy);">Lapor Pemakaian</h4>
                        <p class="text-muted mb-0 small">Gunakan bahan berdasarkan sisa stok yang tersedia</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card p-4 h-100">
                        <h6 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2"></i>Tambah Pemakaian</h6>
                        <form action="../proses/tambah.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Pilih Referensi (Kode Distribusi)</label>
                                <select name="id_distribusi" id="pilih_barang" class="form-select shadow-sm" required>
                                    <option value="">-- Cari Kode Distribusi --</option>
                                    <?php while($opt = mysqli_fetch_assoc($query_opsi)): ?>
                                        <option value="<?= $opt['id_distribusi'] ?>" data-stok="<?= $opt['sisa_stok'] ?>">
                                            <?= $opt['kode_distribusi'] ?> - <?= $opt['nama_bahan'] ?> (Sisa: <?= $opt['sisa_stok'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Jumlah Dipakai</label>
                                <div class="input-group">
                                    <input type="number" name="jumlah_pakai" id="input_jumlah" class="form-control shadow-sm" min="1" placeholder="Masukkan jumlah..." required>
                                    <span class="input-group-text bg-light small" id="label_satuan">Unit</span>
                                </div>
                                <div id="stok_help" class="form-text text-danger d-none">Jumlah melebihi stok tersedia!</div>
                            </div>
                            <button type="submit" name="lapor_pakai" id="btn_submit" class="btn btn-navy w-100 py-2 fw-bold shadow-sm">
                                <i class="bi bi-send-fill me-2"></i>Kirim Laporan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru</h6>
                        <div class="table-responsive">
                            <table id="tabelPakai" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Waktu</th>
                                        <th class="small">Kode Dist</th>
                                        <th class="small">Barang</th>
                                        <th class="small text-center">Jumlah</th>
                                        <th class="small text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($h = mysqli_fetch_assoc($query_history)): ?>
                                    <tr>
                                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($h['tgl_pakai'])) ?></td>
                                        <td><span class="badge badge-kode small"><?= $h['kode_distribusi'] ?></span></td>
                                        <td class="small fw-bold text-navy"><?= $h['nama_bahan'] ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary fw-bold px-3">
                                                <?= $h['jumlah_pakai'] ?> <?= $h['satuan'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="hapusData(<?= $h['id_pemakaian'] ?>)" class="btn btn-sm btn-outline-danger border-0 rounded-circle">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#tabelPakai').DataTable({
        "pageLength": 5,
        "order": [[0, "desc"]],
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" }
    });

    // Validasi Real-time saat pilih barang
    $('#pilih_barang').on('change', function() {
        const stokTersedia = $(this).find(':selected').data('stok');
        $('#input_jumlah').attr('max', stokTersedia);
        validasiInput();
    });

    // Validasi Real-time saat ngetik angka
    $('#input_jumlah').on('input', function() {
        validasiInput();
    });

    function validasiInput() {
        const inputVal = parseInt($('#input_jumlah').val());
        const maxVal = parseInt($('#input_jumlah').attr('max'));

        if (inputVal > maxVal) {
            $('#stok_help').removeClass('d-none');
            $('#btn_submit').attr('disabled', true);
        } else {
            $('#stok_help').addClass('d-none');
            $('#btn_submit').attr('disabled', false);
        }
    }
});

function hapusData(id) {
    Swal.fire({
        title: 'Batalkan pemakaian?',
        text: "Stok akan dikembalikan ke kode distribusi tersebut.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#001f3f',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "../proses/tambah.php?hapus_pakai=" + id;
        }
    })
}
</script>
</body>
</html>