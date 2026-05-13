<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

// 1. TANGKAP PARAMETER URL
$id_lab_selected = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';
$id_j_selected = isset($_GET['id_jurusan']) ? mysqli_real_escape_string($conn, $_GET['id_jurusan']) : '';

// 2. LOGIKA PENCARIAN & PAGINATION
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 3. LOGIKA NOTIFIKASI
// Hitung notifikasi per Jurusan
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

// PERBAIKAN: Hitung notifikasi per Lab (Dihitung terus tanpa syarat IF agar muncul di kartu)
$notif_lab = [];
$sql_notif_lab = "SELECT bp.id_lab, COUNT(*) as total 
                  FROM permintaan_bahan pb
                  JOIN bahan_praktek bp ON pb.id_barang = bp.id_praktek
                  WHERE pb.status = 'pending'
                  GROUP BY bp.id_lab";
$res_notif_l = mysqli_query($conn, $sql_notif_lab);
while($row = mysqli_fetch_assoc($res_notif_l)) {
    $notif_lab[$row['id_lab']] = $row['total'];
}

// Query Dasar untuk hitung total (Pagination)
$where = "WHERE id_lab = '$id_lab_selected'";
if($search) $where .= " AND (nama_bahan LIKE '%$search%' OR kode_bahan LIKE '%$search%')";

