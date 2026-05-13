<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin'); // Hanya Admin Utama yang boleh masuk

// --- Logika Pagination & Pencarian ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Membuat Clause WHERE untuk pencarian (Nama atau Username)
$whereClause = "WHERE role = 'admin-acc'";
if ($search != '') {
    $whereClause .= " AND (nama_lengkap LIKE '%$search%' OR username LIKE '%$search%' OR nip LIKE '%$search%')";
}

// Hitung total data untuk pagination
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users $whereClause");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data dengan limit, offset
$query = "SELECT * FROM users $whereClause ORDER BY id_user DESC LIMIT $offset, $limit";
$res = mysqli_query($conn, $query);

// ==============================================
// LOGIKA TAMBAH ADMIN ACC
// ==============================================
if (isset($_POST['tambah_admin_acc'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nip      = mysqli_real_escape_string($conn, $_POST['nip']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role     = 'admin-acc'; 

    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        $alert_status = "error";
        $alert_pesan  = "Username sudah digunakan, silakan pilih username lain.";
    } else {
        $q_insert = "INSERT INTO users (nama_lengkap, nip, username, password, role) 
                     VALUES ('$nama', '$nip', '$username', '$password', '$role')";
        
        if (mysqli_query($conn, $q_insert)) {
            header("Location: kelola_admin.php?status=sukses");
            exit();
        } else {
            $alert_status = "error";
            $alert_pesan  = "Gagal menyimpan data ke database.";
        }
    }
}

// ==============================================
// LOGIKA EDIT ADMIN ACC
// ==============================================
if (isset($_POST['update_admin_acc'])) {
    $id_user  = mysqli_real_escape_string($conn, $_POST['id_user']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nip      = mysqli_real_escape_string($conn, $_POST['nip']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Ambil password mentah

    // Cek duplikasi username (kecuali username diri sendiri)
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND id_user != '$id_user'");
    if (mysqli_num_rows($cek_user) > 0) {
        $alert_status = "error";
        $alert_pesan  = "Username sudah digunakan oleh akun lain.";
    } else {
        // Query Dasar
        $sql_update = "UPDATE users SET nama_lengkap='$nama', nip='$nip', username='$username'";

        // Jika password diisi, enkripsi dan tambahkan ke query
        if (!empty($password)) {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update .= ", password='$hash_password'";
        }

        $sql_update .= " WHERE id_user='$id_user' AND role='admin-acc'";

        if (mysqli_query($conn, $sql_update)) {
            header("Location: kelola_admin.php?status=update_sukses");
            exit();
        } else {
            $alert_status = "error";
            $alert_pesan  = "Gagal mengupdate data database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin ACC | Lab Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root { --navy-deep: #0a192f; --gold-accent: #ffcc00; }
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        
        .header-section { background: linear-gradient(135deg, var(--navy-deep) 0%, #112240 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-gold { background-color: var(--gold-accent); color: var(--navy-deep); font-weight: 700; border: none; }
        .btn-gold:hover { background-color: #e6b800; color: #000; }
        
        .card-table { border-radius: 15px; border: none; overflow: hidden; }
        .thead-navy { background-color: var(--navy-deep); color: white; }
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
    
    <main class="p-3 p-md-4" style="margin-top: 30px;"></main>
    
    <div class="main-content" style="margin-left: 260px; padding: 25px;">
        <?php include "../../includes/header.php"; ?>

        <div class="header-section d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-shield-check me-2 text-warning"></i> Manajemen Admin ACC</h2>
                <p class="mb-0 text-white-50">Kelola kredensial akses untuk staf operasional gudang dan distribusi.</p>
            </div>
            <button class="btn btn-gold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAdmin">
                <i class="bi bi-person-plus-fill me-2"></i>Tambah Admin ACC
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
                        <label class="small fw-bold text-muted">CARI ADMIN</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control border-2" placeholder="Nama, Username, atau NIP..." value="<?= htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit" style="background-color: var(--navy-deep); border: none;"><i class="bi bi-search"></i></button>
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
                                <th>Informasi Admin</th>
                                <th>Username Akses</th>
                                <th>Role</th>
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
                                    <div class="fw-bold text-dark" style="font-size: 1.05rem;"><?= htmlspecialchars($row['nama_lengkap']); ?></div>
                                    <span class="text-muted small"><i class="bi bi-card-text me-1"></i>NIP: <?= !empty($row['nip']) ? htmlspecialchars($row['nip']) : '-'; ?></span>
                                </td>
                                <td>
                                    <code class="text-primary fw-bold fs-6"><?= htmlspecialchars($row['username']); ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-2">Admin ACC</span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-warning border-0" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_user']; ?>">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                        <button class="btn btn-outline-danger border-0" onclick="konfirmasiHapus(<?= $row['id_user']; ?>)">
                                            <i class="bi bi-trash3-fill fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; 
                            else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>Data Admin ACC tidak ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if($total_pages > 1): ?>
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
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="modalTambahAdmin" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Admin ACC Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">NAMA LENGKAP <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control border-2 form-control-lg" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">NIP / NOMOR PEGAWAI <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control border-2 form-control-lg" placeholder="Masukkan NIP" required>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">USERNAME <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control border-2" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-navy-deep">PASSWORD <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="passTambah" class="form-control border-2" required>
                                <span class="input-group-text cursor-pointer border-2" onclick="toggleInputType('passTambah')"><i class="bi bi-eye"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="tambah_admin_acc" class="btn btn-gold w-100 py-3 rounded-3 fs-6">Simpan Admin ACC</button>
                </div>
            </form>
        </div>
    </div>

    <?php 
    mysqli_data_seek($res, 0); // Reset pointer query agar bisa di-looping lagi
    while($row = mysqli_fetch_assoc($res)): 
    ?>
    <div class="modal fade" id="modalEdit<?= $row['id_user']; ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Akun Admin ACC</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">NAMA LENGKAP</label>
                        <input type="text" name="nama_lengkap" class="form-control border-2 form-control-lg" value="<?= htmlspecialchars($row['nama_lengkap']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">NIP / NOMOR PEGAWAI</label>
                        <input type="text" name="nip" class="form-control border-2 form-control-lg" value="<?= htmlspecialchars($row['nip']); ?>" required>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-navy-deep">USERNAME LOGIN</label>
                        <input type="text" name="username" class="form-control border-2" value="<?= htmlspecialchars($row['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-danger">GANTI PASSWORD (KOSONGKAN JIKA TIDAK DIGANTI)</label>
                        <div class="input-group">
                            <input type="password" name="password" id="passEdit<?= $row['id_user']; ?>" class="form-control border-2" placeholder="Masukkan password baru">
                            <span class="input-group-text cursor-pointer border-2" onclick="toggleInputType('passEdit<?= $row['id_user']; ?>')"><i class="bi bi-eye"></i></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_admin_acc" class="btn btn-navy deep px-4 py-2 text-gold fw-bold">Update Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endwhile; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Set warna tema Navy Deep untuk JavaScript tertentu
        const colors = { navy: '#0a192f', gold: '#ffcc00' };

        function toggleInputType(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }

        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Akun?',
                text: "Akses login Admin ACC ini akan dicabut permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: colors.navy,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-4' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../proses/hapus.php?hapus_admin_acc=" + id;
                }
            })
        }

        // Handle error/pesan dari PHP POST Submit (Tambah/Edit)
        <?php if (isset($alert_status)): ?>
            Swal.fire({
                icon: '<?= $alert_status ?>',
                title: '<?= $alert_status == "error" ? "Gagal!" : "Berhasil!" ?>',
                text: '<?= $alert_pesan ?>',
                confirmButtonColor: colors.navy,
                customClass: { popup: 'rounded-4' }
            });
        <?php endif; ?>

        // Handle success dari GET URL params setelah redirect
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status === 'sukses') Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Akun Admin ACC berhasil dibuat.', confirmButtonColor: colors.navy, customClass: { popup: 'rounded-4' } });
        if (status === 'update_sukses') Swal.fire({ icon: 'success', title: 'Diperbarui!', text: 'Data akun berhasil diupdate.', confirmButtonColor: colors.navy, customClass: { popup: 'rounded-4' } });
        if (status === 'hapus_sukses') Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Akun telah dihapus.', confirmButtonColor: colors.navy, customClass: { popup: 'rounded-4' } });
        
        // Membersihkan URL params agar notifikasi tidak muncul lagi saat refresh
        if(status) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>