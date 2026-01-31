<?php
session_start();

// 1. Kosongkan semua variabel sesi
$_SESSION = array();

// 2. Hapus cookie session dari browser (Penting untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan sesi di server
session_destroy();

// 4. Redirect ke halaman login (Gunakan index.php jika itu halaman login utama kamu)
header("Location: index.php?pesan=logout_berhasil");
exit();
?>