// Hitung Total Data
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* === VARIABEL WARNA & TEMA === */
        :root { 
            --navy: #001f3f; 
            --navy-light: #003366;
            --gold: #ffcc00; 
            --soft-bg: #f4f7fc;
            --card-radius: 20px;
        }
        body { background-color: var(--soft-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #2d3436; overflow-x: hidden; }
        
        /* === LAYOUT UTAMA === */
        .main-content { margin-left: 260px; padding: 2rem; padding-top: 80px; transition: 0.3s ease; min-height: 100vh; }
        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 1rem; padding-top: 80px; } }

        /* === BANNER HEADER === */
        .header-section { 
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); 
            color: white; padding: 35px; border-radius: var(--card-radius); margin-bottom: 30px; 
            box-shadow: 0 10px 30px rgba(0, 31, 63, 0.15); border-bottom: 4px solid var(--gold);
            position: relative; overflow: hidden;
        }
        .header-section::after {
            content: ''; position: absolute; right: -50px; top: -100px; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,204,0,0.1) 0%, transparent 70%); border-radius: 50%;
        }

        /* === KOMPONEN KARTU & TABEL === */
        .glass-card { background: white; border-radius: var(--card-radius); box-shadow: 0 5px 20px rgba(0,0,0,0.03); border: none; }
        
        .list-group-custom .list-group-item {
            border: none; margin-bottom: 8px; border-radius: 12px; transition: all 0.3s ease;
            padding: 14px 20px; font-weight: 600; color: #4b5563; border-left: 4px solid transparent;
        }
        .list-group-custom .list-group-item:hover { background-color: #f8fafc; }
        .list-group-custom .list-group-item.active {
            background-color: var(--navy); color: white; border-left: 4px solid var(--gold);
            box-shadow: 0 8px 15px rgba(0, 31, 63, 0.2); transform: translateX(5px);
        }

        .lab-card {
            background: white; border-radius: 16px; padding: 20px; border: 1px solid #edf2f7;
            transition: all 0.3s ease; display: flex; align-items: center; text-decoration: none; color: inherit;
        }
        .lab-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-color: var(--gold); }

        .badge-kode { background: #f1f5f9; color: var(--navy); padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; border: 1px solid #e2e8f0; }

        /* === TOMBOL & SWITCH === */
        .btn-navy { background-color: var(--navy); color: white; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: 0.3s; border: none; }
        .btn-navy:hover { background-color: #001224; color: var(--gold); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 31, 63, 0.2); }
        .btn-gold { background-color: var(--gold); color: var(--navy); border-radius: 10px; font-weight: 700; padding: 12px 20px; border: none; transition: 0.3s;}
        .btn-gold:hover { background-color: #e6b800; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,204,0,0.3); }

        .form-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; border: none; background-color: #dc3545; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); }
        .form-switch .form-check-input:checked { background-color: #198754; }
        #status-text { min-width: 90px; text-align: center; }

        /* === ANIMASI TRANISI KOLOM === */
        .transition-col { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        
        .table > :not(caption) > * > * { padding: 1rem 0.75rem; vertical-align: middle; }
    </style> 
</head>
<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main-content">
    <?php include "../../includes/header.php"; ?>
    
    <div class="header-section d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div style="z-index: 2;">
            <h2 class="fw-bold mb-1">
                <i class="bi <?= $id_lab_selected ? 'bi-box-seam-fill' : 'bi-intersect' ?> text-warning me-2"></i>
                <?= $id_lab_selected ? $nama_lab_aktif : "Pusat Inventaris Bahan" ?>
            </h2>
            <p class="mb-0 text-white-50 fs-6">Sistem Manajemen Stok Bahan Praktek • Politeknik ATI Makassar</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3" style="z-index: 2;">
            <?php if($id_lab_selected): 
                $check_st = mysqli_query($conn, "SELECT nilai_pengaturan FROM pengaturan_sistem WHERE nama_pengaturan = 'status_input_stok'");
                $st_data = mysqli_fetch_assoc($check_st);
                $is_on = ($st_data['nilai_pengaturan'] == 1);
            ?>
                <div class="bg-white bg-opacity-10 py-2 px-3 rounded-3 border border-white border-opacity-25 d-flex align-items-center shadow-sm" style="backdrop-filter: blur(10px);">
                    <span class="small fw-bold me-3 text-white">
                        <i class="bi bi-power me-1 text-warning"></i> Mode Input Lab: 
                        <span class="badge <?= $is_on ? 'bg-success' : 'bg-danger' ?> ms-1" id="status-text">
                            <?= $is_on ? 'AKTIF' : 'NONAKTIF' ?>
                        </span>
                    </span>
                    <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                        <input class="form-check-input m-0" type="checkbox" role="switch" id="toggleInputStok" <?= $is_on ? 'checked' : '' ?>>
                    </div>
                </div>
                
                <a href="bahan-praktek.php" class="btn btn-outline-light rounded-3 fw-bold px-4">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$id_lab_selected): ?>
    <div class="row g-4">
        <div class="col-lg-3 col-md-4">
            <div class="glass-card p-4 h-100">
                <h6 class="fw-bold text-navy text-uppercase mb-4"><i class="bi bi-funnel-fill me-2 text-warning"></i>Filter Jurusan</h6>
                <div class="list-group list-group-custom" id="list-tab" role="tablist">
                    <?php 
                    $first = true; mysqli_data_seek($qj, 0);
                    while($rj = mysqli_fetch_assoc($qj)): 
                        $id_j_loop = $rj['id_jurusan'];
                    ?>
                    <a class="list-group-item d-flex justify-content-between align-items-center <?= ($first && !$id_j_selected) || $id_j_selected == $id_j_loop ? 'active' : '' ?>" 
                       data-bs-toggle="list" href="#pane-<?= $id_j_loop ?>" role="tab">
                        <span><i class="bi bi-mortarboard me-2"></i><?= $rj['nama_jurusan'] ?></span>
                        <?php if(isset($notif_jurusan[$id_j_loop]) && $notif_jurusan[$id_j_loop] > 0): ?>
                            <span class="badge bg-danger rounded-pill shadow-sm"><?= $notif_jurusan[$id_j_loop] ?></span>
                        <?php endif; ?>
                    </a>
                    <?php $first = false; endwhile; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <div class="tab-content" id="nav-tabContent">
                <?php 
                $first = true; mysqli_data_seek($qj, 0);
                while($rj = mysqli_fetch_assoc($qj)): 
                    $id_j = $rj['id_jurusan'];
                    $ql = mysqli_query($conn, "SELECT lab.*, kepala_lab.nama_kepala 
                                               FROM lab LEFT JOIN kepala_lab ON lab.id_lab = kepala_lab.id_lab 
                                               WHERE lab.id_jurusan = '$id_j'");
                ?>
                <div class="tab-pane fade <?= ($first && !$id_j_selected) || $id_j_selected == $id_j ? 'show active' : '' ?>" id="pane-<?= $id_j ?>" role="tabpanel">
                    <h5 class="fw-bold mb-4 text-navy"><i class="bi bi-grid-fill me-2 text-warning"></i>Daftar Laboratorium</h5>
                    <div class="row g-3">
                        <?php if(mysqli_num_rows($ql) > 0): while($rl = mysqli_fetch_assoc($ql)): ?>
                            <div class="col-xl-4 col-md-6">
                                <a href="?id_lab=<?= $rl['id_lab'] ?>&id_jurusan=<?= $id_j ?>" class="lab-card position-relative">
                                    <?php if(isset($notif_lab[$rl['id_lab']]) && $notif_lab[$rl['id_lab']] > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow" style="z-index: 5;">
                                            <?= $notif_lab[$rl['id_lab']] ?> Req Baru
                                        </span>
                                    <?php endif; ?>
                                    
                                    <div class="me-3 p-3 bg-light rounded-4 text-navy shadow-sm d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                        <i class="bi bi-pc-display-horizontal fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1" style="line-height: 1.2;"><?= $rl['nama_lab'] ?></h6>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= $rl['nama_kepala'] ?? 'Belum ada Ka. Lab' ?></small>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; else: ?>
                            <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm border border-light">
                                <i class="bi bi-inboxes text-muted opacity-25" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-3 fw-bold">Belum ada data laboratorium di jurusan ini.</p>
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
    <div class="alert bg-warning bg-opacity-10 border border-warning shadow-sm d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4 gap-3 p-3" style="border-radius: 15px;">
        <div class="d-flex align-items-center">
            <div class="bg-warning text-dark rounded-circle p-2 me-3 shadow-sm">
                <i class="bi bi-bell-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Konfirmasi Stok Dibutuhkan</h6>
                <small class="text-dark opacity-75">Terdapat <b><?= $count_pending ?> material</b> yang baru saja dilaporkan Kepala Lab.</small>
            </div>
        </div>
        <button class="btn btn-dark fw-bold px-4 rounded-3" data-bs-toggle="modal" data-bs-target="#modalKonfirmasi">
            Periksa Sekarang <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold text-navy mb-0"><i class="bi bi-layers-fill me-2 text-primary"></i>Database Material Lab</h4>
        <button id="btnTambahToggle" class="btn btn-navy shadow-sm" onclick="toggleForm()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Data Baru
        </button>
    </div>

    <div class="row g-4 position-relative">
        <div class="col-xl-12 transition-col" id="tableColumn">
            <div class="glass-card p-0">
                <div class="p-4 bg-light bg-opacity-50 border-bottom d-flex flex-column flex-md-row justify-content-between gap-3">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-grow-1">
                        <input type="hidden" name="id_lab" value="<?= $id_lab_selected ?>">
                        <input type="hidden" name="id_jurusan" value="<?= $id_j_selected ?>">
                        <select name="limit" class="form-select border-0 shadow-sm" onchange="this.form.submit()" style="width: auto; min-width: 100px;">
                            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5 Baris</option>
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 Baris</option>
                            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25 Baris</option>
                        </select>
                        <div class="input-group shadow-sm" style="max-width: 300px;">
                            <input type="text" name="search" class="form-control border-0" placeholder="Cari Kode/Nama..." value="<?= $search ?>">
                            <button class="btn btn-primary fw-bold" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-muted small fw-bold">NO</th>
                                <th class="text-muted small fw-bold">KODE</th>
                                <th class="text-muted small fw-bold">ALAT / BAHAN</th>
                                <th class="text-muted small fw-bold">KONDISI</th>
                                <th class="text-muted small fw-bold">STOK</th>
                                <th class="text-center pe-4 text-muted small fw-bold">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $res = mysqli_query($conn, "SELECT * FROM bahan_praktek $where ORDER BY id_praktek DESC LIMIT $offset, $limit");
                            $no = $offset + 1;
                            if(mysqli_num_rows($res) > 0):
                                while($row = mysqli_fetch_assoc($res)): 
                                    $badge_color = ($row['kondisi'] == 'Baik') ? 'bg-success-subtle text-success border-success' : (($row['kondisi'] == 'Rusak') ? 'bg-danger-subtle text-danger border-danger' : 'bg-warning-subtle text-dark border-warning');
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted"><?= $no++ ?></td>
                                <td><span class="badge-kode"><?= $row['kode_bahan'] ?></span></td>
                                <td>
                                    <div class="fw-bold text-navy mb-0"><?= $row['nama_bahan'] ?></div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;" title="<?= $row['spesifikasi'] ?>"><?= $row['spesifikasi'] ?: '-' ?></small>
                                </td>
                                <td><span class="badge border <?= $badge_color ?> rounded-pill px-3 py-2"><?= $row['kondisi'] ?></span></td>
                                <td><span class="fw-bold text-primary fs-5"><?= $row['stok'] ?></span> <small class="text-muted fw-bold"><?= $row['satuan'] ?></small></td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-light border shadow-sm rounded-3" onclick='openEditModal(<?= json_encode($row) ?>)' title="Edit"><i class="bi bi-pencil-square text-primary"></i></button>
                                        <button class="btn btn-sm btn-light border shadow-sm rounded-3" onclick="confirmDelete(<?= $row['id_praktek'] ?>)" title="Hapus"><i class="bi bi-trash3 text-danger"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-folder-x fs-1 d-block mb-2 opacity-50"></i>Tidak ada material ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-top bg-light bg-opacity-50 d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <p class="text-muted small mb-3 mb-md-0 fw-bold">
                        Menampilkan <?= min($offset + 1, $total_data) ?> - <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?> data
                    </p>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 shadow-sm">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link border-0" href="?id_lab=<?= $id_lab_selected ?>&id_jurusan=<?= $id_j_selected ?>&page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link border-0 fw-bold" href="?id_lab=<?= $id_lab_selected ?>&id_jurusan=<?= $id_j_selected ?>&page=<?= $i ?>&limit=<?= $limit ?>&search=<?= $search ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link border-0" href="?id_lab=<?= $id_lab_selected ?>&id_jurusan=<?= $id_j_selected ?>&page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-xl-4 d-none transition-col" id="formColumn">
            <div class="glass-card p-0" style="position: sticky; top: 90px;">
                <div class="bg-navy p-4 text-white d-flex justify-content-between align-items-center" style="background-color: var(--navy);">
                    <h5 class="fw-bold mb-0"><i class="bi bi-node-plus-fill text-warning me-2"></i>Tambah Material</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="toggleForm()"></button>
                </div>
                <div class="p-4">
                    <form action="../proses/tambah.php" method="POST">
                        <input type="hidden" name="id_lab" value="<?= $id_lab_selected ?>">
                        <input type="hidden" name="id_jurusan" value="<?= $id_j_selected ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">KODE BAHAN <span class="text-danger">*</span></label>
                            <input type="text" name="kode_bahan" class="form-control" placeholder="Cth: MAT-001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">NAMA ALAT / BAHAN <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahan" class="form-control" placeholder="Masukkan nama material..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">SPESIFIKASI</label>
                            <textarea name="spesifikasi" class="form-control" rows="3" placeholder="Detail opsional..."></textarea>
                        </div>
                        <div class="row mb-4 g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">KONDISI <span class="text-danger">*</span></label>
                                <select name="kondisi" class="form-select">
                                    <option value="Baik">Baik</option>
                                    <option value="Kurang Baik">Kurang Baik</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">SATUAN <span class="text-danger">*</span></label>
                                <input type="text" name="satuan" class="form-control" placeholder="Pcs/Roll" required>
                            </div>
                        </div>
                        <button type="submit" name="tambah_bahan_lab" class="btn btn-gold w-100 py-3 shadow-sm fs-6">
                            <i class="bi bi-save2-fill me-2"></i>SIMPAN DATA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalKonfirmasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-navy text-white p-4 border-0" style="background-color: var(--navy);">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2 text-warning"></i>Validasi Laporan Kepala Lab</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-white border-bottom">
                            <tr>
                                <th class="ps-4 py-3 text-muted small fw-bold">NAMA BAHAN</th>
                                <th class="text-muted small fw-bold">STOK LAMA</th>
                                <th class="text-muted small fw-bold">DILAPORKAN (TAMBAHAN)</th>
                                <th class="text-muted small fw-bold">HASIL AKHIR</th>
                                <th class="text-muted small fw-bold">WAKTU LAPOR</th>
                                <th class="text-center pe-4 text-muted small fw-bold">AKSI</th>
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
                                    // LOGIKA PERBAIKAN: Menjumlahkan stok lama dengan yang baru dilaporkan
                                    $stok_akumulasi = $rk['stok_lama'] + $rk['stok_saat_ini'];
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-navy"><?= $rk['nama_bahan'] ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary border px-3 py-2 fs-6"><?= $rk['stok_lama'] ?></span></td>
                                <td><span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fs-6 fw-bold">+ <?= $rk['stok_saat_ini'] ?></span></td>
                                <td><span class="badge bg-success text-white px-3 py-2 fs-6 fw-bold shadow-sm">= <?= $stok_akumulasi ?></span></td>
                                <td><small class="text-muted fw-bold"><?= date('d M Y, H:i', strtotime($rk['tgl_permintaan'])) ?></small></td>
                                <td class="text-center pe-4">
                                    <form action="../proses/tambah.php" method="POST" class="d-flex justify-content-center gap-2">
                                        <input type="hidden" name="id_permintaan" value="<?= $rk['id_permintaan'] ?>">
                                        <input type="hidden" name="id_barang" value="<?= $rk['id_barang'] ?>">
                                        <input type="hidden" name="stok_baru" value="<?= $stok_akumulasi ?>">
                                        <input type="hidden" name="id_lab_back" value="<?= $id_lab_selected ?>">
                                        <input type="hidden" name="id_j_back" value="<?= $id_j_selected ?>">
                                        
                                        <button type="submit" name="setujui" class="btn btn-success btn-sm rounded-3 fw-bold px-3 shadow-sm">
                                            <i class="bi bi-check-lg"></i> Terima
                                        </button>
                                        <button type="submit" name="tolak" class="btn btn-outline-danger btn-sm rounded-3 fw-bold px-3 bg-white" onclick="return confirm('Tolak data ini secara permanen?')">
                                            <i class="bi bi-x-lg"></i> Tolak
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>Semua data telah dikonfirmasi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="button" class="btn btn-light fw-bold px-4 rounded-3" data-bs-dismiss="modal">Tutup Panel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-navy p-4 text-white border-0" style="background-color: var(--navy);">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Inventaris</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../proses/edit.php" method="POST">
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="id_praktek" id="edit_id">
                    <input type="hidden" name="id_lab_back" value="<?= $id_lab_selected ?>">
                    <input type="hidden" name="id_j_back" value="<?= $id_j_selected ?>">
                    <div class="row g-4 bg-white p-4 rounded-4 shadow-sm border border-light">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">KODE BAHAN</label>
                            <input type="text" name="kode_bahan" id="edit_kode" class="form-control fw-bold bg-light" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted">NAMA ALAT / BAHAN</label>
                            <input type="text" name="nama_bahan" id="edit_nama" class="form-control bg-light" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">SPESIFIKASI</label>
                            <textarea name="spesifikasi" id="edit_spek" class="form-control bg-light" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">KONDISI SAAT INI</label>
                            <select name="kondisi" id="edit_kondisi" class="form-select bg-light">
                                <option value="Baik">Baik</option>
                                <option value="Kurang Baik">Kurang Baik</option>
                                <option value="Rusak">Rusak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">SATUAN UKUR</label>
                            <input type="text" name="satuan" id="edit_satuan" class="form-control bg-light" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 border-0 bg-white">
                    <button type="button" class="btn btn-light fw-bold px-4 rounded-3" data-bs-dismiss="modal">Batalkan</button>
                    <button type="submit" name="update_bahan_lab" class="btn btn-gold fw-bold px-4 rounded-3">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ANIMASI SLIDE FORM TAMBAH DATA
    function toggleForm() {
        const formCol = document.getElementById('formColumn');
        const tableCol = document.getElementById('tableColumn');
        const btnTambah = document.getElementById('btnTambahToggle');

        if (formCol.classList.contains('d-none')) {
            formCol.classList.remove('d-none');
            setTimeout(() => {
                tableCol.classList.replace('col-xl-12', 'col-xl-8');
            }, 10);
            btnTambah.classList.replace('btn-navy', 'btn-danger');
            btnTambah.innerHTML = '<i class="bi bi-x-lg me-1"></i> Batal / Tutup Form';
        } else {
            tableCol.classList.replace('col-xl-8', 'col-xl-12');
            btnTambah.classList.replace('btn-danger', 'btn-navy');
            btnTambah.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Tambah Data Baru';
            setTimeout(() => {
                formCol.classList.add('d-none');
            }, 400); 
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
            title: 'Hapus Data Material?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus Permanen',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4 shadow-lg border-0', confirmButton: 'rounded-pill px-4 fw-bold', cancelButton: 'rounded-pill px-4 fw-bold' }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../proses/hapus.php?id=${id}&modul=praktek_pusat&id_lab=<?= $id_lab_selected ?>&id_j=<?= $id_j_selected ?>`;
            }
        });
    }

    // AJAX UNTUK TOGGLE SWITCH AKSES LAB
    $(document).ready(function() {
        $('#toggleInputStok').on('change', function() {
            var isChecked = $(this).is(':checked');
            var statusValue = isChecked ? 1 : 0;

            $.ajax({
                url: '../proses/update_akses.php', 
                type: 'POST',
                data: { status: statusValue },
                success: function(response) {
                    if(response.trim() == 'success') {
                        if(isChecked) {
                            $('#status-text').text('AKTIF').removeClass('bg-danger').addClass('bg-success');
                        } else {
                            $('#status-text').text('NONAKTIF').removeClass('bg-success').addClass('bg-danger');
                        }
                        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                        Toast.fire({ icon: 'success', title: isChecked ? 'Akses Input Lab Diaktifkan' : 'Akses Input Lab Dinonaktifkan' });
                    } else {
                        Swal.fire({ title:'Gagal!', text: "Database gagal diupdate: " + response, icon: 'error', customClass: { popup: 'rounded-4' }});
                        $('#toggleInputStok').prop('checked', !isChecked);
                    }
                },
                error: function() {
                    Swal.fire({ title:'Error!', text: "Gagal menghubungi server", icon: 'error', customClass: { popup: 'rounded-4' }});
                    $('#toggleInputStok').prop('checked', !isChecked);
                }
            });
        });
    });
</script>

<?php if (isset($_GET['status'])): ?>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const status = urlParams.get('status');

    if (status === 'success') {
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: msg ? msg : 'Data berhasil diproses',
            showConfirmButton: false, timer: 2000, customClass: { popup: 'rounded-4 shadow-lg border-0' }
        });
    } else if (status === 'error') {
        Swal.fire({
            icon: 'error', title: 'Gagal!', text: msg ? msg : 'Terjadi kesalahan sistem',
            confirmButtonColor: '#d33', confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Tutup',
            customClass: { popup: 'rounded-4 shadow-lg border-0', confirmButton: 'rounded-pill px-4 fw-bold' }
        });
    }
    window.history.replaceState({}, document.title, window.location.pathname + 
        (urlParams.get('id_lab') ? `?id_lab=${urlParams.get('id_lab')}&id_jurusan=${urlParams.get('id_jurusan')}` : ''));

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Data Material?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus Permanen',
        cancelButtonText: 'Batal',
        customClass: { 
            popup: 'rounded-4 shadow-lg border-0', 
            confirmButton: 'rounded-pill px-4 fw-bold', 
            cancelButton: 'rounded-pill px-4 fw-bold' 
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Gunakan Fetch (AJAX) agar tidak pindah halaman dan bisa menangkap JSON dari PHP
            fetch(`../proses/hapus.php?id=${id}&modul=praktek_pusat`)
                .then(response => response.json()) // Ubah respon mentah menjadi objek JSON
                .then(data => {
                    // Jika PHP mengirim status 'success'
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            customClass: { popup: 'rounded-4 shadow-lg border-0' }
                        }).then(() => {
                            // Refresh halaman setelah pop-up sukses menghilang
                            location.reload(); 
                        });
                    } 
                    // Jika PHP mengirim status 'error' (contoh: karena relasi distribusi)
                    else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ditolak Sistem!',
                            text: data.message,
                            confirmButtonColor: '#001f3f',
                            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Mengerti',
                            customClass: { 
                                popup: 'rounded-4 shadow-lg border-0',
                                confirmButton: 'rounded-pill px-4 fw-bold'
                            }
                        });
                    }
                })
                .catch(error => {
                    // Jika gagal terhubung ke server/file PHP tidak ditemukan
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Gagal membaca respon dari server.',
                        confirmButtonColor: '#001f3f',
                        customClass: { popup: 'rounded-4 shadow-lg border-0' }
                    });
                    console.error('Error:', error);
                });
        }
    });
}
</script>

<?php endif; ?>

</body>
</html>
