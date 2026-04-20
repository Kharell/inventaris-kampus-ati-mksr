<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. TANGKAP PARAMETER URL
$id_lab_selected = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';
$id_j_selected = isset($_GET['id_jurusan']) ? mysqli_real_escape_string($conn, $_GET['id_jurusan']) : '';

// 2. LOGIKA PENCARIAN & PAGINATION
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 3. LOGIKA NOTIFIKASI (BARU)
// Hitung notifikasi per Jurusan untuk ditampilkan di Sidebar/List
$sql_notif_jurusan = "SELECT l.id_jurusan, COUNT(*) as total 
                      FROM permintaan_bahan pb
                      JOIN bahan_praktek bp ON pb.id_barang = bp.id_praktek
                      JOIN lab l ON bp.id_lab = l.id_lab
                      WHERE pb.status = 'pending'
                      GROUP BY l.id_jurusan";
$res_notif_j = mysqli_query($conn, $sql_notif_jurusan);
$notif_jurusan = [];
while($row = mysqli_fetch_assoc($res_notif_j)) {
    $notif_jurusan[$row['id_jurusan']] = $row['total'];
}

// Hitung notifikasi per Lab (hanya jika jurusan dipilih)
$notif_lab = [];
if ($id_j_selected) {
    $sql_notif_lab = "SELECT bp.id_lab, COUNT(*) as total 
                      FROM permintaan_bahan pb
                      JOIN bahan_praktek bp ON pb.id_barang = bp.id_praktek
                      WHERE pb.status = 'pending'
                      GROUP BY bp.id_lab";
    $res_notif_l = mysqli_query($conn, $sql_notif_lab);
    while($row = mysqli_fetch_assoc($res_notif_l)) {
        $notif_lab[$row['id_lab']] = $row['total'];
    }
}

// Query Dasar untuk hitung total (Pagination)
$where = "WHERE id_lab = '$id_lab_selected'";
if($search) $where .= " AND (nama_bahan LIKE '%$search%' OR kode_bahan LIKE '%$search%')";

// Hitung Total Data untuk menentukan jumlah halaman
$total_data_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM bahan_praktek $where");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Info Lab Aktif
$nama_lab_aktif = "";
if ($id_lab_selected) {
    $qlab = mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab = '$id_lab_selected'");
    $rlab = mysqli_fetch_assoc($qlab);
    $nama_lab_aktif = $rlab['nama_lab'] ?? 'Laboratorium';
}

