<?php
session_start();
include "../../config/database.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user    = $_SESSION['id_user'];
    $role       = $_SESSION['role'];
    $nama_baru  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nip_baru   = mysqli_real_escape_string($conn, $_POST['nip']);
    $pass_input = $_POST['password_konfirmasi'];

    // Cek tabel mana yang dipakai
    $table = ($role === 'admin') ? 'users' : 'kepala_lab';
    $id_col = ($role === 'admin') ? 'id_user' : 'id_kepala';
    $name_col = ($role === 'admin') ? 'nama_lengkap' : 'nama_kepala';

    // Verifikasi password sebelum update
    $cek = mysqli_query($conn, "SELECT password FROM $table WHERE $id_col = '$id_user'");
    $user = mysqli_fetch_assoc($cek);

    if (password_verify($pass_input, $user['password'])) {
        $update = mysqli_query($conn, "UPDATE $table SET $name_col = '$nama_baru', nip = '$nip_baru' WHERE $id_col = '$id_user'");
        if ($update) {
            $_SESSION['nama'] = $nama_baru; // Update session biar nama di header langsung ganti
            echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Password konfirmasi salah!']);
    }
}