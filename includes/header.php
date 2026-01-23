<?php
// Pastikan koneksi database tersedia, contoh: include 'koneksi.php';
// Ambil data dasar dari session
$nama_user = $_SESSION['nama'] ?? 'User Admin';
$role_user = $_SESSION['role'] ?? 'Administrator';
$id_lab_session = $_SESSION['id_lab'] ?? null;
$nama_lab = '';

// JIKA LOGIN SEBAGAI KEPALA LAB, AMBIL NAMA LAB DARI DATABASE
if ($role_user !== 'admin' && $id_lab_session) {
    // Sesuaikan variabel $conn dengan variabel koneksi database Anda
    $query_lab = mysqli_query($conn, "SELECT nama_lab FROM lab WHERE id_lab = '$id_lab_session'");
    if ($data_lab = mysqli_fetch_assoc($query_lab)) {
        $nama_lab = $data_lab['nama_lab'];
    }
}

// Logika Label Role untuk tampilan sub-header
if ($role_user == 'admin') {
    $label_welcome = "Administrator Gudang";
} else {
    // Jika nama_lab tidak kosong, tampilkan nama labnya
    $label_welcome = "Kepala " . ($nama_lab ?: "Laboratorium");
}
?>

<header class="topbar shadow-sm">
    <div class="container-fluid h-100 px-0"> 
        <div class="row h-100 g-0 align-items-center">
            
            <div class="col-auto ps-4"> 
                <div class="header-left">
                    <div class="d-none d-md-block">
                        <h5 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px; line-height: 1.2;">
                            Selamat Datang, <span class="text-primary"><?= $nama_user; ?></span>
                        </h5>
                        <small class="text-muted fw-medium" style="font-size: 0.75rem;">
                            Login sebagai: <span class="text-dark fw-bold"><?= $label_welcome; ?></span>
                        </small>
                    </div>
                    <div class="d-md-none ms-5"> 
                        <small class="fw-bold text-primary">SI-INVENTARIS</small>
                    </div>
                </div>
            </div>

            <div class="col"></div>

            <div class="col-auto pe-4">
                <div class="dropdown">
                    <div class="d-flex align-items-center dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="text-end me-3 d-none d-sm-block">
                            <p class="mb-0 fw-bold text-dark" style="font-size: 0.85rem; line-height: 1;">
                                <?= $nama_user; ?>
                            </p>
                            <small class="text-success fw-bold text-uppercase" style="font-size: 0.6rem;">Online</small>
                        </div>
                        <div class="avatar-box shadow-sm">
                            <?= strtoupper(substr($nama_user, 0, 1)); ?>
                        </div>
                    </div>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate slideIn">
                        <li><h6 class="dropdown-header">Pengaturan Akun</h6></li>
                        <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalKeamanan">
                            <i class="bi bi-shield-lock me-2 text-primary"></i>Keamanan & Password
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger fw-bold" href="javascript:void(0)" onclick="prosesLogout()">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar Aplikasi
                        </a></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</header>

<style>
/* STYLE TETAP DIPERTAHANKAN SESUAI PERMINTAAN */
.topbar {
    position: fixed;
    top: 0;
    right: 0;
    height: 70px;
    background: #ffffff;
    border-bottom: 1px solid #edf2f9;
    z-index: 1040;
    transition: 0.3s;
}

@media (min-width: 992px) {
    .topbar {
        left: 260px; 
        width: calc(100% - 260px);
        margin-left: 0 !important; 
    }
}

@media (max-width: 991.98px) {
    .topbar {
        left: 0;
        width: 100%;
    }
}

.avatar-box {
    width: 40px; height: 40px;
    background: #001f3f; color: #FFD700;
    border: 2px solid #FFD700; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-weight: bold;
}

.dropdown-toggle::after { display: none; }
.animate.slideIn { animation: slideIn 0.2s ease-out; }
@keyframes slideIn {
    from { transform: translateY(10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>