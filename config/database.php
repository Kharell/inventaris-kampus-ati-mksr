<?php
// Pastikan session tetap aktif
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- KONFIGURASI URL ---
function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_dir = str_replace(basename($script_name), '', $script_name);
    return $protocol . "://" . $host . $base_dir;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseUrl());
}

// --- KONFIGURASI DATABASE ---
// Ubah bagian ini saat pindah ke hosting
$db_host = "localhost";
$db_user = "root";          
$db_pass = "";              
$db_name = "db_inventaris"; 

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>