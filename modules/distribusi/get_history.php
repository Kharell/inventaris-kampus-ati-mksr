<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);
// 1. Inisialisasi & Sanitasi
$id_lab  = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Limit dari parameter, default 10
$allowed_limits = [5, 10, 25, 50];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, $allowed_limits)) $limit = 10;

$offset  = ($page - 1) * $limit;

if (empty($id_lab)) {
    echo '<div class="empty-state py-5 text-center"><i class="bi bi-exclamation-circle fs-1 text-danger"></i><p class="mt-3">ID Lab tidak ditemukan.</p></div>';
    exit;
}

$search_sql = $keyword ? " AND (b.nama_bahan LIKE '%$keyword%' OR d.kode_distribusi LIKE '%$keyword%')" : "";

// --- BAGIAN A: ANTRIAN PERMINTAAN (ACC) ---
$sql_req = "SELECT p.*, b.nama_bahan, b.satuan, b.stok, b.id_praktek, b.spesifikasi, b.kondisi, b.kode_bahan
            FROM permintaan_barang p
            JOIN bahan_praktek b ON p.id_barang = b.id_praktek
            JOIN kepala_lab kl ON p.id_kepala = kl.id_kepala
            WHERE kl.id_lab = '$id_lab' AND p.status = 'pending'
            ORDER BY p.tgl_permintaan ASC";
$query_req = mysqli_query($conn, $sql_req);
?>

<div id="ajax-state" data-lab-id="<?= $id_lab ?>" data-page="<?= $page ?>" data-keyword="<?= htmlspecialchars($keyword, ENT_QUOTES) ?>" data-limit="<?= $limit ?>" style="display:none;"></div>

<div class="row align-items-center mb-4 anim-fade-up">
    <div class="col-md-6">
        <h5 class="fw-bold text-navy mb-1"><i class="bi bi-cpu-fill me-2 text-primary"></i>Manajemen Distribusi Lab</h5>
        <p class="text-muted small mb-0">Kelola pengiriman barang dan pantau status penerimaan.</p>
    </div>
</div>

