<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

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
        .btn-group .btn { padding: 5px 10px; }
        .btn-group .btn:hover { background-color: #f8f9fa; }
        .table-hover tbody tr:hover { background-color: rgba(10, 25, 47, 0.02); }

        /* Efek kedip pada notifikasi permintaan */
        .pulse-notif { animation: pulse-red 2s infinite; }
        .pulse-dot {
            width: 10px; height: 10px;
            box-shadow: 0 0 0 rgba(220, 53, 69, 0.4);
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
    </style>
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
                            <i class="bi bi-truck me-2" style="color: var(--gold);"></i> Manajemen Distribusi
                        </h2>
                        <p class="opacity-75 mb-0">Kelola dan pantau aliran bahan praktek ke setiap laboratorium.</p>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-0">
                <!-- Tabs Jurusan -->
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
                <!-- Sidebar Lab Berdasarkan Jurusan -->
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
                                $q_lab = mysqli_query($conn, "SELECT l.*, kl.id_kepala, kl.nama_kepala,
                                         (SELECT COUNT(*) FROM permintaan_barang p 
                                          WHERE p.id_kepala = kl.id_kepala 
                                          AND p.status = 'pending') as total_permintaan
                                         FROM lab l 
                                         LEFT JOIN kepala_lab kl ON l.id_lab = kl.id_lab 
                                         WHERE l.id_jurusan = '$id_jur'");
                                
                                while($l = mysqli_fetch_assoc($q_lab)):
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

                <!-- Konten Detail Distribusi (Tabel AJAX) -->
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
        </div>
    </div>
</div>

<!-- ================= MODAL AREA ================= -->

<!-- Modal ACC -->
<div class="modal fade" id="modalACC" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <form id="formACC" action="../proses/tambah.php" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="id_permintaan" id="modIdReq">
            <input type="hidden" name="id_lab" id="modIdLab">
            <input type="hidden" name="id_barang" id="modBarang"> 
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
                    <button type="submit" name="simpan_distribusi" class="btn btn-primary w-100 py-2 rounded-3 fw-bold mb-2 shadow-sm" style="background-color: var(--navy); border: none;">
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

<!-- Modal Histori -->
<div class="modal fade" id="modalHistory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h6 class="modal-title fw-bold"><i class="bi bi-list-ul me-2"></i>Histori Penerimaan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="historiNamaBahan" class="fw-bold text-navy small"></p>
                <div id="isiHistori" class="list-group list-group-flush small"></div>
            </div>
        </div>
    </div>
</div>

<!-- ================= SCRIPT AREA ================= -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Variabel Global
let currentLabId = '';
let currentLabName = '';
let currentJurName = '';
window.currentPage = 1;
window.currentKeyword = '';
window.currentLimit = 10;

// 1. Tampilkan Konten Lab
function viewLabDetails(id, labName, jurName, idJurusan) { 
    currentLabId = id;
    window.currentLabId = id; // Sync with window object
    currentLabName = labName;
    currentJurName = jurName;

    if(document.getElementById('modIdLab')) document.getElementById('modIdLab').value = id;
    
    const view = document.getElementById('distribusi-view');
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
    loadDistribusi(id, window.currentPage, window.currentKeyword, window.currentLimit);
}

// 2. Fungsi Load AJAX
function loadDistribusi(id_lab, page = 1, keyword = '', limit = 10) {
    const tableContainer = document.getElementById('table-content');
    if(!tableContainer) return;

    fetch(`get_history.php?id_lab=${id_lab}&page=${page}&keyword=${keyword}&limit=${limit}`)
        .then(response => response.text())
        .then(data => {
            tableContainer.innerHTML = data;

            // Update State Pagination
            var stateEl = document.getElementById('ajax-state');
            if(stateEl) {
                window.currentPage = parseInt(stateEl.dataset.page);
                window.currentKeyword = stateEl.dataset.keyword;
                window.currentLimit = parseInt(stateEl.dataset.limit);
            }

            // Bind Event Tab
            var tabButtons = tableContainer.querySelectorAll('button[data-bs-toggle="tab"]');
            tabButtons.forEach(function(btn) {
                btn.addEventListener('shown.bs.tab', function(e) {
                    localStorage.setItem('activeDistTab', e.target.id);
                    if (window._isRestoringTab) return;
                    window.currentPage = 1;
                    loadDistribusi(window.currentLabId, 1, window.currentKeyword, window.currentLimit);
                });
            });

            // Restore Tab
            window._isRestoringTab = true;
            var savedTab = localStorage.getItem('activeDistTab');
            if(savedTab) {
                var tabEl = document.getElementById(savedTab);
                if(tabEl) {
                    new bootstrap.Tab(tabEl).show();
                }
            } else {
                var defaultTab = document.getElementById('tab-dikirim');
                if(defaultTab) new bootstrap.Tab(defaultTab).show();
            }
            setTimeout(() => { window._isRestoringTab = false; }, 100);
        })
        .catch(error => {
            tableContainer.innerHTML = '<div class="alert alert-danger">Gagal memuat data.</div>';
        });
}

// 3. Fungsi Refresh Halus (Tanpa Reload Halaman Penuh)
function refreshContentOnly() {
    if(window.currentLabId) {
        loadDistribusi(window.currentLabId, window.currentPage, window.currentKeyword, window.currentLimit);
    }
}

// 4. Kirim Balasan
function kirimBalasan(id) {
    const pesan = document.getElementById(`balasan_${id}`).value;
    if (pesan.trim() === "") {
        Swal.fire('Kosong', 'Tulis pesan balasan terlebih dahulu.', 'warning');
        return;
    }

    fetch('../proses/kirim_catatan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&pesan=${encodeURIComponent(pesan)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: 'Terkirim!',
                text: 'Balasan Anda telah disimpan dan bisa dilihat Kepala Lab.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                refreshContentOnly(); // Refresh tabel saja
            });
        }
    });
}

