<?php
// 1. Definisikan alamat dasar proyek
$base_url = "http://localhost/inventaris-kampus/";

// 2. Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_inventaris";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>