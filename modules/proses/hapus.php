<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// ==========================================
// 1 & 2. LOGIKA HAPUS BARANG (ATK/KEBERSIHAN)
// ==========================================
if (isset($_GET['id']) && ($_GET['modul'] == 'atk' || $_GET['modul'] == 'kebersihan')) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $modul = $_GET['modul'];
    $page = ($modul == 'atk') ? 'atk.php' : 'kebersihan.php';

    // Cek apakah barang ini sedang digunakan di tabel permintaan atau distribusi
    $cek_distribusi = mysqli_query($conn, "SELECT id_barang FROM distribusi WHERE id_barang = '$id'");
    
    if (mysqli_num_rows($cek_distribusi) > 0) {
        header("Location: ../gudang/$page?status=gagal_relasi");
    } else {
        $query = "DELETE FROM barang WHERE id_barang = '$id'";
        if (mysqli_query($conn, $query)) {
            header("Location: ../gudang/$page?status=hapus_sukses");
        } else {
            header("Location: ../gudang/$page?status=gagal");
        }
    }
    exit();
}

// ==========================================
// 3. LOGIKA HAPUS BAHAN PRAKTEK (PUSAT)
// ==========================================
if (isset($_GET['id']) && $_GET['modul'] == 'praktek_pusat') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Cek apakah bahan ini sudah didistribusikan ke Lab
    $cek_relasi = mysqli_query($conn, "SELECT id_praktek FROM distribusi_lab WHERE id_praktek = '$id'");
    
    if (mysqli_num_rows($cek_relasi) > 0) {
        header("Location: ../gudang/bahan-praktek.php?status=gagal_relasi");
    } else {
        $query = "DELETE FROM bahan_praktek WHERE id_praktek = '$id'";
        if (mysqli_query($conn, $query)) {
            header("Location: ../gudang/bahan-praktek.php?status=hapus_sukses");
        } else {
            header("Location: ../gudang/bahan-praktek.php?status=gagal");
        }
    }
    exit();
}

// ==========================================
// 4. LOGIKA HAPUS JURUSAN, LAB, KEPALA (DIPERBAIKI)
// ==========================================
if (isset($_GET['id']) && isset($_GET['modul'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $modul = $_GET['modul'];
    $redirect = "../../index.php";

    if ($modul == 'jurusan') {
        $redirect = "../bahan-praktek/jurusan.php";
        // CEK: Apakah ada Lab di bawah jurusan ini?
        $cek = mysqli_query($conn, "SELECT id_lab FROM lab WHERE id_jurusan = '$id'");
        if (mysqli_num_rows($cek) > 0) {
            header("Location: $redirect?status=gagal_relasi");
            exit();
        }
        $query = "DELETE FROM jurusan WHERE id_jurusan = '$id'";

    } elseif ($modul == 'lab') {
        $redirect = "../bahan-praktek/jurusan.php";
        // CEK: Apakah ada Kepala Lab atau distribusi barang ke Lab ini?
        $cek_kepala = mysqli_query($conn, "SELECT id_kepala FROM kepala_lab WHERE id_lab = '$id'");
        $cek_dist = mysqli_query($conn, "SELECT id_distribusi FROM distribusi_lab WHERE id_lab = '$id'");
        
        if (mysqli_num_rows($cek_kepala) > 0 || mysqli_num_rows($cek_dist) > 0) {
            header("Location: $redirect?status=gagal_relasi");
            exit();
        }
        $query = "DELETE FROM lab WHERE id_lab = '$id'";

    } elseif ($modul == 'kepala') {
        $redirect = "../bahan-praktek/kepala-lab.php";
        // Kepala lab bisa langsung dihapus, atau cek tabel permintaan_barang
        $cek_permintaan = mysqli_query($conn, "SELECT id_permintaan FROM permintaan_barang WHERE id_kepala = '$id'");
        if (mysqli_num_rows($cek_permintaan) > 0) {
            header("Location: $redirect?status=gagal_relasi");
            exit();
        }
        $query = "DELETE FROM kepala_lab WHERE id_kepala = '$id'";
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
    $query_data = mysqli_query($conn, "SELECT id_praktek, jumlah, status FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'");
    
    if (mysqli_num_rows($query_data) > 0) {
        $data = mysqli_fetch_assoc($query_data);
        
        // Hanya bisa dihapus/dibatalkan jika status belum 'diterima'
        if ($data['status'] == 'diterima') {
            header("Location: ../../modules/distribusi/index.php?status=gagal_diterima");
            exit();
        }

        $id_praktek = $data['id_praktek'];
        $jumlah_kembali = $data['jumlah'];
        mysqli_query($conn, "UPDATE bahan_praktek SET stok = stok + $jumlah_kembali WHERE id_praktek = '$id_praktek'");

        if (mysqli_query($conn, "DELETE FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'")) {
            header("Location: ../../modules/distribusi/index.php?status=hapus_sukses");
        } else {
            header("Location: ../../modules/distribusi/index.php?status=gagal");
        }
    }
    exit();
}

header("Location: ../../index.php");
exit();
?>