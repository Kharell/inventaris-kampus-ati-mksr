<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// --- Logika Pagination & Pencarian ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Membuat Clause WHERE untuk pencarian (Nama, NIP, atau Nama Lab)
$whereClause = "";
if ($search != '') {
    $whereClause = "WHERE kl.nama_kepala LIKE '%$search%' 
                    OR kl.nip LIKE '%$search%' 
                    OR l.nama_lab LIKE '%$search%'";
}

// Hitung total data untuk pagination
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM kepala_lab kl JOIN lab l ON kl.id_lab = l.id_lab $whereClause");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data dengan limit, offset, dan join
$query = "SELECT kl.*, l.nama_lab, j.nama_jurusan 
          FROM kepala_lab kl 
          JOIN lab l ON kl.id_lab = l.id_lab 
          JOIN jurusan j ON l.id_jurusan = j.id_jurusan 
          $whereClause
          ORDER BY kl.id_kepala DESC 
          LIMIT $offset, $limit";
$res = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Kepala Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root { --navy-deep: #0a192f; --gold-accent: #ffcc00; }
        body { background-color: #f0f2f5; }
        .header-section { background: linear-gradient(135deg, var(--navy-deep) 0%, #112240 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-gold { background-color: var(--gold-accent); color: var(--navy-deep); font-weight: 700; border: none; }
        .btn-gold:hover { background-color: #e6b800; color: #000; }
        .card-table { border-radius: 15px; border: none; overflow: hidden; }
        .thead-navy { background-color: var(--navy-deep); color: white; }
        .badge-lab { background-color: rgba(10, 25, 47, 0.1); color: var(--navy-deep); border: 1px solid var(--navy-deep); }
        .cursor-pointer { cursor: pointer; }
        .modal-content { border-radius: 15px; border: none; }
        .modal-header { background-color: var(--navy-deep); color: white; border-radius: 15px 15px 0 0; }
        .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        /* Pagination style */
        .pagination .page-link { color: var(--navy-deep); border: none; margin: 0 2px; border-radius: 5px; }
        .pagination .active .page-link { background-color: var(--navy-deep); color: white; }
    </style>
</head>
<body>
    <?php include "../../includes/sidebar.php"; ?>
    
    <div class="main-content" style="margin-left: 260px; padding: 25px;">
        <?php include "../../includes/header.php"; ?>

        <div class="header-section d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-person-badge-fill me-2 text-warning"></i> Manajemen Kepala Lab</h2>
                <p class="mb-0 text-white-50">Kelola pimpinan unit laboratorium dan kredensial akses mereka.</p>
            </div>
            <button class="btn btn-gold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-person-plus-fill me-2"></i>Tambah Kepala Lab
            </button>
        </div>

        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted">TAMPILKAN</label>
                        <select name="limit" class="form-select border-2" onchange="this.form.submit()">
                            <option value="10" <?= $limit == 10 ? 'selected' : ''; ?>>10 Data</option>
                            <option value="25" <?= $limit == 25 ? 'selected' : ''; ?>>25 Data</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : ''; ?>>50 Data</option>
                        </select>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">CARI KEPALA LAB / UNIT</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-2" placeholder="Nama, NIP, atau Lab..." value="<?= htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-table shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="thead-navy">
                            <tr>
                                <th class="ps-4 py-3">No</th>
                                <th>Informasi Kepala</th>
                                <th>Unit Kerja</th>
                                <th>Akun Akses</th>
                                <th>Kontak</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php
                            $no = $offset + 1;
                            if(mysqli_num_rows($res) > 0):
                                while($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold"><?= $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 1.05rem;"><?= $row['nama_kepala']; ?></div>
                                    <span class="text-muted small"><i class="bi bi-card-text me-1"></i>NIP: <?= !empty($row['nip']) ? $row['nip'] : '-'; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-lab px-3 py-2 rounded-pill mb-1"><?= $row['nama_lab']; ?></span>
                                    <div class="small text-muted ps-2"><i class="bi bi-building me-1"></i><?= $row['nama_jurusan']; ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <!-- <small class="text-muted mb-1">User: <code class="text-primary fw-bold"><?= $row['username']; ?></code></small> -->
                                        <small class="text-muted mb-1">Username: <code class="text-primary fw-bold">Dikunci</code></small>
                                        <div class="input-group input-group-sm" style="width: 140px;">
                                            <input type="password" class="form-control border-0 bg-light" id="listPw<?= $row['id_kepala']; ?>" value="<?= $row['password_plain']; ?>" readonly>
                                            <span class="input-group-text bg-light border-0 cursor-pointer" onclick="toggleViewPw(<?= $row['id_kepala']; ?>)">
                                                <i class="bi bi-eye-fill text-navy-deep" id="iconPw<?= $row['id_kepala']; ?>"></i>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(!empty($row['kontak'])): ?>
                                        <a href="https://wa.me/<?= $row['kontak']; ?>" target="_blank" class="text-decoration-none text-success fw-semibold">
                                            <i class="bi bi-whatsapp me-1"></i><?= $row['kontak']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-warning border-0" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_kepala']; ?>">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                        <button class="btn btn-outline-danger border-0" onclick="konfirmasiHapus(<?= $row['id_kepala']; ?>)">
                                            <i class="bi bi-trash3-fill fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; 
                            else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Data kepala lab tidak ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                <nav class="d-flex justify-content-between align-items-center">
                    <p class="text-muted small mb-0">Menampilkan <?= mysqli_num_rows($res); ?> dari <?= $total_data; ?> data</p>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?halaman=<?= $i; ?>&limit=<?= $limit; ?>&search=<?= $search; ?>"><?= $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="../proses/tambah.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kepala Lab Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">UNIT LABORATORIUM</label>
                        <select name="id_lab" class="form-select border-2" required>
                            <option value="">-- Pilih Lab --</option>
                            <?php 
                            $qlab = mysqli_query($conn, "SELECT l.id_lab, l.nama_lab, j.nama_jurusan FROM lab l JOIN jurusan j ON l.id_jurusan = j.id_jurusan ORDER BY j.nama_jurusan ASC");
                            while($rlab = mysqli_fetch_assoc($qlab)) echo "<option value='".$rlab['id_lab']."'>".$rlab['nama_jurusan']." - ".$rlab['nama_lab']."</option>";
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">NAMA LENGKAP & GELAR</label>
                        <input type="text" name="nama_kepala" class="form-control border-2" placeholder="Contoh: Dr. Ahmad, M.T." required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">USERNAME</label>
                            <input type="text" name="username" class="form-control border-2" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">PASSWORD</label>
                            <div class="input-group">
                                <input type="password" name="password" id="passTambah" class="form-control border-2" required>
                                <span class="input-group-text cursor-pointer border-2" onclick="toggleInputType('passTambah')"><i class="bi bi-eye"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">NIP</label>
                            <input type="text" name="nip" class="form-control border-2">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">KONTAK / WA</label>
                            <input type="text" name="kontak" class="form-control border-2" placeholder="08xxxxxxxx">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="tambah_kepala" class="btn btn-gold w-100 py-2">Simpan Kepala Lab</button>
                </div>
            </form>
        </div>
    </div>

    <?php
    mysqli_data_seek($res, 0);
    while($row = mysqli_fetch_assoc($res)): ?>
    <div class="modal fade" id="modalEdit<?= $row['id_kepala']; ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="../proses/edit.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Update Data Kepala Lab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_kepala" value="<?= $row['id_kepala']; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">NAMA LENGKAP</label>
                        <input type="text" name="nama_kepala" class="form-control border-2" value="<?= $row['nama_kepala']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">USERNAME</label>
                        <input type="text" name="username" class="form-control border-2" value="<?= $row['username']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-danger">GANTI PASSWORD (KOSONGKAN JIKA TIDAK)</label>
                        <div class="input-group">
                            <input type="password" name="password" id="passEdit<?= $row['id_kepala']; ?>" class="form-control border-2">
                            <span class="input-group-text cursor-pointer border-2" onclick="toggleInputType('passEdit<?= $row['id_kepala']; ?>')"><i class="bi bi-eye"></i></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">NIP</label>
                            <input type="text" name="nip" class="form-control border-2" value="<?= $row['nip']; ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">KONTAK</label>
                            <input type="text" name="kontak" class="form-control border-2" value="<?= $row['kontak']; ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="update_kepala" class="btn btn-gold w-100 py-2 text-dark">Update Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endwhile; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function toggleViewPw(id) {
            const input = document.getElementById('listPw' + id);
            const icon = document.getElementById('iconPw' + id);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
            } else {
                input.type = "password";
                icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
            }
        }

        function toggleInputType(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }

        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Data?',
                text: "Akses login kepala lab ini akan dicabut permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0a192f',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../proses/hapus.php?id=" + id + "&modul=kepala";
                }
            })
        }

        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status === 'sukses') Swal.fire('Berhasil!', 'Data kepala lab disimpan.', 'success');
        if (status === 'update_sukses') Swal.fire('Berhasil!', 'Data telah diperbarui.', 'success');
        if (status === 'hapus_sukses') Swal.fire('Terhapus!', 'Data telah dihapus.', 'success');
        if (status === 'gagal') Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
    </script>
</body>
</html>