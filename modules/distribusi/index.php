<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

$query_jurusan = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
// Pastikan query ini menyertakan spesifikasi dan kondisi
$list_barang = mysqli_query($conn, "SELECT id_praktek, nama_bahan, kode_bahan, stok, satuan, spesifikasi, kondisi FROM bahan_praktek WHERE stok > 0 ORDER BY nama_bahan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Distribusi - Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">

    
    <style>

        :root { --navy: #0a192f; --gold: #ffcc00; --soft-bg: #f8f9fa; }
        body { background-color: var(--soft-bg); font-family: 'Inter', sans-serif; }
        .wrapper { display: flex; width: 100%; }
        .main-content { flex: 1; margin-left: 260px; min-height: 100vh; }
        
        /* Hero Banner */
        .hero-banner { 
            background: linear-gradient(135deg, var(--navy) 0%, #112240 100%); 
            border-radius: 15px; padding: 30px; color: white; margin-bottom: 25px; 
            position: relative; overflow: hidden;
        }

        /* --- NAV JURUSAN --- */
        .nav-jurusan {
            background: #ffffff;
            padding: 12px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 12px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        .nav-jurusan .nav-link { 
            color: var(--navy) !important; 
            background: #eef2f7; 
            border: 2px solid #b0bfd0;
            padding: 12px 24px; 
            font-weight: 700;
            border-radius: 10px; 
            transition: all 0.3s ease;
            white-space: nowrap;
            opacity: 1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .nav-jurusan .nav-link:hover:not(.active) {
            background: #dde5ef;
            border-color: var(--navy);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(10, 25, 47, 0.15);
        }
        .nav-jurusan .nav-link.active { 
            background: var(--navy) !important; 
            color: var(--gold) !important;
            border-color: var(--navy) !important;
            box-shadow: 0 5px 15px rgba(10, 25, 47, 0.3);
            opacity: 1;
            transform: translateY(-2px);
        }
        .nav-jurusan .nav-link .pulse-dot {
            right: -5px;
            top: -5px;
        }

        /* === NAV LAB (Tombol Pilih Lab) === */
        .nav-lab .nav-link {
            color: var(--navy); 
            background: #ffffff; 
            border: 2px solid #d0d7e0;
            border-left: 4px solid #8899aa;
            margin-bottom: 10px; 
            text-align: left; 
            font-weight: 600; 
            padding: 14px 16px;
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .nav-lab .nav-link:hover:not(.active) {
            background-color: #eef2f7;
            border-color: #6b8bad;
            border-left-color: var(--navy);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .nav-lab .nav-link.active {
            background: linear-gradient(135deg, #0a192f 0%, #112d4e 100%) !important;
            border-color: #0a192f !important;
            border-left-color: var(--gold) !important;
            box-shadow: 0 6px 18px rgba(10, 25, 47, 0.35);
            transform: translateX(4px);
        }
        .nav-lab .nav-link.active .text-navy, 
        .nav-lab .nav-link.active .text-muted,
        .nav-lab .nav-link.active .fw-bold {
            color: #ffffff !important;
        }
        .nav-lab .nav-link.active .text-danger {
            color: #ffcc00 !important;
        }
        .nav-lab .nav-link i { font-size: 1.2rem; }

        /* Table Area */
        .data-container { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); min-height: 400px; }
        .empty-state { padding: 80px 0; text-align: center; color: #adb5bd; }
        
        .btn-add-dist { background: var(--navy); color: var(--gold); border-radius: 8px; font-weight: 600; border: none; padding: 8px 15px; }
        .btn-add-dist:hover { background: #112240; transform: scale(1.05); color: #fff; }
        .btn-group .btn {
            padding: 5px 10px;
        }
        .btn-group .btn:hover {
            background-color: #f8f9fa;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(10, 25, 47, 0.02);
        }

                /* Efek kedip pada notifikasi permintaan */
        .pulse-notif {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        /* Efek animasi berkedip untuk tanda notifikasi agar lebih terlihat */
        .pulse-dot {
            width: 10px;
            height: 10px;
            box-shadow: 0 0 0 rgba(220, 53, 69, 0.4);
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }


    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>
<body>

<div class="wrapper">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content">
        <?php include "../../includes/header.php"; ?>
    <main class="p-3 p-md-4" style="margin-top: 30px;"></main>
        <div class="p-4">
            <div class="hero-banner shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-1">
                            <i class="bi bi-truck me-2" style="color: var(--accent-yellow);"></i> Manajemen Distribusi</span>
                        </h2>
                        <p class="opacity-75 mb-0">Kelola dan pantau aliran bahan praktek ke setiap laboratorium.</p>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-0">
    <ul class="nav nav-pills nav-jurusan mb-4" id="pills-tab" role="tablist">
        <?php 
        $active_j = true;
        mysqli_data_seek($query_jurusan, 0);
        while($j = mysqli_fetch_assoc($query_jurusan)): 
            $id_jur = $j['id_jurusan'];
            
            $check_req = mysqli_query($conn, "SELECT p.id_permintaan 
                FROM permintaan_barang p
                JOIN kepala_lab kl ON p.id_kepala = kl.id_kepala
                JOIN lab l ON kl.id_lab = l.id_lab
                WHERE l.id_jurusan = '$id_jur' AND p.status = 'pending' 
                LIMIT 1"); 
                
            $has_pending = (mysqli_num_rows($check_req) > 0);
        ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link position-relative <?= $active_j ? 'active' : ''; ?>" 
                    id="tab-jur-<?= $id_jur; ?>"
                    data-bs-toggle="pill" 
                    data-bs-target="#jur-<?= $id_jur; ?>" 
                    type="button" 
                    role="tab">
                
                <i class="bi bi-mortarboard me-2"></i><?= $j['nama_jurusan']; ?>

                <?php if ($has_pending) : ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle pulse-dot" style="margin-left: -10px; margin-top: 5px;">
                    </span>
                <?php endif; ?>
            </button>
        </li>
        <?php $active_j = false; endwhile; ?>
    </ul>
</div>



<div class="row">
    <div class="col-md-4 col-lg-3">
        <h6 class="fw-bold text-muted mb-3 small uppercase">PILIH LABORATORIUM:</h6>
        <div class="tab-content">
            <?php 
            $active_j = true;
            mysqli_data_seek($query_jurusan, 0);
            while($j = mysqli_fetch_assoc($query_jurusan)): 
                $id_jur = $j['id_jurusan'];
            ?>
            <div class="tab-pane fade <?= $active_j ? 'show active' : ''; ?>" id="jur-<?= $id_jur; ?>">
                <div class="nav flex-column nav-pills nav-lab">
                    <?php 
                    // Query mengambil l.* dan kl.id_kepala agar bisa dikirim ke JavaScript
                    $q_lab = mysqli_query($conn, "SELECT l.*, kl.id_kepala, kl.nama_kepala,
                             (SELECT COUNT(*) FROM permintaan_barang p 
                              WHERE p.id_kepala = kl.id_kepala 
                              AND p.status = 'pending') as total_permintaan
                             FROM lab l 
                             LEFT JOIN kepala_lab kl ON l.id_lab = kl.id_lab 
                             WHERE l.id_jurusan = '$id_jur'");
                    
                    while($l = mysqli_fetch_assoc($q_lab)):
                        // Pastikan id_kepala ada, jika NULL beri string kosong
                        $id_kepala = !empty($l['id_kepala']) ? $l['id_kepala'] : '';
                        $nama_kepala = !empty($l['nama_kepala']) ? $l['nama_kepala'] : '<span class="text-danger italic">Belum ada Kepala Lab</span>';
                        $jumlah_notif = $l['total_permintaan'];
                    ?>
                    <button class="nav-link mb-2 d-flex justify-content-between align-items-center text-start position-relative shadow-sm" 
                            style="border-radius: 10px;"
                            onclick="viewLabDetails('<?= $l['id_lab']; ?>', '<?= addslashes($l['nama_lab']); ?>', '<?= addslashes($j['nama_jurusan']); ?>', '<?= $id_kepala; ?>')"
                            data-bs-toggle="pill" type="button">
                        <div class="w-100">
                            <div class="fw-bold d-block text-navy"><?= $l['nama_lab']; ?></div>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                <i class="bi bi-person me-1"></i> <?= $nama_kepala; ?>
                            </div>
                        </div>

                        <?php if($jumlah_notif > 0): ?>
                            <span class="badge rounded-pill bg-danger shadow-sm pulse-notif" 
                                  style="font-size: 0.7rem; padding: 5px 8px; border: 1px solid white;">
                                <?= $jumlah_notif; ?> Baru
                            </span>
                        <?php else: ?>
                            <i class="bi bi-chevron-right ms-2 opacity-50"></i>
                        <?php endif; ?>
                    </button>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php $active_j = false; endwhile; ?>
        </div>
    </div>

    <div class="col-md-8 col-lg-9">
        <div class="data-container" id="distribusi-view">
            <div class="empty-state">
                <i class="bi bi-arrow-left-circle mb-3 d-block" style="font-size: 3rem;"></i>
                <h5>Silahkan pilih Laboratorium</h5>
                <p>Klik salah satu lab di samping untuk memproses permintaan atau melihat riwayat.</p>
            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="modalACC" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <form id="formACC" action="../proses/tambah.php" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="id_permintaan" id="modIdReq">
            <input type="hidden" name="id_lab" id="modIdLab">
            <input type="hidden" name="id_praktek" id="modBarang">
            <input type="hidden" name="jumlah" id="modJumlah">
            <input type="hidden" name="kode_distribusi" id="modKode">
            <input type="hidden" name="kondisi" id="modKondisiHidden">
            <input type="hidden" name="tanggal_distribusi" value="<?= date('Y-m-d'); ?>">

            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-navy mb-1">Validasi Distribusi</h6>
                    <p class="small text-muted">Konfirmasi data sebelum material dikeluarkan</p>
                </div>

                <div class="bg-light rounded-4 p-3 border border-dashed border-secondary-subtle">
                    <div class="mb-3">
                        <label class="smaller text-muted d-block text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Nama Material</label>
                        <div id="textNamaBarang" class="fw-bold text-navy h6 mb-0">...</div>
                        <span id="textKodeDisplay" class="badge bg-navy-subtle text-navy font-monospace mt-1" style="font-size: 0.7rem;">-</span>
                    </div>

                    <div class="row g-2">
                        <div class="col-7">
                            <label class="smaller text-muted d-block text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Spesifikasi</label>
                            <div id="textSpekDisplay" class="small text-dark text-truncate">-</div>
                        </div>
                        <div class="col-5">
                            <label class="smaller text-muted d-block text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Kondisi</label>
                            <span id="textKondisiDisplay" class="badge bg-white border text-dark fw-normal">-</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top border-2 border-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-muted">JUMLAH ACC:</span>
                        <span class="h4 fw-bold text-navy mb-0" id="textJumlahDisplay">0</span>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" name="simpan_distribusi" class="btn btn-navy w-100 py-2 rounded-3 fw-bold mb-2 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Setujui Sekarang
                    </button>
                    <button type="button" class="btn btn-link w-100 btn-sm text-muted text-decoration-none" data-bs-dismiss="modal">
                        Kembali
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>






</div> <div class="modal fade" id="editDistModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/edit.php" method="POST" class="modal-content border-0 rounded-4">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Jumlah Distribusi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id_distribusi" id="editIdDist">
                
                <div class="mb-3">
                    <label class="form-label fw-bold small">NAMA BAHAN</label>
                    <input type="text" id="editNamaBahan" class="form-control bg-light font-weight-bold" readonly>
                    <div class="form-text text-danger">*Nama bahan tidak dapat diubah dari sini.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">JUMLAH BARU</label>
                    <div class="input-group">
                        <input type="number" name="jumlah" id="editJumlah" class="form-control border-2 border-warning" min="1" required>
                        <span class="input-group-text bg-warning border-warning fw-bold text-dark">Unit</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">BATAL</button>
                <button type="submit" name="update_distribusi" class="btn btn-warning fw-bold px-4 text-dark">SIMPAN PERUBAHAN</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

// Tambahkan parameter keempat: idJurusan
function viewLabDetails(id, labName, jurName, idJurusan) { 
    currentLabId = id;
    currentLabName = labName;
    currentJurName = jurName;

    // REVISI 1: Isi input hidden modal supaya data ini terkirim ke proses/tambah.php
    if(document.getElementById('modIdLab')) document.getElementById('modIdLab').value = id;
    if(document.getElementById('modLab')) document.getElementById('modLab').value = labName;
    if(document.getElementById('modJurusan')) document.getElementById('modJurusan').value = jurName;
    
    // REVISI 2: Simpan ID Jurusan ke input hidden (buat input ini di dalam modal)
    if(document.getElementById('modIdJurHidden')) {
        document.getElementById('modIdJurHidden').value = idJurusan;
    }

    const view = document.getElementById('distribusi-view');
    
    // Render Container Utama
    view.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #0a192f !important;">
            <div>
                <h4 class="fw-bold mb-0" style="color: #0a192f;">${labName}</h4>
                <span class="badge" style="background-color: #ffcc00; color: #0a192f;">${jurName}</span>
            </div>
         
        </div>
        <div id="table-content">
            <div class="text-center p-5"><div class="spinner-border" style="color: #0a192f;"></div></div>
        </div>
    `;
    loadDistribusi(id, 1, '');
}


    function loadDistribusi(id_lab, page = 1, keyword = '', limit = 10) {
        const tableContainer = document.getElementById('table-content');
        if(!tableContainer) return;

        // Gunakan limit dari window jika ada
        const activeLimit = limit || window.currentLimit || 10;

        fetch(`get_history.php?id_lab=${id_lab}&page=${page}&keyword=${keyword}&limit=${activeLimit}`)
            .then(response => response.text())
            .then(data => {
                tableContainer.innerHTML = data;

                // Baca state dari data-attributes (bukan dari <script> tag)
                var stateEl = document.getElementById('ajax-state');
                if(stateEl) {
                    window.currentLabId = stateEl.dataset.labId;
                    window.currentPage = parseInt(stateEl.dataset.page);
                    window.currentKeyword = stateEl.dataset.keyword;
                    window.currentLimit = parseInt(stateEl.dataset.limit);
                }

                // Bind event listener untuk menyimpan tab aktif ke localStorage
                // dan reset page ke 1 saat pindah tab agar data tidak ikut berpindah
                var tabButtons = tableContainer.querySelectorAll('button[data-bs-toggle="tab"]');
                tabButtons.forEach(function(btn) {
                    btn.addEventListener('shown.bs.tab', function(e) {
                        localStorage.setItem('activeDistTab', e.target.id);
                        // Jika sedang proses restore tab, jangan reload data
                        if (window._isRestoringTab) return;
                        // Reset page ke 1 saat pindah tab supaya data tab lain tidak terpengaruh
                        window.currentPage = 1;
                        loadDistribusi(window.currentLabId, 1, window.currentKeyword, window.currentLimit);
                    });
                });

                // Pulihkan tab aktif dari localStorage (dengan guard agar tidak loop)
                window._isRestoringTab = true;
                var savedTab = localStorage.getItem('activeDistTab');
                if(savedTab) {
                    var tabEl = document.getElementById(savedTab);
                    if(tabEl) {
                        var bsTab = new bootstrap.Tab(tabEl);
                        bsTab.show();
                    }
                } else {
                    // Default: aktifkan tab transit jika belum ada yang tersimpan
                    var defaultTab = document.getElementById('tab-dikirim');
                    if(defaultTab) {
                        var bsTab = new bootstrap.Tab(defaultTab);
                        bsTab.show();
                    }
                }
                // Reset guard setelah restore selesai (gunakan setTimeout agar event selesai dulu)
                setTimeout(function() { window._isRestoringTab = false; }, 100);
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.innerHTML = '<div class="alert alert-danger">Gagal memuat data.</div>';
            });
    }

    // Fungsi global untuk ubah limit dari dropdown di dalam AJAX content
    function changeDistLimit(newLimit) {
        window.currentLimit = parseInt(newLimit);
        loadDistribusi(window.currentLabId, 1, window.currentKeyword, window.currentLimit);
    }

    // Fungsi pencarian
    function executeSearch() {
        var searchInput = document.getElementById('searchInput');
        var key = searchInput ? searchInput.value : '';
        loadDistribusi(window.currentLabId, 1, key, window.currentLimit);
    }

    // Refresh konten tanpa kembali ke menu utama
    function refreshContentOnly() {
        loadDistribusi(window.currentLabId, window.currentPage, window.currentKeyword, window.currentLimit);
    }

    function handleSearch(val) {
        loadDistribusi(currentLabId, 1, val);
    }

 


    // 3. FUNGSI PENCARIAN
    function handleSearch(val) {
        loadDistribusi(currentLabId, 1, val);
    }

    // 4. MODAL KIRIM BAHAN
    function openDistModal(id, lab, jur) {
        document.getElementById('modIdLab').value = id;
        document.getElementById('modLab').value = lab;
        document.getElementById('modJurusan').value = jur;
        document.getElementById('labName').innerText = lab;
        
        const modalElement = document.getElementById('distModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }

    // 5. GENERATE KODE OTOMATIS
    const ambilInisial = (s) => s.split(' ').map(w => w[0]).join('').toUpperCase();

    function generateCode() {
        const selectBarang = document.getElementById('modBarang');
        if(!selectBarang.value) return;

        const selectedOption = selectBarang.options[selectBarang.selectedIndex];
        const kodeBahan = selectedOption.getAttribute('data-kode');
        const jur = ambilInisial(currentJurName);
        const lab = ambilInisial(currentLabName);
        
        document.getElementById('modKode').value = `${jur}/${lab}/${kodeBahan}`;
    }

    // 6. FUNGSI EDIT
    function openEditDist(id, nama, jumlah) {
        document.getElementById('editIdDist').value = id;
        document.getElementById('editNamaBahan').value = nama;
        document.getElementById('editJumlah').value = jumlah;
        
        const myModal = new bootstrap.Modal(document.getElementById('editDistModal'));
        myModal.show();
    }

    // 7. FUNGSI HAPUS (SWEETALERT2)
function hapusDistribusi(id) {
    Swal.fire({
        title: 'Batalkan Distribusi?',
        text: "Stok akan dikembalikan otomatis ke gudang pusat!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#002b5c',
        confirmButtonText: 'Ya, Batalkan!',
        cancelButtonText: 'Tutup'
    }).then((result) => {
        if (result.isConfirmed) {
            // GUNAKAN FETCH (AJAX) - JANGAN window.location.href
            fetch(`../proses/hapus.php?hapus_distribusi=${id}`)
            .then(response => {
                Swal.fire('Terhapus!', 'Distribusi dibatalkan & stok kembali.', 'success');
                // Refresh tabel saja tanpa refresh halaman
                loadDistribusi(currentLabId, 1, ''); 
            })
            .catch(error => {
                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
            });
        }
    });
}

    // 8. NOTIFIKASI URL PARAMETER
    // 8. NOTIFIKASI URL PARAMETER (TEMA NAVY GOLD)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        const config = {
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            // Custom CSS agar pop-up selaras dengan tema Navy Gold
            customClass: {
                popup: 'rounded-4 border-0 shadow-lg'
            }
        };

        if (status === 'sukses') {
            Swal.fire({ 
                ...config, 
                icon: 'success', 
                title: 'Pengiriman Berhasil!', 
                text: 'Bahan telah berhasil didistribusikan ke laboratorium.',
                iconColor: '#ffcc00', // Warna icon Gold
            });
        } else if (status === 'hapus_sukses') {
            Swal.fire({ ...config, icon: 'success', title: 'Terhapus!', text: 'Distribusi dibatalkan & stok kembali.' });
        } else if (status === 'edit_sukses') {
            Swal.fire({ ...config, icon: 'success', title: 'Berhasil!', text: 'Data distribusi telah diperbarui.' });
        } else if (status === 'stok_kurang') {
            Swal.fire({ 
                ...config, 
                icon: 'error', 
                title: 'Stok Tidak Cukup!', 
                text: 'Gagal mengirim karena stok gudang tidak mencukupi.' 
            });
        } else if (status === 'gagal') {
            Swal.fire({ ...config, icon: 'error', title: 'Kesalahan Sistem', text: 'Terjadi kesalahan saat memproses data.' });
        }

        // Membersihkan URL agar notifikasi tidak muncul lagi saat refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Gunakan variabel global agar bisa diakses semua fungsi
let currentLabId = '';
let currentLabName = '';
let currentJurName = '';


function prosesACC(idReq, idPraktek, jml, nama, spek, kondisi, kode, idLab) {
    // 1. Mapping data ke input form
    document.getElementById('modIdReq').value = idReq;
    document.getElementById('modIdLab').value = idLab;
    document.getElementById('modBarang').value = idPraktek;
    document.getElementById('modJumlah').value = jml;
    document.getElementById('modKode').value = kode; // Kode bahan asli
    document.getElementById('modKondisiHidden').value = kondisi;

    // 2. Update Tampilan Visual Modal
    document.getElementById('textNamaBarang').innerText = nama;
    document.getElementById('textSpekDisplay').innerText = spek ? spek : '-';
    document.getElementById('textJumlahDisplay').innerText = jml;
    document.getElementById('textKodeDisplay').innerText = kode;
    document.getElementById('textKondisiDisplay').innerText = kondisi;

    // 3. Eksekusi Modal
    var instance = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalACC'));
    instance.show();
}


function sinkronisasiData() {
    const select = document.getElementById('modBarang');
    
    // Validasi jika tidak ada yang dipilih
    if (!select || select.selectedIndex === -1 || !select.value) {
        return;
    }

    const selected = select.options[select.selectedIndex];
    
    // Ambil attribute dari option yang dipilih
    const kodeBahan = selected.getAttribute('data-kode') || "???";
    const spekBahan = selected.getAttribute('data-spesifikasi') || "-";
    const kondisiBahan = selected.getAttribute('data-kondisi') || "Baik";
    const namaLab = document.getElementById('modLab').value || "LAB";

    // --- LOGIKA KODE OTOMATIS ---
    // Format: DIST - (3 Huruf Lab) - (Kode Bahan) - (Tgl)
    const tgl = new Date().getDate().toString().padStart(2, '0');
    const bln = (new Date().getMonth() + 1).toString().padStart(2, '0');
    const kodeDistribusi = `DIST-${namaLab.substring(0, 3).toUpperCase()}-${kodeBahan}-${tgl}${bln}`;
    
    document.getElementById('modKode').value = kodeDistribusi;
    document.getElementById('modSpesifikasi').value = spekBahan;
    document.getElementById('modKondisiHidden').value = kondisiBahan;

    // --- STYLING VISUAL KONDISI ---
    const displayKondisi = document.getElementById('displayKondisi');
    if (displayKondisi) {
        displayKondisi.innerText = kondisiBahan;
        
        // Gunakan classList agar lebih aman daripada mengganti seluruh className
        displayKondisi.className = "form-control border-0 fw-bold text-center text-uppercase"; // Reset
        if (kondisiBahan.toLowerCase() === 'baik') {
            displayKondisi.classList.add("bg-success-subtle", "text-success");
        } else {
            displayKondisi.classList.add("bg-warning-subtle", "text-warning");
        }
    }
}



</script>

<script>
function updateDetailBahan() {
    const select = document.getElementById('modBarang');
    const selectedOption = select.options[select.selectedIndex];

    if (!select.value) {
        document.getElementById('modKode').value = "";
        document.getElementById('modSpesifikasi').value = "";
        resetVisualKondisi();
        return;
    }

    const kode = selectedOption.getAttribute('data-kode');
    const spesifikasi = selectedOption.getAttribute('data-spesifikasi');
    const kondisi = selectedOption.getAttribute('data-kondisi');

    // 1. Set Kode & Spek
    const tgl = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    document.getElementById('modKode').value = kode + "-" + tgl;
    document.getElementById('modSpesifikasi').value = spesifikasi;

    // 2. Set Value untuk PHP
    document.getElementById('modKondisiHidden').value = kondisi;

    // 3. Update Visual Centang
    updateVisualKondisi(kondisi);
}

function updateVisualKondisi(kondisi) {
    resetVisualKondisi();
    let rowId = '', checkId = '', bgClass = '';

    if (kondisi === 'Baik') { 
        rowId = 'rowBaik'; checkId = 'checkBaik'; bgClass = 'bg-success text-white'; 
    } else if (kondisi === 'Kurang Baik') { 
        rowId = 'rowKurang'; checkId = 'checkKurang'; bgClass = 'bg-warning text-dark'; 
    } else if (kondisi === 'Rusak') { 
        rowId = 'rowRusak'; checkId = 'checkRusak'; bgClass = 'bg-danger text-white'; 
    }

    if (rowId) {
        const row = document.getElementById(rowId);
        row.classList.remove('opacity-50', 'text-muted');
        row.classList.add(...bgClass.split(' '), 'shadow-sm');
        document.getElementById(checkId).classList.remove('d-none');
    }
}

function resetVisualKondisi() {
    ['rowBaik', 'rowKurang', 'rowRusak'].forEach(id => {
        const el = document.getElementById(id);
        el.className = "d-flex align-items-center justify-content-between p-2 mb-1 rounded-2 text-muted opacity-50";
    });
    ['checkBaik', 'checkKurang', 'checkRusak'].forEach(id => {
        document.getElementById(id).classList.add('d-none');
    });
}
</script>

<script>
// Menangani pengiriman form ACC secara AJAX (Latar Belakang)
document.getElementById('formACC').addEventListener('submit', function(e) {
    e.preventDefault(); // Mencegah halaman refresh/pindah

    const formData = new FormData(this);
    formData.append('simpan_distribusi', '1'); 

    // Mengirim data ke file PHP tanpa pindah halaman
    fetch('../proses/tambah.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // 1. Tutup modal secara otomatis
        const modalEl = document.getElementById('distModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        // 2. Tampilkan notifikasi sukses
        Swal.fire({
            icon: 'success',
            title: 'Berhasil di-ACC!',
            text: 'Data distribusi telah diperbarui.',
            timer: 2000,
            showConfirmButton: false
        });

        // 3. KUNCI UTAMA: Memperbarui tabel saja tanpa menutup menu samping
        loadDistribusi(currentLabId, 1, '');
        
        // 4. Reset form agar bersih
        this.reset();
        resetVisualKondisi();
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
    });
});
</script>

</body>
</html>