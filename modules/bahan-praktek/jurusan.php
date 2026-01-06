<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Jurusan & Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --navy-deep: #0a192f;
            --gold-accent: #ffcc00;
        }
        body { background-color: #f0f2f5; }
        
        /* Sidebar Tab Styling */
        .list-group-item.active {
            background-color: var(--navy-deep) !important;
            border-color: var(--navy-deep) !important;
            color: var(--gold-accent) !important;
            border-left: 5px solid var(--gold-accent) !important;
        }
        
        .list-group-item {
            border: none;
            margin-bottom: 5px;
            border-radius: 8px !important;
            transition: 0.3s;
            cursor: pointer;
        }

        .lab-item {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 12px;
            border: 1px solid #e1e4e8;
            transition: transform 0.2s;
        }
        .lab-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .icon-circle {
            width: 45px;
            height: 45px;
            background: rgba(10, 25, 47, 0.1);
            color: var(--navy-deep);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .btn-gold {
            background-color: var(--gold-accent);
            color: var(--navy-deep);
            font-weight: 700;
            border: none;
        }
        .btn-gold:hover { background-color: #e6b800; color: #000; }
        
        .header-section {
            background: linear-gradient(135deg, var(--navy-deep) 0%, #112240 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Modal Custom Style */
        .modal-content { border-radius: 15px; border: none; }
        .modal-header { background-color: var(--navy-deep); color: white; border-radius: 15px 15px 0 0; }
        .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    </style>
</head>
<body>


    <?php include "../../includes/sidebar.php"; ?>
    
    <div class="main-content" style="margin-left: 260px; padding: 25px;">
        <?php include "../../includes/header.php"; ?>

        <div class="header-section d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-grid-1x2-fill me-2 text-warning"></i> Struktur Kampus</h2>
                <p class="mb-0 text-white-50">Manajemen Pengelompokan Laboratorium berdasarkan Jurusan.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-gold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalJurusan">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Jurusan
                </button>
                <button class="btn btn-outline-light px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalLab">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Lab
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <h6 class="fw-bold text-muted mb-3 px-2">PILIH JURUSAN</h6>
                    <div class="list-group list-group-flush" id="list-tab" role="tablist">
                        <?php
                        $no_j = 0;
                        $qj = mysqli_query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
                        while($rj = mysqli_fetch_assoc($qj)):
                            $active = ($no_j == 0) ? "active" : "";
                        ?>
                        <div class="list-group-item list-group-item-action <?= $active ?> d-flex justify-content-between align-items-center" 
                           id="list-j-<?= $rj['id_jurusan'] ?>-list" 
                           data-bs-toggle="list" 
                           role="tab"
                           href="#list-j-<?= $rj['id_jurusan'] ?>">
                            <span><i class="bi bi-folder2-open me-2"></i> <?= $rj['nama_jurusan'] ?></span>
                            <div class="btn-group">
                                <button class="btn btn-sm text-warning p-0 me-2" data-bs-toggle="modal" data-bs-target="#editJurusan<?= $rj['id_jurusan'] ?>"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm text-danger p-0" onclick="hapusData(<?= $rj['id_jurusan'] ?>, 'jurusan')"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>

                        <div class="modal fade" id="editJurusan<?= $rj['id_jurusan'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="../proses/edit.php" method="POST" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Nama Jurusan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="id_jurusan" value="<?= $rj['id_jurusan'] ?>">
                                        <label class="form-label fw-bold">NAMA JURUSAN</label>
                                        <input type="text" name="nama_jurusan" class="form-control form-control-lg" value="<?= $rj['nama_jurusan'] ?>" required>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="submit" name="update_jurusan" class="btn btn-gold w-100 py-2">Update Jurusan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php $no_j++; endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="tab-content" id="nav-tabContent">
                    <?php
                    $no_c = 0;
                    mysqli_data_seek($qj, 0); 
                    while($rj = mysqli_fetch_assoc($qj)):
                        $active_pane = ($no_c == 0) ? "show active" : "";
                        $id_j = $rj['id_jurusan'];
                        $q_lab = mysqli_query($conn, "SELECT * FROM lab WHERE id_jurusan = '$id_j' ORDER BY nama_lab ASC");
                    ?>
                    <div class="tab-pane fade <?= $active_pane ?>" id="list-j-<?= $rj['id_jurusan'] ?>" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                            <h5 class="fw-bold text-navy-deep">
                                <span class="badge bg-navy-deep text-warning me-2"><?= mysqli_num_rows($q_lab) ?></span>
                                Lab di <?= $rj['nama_jurusan'] ?>
                            </h5>
                        </div>

                        <?php if(mysqli_num_rows($q_lab) > 0): ?>
                            <?php $no_lab = 1; while($rl = mysqli_fetch_assoc($q_lab)): ?>
                            <div class="lab-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle me-3"><?= $no_lab++ ?></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= $rl['nama_lab'] ?></h6>
                                        <small class="text-muted">Database ID: #LAB-<?= $rl['id_lab'] ?></small>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-light rounded-pill px-3 me-1 fw-bold" data-bs-toggle="modal" data-bs-target="#editLab<?= $rl['id_lab'] ?>">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-light rounded-pill px-3 text-danger fw-bold" onclick="hapusData(<?= $rl['id_lab'] ?>, 'lab')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>

                            <div class="modal fade" id="editLab<?= $rl['id_lab'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="../proses/edit.php" method="POST" class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Data Laboratorium</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <input type="hidden" name="id_lab" value="<?= $rl['id_lab'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">PINDAHKAN JURUSAN (OPSIONAL)</label>
                                                <select name="id_jurusan" class="form-select">
                                                    <?php 
                                                    $qj_select = mysqli_query($conn, "SELECT * FROM jurusan");
                                                    while($rj_s = mysqli_fetch_assoc($qj_select)){
                                                        $selected = ($rj_s['id_jurusan'] == $rl['id_jurusan']) ? "selected" : "";
                                                        echo "<option value='".$rj_s['id_jurusan']."' $selected>".$rj_s['nama_jurusan']."</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">NAMA LABORATORIUM</label>
                                                <input type="text" name="nama_lab" class="form-control" value="<?= $rl['nama_lab'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="submit" name="update_lab" class="btn btn-gold w-100 py-2">Update Lab</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                                <i class="bi bi-inbox text-muted display-4"></i>
                                <p class="text-muted mt-2">Belum ada data laboratorium untuk jurusan ini.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php $no_c++; endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalJurusan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="../proses/tambah.php" method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Jurusan Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold">NAMA JURUSAN</label>
                    <input type="text" name="nama_jurusan" class="form-control form-control-lg" placeholder="Contoh: Teknik Elektro" required>
                </div>
                <div class="modal-footer border-0"><button type="submit" name="tambah_jurusan" class="btn btn-gold w-100 py-2">Simpan Jurusan</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalLab" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="../proses/tambah.php" method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Laboratorium Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">PILIH JURUSAN</label>
                        <select name="id_jurusan" class="form-select" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <?php 
                            mysqli_data_seek($qj, 0);
                            while($rj_m = mysqli_fetch_assoc($qj)) echo "<option value='".$rj_m['id_jurusan']."'>".$rj_m['nama_jurusan']."</option>";
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">NAMA LABORATORIUM</label>
                        <input type="text" name="nama_lab" class="form-control" placeholder="Contoh: Lab PLC & Robotik" required>
                    </div>
                </div>
                <div class="modal-footer border-0"><button type="submit" name="tambah_lab" class="btn btn-gold w-100 py-2">Simpan Lab</button></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // HANDLING NOTIFIKASI
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status === 'sukses') Swal.fire('Berhasil!', 'Data telah disimpan.', 'success');
        if (status === 'hapus_sukses') Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
        if (status === 'gagal') Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');

        // FUNGSI HAPUS
        function hapusData(id, tipe) {
            let info = tipe === 'jurusan' ? 'Menghapus jurusan akan menghapus semua lab di bawahnya!' : 'Data lab akan dihapus permanen.';
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: info,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0a192f',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../proses/hapus.php?id=" + id + "&modul=" + tipe;
                }
            })
        }
    </script>
</body>
</html>