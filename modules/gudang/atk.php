<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// --- Logika Pagination & Pencarian ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query dasar untuk kategori ATK
$whereClause = "WHERE kategori='ATK'";
if ($search != '') {
    $whereClause .= " AND (nama_barang LIKE '%$search%' OR kode_barang LIKE '%$search%')";
}

// Hitung total data
$total_data_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang $whereClause");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data dari database
$query = "SELECT * FROM barang $whereClause ORDER BY id_barang DESC LIMIT $offset, $limit";
$res = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris ATK - Gudang Pusat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/style.css">
    <style>
        :root { --navy: #0a192f; --navy-light: #112240; --gold: #ffcc00; }
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }

        .header-card { background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white; border-radius: 15px; padding: 30px; margin-bottom: 25px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .filter-card { border-radius: 15px; border: none; margin-bottom: 20px; }
        .search-box { border-radius: 10px 0 0 10px; border: 2px solid #e9ecef; }
        .search-btn { border-radius: 0 10px 10px 0; background-color: var(--navy); color: white; border: none; }
        .table-container { border-radius: 15px; overflow: hidden; background: white; border: none; }
        .table thead { background-color: var(--navy); color: white; }
        .table thead th { padding: 15px; font-weight: 500; text-transform: uppercase; font-size: 0.8rem; }
        .badge-stok { background-color: #e3f2fd; color: #0d6efd; font-weight: 600; padding: 8px 12px; border-radius: 8px; }
        .pagination .page-link { color: var(--navy); border: none; margin: 0 3px; border-radius: 8px; font-weight: 600; }
        .pagination .page-item.active .page-link { background-color: var(--gold); color: var(--navy); }
        .btn-gold { background-color: var(--gold); color: var(--navy); font-weight: 700; border: none; border-radius: 10px; transition: 0.3s; }
        .btn-gold:hover { background-color: #e6b800; transform: translateY(-2px); }
        
        /* Memastikan Modal Muncul di Depan */
        .modal-header { background-color: var(--navy); }
    </style>
</head>
<body>
    <body>

<div class="main-wrapper">
    <?php include "../../includes/sidebar.php"; ?>
    
    <div class="content-area">
        
        <?php include "../../includes/header.php"; ?>

        <main class="p-4">
            <div class="header-card d-flex justify-content-between align-items-center shadow-sm">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-pencil-fill text-warning me-2"></i> Inventaris ATK</h2>
                    <p class="mb-0 text-white-50">Manajemen Alat Tulis Kantor & Perlengkapan Kertas</p>
                </div>
                <button class="btn btn-gold px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah ATK
                </button>
            </div>
 

            <div class="card filter-card shadow-sm">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">TAMPILKAN</label>
                            <select name="limit" class="form-select border-2" onchange="this.form.submit()">
                                <option value="10" <?= $limit == 10 ? 'selected' : ''; ?>>10 Baris</option>
                                <option value="25" <?= $limit == 25 ? 'selected' : ''; ?>>25 Baris</option>
                                <option value="50" <?= $limit == 50 ? 'selected' : ''; ?>>50 Baris</option>
                            </select>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">CARI ATK</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control border-2 search-box" placeholder="Ketik nama atau kode..." value="<?= htmlspecialchars($search); ?>">
                                <button class="btn search-btn px-3" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card table-container shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1;
                                if(mysqli_num_rows($res) > 0):
                                    while($row = mysqli_fetch_assoc($res)): 
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-bold"><?= $no++; ?></td>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= $row['kode_barang']; ?></span></td>
                                    <td class="fw-bold"><?= $row['nama_barang']; ?></td>
                                    <td><span class="badge badge-stok"><?= $row['stok']; ?></span></td>
                                    <td><?= $row['satuan']; ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning border-0" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_barang']; ?>">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger border-0" onclick="confirmDelete('<?= $row['id_barang']; ?>')">
                                            <i class="bi bi-trash3-fill fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Data ATK tidak ditemukan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 py-3">
                    <nav class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Menampilkan <?= mysqli_num_rows($res); ?> dari <?= $total_data; ?> data</span>
                        <ul class="pagination mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?halaman=<?= $page-1; ?>&limit=<?= $limit; ?>&search=<?= $search; ?>"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for($i=1; $i<=$total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?halaman=<?= $i; ?>&limit=<?= $limit; ?>&search=<?= $search; ?>"><?= $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?halaman=<?= $page+1; ?>&limit=<?= $limit; ?>&search=<?= $search; ?>"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= $base_url; ?>modules/proses/tambah.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Tambah ATK Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">NAMA BARANG</label>
                    <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Kertas A4" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">STOK AWAL</label>
                        <input type="number" name="stok" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">SATUAN</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Rim / Box / Pcs" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">TANGGAL MASUK</label>
                    <input type="date" name="tgl_masuk" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah_atk" class="btn btn-gold px-4">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<?php 
mysqli_data_seek($res, 0); // Reset pointer hasil query
while($row = mysqli_fetch_assoc($res)): 
?>
<div class="modal fade" id="modalEdit<?= $row['id_barang']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= $base_url; ?>modules/proses/edit.php" method="POST" class="modal-content">
            <input type="hidden" name="id_barang" value="<?= $row['id_barang']; ?>">
            <input type="hidden" name="modul" value="atk">
            <div class="modal-header">
                <h5 class="modal-title text-white">Edit Barang ATK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">NAMA BARANG</label>
                    <input type="text" name="nama_barang" class="form-control" value="<?= $row['nama_barang']; ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">STOK</label>
                        <input type="number" name="stok" class="form-control" value="<?= $row['stok']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">SATUAN</label>
                        <input type="text" name="satuan" class="form-control" value="<?= $row['satuan']; ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="update_atk" class="btn btn-gold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Alert Status
const urlParams = new URLSearchParams(window.location.search);
const status = urlParams.get('status');
if (status === 'sukses') Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data telah disimpan!', timer: 2000, showConfirmButton: false });
if (status === 'hapus_sukses') Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Data telah dihapus!', timer: 2000, showConfirmButton: false });

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus data ini?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0a192f',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= $base_url; ?>modules/proses/hapus.php?id=" + id + "&modul=atk";
        }
    })
}
</script>
</body>
</html>