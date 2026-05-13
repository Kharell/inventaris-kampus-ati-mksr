<?php
require_once "database.php";

function checkAccess($allowed_roles) {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

    $loginPage = BASE_URL . "login.php";

    // 1. Cek Login
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        session_unset();
        session_destroy();
        header("Location: " . $loginPage . "?pesan=wajib_login");
        exit();
    }

    // --- LOGIKA LOGOUT OTOMATIS (SERVER SIDE) ---
    // Pengecualian untuk admin dan admin-acc (Sesi habis hanya berlaku untuk kepala lab)
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'admin-acc') {
        $timeout_duration = 1800; // 30 menit sesi habis
        
        if (isset($_SESSION['last_activity'])) {
            $elapsed_time = time() - $_SESSION['last_activity'];
            if ($elapsed_time >= $timeout_duration) {
                session_unset();
                session_destroy();
                
                // Jika request berasal dari AJAX, kirim header 401
                if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('HTTP/1.1 401 Unauthorized');
                    exit();
                }

                header("Location: " . $loginPage . "?pesan=sesi_habis");
                exit();
            }
        }
        $_SESSION['last_activity'] = time(); 
    }
    // --------------------------------------------

    // 2. Cek Role (Mendukung Array untuk Multirole)
    $user_role = $_SESSION['role'];
    
    if (is_array($allowed_roles)) {
        // Jika parameter berupa array (contoh: ['admin', 'admin-acc'])
        if (!in_array($user_role, $allowed_roles)) {
            header("Location: " . $loginPage . "?pesan=hak_akses_ditolak");
            exit();
        }
    } else {
        // Jika parameter berupa string tunggal (contoh: 'admin')
        if ($user_role !== $allowed_roles) {
            header("Location: " . $loginPage . "?pesan=hak_akses_ditolak");
            exit();
        }
    }

    // 3. Fingerprint Keamanan (Anti Session Hijacking)
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