// 5. Hapus Distribusi
function hapusDistribusi(id) {
    Swal.fire({
        title: 'Batalkan Distribusi?',
        text: "Data akan dihapus silahkan informasikan agar mengajukan kembali!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Batalkan!',
        cancelButtonText: 'Tutup'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../proses/hapus.php?hapus_distribusi=${id}`)
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Distribusi dibatalkan & Silahkan Ajukan Kembali.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        refreshContentOnly(); // Refresh tabel saja
                    });
                })
                .catch(error => {
                    Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                });
        }
    });
}

// 6. Kirim Ulang Barang
function resendBarang(id, jmlSisa, nama) {
    Swal.fire({
        title: 'Kirim Ulang Barang',
        text: `Kirim ulang sisa ${jmlSisa} unit untuk ${nama}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../proses/tambah.php',
                type: 'POST',
                data: { aksi: 'kirim_ulang', id: id, jumlah: jmlSisa },
                success: function(res) {
                    if(res.trim() === 'success') {
                        Swal.fire({
                            title: 'Berhasil!', 
                            text: 'Barang sisa telah dikirim ulang.', 
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            refreshContentOnly(); // Refresh tabel saja
                        });
                    } else {
                        Swal.fire('Gagal', res, 'error');
                    }
                }
            });
        }
    });
}

// 7. Modal ACC Logic
window.prosesACC = function(idReq, idBarang, jumlah, nama, spek, kondisi, kode, lab, jurusan, idLab) {
    document.getElementById('modIdReq').value = idReq;
    document.getElementById('modIdLab').value = idLab;
    document.getElementById('modBarang').value = idBarang;
    document.getElementById('modJumlah').value = jumlah;
    document.getElementById('modKondisiHidden').value = kondisi;
    if (document.getElementById('modKode')) document.getElementById('modKode').value = kode; 

    document.getElementById('textNamaBarang').innerText = nama;
    document.getElementById('textJumlahDisplay').innerText = jumlah;
    document.getElementById('textKodeDisplay').innerText = kode || '-';
    document.getElementById('textSpekDisplay').innerText = spek || '-';
    document.getElementById('textKondisiDisplay').innerText = kondisi || '-';

    const modalElement = document.getElementById('modalACC');
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

// 8. Submit Form ACC (JQuery)
$(document).ready(function() {
    $('#formACC').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        
        Swal.fire({
            title: 'Memproses Data...',
            text: 'Sedang memvalidasi distribusi material',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize() + '&simpan_distribusi=true',
            success: function(response) {
                if (response.trim() === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Material telah divalidasi dan siap didistribusikan.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        const modalElement = document.getElementById('modalACC');
                        bootstrap.Modal.getInstance(modalElement).hide();
                        
                        refreshContentOnly(); // Refresh tabel saja
                    });
                } else {
                    Swal.fire('Opps!', response, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Gagal terhubung ke server.', 'error');
            }
        });
    });
});

// 9. Histori Penerimaan
function viewHistory(id, nama) {
    $('#historiNamaBahan').text(nama);
    $('#isiHistori').html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>');
    $('#modalHistory').modal('show');
    $.get('get_history_detail.php?id=' + id, function(data) {
        $('#isiHistori').html(data);
    });
}

// Global functions untuk table data (Dipanggil dari HTML yang di-generate get_history.php)
function changeDistLimit(newLimit) {
    window.currentLimit = parseInt(newLimit);
    refreshContentOnly();
}

function handleSearch(val) {
    window.currentKeyword = val;
    window.currentPage = 1;
    refreshContentOnly();
}
</script>

</body>
</html>