<?php if (mysqli_num_rows($query_req) > 0) : ?>
    <div class="card border-0 shadow-lg rounded-4 mb-5 overflow-hidden anim-fade-up border-top border-warning border-5">
        <div class="card-header border-0 bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-navy"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>PERLU VALIDASI (ACC)</h6>
            <span class="badge bg-warning text-dark rounded-pill px-3 shadow-sm"><?= mysqli_num_rows($query_req) ?> Laporan Baru</span>
        </div>
        <div class="table-responsive"> 
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small fw-bold text-muted">
                    <tr>
                        <th class="ps-4">TANGGAL</th>
                        <th class="ps-4">MATERIAL</th>    
                        <th class="text-center">SPEK & KONDISI</th>
                        <th class="text-center">AKUMULASI STOK</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($req = mysqli_fetch_assoc($query_req)) : 
                        $stok_lama = isset($req['stok_awal']) ? (int)$req['stok_awal'] : (int)$req['stok'];
                        $jumlah_baru = isset($req['jumlah_minta']) ? (int)$req['jumlah_minta'] : 0;
                        $stok_akumulasi = $stok_lama + $jumlah_baru;
                    ?>
                    <tr>
                        <td class="ps-4"><div class="small fw-bold text-muted"><?= date('d/m/Y H:i', strtotime($req['tgl_permintaan'])) ?></div></td>
                        <td class="ps-4">
                            <div class="small text-muted font-monospace"><?= htmlspecialchars($req['kode_bahan']) ?></div>
                            <div class="fw-bold text-navy"><?= htmlspecialchars($req['nama_bahan']) ?></div>
                        </td>
                        <td class="text-center">
                            <div class="small mb-1 text-truncate" style="max-width: 150px; margin: 0 auto;" title="<?= htmlspecialchars($req['spesifikasi'] ?: '-') ?>">
                                <?= htmlspecialchars($req['spesifikasi'] ?: '-') ?>
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size: 10px;"><?= $req['kondisi'] ?></span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 border" title="Stok Lama">
                                    <i class="bi bi-box me-1"></i><?= $stok_lama ?>
                                </span>
                                <span class="fw-bold text-muted small">+</span>
                                <span class="badge bg-primary-subtle text-primary px-2 py-1 border border-primary" title="Dilaporkan (Tambahan)">
                                    <i class="bi bi-plus-lg me-1"></i><?= $jumlah_baru ?>
                                </span>
                                <span class="fw-bold text-muted small">=</span>
                                <span class="badge bg-success text-white px-2 py-1 shadow-sm" title="Total Akhir">
                                    <?= $stok_akumulasi ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <button class="btn btn-success btn-sm rounded-pill px-4 shadow-sm fw-bold" 
                                    onclick="prosesACC('<?= $req['id_permintaan'] ?>', '<?= $req['id_praktek'] ?>', '<?= $jumlah_baru ?>', '<?= addslashes($req['nama_bahan']) ?>', '<?= addslashes($req['spesifikasi']) ?>', '<?= $req['kondisi'] ?>', '<?= $req['kode_bahan'] ?>', '', '', '<?= $id_lab ?>')">
                                <i class="bi bi-check2-circle me-1"></i> ACC
                            </button>
                        </td>
                     </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 anim-fade-up">
    <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill shadow-sm border border-light">
        <i class="bi bi-list-ol text-primary"></i>
        <span class="text-muted small fw-bold">Tampilkan</span>
        <select class="form-select form-select-sm border-0 bg-light fw-bold text-navy" style="width: 75px; border-radius: 8px; cursor: pointer;" onchange="changeDistLimit(this.value)">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
        </select>
        <span class="text-muted small fw-bold">Baris</span>
    </div>

    <div class="input-group shadow-sm" style="max-width: 350px; border-radius: 50px; overflow: hidden; border: 1px solid #e2e8f0;">
        <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="searchDist" class="form-control border-0 px-2" placeholder="Cari material/kode..." value="<?= htmlspecialchars($keyword, ENT_QUOTES) ?>" onkeypress="if(event.key === 'Enter') handleSearch(this.value)">
        <button class="btn btn-navy px-4 fw-bold" onclick="handleSearch(document.getElementById('searchDist').value)">Cari</button>
    </div>
</div>

<ul class="nav nav-pills mb-4 gap-2 anim-fade-up" id="distTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm tab-transit" id="tab-dikirim" data-bs-toggle="tab" data-bs-target="#transit-pane">
            <i class="bi bi-truck me-2"></i>In Transit
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm tab-ditolak" id="tab-ditolak" data-bs-toggle="tab" data-bs-target="#reject-pane">
            <i class="bi bi-x-circle me-2"></i>Masalah / Ditolak
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm tab-selesai" id="tab-diterima" data-bs-toggle="tab" data-bs-target="#done-pane">
            <i class="bi bi-check-all me-2"></i>Selesai
        </button>
    </li>
</ul>

<div class="tab-content anim-fade-up">
    <div class="tab-pane fade show active" id="transit-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'dikirim', $search_sql, 'warning', $limit, $offset, $page, $keyword); ?>
    </div>
    <div class="tab-pane fade" id="reject-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'ditolak_special', $search_sql, 'danger', $limit, $offset, $page, $keyword); ?>
    </div>
    <div class="tab-pane fade" id="done-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'diterima', $search_sql, 'success', $limit, $offset, $page, $keyword); ?>
    </div>
</div>

