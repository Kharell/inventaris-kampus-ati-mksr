<?php
// Hubungkan dengan koneksi agar kita punya akses ke BASE_URL
require_once "database.php";

/**
 * Fungsi Pengaman Halaman
 * @param string $required_role 'admin' atau 'kepala_lab'
 */
function checkAccess($required_role) {
    $loginPage = BASE_URL . "login.php";

    // 1. Cek Login
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        session_unset();
        session_destroy();
        header("Location: " . $loginPage . "?pesan=wajib_login");
        exit();
    }

    // 2. Cek Role
    if ($_SESSION['role'] !== $required_role) {
        header("Location: " . $loginPage . "?pesan=hak_akses_ditolak");
        exit();
    }

    // 3. Cek Fingerprint (Anti-Hijacking)
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