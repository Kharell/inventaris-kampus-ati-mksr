<?php
session_start();
include "../../../config/database.php";

// Set zona waktu agar waktu terima tercatat akurat
date_default_timezone_set('Asia/Jakarta');

// --- 1. LOGIKA KONFIRMASI (TERIMA & TOLAK) VIA AJAX ---
if (isset($_POST['id'])) {
    $id_distribusi = mysqli_real_escape_string($conn, $_POST['id']);
    
    // CEK APAKAH ADA AKSI TOLAK DARI JAVASCRIPT
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'tolak') {
        // --- LOGIKA TOLAK ---
        $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
        
        $sql = "UPDATE distribusi_lab SET 
                status = 'ditolak', 
                keterangan = '$keterangan' 
                WHERE id_distribusi = '$id_distribusi'";
    } else {
        // --- LOGIKA TERIMA ---
        $tgl_sekarang = date('Y-m-d H:i:s');
        $sql = "UPDATE distribusi_lab SET 
                status = 'diterima', 
                tanggal_distribusi = '$tgl_sekarang',
                keterangan = NULL 
                WHERE id_distribusi = '$id_distribusi'";
    }

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit; 
}

// --- 2. LOGIKA KIRIM PERMINTAAN (VIA FORM) ---
if (isset($_POST['kirim_permintaan'])) {
    $id_kepala    = $_SESSION['id_user'];
    $id_barang    = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $jumlah_minta = mysqli_real_escape_string($conn, $_POST['jumlah_minta']);
    
    $spesifikasi  = strtoupper(mysqli_real_escape_string($conn, trim($_POST['spesifikasi'])));
    $kondisi      = mysqli_real_escape_string($conn, $_POST['kondisi']);

    if (empty($spesifikasi)) { $spesifikasi = "-"; }
    if (empty($kondisi)) { $kondisi = "BAIK"; }

    $sql_tambah = "INSERT INTO permintaan_barang 
                   (id_kepala, id_barang, spesifikasi, jumlah_minta, kondisi, status, tgl_permintaan) 
                   VALUES 
                   ('$id_kepala', '$id_barang', '$spesifikasi', '$jumlah_minta', '$kondisi', 'pending', NOW())";
    
    if (mysqli_query($conn, $sql_tambah)) {
        $_SESSION['alert'] = 'sukses_tambah';
        header("Location: ../lab/kebutuhan.php");
        exit;
    } else {
        die("Gagal Query: " . mysqli_error($conn));
    }
}

// --- 3. LOGIKA LAPOR PEMAKAIAN ---
if (isset($_POST['lapor_pakai'])) {
    $data_input = explode('|', $_POST['id_distribusi']);
    $id_distribusi = $data_input[0];
    $kode_distribusi = $data_input[1];
    
    $jumlah_pakai = $_POST['jumlah_pakai'];
    $id_lab = $_SESSION['id_lab'];

    $res = mysqli_query($conn, "SELECT id_praktek FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'");
    $data = mysqli_fetch_assoc($res);
    $id_praktek = $data['id_praktek'];

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