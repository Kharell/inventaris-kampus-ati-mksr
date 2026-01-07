<?php
session_start();
include "../../../config/database.php";

if (isset($_POST['update_permintaan'])) {
    $id = $_POST['id_permintaan'];
    $jumlah = $_POST['jumlah_minta'];

    $sql = "UPDATE permintaan_barang SET jumlah_minta = '$jumlah' WHERE id_permintaan = '$id' AND status = 'pending'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['alert'] = 'sukses_update';
        header("Location: ../lab/kebutuhan.php");
    }
}