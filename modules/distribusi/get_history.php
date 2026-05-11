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

<div id="ajax-state" data-lab-id="<?= $id_lab ?>" data-page="<?= $page ?>" data-keyword="<?= htmlspecialchars($keyword) ?>" data-limit="<?= $limit ?>" style="display:none;"></div>

<div class="row align-items-center mb-4 anim-fade-up">
    <div class="col-md-6">
        <h5 class="fw-bold text-navy mb-1"><i class="bi bi-cpu-fill me-2"></i>Manajemen Distribusi Lab</h5>
        <p class="text-muted small mb-0">Kelola pengiriman barang dan pantau status penerimaan.</p>
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
                        <th class="ps-4">TANGGAL</th>
                        <th class="ps-4">MATERIAL</th>    
                        <th class="text-center">SPEK & KONDISI</th>
                        <th class="text-center">STOK & MINTA</th>
                        <th class="text-end pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($req = mysqli_fetch_assoc($query_req)) : ?>
                    <tr>
                        <td class="ps-4"><div class="smaller text-muted"><?= date('d/m/Y H:i', strtotime($req['tgl_permintaan'])) ?></div></td>
                        <td class="ps-4">
                            <div class="small text-muted"><?= htmlspecialchars($req['kode_bahan']) ?></div>
                            <div class="fw-bold text-navy"><?= htmlspecialchars($req['nama_bahan']) ?></div>
                        </td>
                        <td class="text-center">
                            <div class="small mb-1"><?= htmlspecialchars($req['spesifikasi'] ?: '-') ?></div>
                            <span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size: 10px;"><?= $req['kondisi'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary px-2 py-1"><i class="bi bi-house-door me-1"></i><?= $req['stok_awal'] ?></span>
                            <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-cart-plus me-1"></i><?= $req['jumlah_minta'] ?></span>
                        </td>
                        <td class="text-end pe-4">
                           <button class="btn btn-navy btn-sm rounded-pill px-4" onclick="prosesACC('<?= $req['id_permintaan'] ?>', '<?= $req['id_praktek'] ?>', '<?= $req['jumlah_minta'] ?>', '<?= addslashes($req['nama_bahan']) ?>', '<?= addslashes($req['spesifikasi']) ?>', '<?= $req['kondisi'] ?>', '<?= $req['kode_bahan'] ?>', '', '', '<?= $id_lab ?>')">ACC</button>
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
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="tab-dikirim" data-bs-toggle="tab" data-bs-target="#transit-pane"><i class="bi bi-truck me-2"></i>In Transit</button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm border-danger" id="tab-ditolak" data-bs-toggle="tab" data-bs-target="#reject-pane"><i class="bi bi-x-circle me-2"></i>Masalah / Ditolak</button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-4 fw-bold shadow-sm border-success" id="tab-diterima" data-bs-toggle="tab" data-bs-target="#done-pane"><i class="bi bi-check-all me-2"></i>Selesai</button>
    </li>
</ul>

<div class="tab-content anim-fade-up">
    <div class="tab-pane fade" id="transit-pane">
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
        <div class="text-muted small">Menampilkan <b><?= min($offset + 1, $total_data) ?>-<?= min($offset + $limit, $total_data) ?></b> dari <b><?= $total_data ?></b> data</div>
    </div>

    <?php if (mysqli_num_rows($query) > 0) : ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-start border-<?= $theme ?> border-5">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small fw-bold">
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
                        <td><div class="small text-muted"><?= htmlspecialchars($row['spesifikasi'] ?: '-') ?></div></td>
                        <td><div class="small fw-bold"><?= date('d/m/y', strtotime($row['tanggal_distribusi'])) ?></div></td>
                        <td class="text-center"><span class="badge bg-light text-dark border"><?= $row['jumlah'] ?> <?= $row['satuan'] ?></span></td>
                        
                        <?php if($status == 'diterima') : ?>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success"><?= $row['jumlah_diterima'] ?> <?= $row['satuan'] ?></span>
                        </td>
                        <?php endif; ?>

                        <!-- KOLOM STATUS & KONTROL -->
                        <td class="text-center pe-4">
                            <?php if($status == 'ditolak_special') : ?>
                                <?php 
                                    $sisa = $row['jumlah'] - $row['jumlah_diterima']; 
                                    $is_ditolak_total = ($row['status'] == 'ditolak');
                                ?>
                                
                                <!-- TAMPILAN ALASAN KEPALA LAB (Diletakkan di dalam TD) -->
                                <div class="p-2 mb-2 rounded bg-white border border-danger shadow-sm text-start" style="max-width: 250px; margin: 0 auto;">
                                    <small class="fw-bold text-danger d-block mb-1" style="font-size: 0.65rem;">
                                        <?= $is_ditolak_total ? 'ALASAN DITOLAK:' : 'KETERANGAN KEKURANGAN:' ?>
                                    </small>
                                    <p class="mb-0 text-dark small" style="line-height: 1.2;">
                                        "<?= htmlspecialchars($row['keterangan'] ?? 'Tidak ada keterangan') ?>"
                                    </p>
                                </div>

                                <!-- TOMBOL KIRIM ULANG -->
                                <button class="btn btn-sm <?= $is_ditolak_total ? 'btn-danger' : 'btn-warning' ?> rounded-pill px-3 shadow-sm" 
                                        onclick="resendBarang('<?= $row['id_distribusi'] ?>', '<?= $sisa ?>', '<?= addslashes($row['nama_bahan']) ?>')">
                                    <i class="bi bi-arrow-repeat me-1"></i> <?= $is_ditolak_total ? 'Kirim Ulang' : 'Kirim Kekurangan' ?> (<?= $sisa ?>)
                                </button>

                            <?php elseif($status == 'dikirim') : ?>
                                <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill"><i class="bi bi-clock-history me-1"></i>Menunggu</span>
                            <?php else : ?>
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
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
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_page; $i++) : ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link border-0 rounded-pill shadow-sm mx-1" 
                        href="javascript:void(0)" 
                        onclick="loadDistribusi('<?= $id_lab ?>', <?= $i ?>, '<?= addslashes($keyword) ?>', <?= $limit ?>)">
                        <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <?php else : ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="bi bi-inbox text-muted fs-1"></i>
            <p class="text-muted small mt-2">Tidak ada data ditemukan</p>
        </div>
    <?php endif;
} ?>

<style>
.text-navy { color: #002b5c; }
.btn-navy { background: #002b5c; color: white; border: none; }
.btn-navy:hover { background: #004080; color: white; }
.kode-modern { background: #f1f3f5; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-size: 0.75rem; }
.nav-pills .nav-link { color: #555; background: #fff; border: 1px solid #dee2e6; margin-right: 5px; }
.nav-pills .nav-link.active { background: #002b5c !important; color: #fff !important; border-color: #002b5c; }
.anim-fade-up { animation: fadeUp 0.4s ease-out; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>