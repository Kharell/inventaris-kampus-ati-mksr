<?php
include "../../config/database.php";

$id_lab  = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit   = 5; 
$offset  = ($page - 1) * $limit;

if (empty($id_lab)) {
    echo '<div class="empty-state py-5 text-center"><i class="bi bi-exclamation-circle fs-1 text-danger"></i><p class="mt-3">ID Lab tidak ditemukan.</p></div>';
    exit;
}

// --- BAGIAN A: PERMINTAAN MASUK (ANTRIAN) ---
$sql_req = "SELECT p.*, b.nama_bahan, b.satuan, b.stok as stok_gudang, b.id_praktek
            FROM permintaan_barang p
            JOIN bahan_praktek b ON p.id_barang = b.id_praktek
            JOIN kepala_lab kl ON p.id_kepala = kl.id_kepala
            WHERE kl.id_lab = '$id_lab' AND p.status = 'pending'
            ORDER BY p.tgl_permintaan ASC";
$query_req = mysqli_query($conn, $sql_req);

if (mysqli_num_rows($query_req) > 0) : ?>
    <div class="card border-0 shadow-lg rounded-4 mb-5 overflow-hidden anim-fade-up">
        <div class="card-header border-0 bg-gradient-warning py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-lightning-charge-fill me-2"></i>ANTRIAN PERMINTAAN BARU</h6>
            <span class="badge bg-dark rounded-pill"><?= mysqli_num_rows($query_req) ?> Permintaan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small uppercase ls-1">
                            <th class="ps-4 py-3">Detail Material</th>
                            <th class="text-center">Kuantitas</th>
                            <th class="text-center">Ketersediaan</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($req = mysqli_fetch_assoc($query_req)) : 
                            $stok_status = ($req['stok_gudang'] < $req['jumlah_minta']) ? 'bg-danger' : 'bg-success';
                        ?>
                        <tr class="transition-all">
                            <td class="ps-4">
                                <div class="fw-bold text-navy mb-1"><?= $req['nama_bahan'] ?></div>
                                <div class="text-muted smaller"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($req['tgl_permintaan'])) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 rounded-3">
                                    <strong class="fs-6"><?= $req['jumlah_minta'] ?></strong> <?= $req['satuan'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center">
                                    <span class="dot <?= $stok_status ?> me-2"></span>
                                    <span class="small fw-semibold"><?= $req['stok_gudang'] ?> Unit</span>
                                </div>
                            </td>
                           <td class="text-end pe-4">
                            <button class="btn btn-navy btn-sm rounded-pill px-4 shadow-sm hover-up" 
                                    onclick="prosesACC('<?= $req['id_permintaan'] ?>', '<?= $req['id_praktek'] ?>', '<?= $req['jumlah_minta'] ?>', '<?= addslashes($req['nama_bahan']) ?>')">
                                <i class="bi bi-check2-circle me-1"></i> Validasi & ACC
                            </button>
                        </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$where_clause = "WHERE d.id_lab = '$id_lab'";
if ($keyword != '') $where_clause .= " AND (b.nama_bahan LIKE '%$keyword%' OR d.kode_distribusi LIKE '%$keyword%')";

$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab d JOIN bahan_praktek b ON d.id_praktek = b.id_praktek $where_clause");
$total_data  = mysqli_fetch_assoc($total_query)['total'];
$total_page  = ceil($total_data / $limit);

$sql = "SELECT d.*, b.nama_bahan, b.satuan FROM distribusi_lab d 
        JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
        $where_clause ORDER BY d.id_distribusi DESC LIMIT $limit OFFSET $offset";
$query = mysqli_query($conn, $sql);
?>

<div class="section-title d-flex align-items-center mb-4 anim-fade-up" style="animation-delay: 0.1s;">
    <div class="bg-navy p-2 rounded-3 me-3"><i class="bi bi-archive-fill text-gold"></i></div>
    <h5 class="fw-bold text-navy mb-0">Riwayat Distribusi Selesai</h5>
</div>

<?php if (mysqli_num_rows($query) > 0) : ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden anim-fade-up" style="animation-delay: 0.2s;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="table-history">
                <thead class="bg-navy text-white">
                    <tr class="ls-1">
                        <th class="ps-4 py-3">#</th>
                        <th>Material</th>
                        <th>Kode Distribusi</th>
                        <th class="text-center">Kuantitas</th>
                        <th>Status</th>
                        <th class="text-center pe-4">Kontrol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $offset + 1;
                    while ($row = mysqli_fetch_assoc($query)) : 
                        $is_received = ($row['status'] == 'diterima');
                        $initial = strtoupper(substr($row['nama_bahan'], 0, 1));
                    ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-table me-3"><?= $initial ?></div>
                                <div class="fw-bold text-navy"><?= $row['nama_bahan'] ?></div>
                            </div>
                        </td>
                        <td><code class="kode-modern"><?= $row['kode_distribusi'] ?></code></td>
                        <td class="text-center">
                            <span class="fw-bold text-dark"><?= $row['jumlah'] ?></span> <small class="text-muted"><?= $row['satuan'] ?></small>
                        </td>
                        <td>
                            <?php if ($is_received) : ?>
                                <span class="status-pill status-success"><i class="bi bi-check2-all me-1"></i>Selesai</span>
                            <?php else : ?>
                                <span class="status-pill status-warning"><i class="bi bi-truck me-1"></i>In Transit</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-4">
                            <?php if (!$is_received) : ?>
                                <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                    <button class="btn btn-white btn-sm px-3" title="Edit" onclick="openEditDist('<?= $row['id_distribusi'] ?>', '<?= addslashes($row['nama_bahan']) ?>', '<?= $row['jumlah'] ?>')">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button class="btn btn-white btn-sm px-3" title="Hapus" onclick="hapusDistribusi('<?= $row['id_distribusi'] ?>')">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>
                                </div>
                            <?php else : ?>
                                <i class="bi bi-lock-fill text-muted" title="Data Terkunci"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_page > 1) : ?>
    <nav class="mt-4 anim-fade-up">
        <ul class="pagination justify-content-center gap-2">
            <?php for ($i = 1; $i <= $total_page; $i++) : ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link border-0 rounded-3 shadow-sm" href="javascript:void(0)" onclick="loadDistribusi('<?= $id_lab ?>', <?= $i ?>, '<?= $keyword ?>')"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