$qj = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Inventaris Bahan | ATI Makassar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --navy: #0a192f; 
            --navy-light: #112240;
            --gold: #ffcc00; 
            --soft-bg: #f4f7fe;
        }
        body { background-color: var(--soft-bg); font-family: 'Inter', sans-serif; color: #333; }
        .main-content { margin-left: 260px; padding: 30px; transition: 0.3s ease; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }

        .header-section { 
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); 
            color: white; padding: 40px; border-radius: 25px; margin-bottom: 35px; 
            box-shadow: 0 10px 30px rgba(10, 25, 47, 0.15); border-bottom: 4px solid var(--gold);
        }

        .list-group-item-action {
            border: none !important; margin-bottom: 10px; border-radius: 12px !important;
            transition: all 0.3s ease; border-left: 4px solid transparent !important;
            padding: 15px 20px; font-weight: 500;
        }
        .list-group-item-action.active {
            background-color: var(--navy) !important; color: white !important;
            border-left: 4px solid var(--gold) !important; transform: translateX(8px);
        }
        .lab-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 20px !important; border: 1px solid rgba(0,0,0,0.05) !important;
        }
        .lab-card:hover {
            transform: translateY(-5px); border-color: var(--gold) !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }

        .table-container { border-radius: 20px; border: none; overflow: hidden; background: white; }
        .hidden-form { display: none !important; }
        #formColumn { transition: all 0.4s ease; }
        
        .btn-navy { background-color: var(--navy); color: white; border-radius: 12px; font-weight: 600; }
        .btn-navy:hover { background-color: var(--navy-light); color: var(--gold); }
        .btn-gold { background-color: var(--gold); color: var(--navy); border-radius: 12px; font-weight: 700; }
        
        .badge-kode { background: #f0f3f9; color: var(--navy); padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; }

        .pagination .page-link { border: none; color: var(--navy); margin: 0 3px; border-radius: 8px !important; font-weight: 600; }
        .pagination .page-item.active .page-link { background-color: var(--navy); color: var(--gold); }
        .pagination .page-link:hover { background-color: #e9ecef; }
    </style> 
</head>
<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main-content">
    <?php include "../../includes/header.php"; ?>
    <br><br>



    
    
 <div class="header-section d-flex justify-content-between align-items-center">
    <div>
        <h1 class="fw-bold mb-1">
            <i class="bi <?= $id_lab_selected ? 'bi-box-seam-fill' : 'bi-intersect' ?> text-warning me-2"></i>
            <?= $id_lab_selected ? $nama_lab_aktif : "Pusat Inventaris Bahan" ?>
        </h1>
        <p class="mb-0 text-white-50 fs-6">Politeknik ATI Makassar • Sistem Manajemen Stok Bahan Praktek</p>
    </div>

    <div class="d-flex align-items-center gap-3">
        <?php if($id_lab_selected): 
            // Ambil status saat ini dari database
            $check_st = mysqli_query($conn, "SELECT nilai_pengaturan FROM pengaturan_sistem WHERE nama_pengaturan = 'status_input_stok'");
            $st_data = mysqli_fetch_assoc($check_st);
            $is_on = ($st_data['nilai_pengaturan'] == 1);
        ?>
            <div class="bg-white bg-opacity-10 p-2 px-3 rounded-pill border border-white border-opacity-25 d-flex align-items-center shadow-sm">
                <span class="small fw-bold me-3 text-white">
                    <i class="bi bi-power me-1"></i> Mode Input Lab: 
                    <span class="badge <?= $is_on ? 'bg-success' : 'bg-danger' ?> ms-1" id="status-text">
                        <?= $is_on ? 'AKTIF' : 'NONAKTIF' ?>
                    </span>
                </span>
                
                <div class="form-check form-switch m-0 p-0" style="min-height: auto;">
                    <input class="form-check-input custom-switch" type="checkbox" role="switch" 
                           id="toggleInputStok" <?= $is_on ? 'checked' : '' ?> 
                           style="width: 2.5em; height: 1.25em; cursor: pointer;">
                </div>
            </div>
            
            <a href="bahan-praktek.php" class="btn btn-outline-light rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        <?php endif; ?>
    </div>
</div>

<style>

    #status-text {
    min-width: 120px; /* Memberi ruang tetap untuk kata NONAKTIF */
    text-align: center;
    font-weight: 600;
}

.form-switch .form-check-input {
    width: 2.8em !important;
    height: 1.4em !important;
    margin-top: 0.1em;
}
/* Style Custom untuk Switch */
.custom-switch:checked {
    background-color: #198754 !important;
    border-color: #198754 !important;
    box-shadow: 0 0 8px rgba(25, 135, 84, 0.5);
}
.custom-switch {
    background-color: #dc3545;
    border-color: #dc3545;
}
</style>








    <?php if (!$id_lab_selected): ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 25px;">
                <h5 class="fw-bold text-navy mb-4"><i class="bi bi-filter-square me-2"></i>Filter Jurusan</h5>
                <div class="list-group list-group-flush" id="list-tab" role="tablist">
                    <?php 
                    $first = true; mysqli_data_seek($qj, 0);
                    while($rj = mysqli_fetch_assoc($qj)): 
                        $id_j_loop = $rj['id_jurusan'];
                    ?>
                    <a class="list-group-item list-group-item-action <?= ($first && !$id_j_selected) || $id_j_selected == $id_j_loop ? 'active' : '' ?> d-flex justify-content-between align-items-center" 
                       data-bs-toggle="list" href="#pane-<?= $id_j_loop ?>" role="tab">
                       <span><i class="bi bi-chevron-right me-2"></i><?= $rj['nama_jurusan'] ?></span>
                       <?php if(isset($notif_jurusan[$id_j_loop])): ?>
                            <span class="badge bg-danger rounded-pill shadow-sm"><?= $notif_jurusan[$id_j_loop] ?></span>
                       <?php endif; ?>
                    </a>
                    <?php $first = false; endwhile; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="tab-content" id="nav-tabContent">
                <?php 
                $first = true; mysqli_data_seek($qj, 0);
                while($rj = mysqli_fetch_assoc($qj)): 
                    $id_j = $rj['id_jurusan'];
                    $ql = mysqli_query($conn, "SELECT lab.*, kepala_lab.nama_kepala 
                                             FROM lab 
                                             LEFT JOIN kepala_lab ON lab.id_lab = kepala_lab.id_lab 
                                             WHERE lab.id_jurusan = '$id_j'");
                ?>
                <div class="tab-pane fade <?= ($first && !$id_j_selected) || $id_j_selected == $id_j ? 'show active' : '' ?>" id="pane-<?= $id_j ?>" role="tabpanel">
                    <div class="row g-3">
                        <?php if(mysqli_num_rows($ql) > 0): while($rl = mysqli_fetch_assoc($ql)): ?>
                            <div class="col-md-6">
                                <a href="?id_lab=<?= $rl['id_lab'] ?>&id_jurusan=<?= $id_j ?>" class="lab-card text-decoration-none card p-4 d-flex flex-row align-items-center h-100 bg-white shadow-sm border-0 position-relative">
                                    <?php if(isset($notif_lab[$rl['id_lab']])): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow" style="z-index: 5;">
                                            <?= $notif_lab[$rl['id_lab']] ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <div class="me-3 p-3 bg-light rounded-4 text-navy shadow-sm">
                                        <i class="bi bi-building-gear fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><?= $rl['nama_lab'] ?></h6>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= $rl['nama_kepala'] ?? 'N/A' ?></small>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; else: ?>
                            <div class="col-12 text-center py-5 bg-white rounded-5 shadow-sm">
                                <p class="text-muted mt-3">Belum ada data laboratorium tersedia.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $first = false; endwhile; ?>
            </div>
        </div>
    </div>

    <?php else: ?>
    <?php 
    $check_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM permintaan_bahan pb 
                                          JOIN bahan_praktek bp ON pb.id_barang = bp.id_praktek 
                                          WHERE bp.id_lab = '$id_lab_selected' AND pb.status = 'pending'");
    $count_pending = mysqli_fetch_assoc($check_pending)['total'];

    if($count_pending > 0): 
    ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex justify-content-between align-items-center mb-4" style="border-radius: 20px; padding: 20px;">
        <div class="d-flex align-items-center">
            <div class="bg-warning text-dark rounded-circle p-2 me-3">
                <i class="bi bi-bell-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0">Konfirmasi Stok Dibutuhkan</h6>
                <small>Ada <?= $count_pending ?> item yang baru saja diinput oleh Kepala Lab.</small>
            </div>
        </div>
        <button class="btn btn-navy rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalKonfirmasi">
            Periksa Sekarang
        </button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-navy mb-0">Daftar Inventaris Bahan</h4>
        <button id="btnTambahToggle" class="btn btn-navy py-3 px-4 shadow-sm" onclick="toggleForm()">
            <i class="bi bi-plus-lg me-2"></i> Tambah Data Baru
        </button>
    </div>

    <div class="row g-4" id="inventoryContainer">
        <div class="col-12" id="tableColumn">
            <div class="card shadow-sm table-container border-0">
                <div class="p-4 bg-white border-bottom d-flex flex-column flex-md-row justify-content-between gap-3">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="id_lab" value="<?= $id_lab_selected ?>">
                        <input type="hidden" name="id_jurusan" value="<?= $id_j_selected ?>">
                        <select name="limit" class="form-select border-0 bg-light" onchange="this.form.submit()" style="width: 80px; border-radius: 10px;">
                            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                        <div class="input-group ms-md-2" style="max-width: 300px;">
                            <input type="text" name="search" class="form-control border-0 bg-light px-3" placeholder="Cari Kode/Nama..." value="<?= $search ?>" style="border-radius: 10px 0 0 10px;">
                            <button class="btn btn-navy" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">NO</th>
                                <th>KODE</th>
                                <th>ALAT / BAHAN</th>
                                <th>KONDISI</th>
                                <th>STOK</th>
                                <th class="text-center pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $res = mysqli_query($conn, "SELECT * FROM bahan_praktek $where ORDER BY id_praktek DESC LIMIT $offset, $limit");
                            $no = $offset + 1;
                            if(mysqli_num_rows($res) > 0):
                                while($row = mysqli_fetch_assoc($res)): 
                                    $badge_color = ($row['kondisi'] == 'Baik') ? 'bg-success' : (($row['kondisi'] == 'Rusak') ? 'bg-danger' : 'bg-warning text-dark');
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted"><?= $no++ ?></td>
                                <td><span class="badge-kode"><?= $row['kode_bahan'] ?></span></td>
                                <td>
                                    <div class="fw-bold text-navy mb-0"><?= $row['nama_bahan'] ?></div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;"><?= $row['spesifikasi'] ?></small>
                                </td>
                                <td><span class="badge <?= $badge_color ?> rounded-pill px-3"><?= $row['kondisi'] ?></span></td>
                                <td><span class="fw-800 text-primary fs-5"><?= $row['stok'] ?></span> <small class="text-muted"><?= $row['satuan'] ?></small></td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light border shadow-sm me-1" onclick='openEditModal(<?= json_encode($row) ?>)'><i class="bi bi-pencil-square text-primary"></i></button>
                                        <button class="btn btn-sm btn-light border shadow-sm" onclick="confirmDelete(<?= $row['id_praktek'] ?>)"><i class="bi bi-trash3 text-danger"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Data inventaris kosong.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white p-4 border-0 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <p class="text-muted small mb-md-0">
                        Menampilkan <?= min($offset + 1, $total_data) ?> sampai <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?> data
                    </p>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?id_lab=<?= $id_lab_selected ?>&id_jurusan=<?= $id_j_selected ?>&page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?id_lab=<?= $id_lab_selected ?>&id_jurusan=<?= $id_j_selected ?>&page=<?= $i ?>&limit=<?= $limit ?>&search=<?= $search ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?id_lab=<?= $id_lab_selected ?>&id_jurusan=<?= $id_j_selected ?>&page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-md-4 hidden-form" id="formColumn">
            <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 25px; position: sticky; top: 20px;">
                <div class="card-header bg-navy p-4 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white fw-bold mb-0">Tambah Bahan</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="toggleForm()"></button>
                </div>
                <div class="card-body p-4">
                    <form action="../proses/tambah.php" method="POST">
                        <input type="hidden" name="id_lab" value="<?= $id_lab_selected ?>">
                        <input type="hidden" name="id_jurusan" value="<?= $id_j_selected ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">KODE BAHAN</label>
                            <input type="text" name="kode_bahan" class="form-control border-0 bg-light py-2" placeholder="Contoh: ELK-001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NAMA ALAT / BAHAN</label>
                            <input type="text" name="nama_bahan" class="form-control border-0 bg-light py-2" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">SPESIFIKASI</label>
                            <textarea name="spesifikasi" class="form-control border-0 bg-light" rows="3"></textarea>
                        </div>
                        <div class="row mb-4 g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">KONDISI</label>
                                <select name="kondisi" class="form-select border-0 bg-light">
                                    <option value="Baik">Baik</option>
                                    <option value="Kurang Baik">Kurang Baik</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">SATUAN</label>
                                <input type="text" name="satuan" class="form-control border-0 bg-light" placeholder="Pcs/Roll" required>
                            </div>
                        </div>
                        <button type="submit" name="tambah_bahan_lab" class="btn btn-gold w-100 py-3 shadow-sm">SIMPAN DATA</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalKonfirmasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header bg-navy text-white p-4 border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle me-2 text-warning"></i>Konfirmasi Data Kepala Lab</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">NAMA BAHAN</th>
                                <th>STOK SAAT INI</th>
                                <th>STOK DILAPORKAN</th>
                                <th>TANGGAL INPUT</th>
                                <th class="text-center pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q_konf = mysqli_query($conn, "SELECT pb.*, bp.nama_bahan, bp.stok as stok_lama 
                                                           FROM permintaan_bahan pb
                                                           JOIN bahan_praktek bp ON pb.id_barang = bp.id_praktek
                                                           WHERE bp.id_lab = '$id_lab_selected' AND pb.status = 'pending'
                                                           ORDER BY pb.tgl_permintaan ASC");
                            if(mysqli_num_rows($q_konf) > 0):
                                while($rk = mysqli_fetch_assoc($q_konf)):
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-navy"><?= $rk['nama_bahan'] ?></td>
                                <td><span class="badge bg-light text-muted border"><?= $rk['stok_lama'] ?></span></td>
                                <td><span class="badge bg-primary px-3 fs-6"><?= $rk['stok_saat_ini'] ?></span></td>
                                <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($rk['tgl_permintaan'])) ?></small></td>
                                <td class="text-center pe-4">
                                    <form action="../proses/tambah.php" method="POST" class="d-inline">
                                        <input type="hidden" name="id_permintaan" value="<?= $rk['id_permintaan'] ?>">
                                        <input type="hidden" name="id_barang" value="<?= $rk['id_barang'] ?>">
                                        <input type="hidden" name="stok_baru" value="<?= $rk['stok_saat_ini'] ?>">
                                        <input type="hidden" name="id_lab_back" value="<?= $id_lab_selected ?>">
                                        <input type="hidden" name="id_j_back" value="<?= $id_j_selected ?>">
                                        
                                        <button type="submit" name="setujui" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                                            <i class="bi bi-check-lg"></i> Setujui
                                        </button>

                                        <button type="submit" name="tolak" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm ms-1" onclick="return confirm('Apakah Anda yakin ingin menolak data ini?')">
                                            <i class="bi bi-x-lg"></i> Tolak
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-center py-4">Semua data telah dikonfirmasi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light" style="border-radius: 0 0 25px 25px;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 25px;">
            <div class="modal-header bg-navy p-4 text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Inventaris</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/edit.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_praktek" id="edit_id">
                    <input type="hidden" name="id_lab_back" value="<?= $id_lab_selected ?>">
                    <input type="hidden" name="id_j_back" value="<?= $id_j_selected ?>">
                    <div class="row g-4">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Kode Bahan</label>
                            <input type="text" name="kode_bahan" id="edit_kode" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Nama Alat / Bahan</label>
                            <input type="text" name="nama_bahan" id="edit_nama" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Spesifikasi</label>
                            <textarea name="spesifikasi" id="edit_spek" class="form-control border-0 bg-light" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kondisi</label>
                            <select name="kondisi" id="edit_kondisi" class="form-select border-0 bg-light">
                                <option value="Baik">Baik</option>
                                <option value="Kurang Baik">Kurang Baik</option>
                                <option value="Rusak">Rusak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Satuan</label>
                            <input type="text" name="satuan" id="edit_satuan" class="form-control border-0 bg-light" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 border-0 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_bahan_lab" class="btn btn-gold rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function toggleForm() {
        const formCol = document.getElementById('formColumn');
        const tableCol = document.getElementById('tableColumn');
        const btnTambah = document.getElementById('btnTambahToggle');

        if (formCol.classList.contains('hidden-form')) {
            formCol.classList.remove('hidden-form');
            tableCol.classList.replace('col-12', 'col-md-8');
            btnTambah.classList.replace('btn-navy', 'btn-danger');
            btnTambah.innerHTML = '<i class="bi bi-x-lg me-2"></i> Tutup Form';
        } else {
            formCol.classList.add('hidden-form');
            tableCol.classList.replace('col-md-8', 'col-12');
            btnTambah.classList.replace('btn-danger', 'btn-navy');
            btnTambah.innerHTML = '<i class="bi bi-plus-lg me-2"></i> Tambah Data Baru';
        }
    }

    function openEditModal(data) {
        document.getElementById('edit_id').value = data.id_praktek;
        document.getElementById('edit_kode').value = data.kode_bahan;
        document.getElementById('edit_nama').value = data.nama_bahan;
        document.getElementById('edit_spek').value = data.spesifikasi;
        document.getElementById('edit_kondisi').value = data.kondisi;
        document.getElementById('edit_satuan').value = data.satuan;
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#0a192f',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../proses/hapus.php?id=${id}&modul=praktek_pusat&id_lab=<?= $id_lab_selected ?>&id_j=<?= $id_j_selected ?>`;
            }
        });
    }
