<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

// ==============================================================
// 1. LOGIKA HAPUS DATA ADMIN ACC (Format: JSON / AJAX)
// ==============================================================
if (isset($_GET['id']) && isset($_GET['modul']) && $_GET['modul'] == 'admin_acc') {
    // Beritahu browser bahwa ini adalah respon JSON
    header('Content-Type: application/json');
    
    // Ambil ID dari parameter ?id=
    $id_user = mysqli_real_escape_string($conn, $_GET['id']);
    
    // PERBAIKAN: Ubah target hapus ke tabel 'users' dan kolom 'id_user'
    // Tambahkan kondisi AND role = 'admin-acc' untuk keamanan ganda
    $query = "DELETE FROM users WHERE id_user = '$id_user' AND role = 'admin-acc'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Akun Admin ACC berhasil dihapus permanen.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Gagal menghapus akun: ' . mysqli_error($conn)
        ]);
    }
    
    exit(); // Wajib agar tidak tercampur dengan blok di bawahnya
}

// ==============================================================
// 2. LOGIKA HAPUS BAHAN PRAKTEK PUSAT (Format: JSON / AJAX)
// ==============================================================
if (isset($_GET['id']) && isset($_GET['modul']) && $_GET['modul'] == 'praktek_pusat') {
    header('Content-Type: application/json');
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Cek Relasi
    $cek_relasi = mysqli_query($conn, "SELECT id_praktek FROM distribusi_lab WHERE id_praktek = '$id'");
    
    if (mysqli_num_rows($cek_relasi) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak bisa dihapus karena sudah didistribusikan ke Laboratorium!']);
    } else {
        $query = "DELETE FROM bahan_praktek WHERE id_praktek = '$id'";
        if (mysqli_query($conn, $query)) {
            echo json_encode(['status' => 'success', 'message' => 'Data bahan praktek berhasil dihapus permanen.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data dari database.']);
        }
    }
    exit();
}

// ==============================================================
// 3. LOGIKA HAPUS BARANG ATK/KEBERSIHAN (Format: Redirect URL)
// ==============================================================
if (isset($_GET['id']) && isset($_GET['modul']) && ($_GET['modul'] == 'atk' || $_GET['modul'] == 'kebersihan')) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $modul = $_GET['modul'];
    $page = ($modul == 'atk') ? 'atk.php' : 'kebersihan.php';

    $cek_distribusi = mysqli_query($conn, "SELECT id_barang FROM distribusi WHERE id_barang = '$id'");
    if (mysqli_num_rows($cek_distribusi) > 0) {
        header("Location: ../gudang/$page?status=gagal_relasi");
    } else {
        if (mysqli_query($conn, "DELETE FROM barang WHERE id_barang = '$id'")) {
            header("Location: ../gudang/$page?status=hapus_sukses");
        } else {
            header("Location: ../gudang/$page?status=gagal");
        }
    }
    exit();
}

// ==============================================================
// 4. LOGIKA HAPUS JURUSAN, LAB, KEPALA (Format: Redirect URL)
// ==============================================================
if (isset($_GET['id']) && isset($_GET['modul']) && in_array($_GET['modul'], ['jurusan', 'lab', 'kepala'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $modul = $_GET['modul'];
    $redirect = "../../index.php";
    $query = "";

    if ($modul == 'jurusan') {
        $redirect = "../bahan-praktek/jurusan.php";
        $cek = mysqli_query($conn, "SELECT id_lab FROM lab WHERE id_jurusan = '$id'");
        if (mysqli_num_rows($cek) > 0) {
            header("Location: $redirect?status=gagal_relasi");
            exit();
        }
        $query = "DELETE FROM jurusan WHERE id_jurusan = '$id'";

    } elseif ($modul == 'lab') {
        $redirect = "../bahan-praktek/jurusan.php";
        $cek_kepala = mysqli_query($conn, "SELECT id_kepala FROM kepala_lab WHERE id_lab = '$id'");
        $cek_dist = mysqli_query($conn, "SELECT id_distribusi FROM distribusi_lab WHERE id_lab = '$id'");
        
        if (mysqli_num_rows($cek_kepala) > 0 || mysqli_num_rows($cek_dist) > 0) {
            header("Location: $redirect?status=gagal_relasi");
            exit();
        }
        $query = "DELETE FROM lab WHERE id_lab = '$id'";

    } elseif ($modul == 'kepala') {
        $redirect = "../bahan-praktek/kepala-lab.php";
        $cek_permintaan = mysqli_query($conn, "SELECT id_permintaan FROM permintaan_barang WHERE id_kepala = '$id'");
        
        if (mysqli_num_rows($cek_permintaan) > 0) {
            header("Location: $redirect?status=gagal_relasi");
            exit();
        }
        $query = "DELETE FROM kepala_lab WHERE id_kepala = '$id'";
    }

    if ($query != "") {
        if (mysqli_query($conn, $query)) {
            header("Location: $redirect?status=hapus_sukses");
        } else {
            header("Location: $redirect?status=gagal");
        }
    }
    exit();
}

// ==============================================================
// 5. LOGIKA HAPUS DISTRIBUSI / BATAL KIRIM (Format: Redirect URL)
// ==============================================================
if (isset($_GET['hapus_distribusi'])) {
    $id_distribusi = mysqli_real_escape_string($conn, $_GET['hapus_distribusi']);
    $query_data = mysqli_query($conn, "SELECT id_praktek, jumlah, status FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'");
    
    if (mysqli_num_rows($query_data) > 0) {
        $data = mysqli_fetch_assoc($query_data);
        
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
    } else {
        // PERBAIKAN: Jika data tidak ditemukan di database, kembalikan ke halaman sebelumnya agar tidak blank
        header("Location: ../../modules/distribusi/index.php?status=gagal");
    }
    exit();
}

// ==============================================================
// 6. LOGIKA HAPUS DATA GUDANG PERSEDIAAN (Format: Redirect URL)
// ==============================================================
if (isset($_GET['hapus_persediaan'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus_persediaan']);
    $query = "DELETE FROM gudang_persediaan WHERE id_persediaan = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/persediaan.php?status=hapus_sukses");
    } else {
        // PERBAIKAN: Ganti tulisan error biasa menjadi redirect agar UX lebih baik
        header("Location: ../gudang/persediaan.php?status=gagal");
    }
    exit();
}
?>