<?php else : ?>
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
        <img src="../../assets/img/no-data.svg" class="img-fluid mb-3" style="max-height: 150px;" alt="No Data">
        <p class="text-muted">Tidak ada riwayat distribusi ditemukan.</p>
    </div>
<?php endif; ?>

<style>
/* Dashboard Styling */
:root {
    --navy: #002b5c;
    --gold: #FFD700;
}

.bg-gradient-warning { background: linear-gradient(45deg, #ffc107, #ffdb6e); }
.text-navy { color: var(--navy); }
.smaller { font-size: 0.75rem; }
.ls-1 { letter-spacing: 0.5px; }

/* Avatar & Icons */
.avatar-table {
    width: 38px; height: 38px; background: #eef2f7; color: var(--navy);
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    font-weight: 800; border: 1px solid #dee2e6;
}

/* Status Pills */
.status-pill {
    padding: 6px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 700;
    display: inline-flex; align-items: center;
}
.status-success { background: #d1e7dd; color: #0f5132; }
.status-warning { background: #fff3cd; color: #664d03; }

/* Dot Indicator */
.dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; }

/* Kode Modern */
.kode-modern {
    background: #f8f9fa; border: 1px solid #e9ecef; color: #495057;
    padding: 3px 8px; border-radius: 5px; font-family: 'JetBrains Mono', monospace;
    font-size: 0.85rem;
}

/* Transitions */
.transition-all { transition: all 0.3s ease; }
.hover-up:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

/* Animation */
.anim-fade-up {
    animation: fadeUp 0.5s ease backwards;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.pagination .page-link { color: var(--navy); font-weight: 600; padding: 10px 18px; }
.pagination .page-item.active .page-link { background-color: var(--navy); color: var(--gold); }

/* Warna dasar Navy */
.btn-navy {
    background-color: #001f3f;
    color: white;
    border: none;
    transition: all 0.3s ease; /* Membuat transisi halus */
}

/* Aksi saat di-hover */
.btn-navy:hover {
    background-color: #004080; /* Warna biru yang lebih terang saat hover */
    color: #ffffff;
    transform: translateY(-2px); /* Efek melayang sedikit ke atas (hover-up) */
    box-shadow: 0 5px 15px rgba(0, 31, 63, 0.3); /* Bayangan lebih tegas */
}

/* Efek saat diklik */
.btn-navy:active {
    transform: translateY(0);
}
</style>