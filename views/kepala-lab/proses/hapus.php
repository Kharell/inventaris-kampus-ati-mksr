<?php
session_start();
include "../../../config/database.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM permintaan_barang WHERE id_permintaan = '$id' AND status = 'pending'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['alert'] = 'sukses_hapus';
        header("Location: ../lab/kebutuhan.php");
    }
}