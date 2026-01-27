<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi URL dan Deteksi Halaman Aktif
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$current_page = $_SERVER['REQUEST_URI']; 
$current_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$parts = explode('/', trim($current_dir, '/'));
$root_folder = isset($parts[0]) ? "/" . $parts[0] . "/" : "/";
$base_url = $protocol . "://" . $host . $root_folder;

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

/**
 * Fungsi pembantu untuk indikator aktif
 * Menggunakan perbandingan path yang lebih spesifik agar tidak bentrok
 */
function isActive($path) {
    global $current_page;
    // Jika current_page mengandung path spesifik yang dikirimkan
    return (strpos($current_page, $path) !== false) ? 'active' : '';
}

function isExpanded($paths) {
    global $current_page;
    foreach ($paths as $path) {
        if (strpos($current_page, $path) !== false) return 'show';
    }
    return '';
}
?>

<style>
    :root { 
        --sidebar-width: 260px;
        --navy-dark: #001f3f;
        --gold-poly: #ffcc00;
        --hover-bg: rgba(255, 204, 0, 0.15);
    }

    .sidebar-wrapper {
        background-color: var(--navy-dark) !important;
        color: white !important;
        border-right: 1px solid rgba(255,255,255,0.1);
    }

    .offcanvas, .offcanvas-lg, .offcanvas-header {
        background-color: var(--navy-dark) !important;
    }

    .btn-toggle-mobile {
        display: none;
        position: fixed;
        top: 15px; left: 15px;
        z-index: 1060;
        background: var(--navy-dark);
        color: var(--gold-poly);
        border: 1px solid var(--gold-poly);
        padding: 5px 12px;
        border-radius: 8px;
    }

    @media (max-width: 991.98px) {
        .btn-toggle-mobile { display: block; }
        .main-content { margin-left: 0 !important; padding-top: 60px; }
    }

    @media (min-width: 992px) {
        .sidebar-wrapper {
            width: var(--sidebar-width) !important;
            height: 100vh !important;
            position: fixed !important;
            left: 0; top: 0;
            z-index: 1050;
            display: flex !important;
            flex-direction: column;
        }
        .main-content, header { 
            margin-left: var(--sidebar-width) !important; 
            width: calc(100% - var(--sidebar-width)) !important;
        }
    }

    .nav-link { 
        color: rgba(255,255,255,0.75) !important; 
        font-size: 0.9rem;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        border-radius: 8px;
        margin: 2px 10px;
        transition: 0.3s;
        text-decoration: none;
    }
    
    .nav-link:hover { 
        color: var(--gold-poly) !important; 
        background: var(--hover-bg); 
    }

    /* MENU UTAMA AKTIF */
    .nav-link.active { 
        color: var(--navy-dark) !important; 
        background-color: var(--gold-poly) !important;
        font-weight: bold;
    }

    /* SUBMENU AKTIF */
    .submenu .nav-link.active {
        background-color: rgba(255, 204, 0, 0.2) !important;
        color: var(--gold-poly) !important;
        border-left: 3px solid var(--gold-poly);
        border-radius: 0 8px 8px 0;
    }

    .btn-toggle-nav {
        justify-content: space-between;
        width: calc(100% - 20px);
        background: none; border: none;
    }

    .submenu {
        list-style: none;
        padding-left: 20px;
        background: rgba(0,0,0,0.2);
        margin: 0 10px;
        border-radius: 0 0 8px 8px;
    }

    .nav-label-modern {
        color: rgba(255,255,255,0.35);
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 15px 25px 5px;
    }

    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>




<button class="btn btn-toggle-mobile d-lg-none shadow" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="bi bi-list"></i> Menu
</button>



