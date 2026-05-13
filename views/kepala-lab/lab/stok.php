<?php
session_start();
include "../../../config/database.php";

// 1. Proteksi Akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_lab_user = $_SESSION['id_lab'] ?? ''; 

/**
 * LOGIKA BARU: TAMPILKAN HANYA JIKA SUDAH ADA BARANG YANG DI-ACC
 * Menggunakan HAVING total_diterima > 0 agar barang yang belum pernah
 * dikirim atau belum di-ACC oleh Lab tidak muncul di tabel stok ini.
 */
$sql_stok = "SELECT 
                b.id_praktek,
                b.nama_bahan, 
                b.satuan, 
                b.kode_bahan,
                b.spesifikasi, 
                b.kondisi,
                -- Stok asli / Pusat tidak dibaca langsung untuk keamanan visual
                -- Kita hitung manual mutasinya dari distribusi (Masuk) - pemakaian (Keluar)
                COALESCE((
                    SELECT SUM(jumlah_diterima) 
                    FROM distribusi_lab 
                    WHERE id_praktek = b.id_praktek AND id_lab = '$id_lab_user' AND status = 'diterima'
                ), 0) as total_diterima,
                
                COALESCE((
                    SELECT SUM(jumlah_pakai) 
                    FROM pemakaian_lab 
                    WHERE id_praktek = b.id_praktek AND id_lab = '$id_lab_user'
                ), 0) as total_terpakai
             FROM bahan_praktek b
             HAVING total_diterima > 0 
             ORDER BY b.nama_bahan ASC";

$query_stok = mysqli_query($conn, $sql_stok);

if (!$query_stok) {
    die("Gagal mengambil data stok: " . mysqli_error($conn));
}

// --- STEP 2: Ambil Bahan HANYA untuk Dropdown Form Permintaan (Ini boleh baca semua) ---
$query_barang = mysqli_query($conn, "SELECT * FROM bahan_praktek ORDER BY nama_bahan ASC");

// --- STEP 3: Riwayat Permintaan ---
$sql_riwayat = "SELECT p.*, b.kode_bahan,  b.nama_bahan, b.spesifikasi, b.kondisi 
                FROM permintaan_barang p 
                LEFT JOIN bahan_praktek b ON p.id_barang = b.id_praktek 
                WHERE p.id_kepala = '$id_user' 
                ORDER BY p.tgl_permintaan DESC";
$riwayat = mysqli_query($conn, $sql_riwayat);

// 4. Logika Ambil Data untuk Modal Edit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id_edit = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $query_edit = mysqli_query($conn, "SELECT p.*, b.kode_bahan, b.nama_bahan, b.spesifikasi, b.kondisi 
                                       FROM permintaan_barang p 
                                       LEFT JOIN bahan_praktek b ON p.id_barang = b.id_praktek 
                                       WHERE p.id_permintaan = '$id_edit'");
    $edit_data = mysqli_fetch_assoc($query_edit);
}

