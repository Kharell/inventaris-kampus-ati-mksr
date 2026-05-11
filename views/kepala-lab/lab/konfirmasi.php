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
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Masuk | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root { --navy: #001f3f; --bg: #f4f7fa; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .text-navy { color: var(--navy); }
        .table thead th { 
            background-color: #f8f9fa; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 1px;
            color: #6c757d;
            border: none;
        }
        .status-pill { font-size: 0.75rem; padding: 6px 12px; border-radius: 30px; font-weight: 600; }
        .bg-proses { background: #fff3cd; color: #856404; }
        .bg-ditolak { background: #f8d7da; color: #842029; } /* Style Baru untuk Ditolak */
        .row-rejected { background-color: #fff5f5 !important; } /* Highlight baris yang ditolak */
        .text-spek { font-size: 0.8rem; color: #6c757d; display: block; margin-top: 2px; }
        .badge-kondisi { font-size: 0.7rem; padding: 3px 8px; }
    </style>
</head>
<body>

<div class="d-flex" style="background-color: #f8f9fa; min-height: 100vh;">
    <?php include "../../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../../includes/header.php"; ?>
        <main class="p-4" style="margin-top: 70px;">
            <div class="page-header d-flex justify-content-between align-items-center bg-white p-4 shadow-sm rounded-4 border-start border-5 mb-4" style="border-color: var(--navy) !important;">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-light p-3 rounded-3 me-4 shadow-sm border">
                        <i class="bi bi-truck text-dark" style="font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--navy);">Konfirmasi Barang Masuk</h4>
                        <p class="text-muted mb-0 small">Verifikasi barang yang telah sampai di laboratorium Anda</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-uppercase mb-0 text-secondary">Antrian Penerimaan</h6>
                </div>

                <div class="table-responsive">
                    <table id="tabelKonfirmasi" class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Kode Distribusi</th>
                                <th>Nama Material</th>
                                <th>Spek & Kondisi</th>
                                <th class="text-center">Jumlah</th>
                                <th>Status & Koordinasi</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT d.*, b.nama_bahan, b.satuan 
                                    FROM distribusi_lab d 
                                    JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
                                    WHERE d.id_lab = '$id_lab_user' 
                                    AND d.status != 'diterima'
                                    ORDER BY d.id_distribusi DESC";
                            $query = mysqli_query($conn, $sql);

                            while ($row = mysqli_fetch_assoc($query)) :
                                $status = $row['status'];
                                $tgl_kirim = $row['tanggal_distribusi'] ?? null;
                                $kon = $row['kondisi'] ?? 'Baik';
                                $kon_class = ($kon == 'Baik') ? 'bg-success' : (($kon == 'Rusak') ? 'bg-danger' : 'bg-warning text-dark');
                                
                                // INI KUNCINYA: Mengembalikan table-danger jika ditolak
                                $row_class = ($status == 'ditolak') ? 'table-danger' : '';
                            ?>
                                <tr class="<?= $row_class ?>">
                                    <td class="ps-4">
                                        <span class="fw-bold text-navy"><?= htmlspecialchars($row['kode_distribusi']) ?></span>
                                        <div class="smaller text-muted" style="font-size: 0.7rem;">
                                            Dikirim: <?= $tgl_kirim ? date('d/m/Y', strtotime($tgl_kirim)) : '-'; ?>
                                        </div>
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['nama_bahan']) ?></td>
                                    <td>
                                        <span class="text-spek small d-block mb-1"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($row['spesifikasi'] ?? '-') ?></span>
                                        <span class="badge <?= $kon_class ?> rounded-pill" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($kon) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-3">
                                            <?= $row['jumlah'] ?> <?= htmlspecialchars($row['satuan']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ($status == 'ditolak') : ?>
                                            <div class="p-2 rounded bg-white border border-danger shadow-sm" style="max-width: 250px;">
                                                <small class="fw-bold text-danger d-block mb-1" style="font-size: 0.65rem;">ALASAN ANDA:</small>
                                                <p class="mb-1 text-dark small" style="line-height: 1.2;">"<?= htmlspecialchars($row['keterangan'] ?? '-') ?>"</p>
                                                
                                                <?php if (!empty($row['balasan_admin'])) : ?>
                                                    <div class="mt-1 pt-1 border-top">
                                                        <small class="fw-bold text-primary d-block" style="font-size: 0.65rem;">BALASAN ADMIN:</small>
                                                        <p class="mb-0 text-dark fw-semibold small"><?= htmlspecialchars($row['balasan_admin']) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else : ?>
                                            <span class="badge bg-warning text-dark px-3 py-2">
                                                <i class="bi bi-truck me-1"></i> Sedang Dikirim
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <?php if ($status == 'dikirim') : ?>
                                                <button class="btn btn-sm btn-success fw-bold shadow-sm px-3" 
                                                        onclick="modalTerima('<?= $row['id_distribusi'] ?>', '<?= addslashes($row['nama_bahan']) ?>', '<?= $row['jumlah'] ?>', '<?= $row['satuan'] ?>')">
                                                    <i class="bi bi-check2-circle"></i> Terima
                                                </button>
                                                <button class="btn btn-sm btn-danger fw-bold shadow-sm" 
                                                        onclick="modalTolak('<?= $row['id_distribusi'] ?>', '<?= addslashes($row['nama_bahan']) ?>')">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </button>
                                            <?php elseif ($status == 'ditolak') : ?>
                                                <!-- <button class="btn btn-sm btn-outline-danger bg-white" onclick="hapusRiwayat('<?= $row['id_distribusi'] ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button> -->
                                                <button class="btn btn-sm btn-primary" onclick="ajukanLagi('<?= $row['id_distribusi'] ?>')">
                                                    <i class="bi bi-arrow-clockwise"></i> Menunggu Tindakan Admin
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // FUNGSI MODAL TERIMA
function modalTerima(id, nama, jmlKirim, satuan) {
    Swal.fire({
        title: '<span class="fw-bold">Konfirmasi Penerimaan</span>',
        icon: 'info',
        html: `
            <div class="text-start mt-2">
                <p class="text-muted small mb-3">Silakan pilih kondisi kedatangan barang untuk <br><b class="text-dark">${nama}</b>:</p>
                
                <div class="list-group shadow-sm">
                    <label class="list-group-item list-group-item-action d-flex gap-3 py-3 cursor-pointer border-start border-success border-4">
                        <input class="form-check-input flex-shrink-0" type="radio" name="tipeTerima" value="full" checked onchange="togglePartial(false, ${jmlKirim})">
                        <div class="d-flex gap-2 w-100 justify-content-between">
                            <div>
                                <h6 class="mb-0 text-success fw-bold">Terima Semua</h6>
                                <p class="mb-0 small text-muted">Kondisi lengkap: ${jmlKirim} ${satuan}</p>
                            </div>
                            <i class="bi bi-check-all fs-4 text-success"></i>
                        </div>
                    </label>

                    <label class="list-group-item list-group-item-action d-flex gap-3 py-3 cursor-pointer border-start border-warning border-4">
                        <input class="form-check-input flex-shrink-0" type="radio" name="tipeTerima" value="kurang" onchange="togglePartial(true, 'Jumlah yang Diterima', ${jmlKirim})">
                        <div class="d-flex gap-2 w-100 justify-content-between">
                            <div>
                                <h6 class="mb-0 text-warning fw-bold">Terima Sebagian</h6>
                                <p class="mb-0 small text-muted">Fisik kurang atau sebagian rusak</p>
                            </div>
                            <i class="bi bi-minus-circle fs-4 text-warning"></i>
                        </div>
                    </label>
                </div>

                <div id="areaInputPartial" class="mt-4 p-3 bg-light rounded-3 border-dashed" style="display:none; border: 2px dashed #dee2e6;">
                    <label id="labelPartial" class="fw-bold mb-2 small text-primary">Jumlah Fisik (Yang Baik):</label>
                    <div class="input-group mb-3">
                        <input type="number" id="jmlFisik" class="form-control fw-bold" placeholder="0">
                        <span class="input-group-text">${satuan}</span>
                    </div>
                    
                    <!-- TAMBAHAN BARU: TEXTAREA ALASAN -->
                    <label class="fw-bold mb-2 small text-danger">Alasan Kekurangan/Kerusakan:</label>
                    <textarea id="alasanPartial" class="form-control" rows="2" placeholder="Contoh: 2 Pcs pecah di perjalanan..."></textarea>
                    
                    <div class="form-text text-danger mt-1 small" id="errMsg"></div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-box-arrow-in-down me-2"></i>Selesaikan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        preConfirm: () => {
            const tipe = document.querySelector('input[name="tipeTerima"]:checked').value;
            let jml = document.getElementById('jmlFisik').value;
            let alasan = document.getElementById('alasanPartial').value; // Tangkap alasan
            
            if (tipe === 'full') {
                jml = jmlKirim;
                alasan = ''; // Jika full, tidak butuh alasan
            } else {
                if (!jml || jml <= 0) {
                    Swal.showValidationMessage('Jumlah harus diisi dan lebih dari 0!');
                    return false;
                }
                if (parseInt(jml) >= parseInt(jmlKirim)) {
                    Swal.showValidationMessage(`Jumlah kurang harus di bawah ${jmlKirim}!`);
                    return false;
                }
                if (!alasan.trim()) {
                    Swal.showValidationMessage('Alasan kekurangan wajib diisi!');
                    return false;
                }
            }
            return { tipe: tipe, jumlah: jml, alasan: alasan };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../proses/tambah.php',
                type: 'POST',
                // PASTIKAN MENGIRIM KETERANGAN (ALASAN) KE BACKEND
                data: { aksi: 'terima_barang', id: id, jumlah: result.value.jumlah, tipe: result.value.tipe, keterangan: result.value.alasan },
                success: function(res) {
                    if(res.trim() === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data stok telah diperbarui.', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res, 'error');
                    }
                }
            });
        }
    });
}

function togglePartial(show, label, max) {
    const area = document.getElementById('areaInputPartial');
    const input = document.getElementById('jmlFisik');
    area.style.display = show ? 'block' : 'none';
    if(show) {
        document.getElementById('labelPartial').innerText = label + ' (Max ' + max + '):';
        input.focus();
    }
}

  function modalTolak(id, nama) {
    Swal.fire({
        title: '<span class="text-danger">Tolak Pengiriman?</span>',
        icon: 'warning',
        html: `<p class="small text-muted">Anda akan menolak <b>${nama}</b>. <br>Berikan alasan penolakan secara mendetail:</p>`,
        input: 'textarea',
        inputPlaceholder: 'Contoh: Barang tidak sesuai pesanan, dokumen salah...',
        inputAttributes: {
            'aria-label': 'Alasan penolakan',
            'rows': 4
        },
        footer: '<span class="text-danger small"><i class="bi bi-info-circle me-1"></i>Tindakan ini akan mengembalikan stok ke Admin.</span>',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tolak Sekarang',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: (alasan) => {
            if (!alasan) {
                Swal.showValidationMessage('Alasan penolakan wajib diisi!');
            }
            return alasan;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../proses/tambah.php',
                type: 'POST',
                data: { aksi: 'tolak', id: id, keterangan: result.value },
                success: function(res) {
                    if(res.trim() === 'success') {
                        Swal.fire({ icon: 'success', title: 'Ditolak', text: 'Barang telah ditolak dan dikembalikan.', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res, 'error');
                    }
                }
            });
        }
    });
}
</script>