<?php
function renderTableDistribusi($conn, $id_lab, $status, $search_sql, $theme, $limit, $offset, $current_page, $keyword) {
    
    // Logika Filter Status
    if ($status == 'ditolak_special') {
        $where_status = "(d.status = 'ditolak' OR (d.status = 'diterima' AND d.jumlah_diterima < d.jumlah))";
    } else {
        $where_status = "d.status = '$status'";
    }

    $sql = "SELECT d.*, b.nama_bahan, b.satuan FROM distribusi_lab d 
            JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
            WHERE d.id_lab = '$id_lab' AND $where_status $search_sql 
            ORDER BY d.id_distribusi DESC LIMIT $limit OFFSET $offset";
    $query = mysqli_query($conn, $sql);

    // Count Total untuk Pagination
    $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab d JOIN bahan_praktek b ON d.id_praktek = b.id_praktek WHERE d.id_lab = '$id_lab' AND $where_status $search_sql");
    $total_data = mysqli_fetch_assoc($count_res)['total'];
    $total_page = ceil($total_data / $limit) ?: 1;
?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted small">Menampilkan <b class="text-navy"><?= min($offset + 1, $total_data) ?>-<?= min($offset + $limit, $total_data) ?></b> dari <b class="text-navy"><?= $total_data ?></b> data distribusi</div>
    </div>

    <?php if (mysqli_num_rows($query) > 0) : ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-start border-<?= $theme ?> border-5">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small fw-bold text-muted">
                    <tr>
                        <th class="ps-4 text-center">NO</th>
                        <th>MATERIAL</th>
                        <th>SPEK</th>
                        <th>TANGGAL</th> 
                        <th class="text-center">QTY KIRIM</th>
                        <?php if($status == 'diterima') echo '<th class="text-center">QTY TERIMA</th>'; ?>
                        <th class="text-center pe-4">KONTROL / STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset; while ($row = mysqli_fetch_assoc($query)) : $no++; ?>
                    <tr>
                        <td class="ps-4 text-center fw-bold text-muted"><?= $no ?></td>
                        <td>
                            <code class="kode-modern mb-1 d-inline-block"><?= $row['kode_distribusi'] ?></code>
                            <div class="fw-bold text-navy"><?= htmlspecialchars($row['nama_bahan']) ?></div>
                        </td>
                        <td><div class="small text-muted text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($row['spesifikasi'] ?: '-') ?>"><?= htmlspecialchars($row['spesifikasi'] ?: '-') ?></div></td>
                        <td><div class="small fw-bold text-muted"><?= date('d M Y', strtotime($row['tanggal_distribusi'])) ?></div></td>
                        <td class="text-center"><span class="badge bg-light text-dark border"><?= $row['jumlah'] ?> <?= $row['satuan'] ?></span></td>
                        
                        <?php if($status == 'diterima') : ?>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1 shadow-sm"><?= $row['jumlah_diterima'] ?> <?= $row['satuan'] ?></span>
                        </td>
                        <?php endif; ?>

                        <td class="text-center pe-4">
                            <?php if($status == 'ditolak_special') : ?>
                                <?php 
                                    $sisa = $row['jumlah'] - $row['jumlah_diterima']; 
                                    $is_ditolak_total = ($row['status'] == 'ditolak');
                                ?>
                                
                                <div class="p-2 mb-2 rounded bg-white border border-danger shadow-sm text-start" style="max-width: 250px; margin: 0 auto;">
                                    <small class="fw-bold text-danger d-block mb-1" style="font-size: 0.65rem;">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $is_ditolak_total ? 'ALASAN DITOLAK:' : 'KETERANGAN KEKURANGAN:' ?>
                                    </small>
                                    <p class="mb-0 text-dark small" style="line-height: 1.2;">
                                        "<?= htmlspecialchars($row['keterangan'] ?? 'Tidak ada keterangan') ?>"
                                    </p>
                                </div>

                                <button class="btn btn-sm <?= $is_ditolak_total ? 'btn-danger' : 'btn-warning' ?> rounded-pill px-3 shadow-sm fw-bold mt-1" 
                                        onclick="resendBarang('<?= $row['id_distribusi'] ?>', '<?= $sisa ?>', '<?= addslashes($row['nama_bahan']) ?>')">
                                    <i class="bi bi-arrow-repeat me-1"></i> <?= $is_ditolak_total ? 'Kirim Ulang' : 'Kirim Kekurangan' ?> (<?= $sisa ?>)
                                </button>

                            <?php elseif($status == 'dikirim') : ?>
                                <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-clock-history me-1"></i>Menunggu Lab</span>
                            <?php else : ?>
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($total_page > 1) : ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center shadow-sm" style="border-radius: 50px; overflow: hidden; display: inline-flex;">
                
                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link border-0 text-navy fw-bold px-3 py-2" 
                       href="javascript:void(0)" 
                       <?= ($current_page > 1) ? "onclick=\"loadDistribusi('$id_lab', " . ($current_page - 1) . ", '" . htmlspecialchars($keyword, ENT_QUOTES) . "', $limit)\"" : "" ?>>
                       <i class="bi bi-chevron-left me-1"></i> Prev
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $total_page; $i++) : ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link border-0 fw-bold px-3 py-2 <?= ($i == $current_page) ? 'bg-navy text-gold' : 'text-navy bg-light' ?>" 
                           href="javascript:void(0)" 
                           onclick="loadDistribusi('<?= $id_lab ?>', <?= $i ?>, '<?= htmlspecialchars($keyword, ENT_QUOTES) ?>', <?= $limit ?>)">
                           <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= ($current_page >= $total_page) ? 'disabled' : '' ?>">
                    <a class="page-link border-0 text-navy fw-bold px-3 py-2" 
                       href="javascript:void(0)" 
                       <?= ($current_page < $total_page) ? "onclick=\"loadDistribusi('$id_lab', " . ($current_page + 1) . ", '" . htmlspecialchars($keyword, ENT_QUOTES) . "', $limit)\"" : "" ?>>
                       Next <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

    <?php else : ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
            <i class="bi bi-search text-muted opacity-25" style="font-size: 3.5rem;"></i>
            <p class="text-muted mt-3 fw-bold">Data tidak ditemukan.</p>
        </div>
    <?php endif;
} ?>

