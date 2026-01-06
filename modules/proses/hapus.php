<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// ==========================================
// 1. LOGIKA HAPUS ATK
// ==========================================
if (isset($_GET['id']) && $_GET['modul'] == 'atk') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "DELETE FROM barang WHERE id_barang = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/atk.php?status=hapus_sukses");
    } else {
        header("Location: ../gudang/atk.php?status=gagal");
    }
    exit();
}

// ==========================================
// 2. LOGIKA HAPUS KEBERSIHAN
// ==========================================
if (isset($_GET['id']) && $_GET['modul'] == 'kebersihan') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "DELETE FROM barang WHERE id_barang = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/kebersihan.php?status=hapus_sukses");
    } else {
        header("Location: ../gudang/kebersihan.php?status=gagal");
    }
    exit();
}

// ==========================================
// 3. LOGIKA HAPUS BAHAN PRAKTEK (PUSAT)
// ==========================================
if (isset($_GET['id']) && $_GET['modul'] == 'praktek_pusat') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "DELETE FROM bahan_praktek WHERE id_praktek = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/bahan-praktek.php?status=hapus_sukses");
    } else {
        header("Location: ../gudang/bahan-praktek.php?status=gagal");
    }
    exit();
}

// ==========================================
// 4. LOGIKA HAPUS JURUSAN, LAB, KEPALA
// ==========================================
if (isset($_GET['id']) && isset($_GET['modul'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $modul = $_GET['modul'];
    $redirect = "../../index.php"; // Default jika modul tidak dikenal

    if ($modul == 'jurusan') {
        $query = "DELETE FROM jurusan WHERE id_jurusan = '$id'";
        $redirect = "../bahan-praktek/jurusan.php";
    } elseif ($modul == 'lab') {
        $query = "DELETE FROM lab WHERE id_lab = '$id'";
        $redirect = "../bahan-praktek/jurusan.php";
    } elseif ($modul == 'kepala') {
        $query = "DELETE FROM kepala_lab WHERE id_kepala = '$id'";
        $redirect = "../bahan-praktek/kepala-lab.php";
    }

    if (isset($query)) {
        if (mysqli_query($conn, $query)) {
            header("Location: $redirect?status=hapus_sukses");
        } else {
            header("Location: $redirect?status=gagal");
        }
        exit();
    }
}

// ==========================================
// 5. LOGIKA HAPUS DISTRIBUSI (BATAL KIRIM)
// ==========================================
if (isset($_GET['hapus_distribusi'])) {
    $id_distribusi = mysqli_real_escape_string($conn, $_GET['hapus_distribusi']);

    // Ambil data sebelum hapus untuk kembalikan stok
    $query_data = mysqli_query($conn, "SELECT id_praktek, jumlah FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'");
    
    if (mysqli_num_rows($query_data) > 0) {
        $data = mysqli_fetch_assoc($query_data);
        $id_praktek = $data['id_praktek'];
        $jumlah_kembali = $data['jumlah'];

        // Update stok gudang pusat
        mysqli_query($conn, "UPDATE bahan_praktek SET stok = stok + $jumlah_kembali WHERE id_praktek = '$id_praktek'");

        // Hapus data distribusi
        if (mysqli_query($conn, "DELETE FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'")) {
            header("Location: ../../modules/distribusi/index.php?status=hapus_sukses");
        } else {
            header("Location: ../../modules/distribusi/index.php?status=gagal");
        }
    } else {
        header("Location: ../../modules/distribusi/index.php?status=gagal");
    }
    exit();
}

// JIKA TIDAK ADA PARAMETER YANG COCOK
header("Location: ../../index.php");
exit();
?>