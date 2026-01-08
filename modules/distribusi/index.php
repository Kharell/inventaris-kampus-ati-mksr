<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

$query_jurusan = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
$list_barang = mysqli_query($conn, "SELECT id_praktek, nama_bahan, kode_bahan, stok, satuan FROM bahan_praktek WHERE stok > 0 ORDER BY nama_bahan ASC");
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

        .nav-lab .nav-link {
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            padding: 12px 15px;
            background: white;
        }
        .nav-lab .nav-link:hover {
            background-color: #f8fafc;
            border-color: #002b5c;
        }
        .nav-lab .nav-link.active {
            background-color: #002b5c !important;
            border-color: #002b5c;
        }
        .nav-lab .nav-link.active .text-navy, 
        .nav-lab .nav-link.active .text-muted {
            color: white !important;
        }
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

        /* Nav Tab Jurusan (Utama) */
        .nav-jurusan .nav-link { 
            color: #6c757d; border: none; padding: 12px 25px; font-weight: 700; 
            border-radius: 10px; transition: 0.3s; margin-right: 5px;
        }
        .nav-jurusan .nav-link.active { background: var(--navy); color: var(--gold); }

        /* Nav Tab Lab (Sub-Tab) */
        .nav-lab .nav-link { 
            color: var(--navy); background: #fff; border: 1px solid #dee2e6; 
            margin-bottom: 10px; text-align: left; font-weight: 600; padding: 15px;
            border-radius: 12px; display: flex; align-items: center; justify-content: space-between;
        }
        .nav-lab .nav-link.active { border-color: var(--navy); background: #f0f4f8; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
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

            <ul class="nav nav-pills nav-jurusan mb-4" id="pills-tab" role="tablist">
    <?php 
    $active_j = true;
        mysqli_data_seek($query_jurusan, 0);
        while($j = mysqli_fetch_assoc($query_jurusan)): 
            $id_jur = $j['id_jurusan'];
            
            // Query untuk cek apakah ada salah satu lab di jurusan ini yang punya permintaan pending
            $check_req = mysqli_query($conn, "SELECT p.id_permintaan 
                FROM permintaan_barang p
                JOIN kepala_lab kl ON p.id_kepala = kl.id_kepala
                JOIN lab l ON kl.id_lab = l.id_lab
                WHERE l.id_jurusan = '$id_jur' AND p.status = 'pending' 
                LIMIT 1"); // Limit 1 karena kita hanya butuh tahu "ada atau tidak"
                
            $has_pending = (mysqli_num_rows($check_req) > 0);
        ?>
        <li class="nav-item">
            <button class="nav-link position-relative <?= $active_j ? 'active' : ''; ?>" 
                    data-bs-toggle="pill" 
                    data-bs-target="#jur-<?= $id_jur; ?>" 
                    type="button">
                
                <?= $j['nama_jurusan']; ?>

                <?php if ($has_pending) : ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle pulse-dot">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                <?php endif; ?>
                
            </button>
        </li>
        <?php $active_j = false; endwhile; ?>
    </ul>



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


<div class="modal fade" id="distModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../proses/tambah.php" method="POST" class="modal-content border-0 rounded-4 overflow-hidden">
            
            <div class="modal-header border-0" style="background-color: #0a192f; padding: 20px;">
                <h5 class="modal-title fw-bold" style="color: #ffffff;">
                    Kirim Bahan ke: <span id="labName" style="color: #ffcc00;"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <input type="hidden" name="id_lab" id="modIdLab">
                <input type="hidden" name="id_permintaan" id="modIdReq">
                <input type="hidden" name="nama_jurusan" id="modJurusan">
                <input type="hidden" name="nama_lab" id="modLab">   
                
                <div class="mb-3">
                    <label class="form-label fw-bold small" style="color: #0a192f;">PILIH BAHAN PRAKTEK</label>
                    <select name="id_praktek" id="modBarang" class="form-select border-2" onchange="generateCode()" required>
                        <option value="">-- Pilih Bahan --</option>
                        <?php 
                        mysqli_data_seek($list_barang, 0); 
                        while($b = mysqli_fetch_assoc($list_barang)): 
                        ?>
                            <option value="<?= $b['id_praktek']; ?>" data-kode="<?= $b['kode_bahan']; ?>">
                                <?= $b['nama_bahan']; ?> (Stok: <?= $b['stok']; ?> <?= $b['satuan']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small" style="color: #0a192f;">KODE LABEL OTOMATIS</label>
                    <input type="text" name="kode_distribusi" id="modKode" class="form-control bg-light fw-bold font-monospace" readonly style="color: #0a192f; border: 1px dashed #0a192f;">
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="fw-bold small" style="color: #0a192f;">JUMLAH KIRIM</label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="fw-bold small" style="color: #0a192f;">TANGGAL</label>
                        <input type="date" name="tanggal_distribusi" class="form-control" value="<?= date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="simpan_distribusi" class="btn w-100 py-3 fw-bold shadow-sm" style="background-color: #0a192f; color: #ffcc00; border-radius: 10px;">
                    <i class="bi bi-send-check me-2"></i>KONFIRMASI PENGIRIMAN
                </button>
            </div>
        </form>
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

    function viewLabDetails(id, labName, jurName) {
        currentLabId = id;
        currentLabName = labName;
        currentJurName = jurName;

        const view = document.getElementById('distribusi-view');
        // Render Container Utama
        // Kode baru dengan tema Navy
        view.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #0a192f !important;">
                <div>
                    <h4 class="fw-bold mb-0" style="color: #0a192f;">${labName}</h4>
                    <span class="badge" style="background-color: #ffcc00; color: #0a192f;">${jurName}</span>
                </div>
                <div class="d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" class="form-control" placeholder="Cari..." onkeyup="handleSearch(this.value)">
                    </div>
                    <button class="btn btn-sm btn-add-dist shadow-sm" onclick="openDistModal('${id}', '${labName}', '${jurName}')" style="background-color: #0a192f; color: #ffcc00;">
                        <i class="bi bi-plus-lg me-1"></i>Kirim
                    </button>
                </div>
            </div>
            <div id="table-content">
                <div class="text-center p-5"><div class="spinner-border" style="color: #0a192f;"></div></div>
            </div>
        `;
        loadDistribusi(id, 1, '');
    }

    function loadDistribusi(id_lab, page = 1, keyword = '') {
        // Pastikan ID ini SAMA dengan ID tempat tabel muncul
        const tableContainer = document.getElementById('table-content');
        if(!tableContainer) return;

        fetch(`get_history.php?id_lab=${id_lab}&page=${page}&keyword=${keyword}`)
            .then(response => response.text())
            .then(data => {
                tableContainer.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.innerHTML = '<div class="alert alert-danger">Gagal memuat data.</div>';
            });
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
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tutup'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../proses/hapus.php?hapus_distribusi=${id}`;
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

function viewLabDetails(id, labName, jurName) {
    // 1. Simpan ke variabel global
    currentLabId = id;
    currentLabName = labName;
    currentJurName = jurName;

    // 2. Langsung isi input hidden di modal supaya 'siaga'
    document.getElementById('modIdLab').value = id;
    document.getElementById('modLab').value = labName;
    document.getElementById('modJurusan').value = jurName;

    // ... kode fetch/loadDistribusi Anda yang sudah ada ...
    const view = document.getElementById('distribusi-view');
    view.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm border-start border-4" style="border-left-color: #0a192f !important;">
            <div>
                <h4 class="fw-bold mb-0" style="color: #0a192f;">${labName}</h4>
                <span class="badge" style="background-color: #ffcc00; color: #0a192f;">${jurName}</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-add-dist shadow-sm" onclick="openDistModal('${id}', '${labName}', '${jurName}')" style="background-color: #0a192f; color: #ffcc00;">
                    <i class="bi bi-plus-lg me-1"></i>Kirim
                </button>
            </div>
        </div>
        <div id="table-content">
            <div class="text-center p-5"><div class="spinner-border" style="color: #0a192f;"></div></div>
        </div>
    `;
    loadDistribusi(id, 1, '');
}

function prosesACC(idPermintaan, idBahan, jmlMinta, namaBahan) {
    // Pastikan ID Lab terisi dari variabel global jika parameter fungsi tidak membawanya
    if (!currentLabId) {
        Swal.fire('Peringatan', 'ID Lab hilang, silakan klik ulang nama Lab di menu kiri.', 'warning');
        return;
    }

    // Isi formulir modal
    document.getElementById('modIdLab').value = currentLabId;
    document.getElementById('modIdReq').value = idPermintaan;
    document.getElementById('modLab').value = currentLabName;
    document.getElementById('labName').innerText = currentLabName;
    
    // Set pilihan barang dan jumlah
    document.getElementById('modBarang').value = idBahan;
    document.getElementsByName('jumlah')[0].value = jmlMinta;

    generateCode(); // Buat kode distribusi otomatis

    var myModal = new bootstrap.Modal(document.getElementById('distModal'));
    myModal.show();
}
</script>

</body>
</html>