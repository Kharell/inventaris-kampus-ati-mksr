<?php
include "../../config/database.php";

// 1. Inisialisasi & Sanitasi
$id_lab  = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit   = 5; 
$offset  = ($page - 1) * $limit;

if (empty($id_lab)) {
    echo '<div class="empty-state py-5 text-center"><i class="bi bi-exclamation-circle fs-1 text-danger"></i><p class="mt-3">ID Lab tidak ditemukan.</p></div>';
    exit;
}

// State untuk dikirim kembali ke JavaScript
echo "<script>
    window.currentLabId = '$id_lab';
    window.currentPage = $page;
    window.currentKeyword = '$keyword';
</script>";

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
        <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm text-dark border" id="tab-dikirim" data-bs-toggle="tab" data-bs-target="#transit-pane">
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
    <div class="tab-pane fade show active" id="transit-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'dikirim', $search_sql, 'warning', $limit, $offset, true); ?>
    </div>
    <div class="tab-pane fade" id="reject-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'ditolak', $search_sql, 'danger', $limit, $offset, true); ?>
    </div>
    <div class="tab-pane fade" id="done-pane">
        <?php renderTableDistribusi($conn, $id_lab, 'diterima', $search_sql, 'success', $limit, $offset, false); ?>
    </div>
</div>

<?php
function renderTableDistribusi($conn, $id_lab, $status, $search_sql, $theme, $limit, $offset, $hasAction) {
    $sql = "SELECT d.*, b.nama_bahan, b.satuan FROM distribusi_lab d 
            JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
            WHERE d.id_lab = '$id_lab' AND d.status = '$status' $search_sql 
            ORDER BY d.id_distribusi DESC LIMIT $limit OFFSET $offset";
    $query = mysqli_query($conn, $sql);

    $count_sql = mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab d JOIN bahan_praktek b ON d.id_praktek = b.id_praktek WHERE d.id_lab = '$id_lab' AND d.status = '$status' $search_sql");
    $total_data = mysqli_fetch_assoc($count_sql)['total'];
    $total_page = ceil($total_data / $limit);

    if (mysqli_num_rows($query) > 0) : ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-start border-<?= $theme ?> border-5">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small fw-bold">
                        <tr>
                            <th class="ps-4 py-3">KODE & MATERIAL</th>
                            <th>SPEK & KONDISI</th>
                            <th class="text-center">KUANTITAS</th>
                            <?php if($status == 'ditolak') echo '<th>ALASAN</th>'; ?>
                            <th class="text-center pe-4"><?= $hasAction ? 'KONTROL' : 'STATUS' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query)) : ?>
                        <tr>
                            <td class="ps-4">
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
            <nav class="mt-4">
                <ul class="pagination justify-content-center gap-1">
                    <?php for ($i = 1; $i <= $total_page; $i++) : ?>
                        <li class="page-item <?= ($i == ($offset/$limit)+1) ? 'active' : '' ?>">
                            <a class="page-link border-0 rounded-pill shadow-sm" href="javascript:void(0)" 
                               onclick="loadDistribusi(window.currentLabId, <?= $i ?>, window.currentKeyword)">
                               <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else : ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <p class="text-muted small mb-0">Tidak ada data ditemukan.</p>
        </div>
    <?php endif;
} ?>

<script>
// 1. Fungsi Pencarian
function executeSearch() {
    const key = document.getElementById('searchInput').value;
    loadDistribusi(window.currentLabId, 1, key);
}

// 2. Fungsi Refresh Konten Saja (Kunci agar tidak kembali ke menu utama)
function refreshContentOnly() {
    loadDistribusi(window.currentLabId, window.currentPage, window.currentKeyword);
}

// 3. Logic ACC dengan AJAX
function prosesACC(idPermintaan, idBahan, jmlMinta, namaBahan, spek, kondisi) {
    // Memastikan ID Lab saat ini tersimpan
    if (!window.currentLabId) {
        Swal.fire('Peringatan', 'Silahkan pilih Lab terlebih dahulu', 'warning');
        return;
    }

    // Mengisi data ke dalam modal yang ada di index.php
    document.getElementById('modIdLab').value = window.currentLabId;
    document.getElementById('modIdReq').value = idPermintaan;
    document.getElementById('modBarang').value = idBahan;
    document.getElementById('modJumlah').value = jmlMinta;
    document.getElementById('modSpesifikasi').value = spek;
    document.getElementById('modKondisiHidden').value = kondisi;
    
    // Menjalankan fungsi visual (centang kondisi & kode otomatis)
    updateVisualKondisi(conditions);
    generateCode(); 

    // Memunculkan Modal ACC
    var myModal = new bootstrap.Modal(document.getElementById('distModal'));
    myModal.show();
}

// 4. Logic Hapus dengan AJAX
function hapusDistribusi(idDistribusi) {
    if (confirm("Hapus data distribusi ini?")) {
        $.ajax({
            url: "controllers/hapus_distribusi.php", // SESUAIKAN DENGAN FILE HAPUS ANDA
            type: "POST",
            data: { id: idDistribusi },
            success: function(response) {
                refreshContentOnly();
            }
        });
    }
}


// 5. Menjaga Tab Tetap Aktif setelah Refresh AJAX
$(document).ready(function(){
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeTab', $(e.target).attr('id'));
    });
    var activeTab = localStorage.getItem('activeTab');
    if(activeTab){
        $('#' + activeTab).tab('show');
    }
});
</script>

<style>
.nav-pills .nav-link {
    color: #000000 !important; 
    background-color: #ffffff !important; 
    border: 1px solid #004594;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
/* Styling tetap sama seperti sebelumnya */
.text-navy { color: #002b5c; }
.btn-navy { background: #002b5c; color: white; border: none; }
.btn-navy:hover { background: #004080; color: white; }
.kode-modern { background: #f1f3f5; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-size: 0.8rem; }
.nav-pills .nav-link.active { background: #002b5c !important; color: #FFD700 !important; }
.anim-fade-up { animation: fadeUp 0.4s ease-out backwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>