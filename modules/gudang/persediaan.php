<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin'); // Sesuaikan akses

// --- Ambil Data Admin/User yang sedang Login untuk Tanda Tangan KIRI ---
$nama_admin = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? $_SESSION['username'] ?? "Admin Gudang";
$nip_admin  = $_SESSION['nip'] ?? "..........................";

// --- Logika Pagination & Pencarian ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query dasar
$whereClause = "";
if ($search != '') {
    $whereClause = "WHERE nama_barang LIKE '%$search%'";
}

// Hitung total data
$total_data_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM gudang_persediaan $whereClause");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data
$query = "SELECT * FROM gudang_persediaan $whereClause ORDER BY id_persediaan DESC LIMIT $offset, $limit";
$res = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gudang Persediaan - Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        :root { --navy: #001f3f; --navy-light: #003366; --gold: #ffcc00; --bg-soft: #f4f7fc;}
        body { background-color: var(--bg-soft); font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden;}
        
        .main-content { margin-left: 260px; padding: 2rem; padding-top: 80px; min-height: 100vh; transition: 0.3s; }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 1rem; padding-top: 80px;} }

        /* Header Card */
        .header-card { 
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); 
            color: white; border-radius: 20px; padding: 30px 40px; margin-bottom: 25px; 
            box-shadow: 0 10px 30px rgba(0, 31, 63, 0.15); border-bottom: 4px solid var(--gold);
            position: relative; overflow: hidden;
        }
        .header-card::after {
            content: ''; position: absolute; right: -50px; top: -100px; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,204,0,0.1) 0%, transparent 70%); border-radius: 50%;
        }

        .btn-gold { background-color: var(--gold); color: var(--navy); font-weight: 800; border: none; border-radius: 12px; transition: 0.3s; box-shadow: 0 4px 15px rgba(255, 204, 0, 0.3);}
        .btn-gold:hover { background-color: #e6b800; transform: translateY(-3px); }
        .btn-print { background-color: white; color: var(--navy); font-weight: 800; border: none; border-radius: 12px; transition: 0.3s; }
        .btn-print:hover { background-color: #f1f5f9; transform: translateY(-3px); }

        .glass-card { background: white; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); border: none; overflow: hidden; }
        .table thead { background-color: #f8fafc; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;}
        .table thead th { border-bottom: 2px solid #e2e8f0; padding: 15px 10px; }
        .table tbody td { padding: 15px 10px; vertical-align: middle; border-bottom: 1px solid #f1f5f9;}
        
        .stok-akhir-badge { background-color: var(--navy); color: white; font-weight: 800; padding: 8px 15px; border-radius: 8px; font-size: 1rem; box-shadow: 0 4px 10px rgba(0, 31, 63, 0.2); display: inline-block;}
        
        /* Pagination */
        .pagination .page-link { color: var(--navy); border: none; margin: 0 3px; border-radius: 8px; font-weight: 700; }
        .pagination .page-item.active .page-link { background-color: var(--gold); color: var(--navy); box-shadow: 0 4px 10px rgba(255, 204, 0, 0.3);}

        /* Tombol Aksi */
        .btn-aksi { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; transition: 0.2s; border: none;}
        .btn-aksi:hover { transform: scale(1.1); }
        .btn-tambah-stok { background-color: #dcfce7; color: #15803d; }
        .btn-pakai-stok { background-color: #fee2e2; color: #b91c1c; }
        .btn-edit-info { background-color: #fef3c7; color: #b45309; }
        .btn-hapus { background-color: #f1f5f9; color: #475569; }

        .modal-header.bg-navy { background-color: var(--navy); color: white; border-bottom: none; }
        .modal-header.bg-success { background-color: #15803d !important; color: white; border-bottom: none; }
        .modal-header.bg-danger { background-color: #b91c1c !important; color: white; border-bottom: none; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../includes/header.php"; ?>

        <div class="header-card d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div style="z-index: 2;">
                <h2 class="fw-bold mb-1"><i class="bi bi-box-seam text-warning me-2"></i> Gudang Persediaan</h2>
                <p class="mb-0 text-white-50">Monitoring Stok Awal, Riwayat Pengajuan, dan Pemakaian Material</p>
            </div>
            <div class="d-flex gap-2" style="z-index: 2;">
                <!-- Tombol Cetak Memanggil Fungsi JS -->
                <button onclick="promptCetak()" class="btn btn-print px-4 py-2 shadow-sm">
                    <i class="bi bi-printer-fill me-2"></i>Cetak Laporan
                </button>
                <button class="btn btn-gold px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBaru">
                    <i class="bi bi-plus-lg me-2"></i>Input Barang Baru
                </button>
            </div>
        </div>

        <div class="glass-card mb-4 filter-section">
            <div class="p-4 bg-white border-bottom">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-md-2 col-6">
                        <select name="limit" class="form-select border-2 fw-bold text-muted" onchange="this.form.submit()">
                            <option value="10" <?= $limit == 10 ? 'selected' : ''; ?>>10 Baris</option>
                            <option value="25" <?= $limit == 25 ? 'selected' : ''; ?>>25 Baris</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : ''; ?>>50 Baris</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-none d-md-block"></div>
                    <div class="col-md-4 col-12">
                        <div class="input-group shadow-sm">
                            <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Cari nama barang..." value="<?= htmlspecialchars($search); ?>">
                            <button class="btn btn-primary fw-bold px-3" type="submit" style="background-color: var(--navy); border:none;"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABEL DATA -->
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="ps-4 text-center" width="5%">No</th>
                            <th width="15%">Tgl Input</th>
                            <th width="25%">Nama Material</th>
                            <th class="text-center" width="10%">Satuan</th>
                            <th class="text-center" width="9%">Awal</th>
                            <th class="text-center text-success" width="9%">Pengajuan</th>
                            <th class="text-center text-danger" width="9%">Pemakaian</th>
                            <th class="text-center" width="9%">Sisa Stok</th>
                            <th class="text-center" width="10%">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php 
                        $no = $offset + 1;
                        if(mysqli_num_rows($res) > 0):
                            while($row = mysqli_fetch_assoc($res)): 
                        ?>
                        <tr>
                            <td class="ps-4 text-center text-muted fw-bold"><?= $no++; ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= isset($row['tgl_input']) ? date('d M Y', strtotime($row['tgl_input'])) : date('d M Y'); ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-navy" style="font-size: 1.05rem;"><?= htmlspecialchars($row['nama_barang']); ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-3 py-2"><?= htmlspecialchars($row['satuan']); ?></span>
                            </td>
                            <td class="text-center fw-bold text-secondary fs-6"><?= $row['stok_awal']; ?></td>
                            <td class="text-center fw-bold text-success fs-6"><i class="bi bi-arrow-down-left me-1 small"></i><?= $row['pengajuan_barang']; ?></td>
                            <td class="text-center fw-bold text-danger fs-6"><i class="bi bi-arrow-up-right me-1 small"></i><?= $row['pemakaian_barang']; ?></td>
                            <td class="text-center">
                                <span class="stok-akhir-badge <?= $row['stok_akhir'] <= 5 ? 'bg-danger' : '' ?>"><?= $row['stok_akhir']; ?></span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn-aksi btn-tambah-stok" data-bs-toggle="modal" data-bs-target="#modalTambahStok<?= $row['id_persediaan']; ?>" title="Input Barang Masuk">
                                        <i class="bi bi-plus-lg fs-5"></i>
                                    </button>
                                    <button class="btn-aksi btn-pakai-stok" data-bs-toggle="modal" data-bs-target="#modalPakaiStok<?= $row['id_persediaan']; ?>" title="Input Barang Keluar">
                                        <i class="bi bi-dash-lg fs-5"></i>
                                    </button>
                                    <button class="btn-aksi btn-edit-info" data-bs-toggle="modal" data-bs-target="#modalEditInfo<?= $row['id_persediaan']; ?>" title="Edit Nama/Satuan">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn-aksi btn-hapus" onclick="confirmDelete('<?= $row['id_persediaan']; ?>')" title="Hapus Data">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted fw-bold"><i class="bi bi-folder-x fs-1 d-block mb-2"></i>Data persediaan kosong.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 0): ?>
            <div class="p-4 bg-white border-top pagination-wrap">
                <nav class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <span class="small text-muted fw-bold">Menampilkan <?= mysqli_num_rows($res); ?> dari <?= $total_data; ?> data</span>
                    <ul class="pagination mb-0 shadow-sm rounded-3">
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
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: INPUT BARANG BARU -->
<!-- ========================================== -->
<div class="modal fade" id="modalTambahBaru" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/tambah.php" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <input type="hidden" name="jenis_form" value="persediaan_baru">
            <div class="modal-header bg-navy p-4">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-box-seam me-2 text-warning"></i>Daftarkan Barang Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="alert alert-info border-0 shadow-sm small mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>Daftarkan nama material dan stok awalnya saja. Penambahan/Pemakaian dilakukan setelah data tersimpan.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-navy">NAMA BARANG <span class="text-danger">*</span></label>
                    <input type="text" name="nama_barang" class="form-control border-2 p-2" placeholder="Contoh: Kertas HVS A4" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-navy">SATUAN <span class="text-danger">*</span></label>
                        <input type="text" name="satuan" class="form-control border-2 p-2" placeholder="Rim / Box / Pcs" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-navy">STOK AWAL SAAT INI <span class="text-danger">*</span></label>
                        <input type="number" name="stok_awal" class="form-control border-2 p-2" value="0" min="0" required>
                    </div>
                </div>
                <input type="hidden" name="pengajuan_barang" value="0">
                <input type="hidden" name="pemakaian_barang" value="0">
            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="button" class="btn btn-light px-4 fw-bold rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-gold px-4 fw-bold rounded-3 text-dark">Simpan Data Baru</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- LOOPING MODAL UNTUK SETIAP BARIS DATA -->
<!-- ========================================== -->
<?php 
mysqli_data_seek($res, 0); 
while($row = mysqli_fetch_assoc($res)): 
?>

<!-- MODAL 2: TAMBAH STOK (PENGAJUAN/MASUK) -->
<div class="modal fade" id="modalTambahStok<?= $row['id_persediaan']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/edit.php" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <input type="hidden" name="jenis_update" value="tambah_stok">
            <input type="hidden" name="id_persediaan" value="<?= $row['id_persediaan']; ?>">
            
            <div class="modal-header bg-success p-4">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-box-arrow-in-down me-2"></i>Input Barang Masuk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-4 text-center">
                    <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_barang']); ?></h5>
                    <span class="badge bg-secondary">Sisa Stok saat ini: <?= $row['stok_akhir']; ?> <?= htmlspecialchars($row['satuan']); ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-success">JUMLAH BARANG MASUK (+)</label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-success text-success"><i class="bi bi-plus-lg fw-bold"></i></span>
                        <input type="number" name="jumlah_masuk" class="form-control border-success text-success fw-bold" placeholder="0" min="1" required>
                        <span class="input-group-text bg-white border-success fw-bold"><?= htmlspecialchars($row['satuan']); ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="button" class="btn btn-light px-4 fw-bold rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success px-4 fw-bold rounded-3 text-white">Simpan Pemasukan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: KURANGI STOK (PEMAKAIAN/KELUAR) -->
<div class="modal fade" id="modalPakaiStok<?= $row['id_persediaan']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/edit.php" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <input type="hidden" name="jenis_update" value="kurangi_stok">
            <input type="hidden" name="id_persediaan" value="<?= $row['id_persediaan']; ?>">
            
            <div class="modal-header bg-danger p-4">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-box-arrow-up me-2"></i>Input Pemakaian Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-4 text-center">
                    <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_barang']); ?></h5>
                    <span class="badge bg-secondary">Sisa Stok bisa dipakai: <?= $row['stok_akhir']; ?> <?= htmlspecialchars($row['satuan']); ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-danger">JUMLAH BARANG KELUAR / DIPAKAI (-)</label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-danger text-danger"><i class="bi bi-dash-lg fw-bold"></i></span>
                        <input type="number" name="jumlah_keluar" class="form-control border-danger text-danger fw-bold" placeholder="0" min="1" max="<?= $row['stok_akhir']; ?>" required>
                        <span class="input-group-text bg-white border-danger fw-bold"><?= htmlspecialchars($row['satuan']); ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="button" class="btn btn-light px-4 fw-bold rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger px-4 fw-bold rounded-3 text-white">Simpan Pemakaian</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: EDIT INFO DASAR -->
<div class="modal fade" id="modalEditInfo<?= $row['id_persediaan']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/edit.php" method="POST" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <input type="hidden" name="jenis_update" value="edit_info">
            <input type="hidden" name="id_persediaan" value="<?= $row['id_persediaan']; ?>">
            
            <div class="modal-header p-4" style="background-color: #f59e0b; color: #fff;">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Informasi Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">NAMA BARANG</label>
                    <input type="text" name="nama_barang" class="form-control border-2 p-2 fw-bold" value="<?= htmlspecialchars($row['nama_barang']); ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">SATUAN UKUR</label>
                        <input type="text" name="satuan" class="form-control border-2 p-2 fw-bold" value="<?= htmlspecialchars($row['satuan']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">UBAH STOK AWAL</label>
                        <input type="number" name="stok_awal" class="form-control border-2 p-2 fw-bold" value="<?= $row['stok_awal']; ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="button" class="btn btn-light px-4 fw-bold rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn px-4 fw-bold rounded-3 text-white" style="background-color: #f59e0b;">Update Info</button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// --- Notifikasi Sukses ---
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('status') === 'sukses') Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data disimpan!', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-4' } });
if (urlParams.get('status') === 'hapus_sukses') Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Data dihapus permanen!', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-4' } });
if (urlParams.get('status') === 'update_sukses') Swal.fire({ icon: 'success', title: 'Diupdate', text: 'Stok barang berhasil diperbarui!', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-4' } });
if (urlParams.has('status')) { window.history.replaceState({}, document.title, window.location.pathname); }

// --- Konfirmasi Hapus ---
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Persediaan?',
        text: "Data dan riwayat stok akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#001f3f',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4 shadow-lg border-0', confirmButton: 'rounded-pill px-4 fw-bold', cancelButton: 'rounded-pill px-4 fw-bold'}
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "../proses/hapus.php?hapus_persediaan=" + id;
        }
    })
}

