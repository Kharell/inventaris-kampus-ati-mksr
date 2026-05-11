<?php
session_start();
include "../../../config/database.php";

// 1. Proteksi Akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../login.php");
    exit;
}

// Ambil ID dari session (Pastikan saat login, $_SESSION['id_user'] diisi dari kolom id_kepala)
$id_user = $_SESSION['id_user'];

// --- STEP 1: Ambil id_lab milik Kepala Lab ini ---
$q_identitas = mysqli_query($conn, "SELECT id_lab FROM kepala_lab WHERE id_kepala = '$id_user'");
$d_identitas = mysqli_fetch_assoc($q_identitas);
$id_lab_user = $d_identitas['id_lab'];

// --- STEP 2: Ambil Bahan HANYA dari Lab yang sama ---
// Kita ambil stok dan data lainnya langsung dari tabel bahan_praktek
$query_barang = mysqli_query($conn, "SELECT * FROM bahan_praktek WHERE id_lab = '$id_lab_user' ORDER BY nama_bahan ASC");

// --- STEP 3: Riwayat Permintaan ---
$sql_riwayat = "SELECT p.*, b.kode_bahan,  b.nama_bahan, b.spesifikasi, b.kondisi , b.stok
                FROM permintaan_barang p 
                LEFT JOIN bahan_praktek b ON p.id_barang = b.id_praktek 
                WHERE p.id_kepala = '$id_user' 
                ORDER BY p.tgl_permintaan DESC";
$riwayat = mysqli_query($conn, $sql_riwayat);

// 4. Logika Ambil Data untuk Modal Edit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id_edit = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $query_edit = mysqli_query($conn, "SELECT p.*, b.kode_bahan, b.nama_bahan, b.spesifikasi, b.kondisi, b.stok
                                       FROM permintaan_barang p 
                                       LEFT JOIN bahan_praktek b ON p.id_barang = b.id_praktek 
                                       WHERE p.id_permintaan = '$id_edit'");
    $edit_data = mysqli_fetch_assoc($query_edit);
}



