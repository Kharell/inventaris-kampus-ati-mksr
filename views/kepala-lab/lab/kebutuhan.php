<?php
session_start();
include "../../../config/database.php";

// 1. Proteksi Akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// 2. Query Ambil Barang dari tabel bahan_praktek
// Tambahkan kode_bahan, spesifikasi, dan kondisi ke dalam SELECT
$query_barang = mysqli_query($conn, "SELECT id_praktek, kode_bahan, nama_bahan, spesifikasi, kondisi, satuan FROM bahan_praktek ORDER BY nama_bahan ASC");
// 3. Query Ambil Riwayat Permintaan (Gunakan LEFT JOIN agar data p.id_barang tetap muncul)
$sql_riwayat = "SELECT p.*, b.nama_bahan 
                FROM permintaan_barang p 
                LEFT JOIN bahan_praktek b ON p.id_barang = b.id_praktek 
                WHERE p.id_kepala = '$id_user' 
                ORDER BY p.tgl_permintaan DESC";
$riwayat = mysqli_query($conn, $sql_riwayat);

// 4. Logika Ambil Data untuk Modal Edit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id_edit = $_GET['edit_id'];
    $query_edit = mysqli_query($conn, "SELECT p.*, b.nama_bahan 
                                       FROM permintaan_barang p 
                                       LEFT JOIN bahan_praktek b ON p.id_barang = b.id_praktek 
                                       WHERE p.id_permintaan = '$id_edit'");
    $edit_data = mysqli_fetch_assoc($query_edit);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Kebutuhan | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root { --navy: #001f3f; --bg: #f4f7fa; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .btn-navy { background: var(--navy); color: white; border-radius: 8px; font-weight: 600; }
        .btn-navy:hover { background: #003366; color: white; }
        
        .table thead th { 
            background-color: #f8f9fa; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 1px;
            color: #6c757d;
            border: none;
        }
        .badge-status { font-size: 0.75rem; padding: 6px 12px; border-radius: 30px; font-weight: 600; }
        .bg-pending { background: #fff3cd; color: #856404; }
        .bg-disetujui { background: #d4edda; color: #155724; }
        .bg-ditolak { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="flex-grow-1" style="min-height: 100vh;">
        <?php if(file_exists("../../../includes/header.php")) include "../../../includes/header.php"; ?>

        <main class="p-4">
            <div class="page-header d-flex justify-content-between align-items-center bg-white p-4 shadow-sm rounded-4 border-start border-5" style="border-color: var(--navy) !important; position: relative; overflow: hidden;">
                <div style="position: absolute; right: -20px; top: -20px; width: 150px; height: 150px; background: rgba(0, 31, 63, 0.03); border-radius: 50%;"></div>
                
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-primary-subtle p-3 rounded-3 me-4 text-primary shadow-sm" style="background: linear-gradient(135deg, #f0f7ff 0%, #e0ebf5 100%);">
                        <i class="bi bi-box-seam-fill" style="font-size: 1.8rem; color: var(--navy);"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--navy); letter-spacing: -0.5px;">Manajemen Kebutuhan</h4>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-soft-primary text-primary me-2" style="background: #e8f0fe; font-size: 0.65rem;">LOGISTIK</span>
                            <p class="text-muted mb-0 small">Ajukan permintaan bahan sesuai data inventaris</p>
                        </div>
                    </div>
                </div>

                <div class="d-none d-md-block text-end">
                    <small class="text-muted d-block mb-1">Status Sistem</small>
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">
                        <i class="bi bi-check-circle-fill me-1"></i> Terhubung
                    </span>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-4">
                    <div class="card p-4">
                        <?php if($edit_data): ?>
                            <h6 class="fw-bold mb-4 text-uppercase text-primary">Edit Permintaan</h6>
                            <form action="../proses/edit.php" method="POST">
                                <input type="hidden" name="id_permintaan" value="<?= $edit_data['id_permintaan']; ?>">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">BAHAN</label>
                                    <input type="text" class="form-control border-0 bg-light py-2" value="<?= $edit_data['nama_bahan'] ?? 'Bahan Tidak Ditemukan'; ?>" readonly>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">JUMLAH BARU</label>
                                    <input type="number" name="jumlah_minta" class="form-control border-0 bg-light py-2" value="<?= $edit_data['jumlah_minta']; ?>" min="1" required>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="update_permintaan" class="btn btn-navy w-100">Update</button>
                                    <a href="kebutuhan.php" class="btn btn-light w-100">Batal</a>
                                </div>
                            </form>
                        <?php else: ?>
                            
                        <h6 class="fw-bold mb-4 text-uppercase">Form Pengajuan</h6>
                        <form action="../proses/tambah.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">PILIH BAHAN</label>
                                <select name="id_barang" id="pilih_bahan" class="form-select border-0 bg-light py-2" required>
                                    <option value="" disabled selected>Pilih bahan...</option>
                                    <?php while($b = mysqli_fetch_assoc($query_barang)): ?>
                                        <option value="<?= $b['id_praktek']; ?>" 
                                                data-spesifikasi="<?= htmlspecialchars($b['spesifikasi'] ?? '-'); ?>" 
                                                data-kondisi="<?= htmlspecialchars($b['kondisi'] ?? '-'); ?>">
                                            <?= $b['nama_bahan']; ?> | <?= $b['kode_bahan']; ?> (<?= $b['satuan']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">SPESIFIKASI</label>
                                <textarea id="display_spesifikasi" name="spesifikasi" class="form-control border-0 bg-light py-2" rows="2" readonly placeholder="Otomatis terisi..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label small fw-bold">JUMLAH</label>
                                    <input type="number" name="jumlah_minta" class="form-control border-0 bg-light py-2" min="1" required placeholder="0">
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label small fw-bold text-muted">KONDISI</label>
                                    <input type="text" id="display_kondisi" name="kondisi" class="form-control border-0 bg-light py-2" readonly placeholder="-">
                                </div>
                            </div>

                            <button type="submit" name="kirim_permintaan" class="btn btn-navy w-100 py-2">
                                <i class="bi bi-plus-lg me-2"></i>Tambah Permintaan
                            </button>
                        </form>

                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-4 text-uppercase">Riwayat Permintaan Saya</h6>
                        <div class="table-responsive">
                            <table id="tabelKebutuhan" class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Tanggal</th>
                                        <th>Nama Bahan</th>
                                        <th>Spesifikasi</th> <th class="text-center">Minta</th>
                                        <th class="text-center">Kondisi</th> <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $no = 1;
                                        while($r = mysqli_fetch_assoc($riwayat)): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><small class="text-muted"><?= date('d/m/Y', strtotime($r['tgl_permintaan'])); ?></small></td>
                                                <td>
                                                    <div class="fw-bold text-navy"><?= $r['nama_bahan'] ?? '<span class="text-danger">Bahan dihapus</span>'; ?></div>
                                                </td>
                                                <td>
                                                    <small class="text-muted" style="font-size: 0.75rem;"><?= $r['spesifikasi'] ?: '-'; ?></small>
                                                </td>
                                                <td class="text-center"><?= $r['jumlah_minta']; ?></td>
                                                <td class="text-center">
                                                    <small class="badge bg-light text-dark border" style="font-size: 0.7rem;"><?= $r['kondisi'] ?: '-'; ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge-status bg-<?= $r['status']; ?>">
                                                        <?= ucfirst($r['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if($r['status'] == 'pending'): ?>
                                                        <a href="?edit_id=<?= $r['id_permintaan']; ?>" class="btn btn-sm btn-outline-primary border-0">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <a href="javascript:void(0)" onclick="confirmDelete('<?= $r['id_permintaan']; ?>')" class="btn btn-sm btn-outline-danger border-0">
                                                            <i class="bi bi-trash3"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small"><i class="bi bi-lock-fill"></i></span>
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
        // 1. Inisialisasi DataTable
        $('#tabelKebutuhan').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
            "pageLength": 10
        });

        // 2. Logika Auto-Fill (Pindahkan ke sini agar selalu terbaca)
        const pilihBahan = document.getElementById('pilih_bahan');
        if (pilihBahan) {
            pilihBahan.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                // Ambil data dari atribut data- yang kita buat di PHP
                const spesifikasi = selectedOption.getAttribute('data-spesifikasi') || '-';
                const kondisi = selectedOption.getAttribute('data-kondisi') || '-';

                // Masukkan data ke dalam field input/textarea
                document.getElementById('display_spesifikasi').value = spesifikasi;
                document.getElementById('display_kondisi').value = kondisi;
            });
        }
    });

    // 3. Fungsi Hapus Data
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Permintaan?',
            text: "Data akan dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#001f3f',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../proses/hapus.php?id=" + id;
            }
        });
    }
</script>

<?php if(isset($_SESSION['alert'])): ?>
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Berhasil!', 
        text: 'Proses berhasil dilakukan.', 
        timer: 2000, 
        showConfirmButton: false 
    });
</script>
<?php unset($_SESSION['alert']); endif; ?>

</body>
</html>