<?php
session_start();
include "../../../config/database.php";

// Set zona waktu agar waktu terima tercatat akurat
date_default_timezone_set('Asia/Jakarta');

// --- 1. LOGIKA KONFIRMASI TERIMA (VIA AJAX) ---
if (isset($_POST['id'])) {
    $id_distribusi = mysqli_real_escape_string($conn, $_POST['id']);
    $tgl_sekarang = date('Y-m-d H:i:s'); // Variabel waktu sekarang

    // Query diperbaiki sesuai nama kolom database Anda: tanggal_distribusi
    $sql_konfirmasi = "UPDATE distribusi_lab SET 
                       status = 'diterima', 
                       tanggal_distribusi = '$tgl_sekarang' 
                       WHERE id_distribusi = '$id_distribusi'";

    if (mysqli_query($conn, $sql_konfirmasi)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit; 
}

// --- 2. LOGIKA KIRIM PERMINTAAN (VIA FORM) ---
if (isset($_POST['kirim_permintaan'])) {
    $id_kepala = $_SESSION['id_user'];
    $id_barang = $_POST['id_barang'];
    $jumlah_minta = $_POST['jumlah_minta'];

    $sql_tambah = "INSERT INTO permintaan_barang (id_kepala, id_barang, jumlah_minta, status) 
                   VALUES ('$id_kepala', '$id_barang', '$jumlah_minta', 'pending')";
    
    if (mysqli_query($conn, $sql_tambah)) {
        $_SESSION['alert'] = 'sukses_tambah';
        header("Location: ../lab/kebutuhan.php");
        exit;
    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
}

// --- 3. LOGIKA LAPOR PEMAKAIAN ---
// --- SIMPAN PEMAKAIAN ---
if (isset($_POST['lapor_pakai'])) {
    // Memecah value id_distribusi dan kode_distribusi dari dropdown
    $data_input = explode('|', $_POST['id_distribusi']);
    $id_distribusi = $data_input[0];
    $kode_distribusi = $data_input[1];
    
    $jumlah_pakai = $_POST['jumlah_pakai'];
    $id_lab = $_SESSION['id_lab'];

    // Ambil id_praktek otomatis
    $res = mysqli_query($conn, "SELECT id_praktek FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'");
    $data = mysqli_fetch_assoc($res);
    $id_praktek = $data['id_praktek'];

    // Simpan ke pemakaian_lab termasuk kolom kode_distribusi
    $sql = "INSERT INTO pemakaian_lab (id_distribusi, kode_distribusi, id_praktek, id_lab, jumlah_pakai) 
            VALUES ('$id_distribusi', '$kode_distribusi', '$id_praktek', '$id_lab', '$jumlah_pakai')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['alert'] = 'sukses_pakai';
        header("Location: ../lab/pemakaian.php");
        exit;
    }
}
// --- HAPUS PEMAKAIAN ---
if (isset($_GET['hapus_pakai'])) {
    $id = $_GET['hapus_pakai'];
    if (mysqli_query($conn, "DELETE FROM pemakaian_lab WHERE id_pemakaian = '$id'")) {
        $_SESSION['alert'] = 'sukses_hapus';
        header("Location: ../lab/pemakaian.php");
        exit;
    }
}
?>