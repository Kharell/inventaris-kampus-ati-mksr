<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$current_page = $_SERVER['REQUEST_URI']; 

// ==========================================
// AUTO-DETEKSI LINGKUNGAN (LOCAL VS LIVE)
// ==========================================
if ($host === 'localhost' || $host === '127.0.0.1') {
    // ---- SETTINGAN UNTUK LOCALHOST ----
    // Ganti 'nama_folder_project_anda' sesuai dengan nama folder di htdocs
    $folder_local = "inventaris-kampus-ati"; 
    $base_url = $protocol . "://" . $host . "/" . $folder_local . "/";
} else {
    // ---- SETTINGAN UNTUK HOSTING / LIVE ----
    $base_url = $protocol . "://" . $host . "/";
}

// Menangkap role dari session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

function isActive($path) {
    global $current_page;
    return (strpos($current_page, $path) !== false) ? 'active' : '';
}

function isExpandedByFolder($folder_name) {
    global $current_page;
    return (strpos($current_page, '/' . $folder_name . '/') !== false) ? 'show' : '';
}
?>

<style>
    :root { 
        --sidebar-width: 260px;
        --navy-dark: #001f3f;
        --gold-poly: #ffcc00;
        --hover-bg: rgba(255, 204, 0, 0.15);
        --font-fixed: 15px !important; 
    }

    .sidebar-wrapper {
        background-color: var(--navy-dark) !important;
        color: white !important;
        border-right: 1px solid rgba(255,255,255,0.1);
        display: flex;
        flex-direction: column;
    }

    .menu-container { flex-grow: 1; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none; }
    .menu-container::-webkit-scrollbar { display: none; }

    .sidebar-wrapper a, .sidebar-wrapper button, .sidebar-wrapper span, .sidebar-wrapper i, .nav-link, .btn-toggle-nav {
        font-size: var(--font-fixed) !important;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        line-height: 1.2 !important;
        text-transform: none !important;
    }

    .nav-link, .btn-toggle-nav {
        color: rgba(255,255,255,0.8) !important; padding: 12px 15px !important; display: flex !important;
        align-items: center; border-radius: 8px; margin: 4px 10px; transition: background 0.2s ease;
        text-decoration: none !important; background: transparent; border: none; width: calc(100% - 20px); cursor: pointer;
    }

    .nav-link:focus, .btn-toggle-nav:focus, .nav-link:active, .btn-toggle-nav:active { outline: none !important; box-shadow: none !important; background: transparent; }
    .nav-link:hover, .btn-toggle-nav:hover { color: var(--gold-poly) !important; background: var(--hover-bg) !important; }
    .nav-link.active { color: var(--navy-dark) !important; background-color: var(--gold-poly) !important; font-weight: 600 !important; }

    .submenu { list-style: none; padding: 0; margin: 0 10px 5px 10px; background: rgba(0,0,0,0.15); border-radius: 0 0 8px 8px; }
    .submenu .nav-link { margin: 0; padding-left: 45px !important; width: 100%; }
    .submenu .nav-link.active { background: rgba(255, 204, 0, 0.1) !important; color: var(--gold-poly) !important; border-left: 3px solid var(--gold-poly); border-radius: 0 8px 8px 0; }

    .btn-toggle-nav::after {
        display: inline-block; margin-left: auto;
        content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        width: 10px; opacity: 0.5; transition: transform 0.3s;
    }
    .btn-toggle-nav:not(.collapsed)::after { transform: rotate(180deg); }

    .nav-label-modern { color: rgba(255,255,255,0.3); font-size: 11px !important; text-transform: uppercase; letter-spacing: 1px; padding: 20px 25px 8px; font-weight: bold; }

    @media (min-width: 992px) {
        .sidebar-wrapper { width: var(--sidebar-width) !important; height: 100vh !important; position: fixed !important; left: 0; top: 0; z-index: 1050; }
        .main-content { margin-left: var(--sidebar-width) !important; }
    }

    .btn-toggle-mobile { position: fixed; top: 15px; left: 15px; z-index: 1100; background: var(--navy-dark); color: var(--gold-poly); border: 1px solid var(--gold-poly); padding: 8px 12px; border-radius: 8px; }
</style>

<button class="btn btn-toggle-mobile d-lg-none shadow" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="bi bi-list"></i> Menu
</button>

