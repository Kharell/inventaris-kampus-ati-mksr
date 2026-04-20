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
    // 1. Ambil ID Kepala Lab dari Session
    $id_kepala     = $_SESSION['id_user'];
    
    // 2. Tangkap & Amankan data dari Form
    $id_barang     = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $jumlah_minta  = mysqli_real_escape_string($conn, $_POST['jumlah_minta']);
    
    // Ambil stok_awal dari input readonly (Pastikan di HTML name="stok_awal")
    $stok_awal     = isset($_POST['stok_awal']) ? mysqli_real_escape_string($conn, $_POST['stok_awal']) : 0;
    
    // Ambil keterangan (jika ada input keterangan_kepala di form)
    $keterangan    = isset($_POST['keterangan_kepala']) ? mysqli_real_escape_string($conn, $_POST['keterangan_kepala']) : '';

    // 3. Ambil data Spesifikasi & Kondisi terbaru langsung dari DB agar akurat
    $query_cek = mysqli_query($conn, "SELECT spesifikasi, kondisi FROM bahan_praktek WHERE id_praktek = '$id_barang'");
    $data_cek  = mysqli_fetch_assoc($query_cek);

    $spesifikasi = mysqli_real_escape_string($conn, $data_cek['spesifikasi'] ?? '-');
    $kondisi     = mysqli_real_escape_string($conn, $data_cek['kondisi'] ?? 'Baik');

    // 4. Query INSERT sesuai struktur tabel permintaan_barang
    $sql_tambah = "INSERT INTO permintaan_barang 
                   (id_kepala, id_barang, stok_awal, spesifikasi, jumlah_minta, kondisi, status, tgl_permintaan, keterangan_kepala) 
                   VALUES 
                   ('$id_kepala', '$id_barang', '$stok_awal', '$spesifikasi', '$jumlah_minta', '$kondisi', 'pending', NOW(), '$keterangan')";
    
    if (mysqli_query($conn, $sql_tambah)) {
        // Menggunakan sistem status agar SweetAlert muncul
        header("Location: ../lab/kebutuhan.php?status=success&msg=Permintaan barang berhasil dikirim");
        exit;
    } else {
        // Jika gagal, kirim pesan error
        header("Location: ../lab/kebutuhan.php?status=error&msg=" . urlencode(mysqli_error($conn)));
        exit;
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

// tambah stok paling awal

if (isset($_POST['kirimm'])) {
    $id_barang_array = $_POST['id_barang'] ?? [];
    $stok_fisik_array = $_POST['stok_fisik_lab'] ?? [];
    $id_user = $_SESSION['id_user']; 
    $tgl = date('Y-m-d H:i:s');

    if (empty($id_barang_array)) {
        header("Location: ../bahan-praktek.php?status=empty");
        exit;
    }

    foreach ($id_barang_array as $key => $id_barang_raw) {
        $id_barang = mysqli_real_escape_string($conn, $id_barang_raw);
        $stok_raw = $stok_fisik_array[$key] ?? 0;
        $stok_fisik = mysqli_real_escape_string($conn, $stok_raw);

        $query = "INSERT INTO permintaan_bahan (id_barang, id_user, stok_saat_ini, tgl_permintaan, status) 
                  VALUES ('$id_barang', '$id_user', '$stok_fisik', '$tgl', 'pending')";
        
        mysqli_query($conn, $query);
    }

    // Kembali ke halaman sebelumnya dengan parameter sukses
    header("Location: ../lab/stok.php?status=success");
    exit;
}


?>