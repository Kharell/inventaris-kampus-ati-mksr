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
 * QUERY STOK:
 * Mengambil data stok, spesifikasi, dan kondisi dari tabel distribusi_lab
 */
$sql_stok = "SELECT 
                d.id_distribusi,
                d.kode_distribusi, 
                d.id_praktek,
                d.jumlah as qty_awal,
                d.spesifikasi, 
                d.kondisi,
                b.nama_bahan, 
                b.satuan, 
                d.tanggal_distribusi, 
                COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0) as total_terpakai,
                (d.jumlah - COALESCE((SELECT SUM(jumlah_pakai) FROM pemakaian_lab WHERE id_distribusi = d.id_distribusi), 0)) as sisa_stok
             FROM distribusi_lab d
             JOIN bahan_praktek b ON d.id_praktek = b.id_praktek
             WHERE d.id_lab = '$id_lab_user' 
             AND d.status = 'diterima'
             ORDER BY d.tanggal_distribusi DESC";

$query_stok = mysqli_query($conn, $sql_stok);

if (!$query_stok) {
    die("Gagal mengambil data stok: " . mysqli_error($conn));
}

// --- STEP 2: Ambil Bahan HANYA dari Lab yang sama ---
// Kita ambil stok dan data lainnya langsung dari tabel bahan_praktek
$query_barang = mysqli_query($conn, "SELECT * FROM bahan_praktek WHERE id_lab = '$id_lab_user' ORDER BY nama_bahan ASC");

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
        .bg-low { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .bg-empty { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .bg-safe { background: #e0f2f1; color: #00695c; border: 1px solid #b2dfdb; }

        .icon-shape {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        /* [ TAMBAHAN STYLE UNTUK BADGE SOFT ] */
        .bg-soft-primary { background: #e8f0fe; color: #0d6efd; }
        .bg-success-subtle { background-color: #d1e7dd; }
    </style>

    <style>
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

                        <style>
                            .disabled-style {
                                background-color: #e9ecef !important;
                                border-color: #dee2e6 !important;
                                color: #6c757d !important;
                                filter: grayscale(1);
                            }
                            
                            .btn-disabled {
                                pointer-events: none;
                                background-color: #f8f9fa;
                                color: #adb5bd;
                            }
                        </style>
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
                    <label class="form-label fw-bold small text-muted">CARI & PILIH BAHAN</label>
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
                    <label class="form-label fw-bold small text-muted">STOK</label>
                    <input type="number" id="input_stok_fisik" class="form-control border-primary" min="0" value="0">
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
                                <th width="15%">Stok Lab Saat Ini</th>
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
                        <i class="bi bi-send-check me-2"></i>Kirim Data Pemakaian
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
                                <th class="text-center">Stok Awal</th>
                                <th class="text-center">Terpakai</th>
                                <th class="text-center">Sisa Stok</th>
                                <th>Spesifikasi</th>
                                <th class="text-center">Kondisi</th>
                                <th class="text-center">Satuan</th>
                                <th>Tgl Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_stok) > 0) :
                                while ($row = mysqli_fetch_assoc($query_stok)) : 
                                    $sisa = $row['sisa_stok'];
                                    
                                    // Logic penentuan warna stok
                                    if ($sisa <= 0) {
                                        $status_class = 'bg-empty';
                                    } elseif ($sisa < 5) {
                                        $status_class = 'bg-low';
                                    } else {
                                        $status_class = 'bg-safe';
                                    }

                                    // Logic warna Kondisi
                                    $kondisi_badge = 'bg-secondary';
                                    if($row['kondisi'] == 'Baik') $kondisi_badge = 'bg-success';
                                    if($row['kondisi'] == 'Kurang Baik') $kondisi_badge = 'bg-warning text-dark';
                                    if($row['kondisi'] == 'Rusak') $kondisi_badge = 'bg-danger';
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-shape bg-light me-3">
                                            <i class="bi bi-box-seam text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-navy"><?= $row['nama_bahan']; ?></div>
                                            <span class="badge bg-light text-muted fw-normal border" style="font-size: 0.7rem;">
                                                <?= $row['kode_distribusi']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-semibold text-muted">
                                    <?= number_format($row['qty_awal'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center text-danger fw-semibold">
                                     <?= number_format($row['total_terpakai'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <span class="stok-badge <?= $status_class; ?>">
                                        <?= number_format($sisa, 0, ',', '.'); ?>
                                    </span>
                                </td>

                                <td class="small text-muted">
                                    <?= !empty($row['spesifikasi']) ? nl2br($row['spesifikasi']) : '-'; ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge <?= $kondisi_badge; ?> rounded-pill">
                                        <?= $row['kondisi'] ?? 'N/A'; ?>
                                    </span>
                                </td>

                                <td class="text-center fw-bold text-muted"><?= $row['satuan']; ?></td>
                                <td>
                                    <small class="text-muted">
                                        <?= !empty($row['tanggal_distribusi']) ? date('d/m/Y', strtotime($row['tanggal_distribusi'])) : '-'; ?>
                                    </small>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            endif; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelStok').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            },
            "pageLength": 10,
            "order": [[8, "desc"]], 
            "responsive": true
        });
    });
</script>
<script>
$(document).ready(function() {
    // Fungsi untuk mereset ulang nomor urut di tabel
    function updateNomor() {
        $('#list-permintaan tr').each(function(index) {
            $(this).find('.nomor-urut').text(index + 1);
        });
    }

    // 1. Logika Auto-fill saat pilih bahan
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

    // 2. Logika Tambah ke Tabel
    $('#btn-tambah-item').on('click', function() {
        const select = $('#pilih_bahan').find(':selected');
        const idBahan = select.val();
        const namaBahan = select.data('nama');
        const kodeBahan = select.data('kode');
        const spesifikasi = select.data('spesifikasi');
        const kondisi = select.data('kondisi');
        const stokFisik = $('#input_stok_fisik').val();

        if (!idBahan) {
            alert('Silahkan pilih bahan terlebih dahulu!');
            return;
        }

        $('#empty-row').remove();

        // Gunakan class "nomor-urut" untuk kolom pertama
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
        
        // Panggil update nomor setelah tambah baris
        updateNomor();
        
        $('#btn-submit-all').prop('disabled', false);

        // Reset input atas
        $('#pilih_bahan').val('').trigger('change');
        $('#input_stok_fisik').val(0);
        $('#auto_spesifikasi').val('-');
        $('#auto_kondisi').val('-');
    });

    // 3. Hapus Item
    $(document).on('click', '.btn-hapus-item', function() {
        $(this).closest('tr').remove();
        
        if ($('#list-permintaan tr').length === 0) {
            $('#list-permintaan').append('<tr id="empty-row"><td colspan="6" class="text-center text-muted small">Belum ada bahan yang ditambahkan</td></tr>');
            $('#btn-submit-all').prop('disabled', true);
        } else {
            // Panggil update nomor setelah hapus baris agar tetap urut
            updateNomor();
        }
    });
});
</script>

<script>
$(document).ready(function() {
    // Cek parameter 'status' di URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'success') {
        Swal.fire({
            title: 'Berhasil!',
            text: 'Daftar permintaan stok telah dikirim ke Admin.',
            icon: 'success',
            confirmButtonColor: '#ffc107', // Warna kuning sesuai tema Anda
            confirmButtonText: 'Oke'
        }).then((result) => {
            // Bersihkan parameter URL agar pop-up tidak muncul lagi saat refresh
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    } else if (status === 'empty') {
        Swal.fire({
            title: 'Gagal!',
            text: 'Tidak ada bahan yang dipilih.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    }
    
    // ... kode JS Anda yang lain (tambah item, hapus item, dll) ...
});
</script>

</body>
</html>