<div class="offcanvas-lg offcanvas-start sidebar-wrapper border-0" tabindex="-1" id="sidebarOffcanvas">
    
    <div class="py-4 px-4 text-center">
        <img src="<?= $base_url; ?>images/logo.png" alt="Logo" style="width: 50px;" class="mb-2">
        <h4 class="fw-bold mb-0" style="color: var(--gold-poly); letter-spacing: 1px; font-size: 16px !important;">INVENTARIS</h4>
        <small class="text-white-50" style="font-size: 10px !important;">Politeknik ATI Makassar</small>
    </div>
    
    <div class="menu-container">
        <div class="nav-label-modern">Menu Utama</div>
        
        <?php 
            $dashboard_folder = 'kepala-lab';
            if($role == 'admin') $dashboard_folder = 'admin';
            if($role == 'admin-acc') $dashboard_folder = 'admin-acc';
        ?>
        <a class="nav-link <?= isActive("views/$dashboard_folder/index.php"); ?>" href="<?= $base_url; ?>views/<?= $dashboard_folder; ?>/index.php">
            <i class="bi bi-speedometer2 me-3"></i> Dashboard
        </a>

        <?php if($role == 'admin' || $role == 'admin-acc'): ?>

            <?php if($role == 'admin'): ?>
            <button class="btn-toggle-nav <?= isExpandedByFolder('bahan-praktek') ? '' : 'collapsed'; ?>" data-bs-toggle="collapse" data-bs-target="#collapseMaster">
                <span><i class="bi bi-database me-3"></i> Data Master</span>
            </button>
            <div class="collapse <?= isExpandedByFolder('bahan-praktek'); ?>" id="collapseMaster">
                <ul class="submenu">
                    <li><a class="nav-link <?= isActive('bahan-praktek/jurusan.php'); ?>" href="<?= $base_url; ?>modules/bahan-praktek/jurusan.php"><i class="bi bi-buildings-fill me-2"></i> Jurusan & Lab</a></li>
                    <li><a class="nav-link <?= isActive('bahan-praktek/kepala-lab.php'); ?>" href="<?= $base_url; ?>modules/bahan-praktek/kepala-lab.php"><i class="bi bi-person-badge-fill me-2"></i> Kepala Lab</a></li>
                    <li><a class="nav-link <?= isActive('bahan-praktek/kelola_admin.php'); ?>" href="<?= $base_url; ?>modules/bahan-praktek/kelola_admin.php"><i class="bi bi-person-lines-fill me-2"></i> Kelola Admin ACC</a></li>
                </ul>
            </div>
            <?php endif; ?>

            <button class="btn-toggle-nav <?= isExpandedByFolder('gudang') ? '' : 'collapsed'; ?>" data-bs-toggle="collapse" data-bs-target="#collapseGudang">
                <span><i class="bi bi-box-seam me-3"></i> Gudang</span>
            </button>
            <div class="collapse <?= isExpandedByFolder('gudang'); ?>" id="collapseGudang">
                <ul class="submenu">
                    <li><a class="nav-link <?= isActive('gudang/bahan-praktek.php'); ?>" href="<?= $base_url; ?>modules/gudang/bahan-praktek.php"><i class="bi bi-tools me-2"></i> Input Bahan Praktek</a></li>
                    <?php if($role == 'admin'): ?>
                    <li><a class="nav-link <?= isActive('gudang/persediaan.php'); ?>" href="<?= $base_url; ?>modules/gudang/persediaan.php"><i class="bi bi-box-seam me-2"></i> Gudang Persediaan</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <button class="btn-toggle-nav <?= isExpandedByFolder('distribusi') ? '' : 'collapsed'; ?>" data-bs-toggle="collapse" data-bs-target="#collapseDistribusi">
                <span><i class="bi bi-truck me-3"></i> Distribusi Lab</span>
            </button>
            <div class="collapse <?= isExpandedByFolder('distribusi'); ?>" id="collapseDistribusi">
                <ul class="submenu">
                    <li><a class="nav-link <?= isActive('distribusi/index.php'); ?>" href="<?= $base_url; ?>modules/distribusi/index.php"><i class="bi bi-card-checklist me-2"></i> Konfirmasi Barang</a></li>
                </ul>
            </div>

            <?php if($role == 'admin'|| $role == 'admin-acc'): ?>
            <button class="btn-toggle-nav <?= isExpandedByFolder('laporan') ? '' : 'collapsed'; ?>" data-bs-toggle="collapse" data-bs-target="#collapseLaporan">
                <span><i class="bi bi-file-earmark-text me-3"></i> Laporan</span>
            </button>
            <div class="collapse <?= isExpandedByFolder('laporan'); ?>" id="collapseLaporan">
                <ul class="submenu">
                    <li><a class="nav-link <?= isActive('laporan/laporan_stok.php'); ?>" href="<?= $base_url; ?>modules/laporan/laporan_stok.php"><i class="bi bi-bar-chart-fill me-2"></i> Stok Gudang</a></li>
                    <li><a class="nav-link <?= isActive('laporan/laporan_distribusi.php'); ?>" href="<?= $base_url; ?>modules/laporan/laporan_distribusi.php"><i class="bi bi-clipboard-data-fill me-2"></i> Distribusi</a></li>
                    <li><a class="nav-link <?= isActive('laporan/laporan_pemakaian.php'); ?>" href="<?= $base_url; ?>modules/laporan/laporan_pemakaian.php"><i class="bi bi-clipboard-check-fill me-2"></i> Pemakaian</a></li>
                    <li><a class="nav-link <?= isActive('laporan/laporan_grafik.php'); ?>" href="<?= $base_url; ?>modules/laporan/laporan_grafik.php"><i class="bi bi-graph-up-arrow me-2"></i> Visualisasi Grafik</a></li>
                </ul>
            </div>
            <?php endif; ?>

        <?php endif; ?>

        <?php if($role == 'kepala_lab' || $role == 'kepala-lab'): ?>
            <div class="nav-label-modern">Aktivitas Lab</div>
            <a class="nav-link <?= isActive('lab/kebutuhan.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/kebutuhan.php"><i class="bi bi-cart-plus me-3"></i> Buat Pembelian</a>
            <a class="nav-link <?= isActive('lab/konfirmasi.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/konfirmasi.php"><i class="bi bi-check2-square me-3"></i> Konfirmasi</a>
            <a class="nav-link <?= isActive('lab/stok.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/stok.php"><i class="bi bi-archive me-3"></i> Stok Lab</a>
            <a class="nav-link <?= isActive('lab/pemakaian.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/pemakaian.php"><i class="bi bi-clipboard-data me-3"></i> Lapor Pakai</a>
            <a class="nav-link <?= isActive('lab/laporan.php'); ?>" href="<?= $base_url; ?>views/kepala-lab/lab/laporan.php"><i class="bi bi-printer me-3"></i> Cetak Laporan</a>
        <?php endif; ?>
    </div>

    <div class="p-3 border-top border-white border-opacity-10">
        <a class="nav-link text-danger fw-bold justify-content-center" href="javascript:void(0)" onclick="prosesLogout()" 
           style="background: rgba(220, 53, 69, 0.1) !important; border: 1px solid rgba(220, 53, 69, 0.2) !important; margin: 0; width: 100%; color: #ff6b6b !important;">
            <i class="bi bi-box-arrow-right me-2"></i> Keluar Sistem
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function trapBack() {
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function() {
        window.history.pushState(null, null, window.location.href);
        window.history.forward();
    };
}
trapBack();

