<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// --- Logika Pagination & Pencarian ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query khusus kategori Kebersihan
$whereClause = "WHERE kategori='Kebersihan'";
if ($search != '') {
    $whereClause .= " AND (nama_barang LIKE '%$search%' OR kode_barang LIKE '%$search%')";
}

// Hitung total data
$total_data_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang $whereClause");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data
$query = "SELECT * FROM barang $whereClause ORDER BY id_barang DESC LIMIT $offset, $limit";
$res = mysqli_query($conn, $query);

// Simpan hasil ke array agar bisa digunakan dua kali (Tabel & Modal)
$data_barang = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data_barang[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Kebersihan - Gudang Pusat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
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
        .badge-stok { background-color: #f0fdf4; color: #16a34a; font-weight: 600; padding: 8px 12px; border-radius: 8px; border: 1px solid #dcfce7; }
        .btn-gold { background-color: var(--gold); color: var(--navy); font-weight: 700; border: none; border-radius: 10px; transition: 0.3s; }
        .btn-gold:hover { background-color: #e6b800; transform: translateY(-2px); }
        .modal-header { background-color: var(--navy); color: white; border-radius: 15px 15px 0 0; }
        .modal-content { border-radius: 15px; border: none; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="content-area">
        <?php include "../../includes/header.php"; ?>

        <main class="p-4">
            
            <div class="header-card d-flex justify-content-between align-items-center shadow-sm">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-water text-warning me-2"></i> Inventaris Kebersihan
                    </h2>
                    <p class="mb-0 text-white-50">Stok alat kebersihan, sabun, dan perlengkapan sanitasi</p>
                </div>
                <button class="btn btn-gold px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Alat
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
                    <label class="form-label small fw-bold text-muted">CARI DATA</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control border-2 search-box" placeholder="Nama atau kode..." value="<?= htmlspecialchars($search); ?>">
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
                            <th>Nama Alat/Bahan</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1;
                        if(count($data_barang) > 0):
                            foreach($data_barang as $row): 
                        ?>
                        <tr>
                            <td class="ps-4 text-muted fw-bold"><?= $no++; ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= $row['kode_barang']; ?></span></td>
                            <td class="fw-bold"><?= $row['nama_barang']; ?></td>
                            <td><span class="badge badge-stok"><?= $row['stok']; ?></span></td>
                            <td><?= $row['satuan']; ?></td>
                            <td class="text-center pe-4">
                                <button class="btn btn-sm btn-outline-warning border-0" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_barang']; ?>">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger border-0" onclick="confirmDelete('<?= $row['id_barang']; ?>')">
                                    <i class="bi bi-trash3-fill fs-5"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-3">
            <nav class="d-flex justify-content-between align-items-center">
                <p class="text-muted small mb-0">Total: <?= $total_data; ?> item</p>
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
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/tambah.php" method="POST" class="modal-content shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Data Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">NAMA ALAT/BAHAN</label>
                    <input type="text" name="nama_barang" class="form-control border-2" placeholder="Contoh: Sabun Pel 5L" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold small">STOK AWAL</label>
                        <input type="number" name="stok" class="form-control border-2" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold small">SATUAN</label>
                        <input type="text" name="satuan" class="form-control border-2" placeholder="Pcs / Botol" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">TANGGAL MASUK</label>
                    <input type="date" name="tgl_masuk" class="form-control border-2" value="<?= date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah_kebersihan" class="btn btn-gold px-4">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<?php foreach($data_barang as $row): ?>
<div class="modal fade" id="modalEdit<?= $row['id_barang']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/edit.php" method="POST" class="modal-content shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Data Kebersihan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id_barang" value="<?= $row['id_barang']; ?>">
                <input type="hidden" name="modul" value="kebersihan">
                <div class="mb-3">
                    <label class="form-label fw-bold small">NAMA BARANG</label>
                    <input type="text" name="nama_barang" class="form-control border-2" value="<?= $row['nama_barang']; ?>" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold small">STOK</label>
                        <input type="number" name="stok" class="form-control border-2" value="<?= $row['stok']; ?>" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold small">SATUAN</label>
                        <input type="text" name="satuan" class="form-control border-2" value="<?= $row['satuan']; ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="update_kebersihan" class="btn btn-gold px-4">Update Data</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus data?',
            text: "Data kebersihan akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0a192f',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../proses/hapus.php?id=" + id + "&modul=kebersihan";
            }
        })
    }

    const status = new URLSearchParams(window.location.search).get('status');
    if (status) {
        const toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
        });
        if (status === 'sukses') toast.fire({ icon: 'success', title: 'Data berhasil disimpan' });
        if (status === 'update_sukses') toast.fire({ icon: 'success', title: 'Data diperbarui' });
        if (status === 'hapus_sukses') toast.fire({ icon: 'success', title: 'Data dihapus' });
    }
</script>
</body>
</html>