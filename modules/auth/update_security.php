<?php
session_start();
include "../../config/database.php";

if (isset($_POST['update_security'])) {
    $id_session = $_SESSION['id_user'];
    $role        = $_SESSION['role'];
    $username    = mysqli_real_escape_string($conn, $_POST['username']);
    $new_pw      = $_POST['new_password'];
    $conf_pw     = $_POST['confirm_password'];

    // 1. Tentukan Tabel dan Nama Kolom ID berdasarkan Role
    if ($role == 'admin') {
        $table     = 'users';
        $id_column = 'id_user'; 
    } else {
        $table     = 'kepala_lab';
        $id_column = 'id_kepala'; 
    }

    // 2. Jalankan Update Username
    $sql_user = "UPDATE $table SET username = '$username' WHERE $id_column = '$id_session'";
    $query_user = mysqli_query($conn, $sql_user);

    if (!$query_user) {
        $_SESSION['alert'] = "database_error";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // 3. Update Password (Hanya jika diisi)
    if (!empty($new_pw)) {
        if ($new_pw === $conf_pw) {
            $hashed_password = password_hash($new_pw, PASSWORD_DEFAULT);
            $sql_pw = "UPDATE $table SET password = '$hashed_password' WHERE $id_column = '$id_session'";
            mysqli_query($conn, $sql_pw);
        } else {
            // Jika password tidak cocok
            $_SESSION['alert'] = "password_mismatch";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    // Update Session agar nama di header berubah jika username diganti
    $_SESSION['username'] = $username;
    
    // Set notifikasi sukses
    $_SESSION['alert'] = "update_success";
    
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}