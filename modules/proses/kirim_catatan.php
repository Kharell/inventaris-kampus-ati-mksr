<?php
include "../../config/database.php";
<<<<<<< HEAD
checkAccess(['admin', 'admin-acc']);
=======

>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
$id = $_POST['id'] ?? '';
$pesan = $_POST['pesan'] ?? '';

if ($id && $pesan) {
    $pesan_db = mysqli_real_escape_string($conn, $pesan);
    
    // UPDATE kolom balasan_admin, bukan keterangan
    $query = "UPDATE distribusi_lab SET balasan_admin = '$pesan_db' WHERE id_distribusi = '$id'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>