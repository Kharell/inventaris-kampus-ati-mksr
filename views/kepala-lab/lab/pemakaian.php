<?php
session_start();
include "../../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../../login.php");
    exit;
}

$id_lab_user = $_SESSION['id_lab'] ?? '';

// 1. Query Opsi Barang
$sql_opsi = "SELECT 
                d.id_distribusi, 
                d.kode_distribusi, 
                d.id_praktek, 
                d.jumlah as qty_awal,
                d.spesifikasi, 
                d.kondisi,
                b.nama_bahan, 
                b.satuan,
                (d.jumlah - COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0)) as sisa_stok
             FROM distribusi_lab d
             JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
             WHERE d.id_lab = '$id_lab_user' AND d.status = 'diterima'
             HAVING sisa_stok > 0";
$query_opsi = mysqli_query($conn, $sql_opsi);

// 2. Query Riwayat Pemakaian
$sql_history = "SELECT p.*, b.nama_bahan, b.satuan, d.kode_distribusi, d.spesifikasi, d.kondisi
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor Pemakaian | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root { 
            --navy: #001f3f; 
            --bg: #f4f7fa; 
            --sidebar-width: 260px;
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Inter', sans-serif; 
            overflow-x: hidden; 
        }

        /* --- LAYOUT STRUCTURE FIX --- */
        .wrapper { 
            display: flex; 
            width: 100%; 
            align-items: stretch; 
        }

        .main-content { 
            flex: 1; 
            min-height: 100vh; 
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
        }

        /* Desktop Adjustment */
        @media (min-width: 992px) {
            .main-content { margin-left: var(--sidebar-width); }
        }

        /* Mobile Adjustment */
        @media (max-width: 991px) {
            .main-content { margin-left: 0; }
            .page-header { margin-top: 15px; }
        }

        /* --- UI COMPONENTS --- */
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); 
        }
        
        .btn-navy { 
            background: var(--navy); 
            color: white; 
            border-radius: 10px; 
            transition: 0.3s; 
            border: none;
        }
        
        .btn-navy:hover { 
            background: #003366; 
            color: white; 
            transform: translateY(-2px); 
        }

        .badge-kode { 
            background: #f1f5f9; 
            color: #475569; 
            border: 1px solid #e2e8f0; 
            font-size: 0.7rem; 
            font-weight: 600;
        }

        .icon-shape {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="main-content">
        <?php include "../../../includes/header.php"; ?>

        <main class="p-3 p-md-4"style="margin-top: 70px;">
            
            <div class="page-header d-flex justify-content-between align-items-center bg-white p-4 shadow-sm rounded-4 border-start border-5 mb-4" style="border-color: var(--navy) !important;">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-danger-subtle p-3 rounded-3 me-3 me-md-4 text-danger d-none d-sm-flex">
                        <i class="bi bi-box-arrow-right" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--navy);">Lapor Pemakaian</h4>
                        <p class="text-muted mb-0 small">Input penggunaan bahan laboratorium</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2"></i>Tambah Pemakaian</h6>
                        <form action="../proses/tambah.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Referensi Kode Distribusi</label>
                                <select name="id_distribusi" id="pilih_barang" class="form-select shadow-sm" required>
                                    <option value="">-- Pilih Kode --</option>
                                    <?php while($opt = mysqli_fetch_assoc($query_opsi)): ?>
                                        <option value="<?= $opt['id_distribusi'] ?>" 
                                                data-stok="<?= $opt['sisa_stok'] ?>" 
                                                data-satuan="<?= $opt['satuan'] ?>">
                                            <?= $opt['kode_distribusi'] ?> - <?= $opt['nama_bahan'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Jumlah Dipakai</label>
                                <div class="input-group">
                                    <input type="number" name="jumlah_pakai" id="input_jumlah" class="form-control shadow-sm" min="1" placeholder="0" required>
                                    <span class="input-group-text bg-light small" id="label_satuan">Unit</span>
                                </div>
                                <div id="stok_help" class="form-text text-danger d-none small mt-2">
                                    <i class="bi bi-exclamation-circle me-1"></i> Jumlah melebihi sisa stok!
                                </div>
                            </div>
                            <button type="submit" name="lapor_pakai" id="btn_submit" class="btn btn-navy w-100 py-2 fw-bold shadow-sm">
                                <i class="bi bi-send-check-fill me-2"></i>Kirim Laporan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru</h6>
                        <div class="table-responsive">
                            <table id="tabelPakai" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Waktu</th>
                                        <th class="small">Item</th>
                                        <th class="small">Spesifikasi</th>
                                        <th class="small">Kondisi</th>
                                        <th class="small text-center">Jumlah</th>
                                        <th class="small text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($h = mysqli_fetch_assoc($query_history)): 
                                        $kondisi_badge = 'bg-secondary';
                                        if($h['kondisi'] == 'Baik') $kondisi_badge = 'bg-success';
                                        if($h['kondisi'] == 'Rusak') $kondisi_badge = 'bg-danger';
                                    ?>
                                    <tr>
                                        <td class="small text-muted"><?= date('d/m/y H:i', strtotime($h['tgl_pakai'])) ?></td>
                                        <td>
                                            <div class="fw-bold text-navy small"><?= $h['nama_bahan'] ?></div>
                                            <span class="badge badge-kode"><?= $h['kode_distribusi'] ?></span>
                                        </td>
                                        <td>
                                            <div class="text-navy small"><?= $h['spesifikasi'] ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy small"><?= $h['kondisi'] ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary fw-bold">
                                                <?= $h['jumlah_pakai'] ?> <?= $h['satuan'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="hapusData(<?= $h['id_pemakaian'] ?>)" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="bi bi-trash3"></i>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#tabelPakai').DataTable({
        "pageLength": 5,
        "order": [[0, "desc"]],
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
        "dom": '<"row"<"col-sm-12"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    // Handle Selection Change
    $('#pilih_barang').on('change', function() {
        const selected = $(this).find(':selected');
        const stokMax = selected.data('stok');
        const satuan = selected.data('satuan');
        
        $('#input_jumlah').attr('max', stokMax);
        $('#label_satuan').text(satuan || 'Unit');
        validateStock();
    });

    // Handle Input Validation
    $('#input_jumlah').on('input', validateStock);

    function validateStock() {
        const val = parseInt($('#input_jumlah').val()) || 0;
        const max = parseInt($('#input_jumlah').attr('max')) || 0;

        if (val > max && max !== 0) {
            $('#stok_help').removeClass('d-none');
            $('#btn_submit').prop('disabled', true);
        } else {
            $('#stok_help').addClass('d-none');
            $('#btn_submit').prop('disabled', false);
        }
    }
});

function hapusData(id) {
    Swal.fire({
        title: 'Batalkan pemakaian?',
        text: "Stok akan dikembalikan ke inventaris lab.",
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