<style>
/* Utilities */
.text-navy { color: #001f3f !important; }
.bg-navy { background-color: #001f3f !important; }
.text-gold { color: #ffcc00 !important; }
.btn-navy { background: #001f3f; color: white; border: none; transition: 0.3s; }
.btn-navy:hover { background: #003366; color: white; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,31,63,0.2); }
.kode-modern { background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-size: 0.8rem; color: #001f3f; border: 1px solid #e2e8f0;}

/* Pagination Custom Warna */
.pagination .page-item.active .page-link.bg-navy {
    background-color: #001f3f !important;
    color: #ffcc00 !important;
    border-color: #001f3f !important;
}

/* Tab Warna Pastel */
.nav-pills .nav-link { margin-right: 8px; transition: 0.3s; border-left: 4px solid transparent; }
.nav-pills .nav-link.tab-transit { background-color: #e2e8f0; color: #001f3f; border: 1px solid #cbd5e1; }
.nav-pills .nav-link.tab-ditolak { background-color: #fee2e2; color: #dc3545; border: 1px solid #fecaca; }
.nav-pills .nav-link.tab-selesai { background-color: #dcfce7; color: #198754; border: 1px solid #bbf7d0; }

.nav-pills .nav-link.tab-transit:hover { background-color: #cbd5e1; }
.nav-pills .nav-link.tab-ditolak:hover { background-color: #fecaca; }
.nav-pills .nav-link.tab-selesai:hover { background-color: #bbf7d0; }

.nav-pills .nav-link.tab-transit.active { background-color: #001f3f !important; color: #ffcc00 !important; border-color: #001f3f !important; }
.nav-pills .nav-link.tab-ditolak.active { background-color: #dc3545 !important; color: white !important; border-color: #dc3545 !important; }
.nav-pills .nav-link.tab-selesai.active { background-color: #198754 !important; color: white !important; border-color: #198754 !important; }

/* Animasi */
.anim-fade-up { animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>