// Ambil status kontrol dari database
$query_status = mysqli_query($conn, "SELECT nilai_pengaturan FROM pengaturan_sistem WHERE nama_pengaturan = 'status_input_stok'");
$row_status = mysqli_fetch_assoc($query_status);
$is_active = $row_status['nilai_pengaturan'] == 1; // Akan bernilai true jika 1, false jika 0
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Lab Saya | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --navy: #001f3f; 
            --bg: #f8f9fc; 
            --accent: #4e73df;
        }
        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #2d3436;
        }
        .card { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
        }
        .text-navy { color: var(--navy); }
        .stok-badge { 
            font-size: 0.9rem; 
            font-weight: 700; 
            padding: 6px 12px; 
            border-radius: 10px; 
            display: inline-block; 
            min-width: 50px;
        }
        .bg-safe { background-color: #28a745; color: white; padding: 5px 10px; border-radius: 5px; }
        .bg-low { background-color: #ffc107; color: black; padding: 5px 10px; border-radius: 5px; }
        .bg-empty { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; }
        .icon-shape {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .bg-soft-primary { background: #e8f0fe; color: #0d6efd; }
        .bg-success-subtle { background-color: #d1e7dd; }

        /* Variabel Warna */
        :root {
            --navy-primary: #001f3f;
            --navy-light: #003366;
            --glass-white: rgba(255, 255, 255, 0.9);
        }

        /* Tombol Utama (Navy) */
        .btn-navy {
            background: linear-gradient(135deg, var(--navy-primary) 0%, var(--navy-light) 100%);
            color: white !important;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 31, 63, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-navy:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 31, 63, 0.3);
            filter: brightness(1.1);
        }

        .btn-navy:active:not(:disabled) {
            transform: translateY(0);
        }

        /* Style Tombol Reset (White Border) */
        .btn-white-custom {
            background: white;
            color: var(--navy-primary) !important;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .btn-white-custom:hover:not(:disabled) {
            background: #f8f9fa;
            border-color: var(--navy-primary);
        }

        /* State: DISABLED (Saat Admin Matikan) */
        .disabled-style {
            background: #f1f3f5 !important;
            color: #adb5bd !important;
            border: 1px solid #e9ecef !important;
            cursor: not-allowed !important;
            opacity: 0.7;
            filter: grayscale(1);
            box-shadow: none !important;
        }

        /* Efek khusus untuk tombol Kirim */
        #btn-submit-all:not(:disabled) {
            background: linear-gradient(135deg, #0d6efd 0%, #001f3f 100%);
            animation: pulse-blue 2s infinite;
        }

        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
        }

        .btn-disabled {
            pointer-events: none;
            background-color: #f8f9fa;
            color: #adb5bd;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../../includes/header.php"; ?>
         <main class="p-3 p-md-4" style="margin-top: 30px;"></main>
        <main class="p-4">
            
            <div class="page-header d-flex justify-content-between align-items-center bg-white p-4 shadow-sm rounded-4 border-start border-5 mb-4" style="border-color: var(--navy) !important; position: relative; overflow: hidden;">
                <div style="position: absolute; right: -20px; top: -20px; width: 150px; height: 150px; background: rgba(0, 31, 63, 0.03); border-radius: 50%;"></div>
                
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-primary-subtle p-3 rounded-3 me-4 text-primary shadow-sm" style="background: linear-gradient(135deg, #f0f7ff 0%, #e0ebf5 100%);">
                        <i class="bi bi-intersect" style="font-size: 1.8rem; color: var(--navy);"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--navy); letter-spacing: -0.5px;">Stok Lab Saya</h4>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-soft-primary text-primary me-2" style="font-size: 0.65rem;">INVENTARIS</span>
                            <p class="text-muted mb-0 small">Monitoring real-time ketersediaan bahan berdasarkan pemakaian.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3" style="z-index: 1;">
                    <a href="laporan.php" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-printer me-2"></i> Cetak Laporan
                    </a>

                   <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <div class="btn-group shadow-sm">
                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                            <button type="button" 
                                    class="btn btn-white-custom px-4 <?= !$is_active ? 'disabled-style' : ''; ?>" 
                                    onclick="<?= $is_active ? "location.href='stok.php'" : "void(0)"; ?>"
                                    <?= !$is_active ? 'disabled' : ''; ?>>
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </button>

                            <button type="button" 
                                    class="btn <?= $is_active ? 'btn-navy' : 'disabled-style'; ?> px-4" 
                                    data-bs-toggle="<?= $is_active ? 'collapse' : ''; ?>" 
                                    data-bs-target="<?= $is_active ? '#formCollapse' : ''; ?>"
                                    <?= !$is_active ? 'disabled' : ''; ?>>
                                <i class="bi <?= $is_active ? 'bi-plus-circle' : 'bi-lock'; ?> me-2"></i> 
                                <span class="fw-semibold">
                                    <?= $is_active ? 'Penginputan STOK Tercatat' : 'Input Dikunci Admin' ?>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-12 collapse <?= $edit_data ? 'show' : '' ?>" id="formCollapse">
                    <div class="card p-4 border-bottom border-4 border-warning shadow-sm">
                        <h5 class="fw-bold mb-4"><i class="bi bi-cart-plus me-2 text-warning"></i>Buat Daftar Permintaan</h5>
                        
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">CARI & PILIH BAHAN DARI PUSAT</label>
                                <select id="pilih_bahan" class="form-select select2-pencarian">
                                    <option value="">Pilih bahan...</option>
                                    <?php 
                                    mysqli_data_seek($query_barang, 0);
                                    while($b = mysqli_fetch_assoc($query_barang)): ?>
                                        <option value="<?= $b['id_praktek']; ?>" 
                                                data-nama="<?= htmlspecialchars($b['nama_bahan']); ?>"
                                                data-kode="<?= htmlspecialchars($b['kode_bahan']); ?>"
                                                data-spesifikasi="<?= htmlspecialchars($b['spesifikasi'] ?? '-'); ?>" 
                                                data-kondisi="<?= htmlspecialchars($b['kondisi'] ?? '-'); ?>">
                                            <?= $b['kode_bahan']; ?> - <?= $b['nama_bahan']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-muted">SPESIFIKASI</label>
                                <input type="text" id="auto_spesifikasi" class="form-control bg-light" readonly placeholder="-">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-muted">KONDISI</label>
                                <input type="text" id="auto_kondisi" class="form-control bg-light" readonly placeholder="-">
                            </div>

                            <div class="col-md-1">
                                <label class="form-label fw-bold small text-muted">JUMLAH (UNIT)</label>
                                <input type="number" id="input_stok_fisik" class="form-control border-primary" min="1" value="0">
                            </div>

                            <div class="col-md-2">
                                <button type="button" id="btn-tambah-item" class="btn btn-warning w-100 fw-bold">
                                    <i class="bi bi-plus-lg"></i> Tambah
                                </button>
                            </div>
                        </div>

                        <hr class="my-4">

                        <form action="../proses/tambah.php" method="POST">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered align-middle" id="tabel-keranjang">
                                    <thead class="table-light">
                                        <tr class="small text-uppercase">
                                            <th>No</th>
                                            <th>Bahan</th>
                                            <th>Detail (Spec/Kon)</th>
                                            <th width="15%">Jumlah Di Lab</th>
                                            <th width="5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="list-permintaan">
                                        <tr id="empty-row">
                                            <td colspan="5" class="text-center text-muted small">Belum ada bahan yang ditambahkan</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end">
                                <button type="submit" 
                                        name="kirimm" 
                                        id="btn-submit-all" 
                                        class="btn btn-navy px-5 py-2 rounded-pill shadow-sm fw-bold" 
                                        <?= !$is_active ? 'disabled' : ''; ?>>
                                    <i class="bi bi-send-check me-2"></i>Kirim Stok Ke Admin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <div class="table-responsive">
                    <table id="tabelStok" class="table table-hover align-middle" style="width:100%">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th width="5%">No</th>
                                <th>Informasi Bahan</th>
                                <th class="text-center">Stok Awal Masuk</th>
                                <th class="text-center">Total Terpakai</th>
                                <th class="text-center">Sisa Stok Lab</th>
                                <th>Spesifikasi</th>
                                <th class="text-center">Kondisi</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-center">Histori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_stok) > 0) :
                                while ($row = mysqli_fetch_assoc($query_stok)) : 
                                    // Logika perhitungan yang baru:
                                    // Stok Awal (masuk) diambil murni dari total barang yang 'diterima' dari admin
                                    $qty_awal = (float)($row['total_diterima'] ?? 0);
                                    
                                    // Terpakai (keluar) diambil dari total 'jumlah_pakai'
                                    $terpakai = (float)($row['total_terpakai'] ?? 0);
                                    
                                    // Sisa Stok di Lab adalah Awal - Terpakai
                                    $sisa = $qty_awal - $terpakai;
                                    
                                    // 1. Logic penentuan warna badge sisa stok
                                    if ($sisa <= 0) {
                                        $status_class = 'bg-empty'; 
                                    } elseif ($sisa < 5) {
                                        $status_class = 'bg-low';   
                                    } else {
                                        $status_class = 'bg-safe';  
                                    }

                                    // 2. Logic warna badge Kondisi
                                    $kondisi_badge = 'bg-secondary';
                                    $kondisi_teks  = $row['kondisi'] ?? 'N/A';
                                    if($kondisi_teks == 'Baik') $kondisi_badge = 'bg-success';
                                    if($kondisi_teks == 'Kurang Baik') $kondisi_badge = 'bg-warning text-dark';
                                    if($kondisi_teks == 'Rusak') $kondisi_badge = 'bg-danger';
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-shape bg-light me-3">
                                            <i class="bi bi-box-seam text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-navy"><?= htmlspecialchars($row['nama_bahan']); ?></div>
                                            <span class="badge bg-light text-muted fw-normal border" style="font-size: 0.7rem;">
                                                <?= htmlspecialchars($row['kode_bahan']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="text-center fw-semibold text-success">
                                     <?= number_format($qty_awal, 0, ',', '.'); ?>
                                </td>
                                
                                <td class="text-center text-danger fw-semibold">
                                     <?= number_format($terpakai, 0, ',', '.'); ?>
                                </td>
                                
                                <td class="text-center">
                                    <span class="stok-badge <?= $status_class; ?>">
                                        <?= number_format($sisa, 0, ',', '.'); ?>
                                    </span>
                                </td>

                                <td class="small text-muted">
                                    <?= !empty($row['spesifikasi']) ? nl2br(htmlspecialchars($row['spesifikasi'])) : '-'; ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge <?= $kondisi_badge; ?> rounded-pill">
                                        <?= $kondisi_teks; ?>
                                    </span>
                                </td>

                                <td class="text-center fw-bold text-muted"><?= htmlspecialchars($row['satuan']); ?></td>
                                
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm border-2" 
                                            style="font-size: 0.65rem; font-weight: 700; transition: 0.3s;"
                                            onclick="viewHistory('<?= $row['id_praktek'] ?>', '<?= addslashes($row['nama_bahan']) ?>')">
                                        <i class="bi bi-clock-history me-1"></i> LOG
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header text-white border-0" style="background-color: #001f3f; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clock-history me-2 text-warning"></i> Kartu Stok (Histori Mutasi)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3 border-start border-4 border-warning shadow-sm">
                    <div class="me-3">
                        <i class="bi bi-box-seam fs-2 text-primary"></i>
                    </div>
                    <div>
                        <span class="d-block small text-muted text-uppercase fw-bold mb-1">Nama Material / Bahan</span>
                        <h5 id="historiNamaBahan" class="fw-bold text-dark mb-0">Memuat...</h5>
                    </div>
                </div>
                
                <div id="isiHistori">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Memuat histori mutasi...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light" style="border-radius: 0 0 15px 15px;">
                <button type="button" class="btn btn-secondary rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function viewHistory(idPraktek, namaBahan) {
    document.getElementById('historiNamaBahan').innerText = namaBahan;
    document.getElementById('isiHistori').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Memuat histori mutasi...</p></div>';
    
    var myModal = new bootstrap.Modal(document.getElementById('modalHistory'));
    myModal.show();

    $.ajax({
        url: '../../../modules/distribusi/get_history_detail.php', 
        type: 'GET',
        data: { id_praktek: idPraktek },
        success: function(response) {
            $('#isiHistori').html(response);
            
            if ($.fn.DataTable.isDataTable('#tabelMutasi')) {
                $('#tabelMutasi').DataTable().destroy();
            }
            
            var t = $('#tabelMutasi').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
                "pageLength": 5, 
                "lengthMenu": [[5, 10, 50, 100, -1], [5, 10, 50, 100, "Semua"]], 
                "order": [[1, "desc"]], 
                "columnDefs": [{ "searchable": false, "orderable": false, "targets": 0 }],
                "responsive": true
            });

            t.on('order.dt search.dt', function () {
                let i = 1;
                t.cells(null, 0, {search: 'applied', order: 'applied'}).every(function (cell) {
                    this.data(i++);
                });
            }).draw();
        },
        error: function() {
            document.getElementById('isiHistori').innerHTML = '<div class="alert alert-danger">Gagal mengambil data histori.</div>';
        }
    });
}

$(document).ready(function() {
    $('#tabelStok').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
        "pageLength": 10,
        "order": [[8, "desc"]], 
        "responsive": true
    });

    function updateNomor() {
        $('#list-permintaan tr').each(function(index) {
            $(this).find('.nomor-urut').text(index + 1);
        });
    }

    $('#pilih_bahan').on('change', function() {
        const selected = $(this).find(':selected');
        if ($(this).val() !== "") {
            $('#auto_spesifikasi').val(selected.data('spesifikasi'));
            $('#auto_kondisi').val(selected.data('kondisi'));
        } else {
            $('#auto_spesifikasi').val('-');
            $('#auto_kondisi').val('-');
        }
    });

    $('#btn-tambah-item').on('click', function() {
        const select = $('#pilih_bahan').find(':selected');
        const idBahan = select.val();
        const namaBahan = select.data('nama');
        const kodeBahan = select.data('kode');
        const spesifikasi = select.data('spesifikasi');
        const kondisi = select.data('kondisi');
        const stokFisik = $('#input_stok_fisik').val();

        if (!idBahan) {
            alert('Silahkan pilih bahan dari pusat terlebih dahulu!');
            return;
        }

        $('#empty-row').remove();

        const newRow = `
            <tr>
                <td class="text-center nomor-urut"></td>
                <td>
                    <input type="hidden" name="id_barang[]" value="${idBahan}">
                    <div class="fw-bold">${namaBahan}</div>
                    <small class="text-muted">${kodeBahan}</small>
                </td>
                <td>
                    <span class="badge bg-secondary small">${spesifikasi}</span>
                    <span class="badge bg-info text-dark small">${kondisi}</span>
                </td>
                <td>
                    <div class="fw-bold">${stokFisik}</div>
                    <input type="hidden" name="stok_fisik_lab[]" value="${stokFisik}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-hapus-item"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;

        $('#list-permintaan').append(newRow);
        updateNomor();
        $('#btn-submit-all').prop('disabled', false);

        $('#pilih_bahan').val('').trigger('change');
        $('#input_stok_fisik').val(0);
        $('#auto_spesifikasi').val('-');
        $('#auto_kondisi').val('-');
    });

    $(document).on('click', '.btn-hapus-item', function() {
        $(this).closest('tr').remove();
        
        if ($('#list-permintaan tr').length === 0) {
            $('#list-permintaan').append('<tr id="empty-row"><td colspan="5" class="text-center text-muted small">Belum ada bahan yang ditambahkan</td></tr>');
            $('#btn-submit-all').prop('disabled', true);
        } else {
            updateNomor();
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'success') {
        Swal.fire({
            title: 'Berhasil!',
            text: 'Daftar permintaan stok telah dikirim ke Admin Pusat.',
            icon: 'success',
            confirmButtonColor: '#001f3f', // Warna Navy Tema Kita
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Oke, Mengerti',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0', // Membulatkan sudut pop-up
                confirmButton: 'rounded-pill px-4 fw-bold' // Membulatkan tombol
            }
        }).then((result) => {
            // Membersihkan URL agar notifikasi tidak muncul berulang
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    } else if (status === 'empty') {
        Swal.fire({
            title: 'Gagal!',
            text: 'Tidak ada bahan yang dipilih. Silakan tambah material terlebih dahulu.',
            icon: 'error',
            confirmButtonColor: '#dc3545', // Merah
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Tutup',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'rounded-pill px-4 fw-bold'
            }
        }).then(() => {
            // Tetap bersihkan URL meski gagal
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
});
</script>

</body>
</html>