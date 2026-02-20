<?php
include "../../config/database.php";

// 1. Inisialisasi & Sanitasi
$id_lab  = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Limit dari parameter, default 10
$allowed_limits = [10, 25, 50];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, $allowed_limits)) $limit = 10;

$offset  = ($page - 1) * $limit;

if (empty($id_lab)) {
    echo '<div class="empty-state py-5 text-center"><i class="bi bi-exclamation-circle fs-1 text-danger"></i><p class="mt-3">ID Lab tidak ditemukan.</p></div>';
    exit;
}

// State untuk dikirim kembali ke JavaScript (tanpa <script> tag, akan di-set oleh parent)
$search_sql = $keyword ? " AND (b.nama_bahan LIKE '%$keyword%' OR d.kode_distribusi LIKE '%$keyword%')" : "";

// --- BAGIAN A: ANTRIAN PERMINTAAN (ACC) ---
$sql_req = "SELECT p.*, b.nama_bahan, b.satuan, b.stok as stok_gudang, b.id_praktek, b.spesifikasi, b.kondisi, b.kode_bahan
            FROM permintaan_barang p
            JOIN bahan_praktek b ON p.id_barang = b.id_praktek
            JOIN kepala_lab kl ON p.id_kepala = kl.id_kepala
            WHERE kl.id_lab = '$id_lab' AND p.status = 'pending'
            ORDER BY p.tgl_permintaan ASC";
$query_req = mysqli_query($conn, $sql_req);
?>

<!-- Data attributes untuk JavaScript parent (index.php) baca -->
<div id="ajax-state" 
     data-lab-id="<?= $id_lab ?>" 
     data-page="<?= $page ?>" 
     data-keyword="<?= htmlspecialchars($keyword) ?>" 
     data-limit="<?= $limit ?>" 
     style="display:none;"></div>

<div class="row align-items-center mb-4 anim-fade-up">
    <div class="col-md-6">
        <h5 class="fw-bold text-navy mb-1"><i class="bi bi-cpu-fill me-2"></i>Manajemen Distribusi Lab</h5>
        <p class="text-muted small mb-0">Halaman otomatis diperbarui tanpa muat ulang (No Refresh).</p>
    </div>
</div>


<?php if (mysqli_num_rows($query_req) > 0) : ?>
    <div class="card border-0 shadow-lg rounded-4 mb-5 overflow-hidden anim-fade-up border-top border-warning border-5">
        <div class="card-header border-0 bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-navy"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>PERLU VALIDASI (ACC)</h6>
            <span class="badge bg-warning text-dark rounded-pill px-3"><?= mysqli_num_rows($query_req) ?> Baru</span>
        </div>
        <div class="table-responsive"> 
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small fw-bold text-muted">
                    <tr>
                        <th class="ps-4">MATERIAL</th>
                        <th class="text-center">SPEK & KONDISI</th>
                        <th class="text-center">KUANTITAS</th>
                        <th class="text-center">STOK GUDANG</th>
                        <th class="text-end pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($req = mysqli_fetch_assoc($query_req)) : ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy"><?= htmlspecialchars($req['nama_bahan']) ?></div>
                            <div class="smaller text-muted"><?= date('d/m/Y H:i', strtotime($req['tgl_permintaan'])) ?></div>
                        </td>
                        <td class="text-center">
                            <div class="small text-dark mb-1"><?= htmlspecialchars($req['spesifikasi'] ?: '-') ?></div>
                            <span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size: 10px;"><?= $req['kondisi'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning-subtle text-dark px-3 py-2">
                                <b><?= $req['jumlah_minta'] ?></b> <?= $req['satuan'] ?>
                            </span>
                        </td>
                        <td class="text-center small fw-bold <?= ($req['stok_gudang'] < $req['jumlah_minta']) ? 'text-danger' : 'text-success' ?>">
                            <i class="bi bi-box-seam me-1"></i><?= $req['stok_gudang'] ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-navy btn-sm rounded-pill px-4 shadow-sm" 
                                style="transition: all 0.3s ease; border: none;"
                                onmouseover="this.style.backgroundColor='#00c853'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 15px rgba(0, 200, 83, 0.4)';"
                                onmouseout="this.style.backgroundColor='#002b5c'; this.style.transform='translateY(0)'; this.style.boxShadow='none';"
                                onclick="prosesACC(
                                    '<?= $req['id_permintaan'] ?>', 
                                    '<?= $req['id_praktek'] ?>', 
                                    '<?= $req['jumlah_minta'] ?>', 
                                    '<?= addslashes($req['nama_bahan']) ?>', 
                                    '<?= addslashes($req['spesifikasi']) ?>', 
                                    '<?= $req['kondisi'] ?>', 
                                    '<?= $req['kode_bahan'] ?>'
                                )">
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