</script>

<script>
$(document).ready(function() {
    $('#toggleInputStok').on('change', function() {
        var isChecked = $(this).is(':checked');
        var statusValue = isChecked ? 1 : 0;

        console.log("Mengirim status: " + statusValue); // Debug log

        $.ajax({
            url: '../proses/update_akses.php', 
            type: 'POST',
            data: { status: statusValue },
            success: function(response) {
                console.log("Respon Server: " + response); // Debug log
                
                if(response.trim() == 'success') {
                    // Update Badge secara visual
                    if(isChecked) {
                        $('#status-text').text('AKTIF').removeClass('bg-danger').addClass('bg-success');
                    } else {
                        $('#status-text').text('NONAKTIF').removeClass('bg-success').addClass('bg-danger');
                    }
                    
                    // Notifikasi SweetAlert
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    Toast.fire({
                        icon: 'success',
                        title: isChecked ? 'Input Lab Diaktifkan' : 'Input Lab Dinonaktifkan'
                    });
                } else {
                    alert("Database gagal diupdate: " + response);
                }
            },
            error: function(xhr, status, error) {
                console.error("Gagal koneksi ke file update_akses.php");
            }
        });
    });
});
</script>

<?php if (isset($_GET['status'])): ?>
<script>
    // Ambil pesan dari URL
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const status = urlParams.get('status');

    if (status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: msg ? msg : 'Data berhasil diproses',
            showConfirmButton: false,
            timer: 2000 // Pop-up hilang otomatis dalam 2 detik
        });
    } else if (status === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: msg ? msg : 'Terjadi kesalahan sistem',
            confirmButtonColor: '#d33'
        });
    }

    // Bersihkan URL agar pop-up tidak muncul lagi saat halaman di-refresh
    window.history.replaceState({}, document.title, window.location.pathname + 
        (urlParams.get('id_lab') ? `?id_lab=${urlParams.get('id_lab')}&id_jurusan=${urlParams.get('id_jurusan')}` : ''));
</script>
<?php endif; ?>
</body>
</html>