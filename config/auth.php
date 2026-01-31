<?php
// Hubungkan dengan koneksi agar kita punya akses ke BASE_URL
require_once "database.php";

/**
 * Fungsi Pengaman Halaman
 * @param string $required_role 'admin' atau 'kepala_lab'
 */
function checkAccess($required_role) {
    // Pastikan session jalan
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    
    // --- TAMBAHKAN HEADER ANTI-CACHE ---
    // Memaksa browser untuk selalu meminta data baru ke server (bukan dari cache)
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Tanggal di masa lalu
    // ------------------------------------

    $loginPage = BASE_URL . "login.php";

    // 1. Cek Login
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        session_unset();
        session_destroy();
        header("Location: " . $loginPage . "?pesan=wajib_login");
        exit();
    }

    // --- LOGIKA LOGOUT OTOMATIS (SERVER SIDE) ---
    // 900 detik = 15 menit. Gunakan angka kecil (3 atau 10) hanya untuk tes.
    $timeout_duration = 900; 
    
    if (isset($_SESSION['last_activity'])) {
        $elapsed_time = time() - $_SESSION['last_activity'];
        if ($elapsed_time >= $timeout_duration) {
            session_unset();
            session_destroy();
            
            // Cek jika ini request dari fetch/AJAX
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('HTTP/1.1 401 Unauthorized');
                exit();
            }

            header("Location: " . $loginPage . "?pesan=sesi_habis");
            exit();
        }
    }
    
    // Update aktivitas hanya jika bukan request background
    if (!isset($_GET['keep_alive'])) {
        $_SESSION['last_activity'] = time();
    }
    // --------------------------------------------

    // 2. Cek Role
    if ($_SESSION['role'] !== $required_role) {
        header("Location: " . $loginPage . "?pesan=hak_akses_ditolak");
        exit();
    }

    // 3. Fingerprint Keamanan
    $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    if (!isset($_SESSION['secure_fingerprint'])) {
        $_SESSION['secure_fingerprint'] = $fingerprint;
    }

    if ($_SESSION['secure_fingerprint'] !== $fingerprint) {
        session_unset();
        session_destroy();
        header("Location: " . $loginPage . "?pesan=sesi_tidak_aman");
        exit();
    }
}
?>