// --- FUNGSI PROMPT CETAK & REDIRECT KE FILE PDF ---
async function promptCetak() {
    const { value: formValues } = await Swal.fire({
        title: 'Pengaturan Cetak Laporan',
        width: 600,
        html:
            '<div class="text-start mb-3"><small class="text-muted">Tentukan rentang tanggal laporan dan pejabat yang bertanda tangan.</small></div>' +
            
            // Tambahan Filter Tanggal
            '<div class="row g-2 mb-3">' +
                '<div class="col-6 text-start">' +
                    '<label class="fw-bold small">DARI TANGGAL:</label>' +
                    '<input type="date" id="swal-tgl-awal" class="form-control mt-1 border-2">' +
                '</div>' +
                '<div class="col-6 text-start">' +
                    '<label class="fw-bold small">SAMPAI TANGGAL:</label>' +
                    '<input type="date" id="swal-tgl-akhir" class="form-control mt-1 border-2">' +
                '</div>' +
            '</div><hr>' +

            // Input Tanda Tangan
            '<div class="mb-3 text-start">' +
                '<label class="fw-bold small">NAMA PENYETUJU / PEJABAT (Kanan):</label>' +
                '<input id="swal-nama" class="form-control mt-1 border-2" placeholder="Cth: Dr. Ahmad, M.T">' +
            '</div>' +
            '<div class="mb-3 text-start">' +
                '<label class="fw-bold small">NIP PEJABAT (Kanan):</label>' +
                '<input id="swal-nip" class="form-control mt-1 border-2" placeholder="Cth: 19800524...">' +
            '</div>' +
            '<div class="text-start"><small class="text-danger">*Kosongkan tanggal jika ingin mencetak SEMUA data.</small></div>',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-printer-fill me-2"></i> Lanjutkan Cetak',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ffcc00',
        cancelButtonColor: '#6c757d',
        customClass: { 
            popup: 'rounded-4 shadow-lg border-0', 
            confirmButton: 'rounded-pill px-4 fw-bold text-dark', 
            cancelButton: 'rounded-pill px-4 fw-bold text-white'
        },
        preConfirm: () => {
            return {
                tglAwal: document.getElementById('swal-tgl-awal').value,
                tglAkhir: document.getElementById('swal-tgl-akhir').value,
                namaKanan: document.getElementById('swal-nama').value || '...........................................',
                nipKanan: document.getElementById('swal-nip').value || '...........................................'
            }
        }
    });

    if (formValues) {
        // Ambil pencarian yang sedang aktif di tabel
        const search = "<?= htmlspecialchars($search) ?>";
        
        // Rakit URL beserta Parameter Tanggal
        const url = `cetak_persediaan.php?search=${search}` +
                    `&tgl_awal=${formValues.tglAwal}` +
                    `&tgl_akhir=${formValues.tglAkhir}` +
                    `&nama_kanan=${encodeURIComponent(formValues.namaKanan)}` +
                    `&nip_kanan=${encodeURIComponent(formValues.nipKanan)}`;
        
        // Buka tab baru untuk proses cetak
        window.open(url, '_blank');
    }
}
</script>
</body>
</html>