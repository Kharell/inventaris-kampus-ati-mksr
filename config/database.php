<?php
// 1. Definisikan alamat dasar proyek secara OTOMATIS
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Cek apakah sedang di localhost atau di hosting
if ($host == 'localhost') {
    // Jika di localhost, arahkan ke folder projek Anda
    $base_url = $protocol . "://" . $host . "/inventaris-kampus/";
} else {
    // Jika sudah di hosting, dia otomatis mengambil nama domain Anda (misal: https://domainanda.com/)
    $base_url = $protocol . "://" . $host . "/";
}

// 2. Konfigurasi Database (Gunakan variabel jika ingin lebih rapi)
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "db_inventaris";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>