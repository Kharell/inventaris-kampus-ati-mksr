<?php
// Pastikan session sudah dimulai sebelum mengakses $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PROTEKSI: Otomatis mendeteksi URL Dasar Project
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Mengambil folder root project secara dinamis
$current_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$parts = explode('/', trim($current_dir, '/'));
$root_folder = isset($parts[0]) ? "/" . $parts[0] . "/" : "/";
$base_url = $protocol . "://" . $host . $root_folder;

// Default role jika session kosong
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>

<div class="sidebar d-flex flex-column p-0"> 
    <div class="py-4 px-4 text-center">
        <img src="<?= $base_url; ?>images/logo.png" alt="Logo" style="width: 50px;" class="mb-2">
        <h4 class="fw-bold mb-0" style="color: #ffcc00; letter-spacing: 1px; font-size: 1.2rem;">INVENTARIS</h4>
        <small class="text-white-50 small">Politeknik ATI Makassar</small>
    </div>
    
    <hr class="mx-4 opacity-25 my-1" style="background-color: #ffcc00;"> 
    
    <ul class="nav flex-column mb-auto px-2">
        <li class="nav-item">
            <a class="nav-link py-2" href="<?= $base_url; ?>views/<?= ($role == 'admin') ? 'admin' : 'kepala-lab'; ?>/index.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        
        <?php if($role == 'admin'): ?>
            <div class="nav-label mt-3 mb-1 px-3">Manajemen Gudang</div>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/gudang/atk.php"><i class="bi bi-pencil-square me-2"></i> ATK</a></li>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/gudang/kebersihan.php"><i class="bi bi-trash3 me-2"></i> Kebersihan</a></li>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/gudang/bahan-praktek.php"><i class="bi bi-box-seam me-2"></i> Bahan Praktek</a></li>

            <div class="nav-label mt-3 mb-1 px-3">Data Master</div>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/bahan-praktek/jurusan.php"><i class="bi bi-mortarboard me-2"></i> Jurusan & Lab</a></li>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/bahan-praktek/kepala-lab.php"><i class="bi bi-person-badge me-2"></i> Kepala Lab</a></li>
            
            <div class="nav-label mt-3 mb-1 px-3">Akademik</div>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/distribusi/index.php"><i class="bi bi-truck me-2"></i> Distribusi Lab</a></li>
        <?php endif; ?>

        <?php if($role == 'kepala_lab'): ?>
            <div class="nav-label mt-3 mb-1 px-3">Aktivitas Laboratorium</div>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/lab/konfirmasi.php"><i class="bi bi-box-arrow-in-down me-2"></i> Konfirmasi Masuk</a></li>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/lab/pemakaian.php"><i class="bi bi-clipboard-data me-2"></i> Lapor Pemakaian</a></li>
            <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/lab/stok.php"><i class="bi bi-archive me-2"></i> Stok Lab Saya</a></li>
        <?php endif; ?>

        <div class="nav-label mt-3 mb-1 px-3">Analitik</div>
        <li class="nav-item"><a class="nav-link py-1" href="<?= $base_url; ?>modules/laporan/index.php"><i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan Pusat</a></li>
    </ul>

    <div class="logout-box mt-auto p-3 border-top border-white border-opacity-10">
        <a class="nav-link py-2 text-danger fw-bold d-flex align-items-center justify-content-center" 
           href="javascript:void(0)" onclick="prosesLogout()" 
           style="background: rgba(220, 53, 69, 0.1); border-radius: 10px;">
            <i class="bi bi-box-arrow-right me-2"></i> Keluar Sistem
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function prosesLogout() {
    Swal.fire({
        title: 'Konfirmasi Keluar',
        text: "Sesi anda akan diakhiri dari sistem Politeknik ATI Makassar",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#001f3f', // Navy
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Menutup sesi aman... Mohon tunggu.',
                icon: 'success',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: () => { Swal.showLoading() }
            }).then(() => {
                // DIARAHKAN KE LOGOUT.PHP (BUKAN LOGIN.PHP) agar session hancur
                window.location.href = "<?= $base_url; ?>logout.php";
            });
        }
    })
}
</script>