function prosesLogout() {
    if (typeof Swal === 'undefined') {
        if(confirm("Sesi anda akan diakhiri dari sistem?")) window.location.replace("<?= $base_url; ?>logout.php");
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Keluar',
        text: "Sesi anda akan diakhiri dari sistem",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#001f3f',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.replace("<?= $base_url; ?>logout.php");
        }
    });
}

<?php if ($role !== 'admin' && $role !== 'admin-acc'): ?>
(function() {
    const maxInactivityTime = 1800000;
    let logoutTimer;
    let lastKeepAlive = 0;

    function forceLogout() {
        window.history.pushState(null, null, '<?= $base_url; ?>login.php');
        window.location.replace("<?= $base_url; ?>login.php?pesan=sesi_habis");
        setTimeout(function() { window.history.forward(); }, 0);
    }

    function perbaruiSesiServer() {
        fetch("<?= $base_url; ?>config/refresh_session.php")
        .then(response => { if (response.status === 401) forceLogout(); })
        .catch(err => console.log("Koneksi terputus"));
    }

    function resetTimer() {
        clearTimeout(logoutTimer);
        logoutTimer = setTimeout(forceLogout, maxInactivityTime);

        let now = Date.now();
        if (now - lastKeepAlive > 300000) { 
            perbaruiSesiServer();
            lastKeepAlive = now;
        }
    }

    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    events.forEach(function(name) { document.addEventListener(name, resetTimer, true); });

    resetTimer();
})();
<?php endif; ?>
</script>