<ul class="nav nav-pills mb-4 gap-2 anim-fade-up" id="distTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm text-dark border" id="tab-dikirim" data-bs-toggle="tab" data-bs-target="#transit-pane">
            <i class="bi bi-truck me-2"></i>In Transit
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm text-dark border-danger" id="tab-ditolak" data-bs-toggle="tab" data-bs-target="#reject-pane">
            <i class="bi bi-x-circle me-2"></i>Ditolak
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm text-dark border-success" id="tab-diterima" data-bs-toggle="tab" data-bs-target="#done-pane">
            <i class="bi bi-check-all me-2"></i>Selesai
        </button>
    </li>
</ul>

<div class="tab-content anim-fade-up">
    <div class="tab-pane fade" id="transit-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'dikirim', $search_sql, 'warning', $limit, $offset, true, $page); ?>
    </div>
    <div class="tab-pane fade" id="reject-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'ditolak', $search_sql, 'danger', $limit, $offset, true, $page); ?>
    </div>
    <div class="tab-pane fade" id="done-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'diterima', $search_sql, 'success', $limit, $offset, false, $page); ?>
    </div>
</div>

<?php
function renderTableDistribusi($conn, $id_lab, $status, $search_sql, $theme, $limit, $offset, $hasAction, $current_page) {
    $sql = "SELECT d.*, b.nama_bahan, b.satuan FROM distribusi_lab d 
            JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
            WHERE d.id_lab = '$id_lab' AND d.status = '$status' $search_sql 
            ORDER BY d.id_distribusi DESC LIMIT $limit OFFSET $offset";
    $query = mysqli_query($conn, $sql);

    $count_sql = mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab d JOIN bahan_praktek b ON d.id_praktek = b.id_praktek WHERE d.id_lab = '$id_lab' AND d.status = '$status' $search_sql");
    $total_data = mysqli_fetch_assoc($count_sql)['total'];
    $total_page = ceil($total_data / $limit);
    if ($total_page < 1) $total_page = 1;

    // Hitung range data yang ditampilkan
    $start_entry = $total_data > 0 ? $offset + 1 : 0;
    $end_entry = min($offset + $limit, $total_data);

    // Unique ID untuk tiap tab
    $uid = $status;
    ?>

    <!-- Header: Show Entries & Info -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-semibold">Tampilkan</span>
            <select class="form-select form-select-sm border-2 rounded-pill shadow-sm fw-bold dist-limit-select" 
                    style="width: auto; min-width: 75px; border-color: #002b5c; color: #002b5c; cursor: pointer;" 
                    data-status="<?= $uid ?>"
                    onchange="changeDistLimit(this.value)">
                <?php foreach([10, 25, 50] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($limit == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
            <span class="text-muted small fw-semibold">data</span>
        </div>
        <div class="text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Menampilkan <b><?= $start_entry ?></b>–<b><?= $end_entry ?></b> dari <b><?= $total_data ?></b> data
        </div>
    </div>

    <?php if (mysqli_num_rows($query) > 0) : ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-start border-<?= $theme ?> border-5">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small fw-bold">
                        <tr>
                            <th class="ps-4 py-3 text-center" style="width: 60px;">NO</th>
                            <th class="py-3">KODE & MATERIAL</th>
                            <th>SPEK & KONDISI</th>
                            <th class="text-center">KUANTITAS</th>
                            <?php if($status == 'ditolak') echo '<th>ALASAN</th>'; ?>
                            <th class="text-center pe-4"><?= $hasAction ? 'KONTROL' : 'STATUS' ?></th>
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
                            <td>
                                <div class="small text-muted"><?= htmlspecialchars($row['spesifikasi'] ?: '-') ?></div>
                                <div class="smaller fw-bold text-<?= ($row['kondisi'] == 'Baik') ? 'success' : 'warning' ?>"><?= $row['kondisi'] ?></div>
                            </td>
                            <td class="text-center"><b><?= $row['jumlah'] ?></b> <small><?= $row['satuan'] ?></small></td>
                            <?php if($status == 'ditolak') : ?>
                                <td><div class="alert alert-danger py-1 px-2 mb-0 smaller">"<?= $row['keterangan'] ?>"</div></td>
                            <?php endif; ?>
                            <td class="text-center pe-4">
                                <?php if($hasAction) : ?>
                                  <div class="btn-group shadow-sm rounded-pill overflow-hidden bg-white border">
                                    <button class="btn btn-sm btn-outline-danger border-0 px-3" 
                                        style="transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;"
                                        onmouseover="this.style.backgroundColor='#dc3545'; this.style.color='white'; this.style.transform='scale(1.1)'; this.style.boxShadow='inset 0 0 10px rgba(0,0,0,0.1)';"
                                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='#dc3545'; this.style.transform='scale(1)';"
                                        onclick="hapusDistribusi('<?= $row['id_distribusi'] ?>')">
                                        <i class="bi bi-trash" style="font-size: 1.1rem;"></i>
                                    </button>
                                </div>
                                <?php else : ?>
                                    <span class="status-pill status-success "><i class="bi text-success bi-check-circle-fill me-1"></i>Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($total_page > 1) : ?>
            <!-- Pagination -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center gap-1 flex-wrap">
                    <!-- Previous Button -->
                    <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link border-0 rounded-pill shadow-sm px-3" href="javascript:void(0)"
                           <?php if ($current_page > 1): ?>
                           onclick="loadDistribusi(window.currentLabId, <?= $current_page - 1 ?>, window.currentKeyword, window.currentLimit)"
                           <?php endif; ?>
                           style="color: #002b5c;">
                            <i class="bi bi-chevron-left"></i> Prev
                        </a>
                    </li>

                    <?php
                    // Smart page range: show max 5 pages around current
                    $range = 2;
                    $start_page = max(1, $current_page - $range);
                    $end_page = min($total_page, $current_page + $range);

                    // Always show first page
                    if ($start_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link border-0 rounded-pill shadow-sm" href="javascript:void(0)" 
                               onclick="loadDistribusi(window.currentLabId, 1, window.currentKeyword, window.currentLimit)">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled"><span class="page-link border-0 bg-transparent">…</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++) : ?>
                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                            <a class="page-link border-0 rounded-pill shadow-sm" href="javascript:void(0)" 
                               onclick="loadDistribusi(window.currentLabId, <?= $i ?>, window.currentKeyword, window.currentLimit)">
                               <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php // Always show last page
                    if ($end_page < $total_page): ?>
                        <?php if ($end_page < $total_page - 1): ?>
                            <li class="page-item disabled"><span class="page-link border-0 bg-transparent">…</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link border-0 rounded-pill shadow-sm" href="javascript:void(0)" 
                               onclick="loadDistribusi(window.currentLabId, <?= $total_page ?>, window.currentKeyword, window.currentLimit)">
                               <?= $total_page ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Next Button -->
                    <li class="page-item <?= ($current_page >= $total_page) ? 'disabled' : '' ?>">
                        <a class="page-link border-0 rounded-pill shadow-sm px-3" href="javascript:void(0)"
                           <?php if ($current_page < $total_page): ?>
                           onclick="loadDistribusi(window.currentLabId, <?= $current_page + 1 ?>, window.currentKeyword, window.currentLimit)"
                           <?php endif; ?>
                           style="color: #002b5c;">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else : ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
            <p class="text-muted small mb-0 mt-2">Belum ada data</p>
        </div>
    <?php endif;
} ?>

<style>
.nav-pills .nav-link {
    color: #000000 !important; 
    background-color: #ffffff !important; 
    border: 1px solid #004594;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.text-navy { color: #002b5c; }
.btn-navy { background: #002b5c; color: white; border: none; }
.btn-navy:hover { background: #004080; color: white; }
.kode-modern { background: #f1f3f5; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-size: 0.8rem; }
.nav-pills .nav-link.active { background: #002b5c !important; color: #FFD700 !important; }
.anim-fade-up { animation: fadeUp 0.4s ease-out backwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* Pagination styling */
.pagination .page-item.active .page-link {
    background-color: #002b5c !important;
    color: #FFD700 !important;
    border: none;
    font-weight: bold;
}
.pagination .page-link {
    color: #002b5c;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}
.pagination .page-link:hover {
    background-color: #e8edf3;
    transform: translateY(-2px);
}
.pagination .page-item.disabled .page-link {
    color: #adb5bd;
    background: transparent;
    pointer-events: none;
}
</style>