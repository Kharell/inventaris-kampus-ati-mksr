<?php
session_start();
include "../../../config/database.php";

// 1. Proteksi Akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kepala_lab') {
    header("Location: ../../../login.php");
    exit;
}

$id_lab_user = $_SESSION['id_lab'] ?? '';

// 2. Query Opsi Barang (Langsung baca dari bahan_praktek karena stok sudah terpusat)
$sql_opsi = "SELECT id_praktek, kode_bahan, nama_bahan, spesifikasi, kondisi, satuan, stok as sisa_stok 
             FROM bahan_praktek 
             WHERE id_lab = '$id_lab_user' AND stok > 0 
             ORDER BY nama_bahan ASC";
$query_barang = mysqli_query($conn, $sql_opsi);

// 3. Query Riwayat Pemakaian (Langsung JOIN ke bahan_praktek saja)
$sql_history = "SELECT p.*, b.nama_bahan, b.satuan, b.kode_bahan, b.spesifikasi, b.kondisi
                FROM pemakaian_lab p 
                JOIN bahan_praktek b ON p.id_praktek = b.id_praktek 
                WHERE p.id_lab = '$id_lab_user' 
                ORDER BY p.tgl_pakai DESC";
$riwayat = mysqli_query($conn, $sql_history);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor Pemakaian | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        :root { --navy: #001f3f; --bg: #f8fafc; }
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: #ffffff; }
        .btn-navy { background: var(--navy); color: white; border-radius: 10px; padding: 10px 20px; transition: 0.3s; }
        .btn-navy:hover { background: #003366; transform: translateY(-2px); color: white; }
        table.dataTable thead th { background-color: #f8fafc; color: #475569; font-weight: 700; border-bottom: 2px solid #eee!important; }
        .stok-info { font-size: 0.75rem; font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="main-content w-100 p-4"> 
        <?php include "../../../includes/header.php"; ?>
        
        <div class="container-fluid" style="margin-top: 70px;">
            
            <div class="card-custom p-4 mb-4 border-start border-5" style="border-color: var(--navy) !important;">
                <div class="row align-items-center">
                   <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-pencil-square fs-3 me-3 text-dark"></i>
                        <div>
                            <h3 class="fw-bold mb-1">Lapor Pemakaian</h3>
                            <p class="text-muted mb-0">Catat penggunaan bahan dari stok laboratorium Anda</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="btn-group shadow-sm">
                             <button type="button" class="btn btn-white border" onclick="location.href='pemakaian.php'"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
                             <button type="button" class="btn btn-navy" data-bs-toggle="collapse" data-bs-target="#formCollapse">
                                <i class="bi bi-plus-circle me-2"></i>Input Pemakaian
                             </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-12 collapse" id="formCollapse">
                    <div class="card-custom p-4 border-bottom border-4 border-primary">
                      <h5 class="fw-bold mb-4">
                            <i class="bi bi-pencil-square me-2 text-warning"></i>Form Laporan Pemakaian
                        </h5>  
                        <form action="../proses/tambah.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">CARI KODE / NAMA BAHAN</label>
                                    <!-- UBAH NAME JADI id_praktek -->
                                    <select name="id_praktek" id="pilih_bahan" class="form-select select2-pencarian" required>
                                        <option value="">Pilih bahan yang tersedia...</option>
                                        <?php while($b = mysqli_fetch_assoc($query_barang)): ?>
                                            <!-- Value sekarang mengirim id_praktek|kode_bahan -->
                                            <option value="<?= $b['id_praktek']; ?>|<?= $b['kode_bahan']; ?>" 
                                                    data-spesifikasi="<?= htmlspecialchars($b['spesifikasi']); ?>" 
                                                    data-kondisi="<?= htmlspecialchars($b['kondisi']); ?>"
                                                    data-stok="<?= $b['sisa_stok']; ?>"
                                                    data-satuan="<?= $b['satuan']; ?>">
                                                <?= $b['kode_bahan']; ?> - <?= $b['nama_bahan']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-muted">SPESIFIKASI</label>
                                    <input type="text" id="display_spesifikasi" class="form-control bg-light" readonly placeholder="-">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-muted">KONDISI</label>
                                    <input type="text" id="display_kondisi" class="form-control bg-light" readonly placeholder="-">
                                </div>

                                <div class="col-md-1">
                                    <label class="form-label fw-bold small text-muted">QTY</label>
                                    <input type="number" name="jumlah_pakai" id="input_jumlah" class="form-control" min="1" required>
                                    <div id="stok_info" class="stok-info mt-1"></div>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" name="lapor_pakai" id="btn_submit" class="btn btn-navy w-100">
                                        <i class="bi bi-check-circle me-2"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card-custom p-4">
                        <h5 class="fw-bold mb-4">Riwayat Pemakaian Bahan</h5>
                        <div class="table-responsive">
                            <table id="tabelPemakaian" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr class="text-muted small text-uppercase">
                                        <th class="text-center" width="5%">No</th>
                                        <th>Waktu Lapor</th>
                                        <th>Nama Bahan</th>
                                        <th>Spesifikasi</th>
                                        <th class="text-center">Kondisi</th>
                                        <th class="text-center">Jumlah Pakai</th>
                                        <th class="text-center" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; while($r = mysqli_fetch_assoc($riwayat)): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++; ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= date('d M Y', strtotime($r['tgl_pakai'])); ?></div>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($r['tgl_pakai'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy"><?= $r['nama_bahan']; ?></div>
                                            <code class="text-muted bg-light px-2 py-1 rounded border shadow-sm" style="font-size: 0.75rem;"><?= $r['kode_bahan']; ?></code>
                                        </td>
                                        <td><div class="small text-wrap" style="max-width: 150px;"><?= $r['spesifikasi'] ?: '-'; ?></div></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1"><?= $r['kondisi']; ?></span>
                                        </td>
                                        <td class="text-center fw-bold text-danger">
                                             <?= $r['jumlah_pakai']; ?> <small class="text-muted fw-normal"><?= $r['satuan']; ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            // Cek apakah data sudah dikunci (status_kunci = 1)
                                            $is_locked = isset($r['status_kunci']) && $r['status_kunci'] == 1;
                                            
                                            if (!$is_locked): 
                                            ?>
                                                <!-- Jika BELUM dikunci: Tampilkan tombol Kunci & Hapus -->
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button onclick="confirmLock('<?= $r['id_pemakaian']; ?>')" class="btn btn-sm btn-outline-success rounded-circle shadow-sm" title="Kunci Laporan">
                                                        <i class="bi bi-lock-fill"></i>
                                                    </button>
                                                    <button onclick="confirmDelete('<?= $r['id_pemakaian']; ?>')" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Batalkan/Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <!-- Jika SUDAH dikunci: Tampilkan Label Terkunci -->
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-3 py-2">
                                                    <i class="bi bi-lock-fill me-1"></i> Terkunci
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#tabelPemakaian').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
            "pageLength": 10,
            "order": [[1, "desc"]]
        });

        $('.select2-pencarian').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            dropdownParent: $('#formCollapse')
        });

        // Logika Auto-Fill & Validasi Stok
        $('#pilih_bahan').on('change', function() {
            const selected = $(this).find(':selected');
            const spesifikasi = selected.data('spesifikasi');
            const kondisi = selected.data('kondisi');
            const stok = selected.data('stok');
            const satuan = selected.data('satuan');
            
            $('#display_spesifikasi').val(spesifikasi || '-');
            $('#display_kondisi').val(kondisi || '-');
            $('#input_jumlah').attr('max', stok);
            
            if(stok) {
                $('#stok_info').text('Sisa: ' + stok + ' ' + satuan);
            } else {
                $('#stok_info').text('');
            }
        });

        // Validasi input QTY tidak boleh melebihi stok
        $('#input_jumlah').on('input', function() {
            const val = parseInt($(this).val());
            const max = parseInt($(this).attr('max'));
            if(val > max) {
                $(this).addClass('is-invalid');
                $('#btn_submit').prop('disabled', true);
            } else {
                $(this).removeClass('is-invalid');
                $('#btn_submit').prop('disabled', false);
            }
        });
    });

    function confirmLock(id) {
    Swal.fire({
        title: 'Kunci Laporan Ini?',
        text: "Laporan pemakaian yang sudah dikunci tidak akan bisa dibatalkan atau dihapus lagi!",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#198754', // Warna Hijau Success
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-lock-fill me-1"></i> Ya, Kunci Permanen',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengunci...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            window.location.href = "../proses/tambah.php?kunci_pakai=" + id;
        }
    });
}

    function confirmDelete(id) {
        Swal.fire({
            title: 'Batalkan Laporan Pemakaian?',
            text: "Riwayat pemakaian ini akan dihapus dan stok fisik bahan akan DIKEMBALIKAN (+). Lanjutkan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#001f3f', // Warna Navy untuk tombol konfirmasi
            cancelButtonColor: '#dc3545',  // Warna Merah untuk batal
            confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Kembalikan Stok',
            cancelButtonText: 'Tutup'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan efek loading saat sistem memproses pengembalian stok
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang mengembalikan stok bahan',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                // Arahkan ke backend hapus
                window.location.href = "../proses/tambah.php?hapus_pakai=" + id;
            }
        });
    }
</script>
</body>
</html>