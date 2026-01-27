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
    <div class="container-fluid h-100"> 
        <div class="row h-100 align-items-center position-relative">
            
            <div class="col-auto d-lg-none position-absolute start-0 ps-3" style="z-index: 1060; top: 15px;">
                <button class="btn d-flex align-items-center shadow-sm" 
                        type="button" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#sidebarOffcanvas"
                        style="background: #001f3f; color: #FFD700; border-radius: 8px; padding: 6px 12px; border: 1px solid #FFD700;">
                    <i class="bi bi-list fs-4 me-1"></i>
                    <span class="fw-bold" style="font-size: 0.8rem;">MENU</span>
                </button>
            </div>

            <div class="col text-center text-lg-start"> 
                <div class="header-left ps-lg-4">
                    <div class="d-none d-lg-block">
                        <h5 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 250px; letter-spacing: -0.5px; line-height: 1.2;">
                            Halo, <span class="text-primary"><?= explode(' ', trim($nama_user))[0]; ?></span>
                        </h5>
                        <small class="text-muted fw-medium" style="font-size: 0.7rem;">
                            <?= $label_welcome; ?>
                        </small>
                    </div>
                    
                    <div class="d-lg-none"> 
                        <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2" style="font-size: 0.85rem; letter-spacing: 0.5px; border: 1px solid rgba(13, 110, 253, 0.2);">
                            SI-INVENTARIS
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-auto pe-3 pe-md-4">
                <div class="dropdown">
                    <div class="d-flex align-items-center dropdown-toggle" role="button" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <div class="text-end me-2 d-none d-md-block">
                            <p class="mb-0 fw-bold text-dark" style="font-size: 0.8rem; line-height: 1;"><?= $nama_user; ?></p>
                            <small class="text-success" style="font-size: 0.6rem;"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Online</small>
                        </div>
                        <div class="avatar-box shadow-sm">
                            <?= strtoupper(substr($nama_user, 0, 1)); ?>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 animate slideIn">
                        <li><h6 class="dropdown-header">Menu Akun</h6></li>
                        <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalKeamanan"><i class="bi bi-shield-lock me-2 text-primary"></i>Keamanan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger fw-bold" href="javascript:void(0)" onclick="prosesLogout()"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
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

@media (max-width: 991.98px) {
        /* Hilangkan tombol lama agar tidak double atau menutupi teks */
        .btn-toggle-mobile { 
            display: none !important; 
        }
        
        /* Tambahkan padding top pada main content agar tidak tertutup header fixed */
        .main-content { 
            margin-left: 0 !important; 
            padding-top: 80px !important; 
        }
    }
</style>