<div class="offcanvas-lg offcanvas-start sidebar-wrapper border-0" tabindex="-1" id="sidebarOffcanvas">
    
    <div class="offcanvas-header d-lg-none border-bottom border-white border-opacity-10">
        <h5 class="offcanvas-title text-white">Navigasi Sistem</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarOffcanvas"></button>
    </div>

    <div class="py-4 px-4 text-center">
        <img src="<?= $base_url; ?>images/logo.png" alt="Logo" style="width: 45px;" class="mb-2">
        <h4 class="fw-bold mb-0" style="color: var(--gold-poly); letter-spacing: 1px; font-size: 1rem;">INVENTARIS</h4>
        <small class="text-white-50" style="font-size: 0.65rem;">Politeknik ATI Makassar</small>
    </div>
    
    <div class="flex-grow-1 overflow-auto px-2">
        <div class="nav-label-modern">Menu Utama</div>
        
        <a class="nav-link <?= isActive('views/' . (($role == 'admin') ? 'admin' : 'kepala-lab') . '/index.php'); ?>" 
           href="<?= $base_url; ?>views/<?= ($role == 'admin') ? 'admin' : 'kepala-lab'; ?>/index.php">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>

        <?php if($role == 'admin'): ?>
            <a class="nav-link btn-toggle-nav <?= isExpanded(['atk.php', 'kebersihan.php', 'bahan-praktek.php']) ? '' : 'collapsed'; ?>" 
               data-bs-toggle="collapse" href="#collapseGudang">
                <span><i class="bi bi-box-seam me-2"></i> Gudang</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= isExpanded(['atk.php', 'kebersihan.php', 'bahan-praktek.php']); ?>" id="collapseGudang">
                <ul class="submenu">
                    <li><a class="nav-link <?= isActive('gudang/bahan-praktek.php'); ?>" href="<?= $base_url; ?>modules/gudang/bahan-praktek.php"><i class="bi bi-tools me-2"></i>Bahan Praktek</a></li>
                    <li><a class="nav-link <?= isActive('gudang/atk.php'); ?>" href="<?= $base_url; ?>modules/gudang/atk.php"> <i class="bi bi-pencil-fill me-2"></i>Alat Tulis Kantor</a></li>
                    <li><a class="nav-link <?= isActive('gudang/kebersihan.php'); ?>" href="<?= $base_url; ?>modules/gudang/kebersihan.php"> <i class="bi bi-bucket-fill me-2"></i>Kebersihan</a></li>
                    </ul>
            </div>

            <a class="nav-link btn-toggle-nav <?= isExpanded(['jurusan.php', 'kepala-lab.php']) ? '' : 'collapsed'; ?>" 
               data-bs-toggle="collapse" href="#collapseMaster">
                <span><i class="bi bi-database me-2"></i> Data Master</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= isExpanded(['jurusan.php', 'kepala-lab.php']); ?>" id="collapseMaster">
                <ul class="submenu">
                    <li><a class="nav-link <?= isActive('bahan-praktek/jurusan.php'); ?>" href="<?= $base_url; ?>modules/bahan-praktek/jurusan.php"><i class="bi bi-buildings-fill me-2"></i> Jurusan & Lab</a></li>
                    <li><a class="nav-link <?= isActive('bahan-praktek/kepala-lab.php'); ?>" href="<?= $base_url; ?>modules/bahan-praktek/kepala-lab.php"><i class="bi bi-person-badge-fill me-2"></i> Kepala Lab</a></li>
                </ul>
            </div>


            <a class="nav-link btn-toggle-nav <?= isExpanded(['distribusi/index.php', 'distribusi/kebersihan.php', 'distribusi/atk.php']) ? '' : 'collapsed'; ?>" 
                data-bs-toggle="collapse" href="#collapseDistribusi">
                <span><i class="bi bi-truck me-2"></i> Distribusi Lab</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= isExpanded(['distribusi/index.php', 'distribusi/kebersihan.php', 'distribusi/atk.php']); ?>" id="collapseDistribusi">
                <ul class="submenu">
                    <li>
                        <a class="nav-link <?= isActive('modules/distribusi/index.php'); ?>" href="<?= $base_url; ?>modules/distribusi/index.php">
                            <i class="bi bi-tools me-2"></i> Bahan Praktek
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?= isActive('modules/distribusi/atk.php'); ?>" href="<?= $base_url; ?>modules/distribusi/atk.php">
                            <i class="bi bi-pencil-fill me-2"></i> Alat Tulis Kantor
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?= isActive('modules/distribusi/kebersihan.php'); ?>" href="<?= $base_url; ?>modules/distribusi/kebersihan.php">
                            <i class="bi bi-bucket-fill me-2"></i> Kebersihan
                        </a>
                    </li>
                </ul>
            </div>

            <a class="nav-link btn-toggle-nav <?= isExpanded(['laporan_stok.php', 'laporan_distribusi.php', 'permintaan.php']) ? '' : 'collapsed'; ?>" 
                data-bs-toggle="collapse" href="#collapseLaporan">
                <span><i class="bi bi-file-earmark-text me-2"></i> Laporan</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= isExpanded(['laporan_stok.php', 'laporan_distribusi.php', 'permintaan.php']); ?>" id="collapseLaporan">
                <ul class="submenu">
                    <li><a class="nav-link <?= isActive('laporan/laporan_stok.php'); ?>" href="<?= $base_url; ?>modules/laporan/laporan_stok.php"><i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Stok Gudang</a></li>
                    <li><a class="nav-link <?= isActive('laporan/laporan_distribusi.php'); ?>" href="<?= $base_url; ?>modules/laporan/laporan_distribusi.php"><i class="bi bi-clipboard-data-fill me-2"></i> Distribusi Bahan Praktek</a></li>
                    <li><a class="nav-link <?= isActive('laporan/laporan_pemakaian.php'); ?>" href="<?= $base_url; ?>modules/laporan/laporan_pemakaian.php"><i class="bi bi-clipboard-data-fill me-2"></i> Pemakaian Kepala Lab</a></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if($role == 'kepala_lab' || $role == 'kepala-lab'): ?>
            <div class="nav-label-modern">Aktivitas Lab</div>
            <a class="nav-link <?= isActive('lab/stok.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/stok.php"><i class="bi bi-archive me-2"></i> Stok Lab</a>
            <a class="nav-link <?= isActive('lab/kebutuhan.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/kebutuhan.php"><i class="bi bi-cart-plus me-2"></i> Input Kebutuhan</a>
            <a class="nav-link <?= isActive('lab/konfirmasi.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/konfirmasi.php"><i class="bi bi-check2-square me-2"></i> Konfirmasi</a>
            <a class="nav-link <?= isActive('lab/pemakaian.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/pemakaian.php"><i class="bi bi-clipboard-data me-2"></i> Lapor Pakai</a>
            <a class="nav-link <?= isActive('lab/laporan.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/laporan.php"><i class="bi bi-printer me-2"></i> Cetak Laporan</a>
        <?php endif; ?>
    </div>

    <div class="logout-box p-3">
        <a class="nav-link text-danger fw-bold justify-content-center" href="javascript:void(0)" onclick="prosesLogout()" 
           style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2);">
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
        confirmButtonColor: '#001f3f',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= $base_url; ?>logout.php";
        }
    })
}
</script>