// Query untuk mengambil total stok per barang dari tabel distribusi_lab yang sudah diterima
$stok_lab_query = mysqli_query($conn, "SELECT id_praktek, SUM(jumlah) as total_stok 
                                       FROM distribusi_lab 
                                       WHERE status = 'diterima' 
                                       GROUP BY id_praktek");
$stok_list = [];
while($row = mysqli_fetch_assoc($stok_lab_query)) {
    $stok_list[$row['id_praktek']] = $row['total_stok'];
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kebutuhan | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        :root { --navy: #001f3f; --bg: #f8fafc; }
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: #ffffff; }
        .btn-navy { background: var(--navy); color: white; border-radius: 10px; padding: 10px 20px; transition: 0.3s; }
        .btn-navy:hover { background: #003366; transform: translateY(-2px); color: white; }
        .badge-status { padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
        .bg-pending { background: #fef3c7; color: #92400e; }
        .bg-disetujui { background: #dcfce7; color: #166534; }
        .bg-ditolak { background: #fee2e2; color: #991b1b; }
        table.dataTable thead th { background-color: #f8fafc; color: #475569; font-weight: 700; border-bottom: 2px solid #eee!important; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="main-content w-100 p-4"> 
        <?php include "../../../includes/header.php"; ?>
        
        <div class="container-fluid" style="margin-top: 70px;">
            <div class="card-custom p-4 mb-4 border-start border-5" style="border-color: var(--navy) !important;">
                <div class="row align-items-center">
                  <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="icon-box me-3 d-flex align-items-center justify-content-center rounded-3" 
                            style="width: 50px; height: 50px; background-color: rgba(0, 31, 63, 0.1); color: var(--navy);">
                            <i class="bi bi-box-seam fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1">Pengajuan Logistik</h3>
                            <p class="text-muted mb-0 small">Kelola permintaan bahan praktek laboratorium Anda</p>
                        </div>
                    </div>
                </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="btn-group shadow-sm">
                             <button type="button" class="btn btn-white border" onclick="location.href='kebutuhan.php'"><i class="bi bi-arrow-clockwise"></i> Reset</button>
                             <button type="button" class="btn btn-navy" data-bs-toggle="collapse" data-bs-target="#formCollapse">
                                <i class="bi bi-plus-circle me-2"></i>Buat Pembelian
                             </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-12 collapse <?= $edit_data ? 'show' : '' ?>" id="formCollapse">
                    <div class="card-custom p-4 border-bottom border-4 border-warning">
                        <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2 text-warning"></i><?= $edit_data ? 'Update' : 'Form Baru' ?> Permintaan</h5>
                        
                        <form action="../proses/<?= $edit_data ? 'edit.php' : 'tambah.php' ?>" method="POST">
                            <?php if($edit_data): ?>
                                <input type="hidden" name="id_permintaan" value="<?= $edit_data['id_permintaan']; ?>">
                            <?php endif; ?>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">CARI & PILIH BAHAN</label>
                                    <?php if($edit_data): ?>
                                        <input type="text" class="form-control bg-light" value="<?= $edit_data['nama_bahan']; ?>" readonly>
                                    <?php else: ?>
                                        <select name="id_barang" id="pilih_bahan" class="form-select select2-pencarian" required>
                                            <option value="">Ketik nama atau kode bahan...</option>
                                            <?php 
                                            mysqli_data_seek($query_barang, 0);
                                            while($b = mysqli_fetch_assoc($query_barang)): 
                                                // Ambil stok dari array yang kita buat di atas, jika tidak ada set 0
                                                $stok_saat_ini = $stok_list[$b['id_praktek']] ?? 0;
                                            ?>
                                                <option value="<?= $b['id_praktek']; ?>" 
                                                        data-spesifikasi="<?= htmlspecialchars($b['spesifikasi'] ?? '-'); ?>" 
                                                        data-kondisi="<?= htmlspecialchars($b['kondisi'] ?? '-'); ?>"
                                                        data-stok="<?= $stok_saat_ini; ?>"> <?= $b['kode_bahan']; ?> - <?= $b['nama_bahan']; ?> (<?= $b['satuan']; ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-muted">SPESIFIKASI</label>
                                    <input type="text" id="display_spesifikasi" class="form-control bg-light" value="<?= $edit_data['spesifikasi'] ?? ''; ?>" readonly placeholder="Otomatis...">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-muted">KONDISI</label>
                                    <input type="text" id="display_kondisi" class="form-control bg-light" value="<?= $edit_data['kondisi'] ?? ''; ?>" readonly placeholder="Otomatis...">
                                </div>

                                <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-muted">STOK DI LAB SAAT INI</label>
                                    <input type="text" 
                                        id="display_stok" 
                                        name="stok_awal"  class="form-control bg-light fw-bold text-primary" 
                                        value="0" 
                                        readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-muted">JUMLAH DIMINTA</label>
                                    <input type="number" name="jumlah_minta" class="form-control border-success" 
                                        value="<?= $edit_data['jumlah_minta'] ?? ''; ?>" min="1" required 
                                        placeholder="Qty minta...">
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" name="<?= $edit_data ? 'update_permintaan' : 'kirim_permintaan' ?>" class="btn btn-navy w-100">
                                        <i class="bi bi-send-check me-2"></i><?= $edit_data ? 'Update' : 'Kirim' ?>
                                    </button>
                                </div>
                            </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0">Riwayat Pengajuan</h5>
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCetak">
                                <i class="bi bi-printer me-2"></i>Cetak Laporan
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="tabelKebutuhan" class="table table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kode Bahan</th>
                                        <th>Nama Bahan</th>
                                        <th>Spesifikasi</th>
                                        <th>Kondisi</th>
                                        <th class="text-center">Stok Awal</th>
                                        <th class="text-center">Jumlah Permintaan</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; while($r = mysqli_fetch_assoc($riwayat)): ?>
                                    <tr>
                                        <td class="text-muted"><?= $no++; ?></td>
                                        <td><span class="small fw-semibold"><?= date('d/m/Y', strtotime($r['tgl_permintaan'])); ?></span></td>
                                        <td class="text-center">
                                            <span class="badge-status bg-<?= $r['kode_bahan']; ?>"><?= $r['kode_bahan']; ?></span>
                                        </td>
                                        <td class="fw-bold text-navy"><?= $r['nama_bahan'] ?? 'N/A'; ?></td><td>
                                            <div class="text-wrap" style="max-width: 200px; font-size: 0.85rem; line-height: 1.4;">
                                                <?php if (!empty($r['spesifikasi'])): ?>
                                                    <span class="text-dark" title="<?= htmlspecialchars($r['spesifikasi']); ?>">
                                                        <?= $r['spesifikasi']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small italic">Tidak ada spesifikasi</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= $r['kondisi'] ?: '-'; ?></span></td>
                                        <td class="text-center fw-bold"><?= $r['stok_awal']; ?></td>
                                        <td class="text-center fw-bold"><?= $r['jumlah_minta']; ?></td>
                                        <td class="text-center">
                                            <span class="badge-status bg-<?= $r['status']; ?>"><?= $r['status']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if($r['status'] == 'pending'): ?>
                                                <div class="btn-group">
                                                    <a href="?edit_id=<?= $r['id_permintaan']; ?>" class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil"></i></a>
                                                    <button onclick="confirmDelete('<?= $r['id_permintaan']; ?>')" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                                </div>
                                            <?php else: ?>
                                                <i class="bi bi-lock-fill text-muted"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCetak" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="fw-bold m-0 text-danger"><i class="bi bi-file-pdf me-2"></i>Cetak Laporan PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="cetak_kebutuhan.php" method="GET" target="_blank">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small fw-bold text-muted">DARI TANGGAL</label>
                            <input type="date" name="tgl_awal" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold text-muted">SAMPAI TANGGAL</label>
                            <input type="date" name="tgl_akhir" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">GENERATE PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#tabelKebutuhan').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
            "pageLength": 10
        });

        $('.select2-pencarian').select2({
            theme: 'bootstrap-5',
            allowClear: true
        });

        // Logika Auto-Fill Spesifikasi & Kondisi saat bahan dipilih
        $('#pilih_bahan').on('change', function() {
            const selected = $(this).find(':selected');
            const spesifikasi = selected.data('spesifikasi');
            const kondisi = selected.data('kondisi');
            
            $('#display_spesifikasi').val(spesifikasi || '-');
            $('#display_kondisi').val(kondisi || '-');
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus data?',
            text: "Aksi ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#001f3f',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = "../proses/hapus.php?id=" + id;
        });
    }
</script>


<script>
$(document).ready(function() {
    $('#id_barang').on('change', function() {
        var id_praktek = $(this).val();
        // Pastikan variabel ini mengambil ID Lab dari session login
        var id_lab = "<?= $_SESSION['id_lab']; ?>"; 

        if (id_praktek) {
            $.ajax({
                url: '../proses/get_stok.php',
                type: 'GET',
                data: { 
                    id_praktek: id_praktek,
                    id_lab: id_lab 
                },
                success: function(response) {
                    $('#display_stok').val(response);
                }
            });
        }
    });
});

$(document).ready(function() {
    $('#pilih_bahan').on('change', function() {
        // Ambil data dari option yang dipilih
        const selectedOption = $(this).find(':selected');
        const spesifikasi = selectedOption.data('spesifikasi');
        const kondisi = selectedOption.data('kondisi');
        const stok = selectedOption.data('stok');

        // Masukkan ke input display
        $('#display_spesifikasi').val(spesifikasi);
        $('#display_kondisi').val(kondisi);
        $('#display_stok').val(stok); // Stok otomatis terisi
    });
});
</script>


</body>
</html>