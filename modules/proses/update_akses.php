<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);
if (isset($_POST['status'])) {
    $status = (int)$_POST['status'];
    
    // Pastikan nama tabel dan kolom sesuai dengan database Anda
    $query = "UPDATE pengaturan_sistem SET nilai_pengaturan = '$status' WHERE nama_pengaturan = 'status_input_stok'";
    
    if (mysqli_query($conn, $query)) {
        echo "success"; // Ini yang dibaca oleh AJAX
    } else {
        echo "error";
    }
    exit(); // Penting: Hentikan script agar tidak mengirim output lain
}
?>