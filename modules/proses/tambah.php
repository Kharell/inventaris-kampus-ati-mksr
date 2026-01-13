<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// ==========================================
// LOGIKA KHUSUS TAMBAH ATK
// ==========================================
if (isset($_POST['tambah_atk'])) {
    // 1. Logika Kode Otomatis ATK
    $prefix = "ATK-" . date('y') . "-"; 
    $sql_cari = "SELECT kode_barang FROM barang WHERE kode_barang LIKE '$prefix%' ORDER BY kode_barang DESC LIMIT 1";
    $query_cari = mysqli_query($conn, $sql_cari);
    $data = mysqli_fetch_assoc($query_cari);

    if ($data) {
        $no_urut = substr($data['kode_barang'], -3);
        $no_urut = (int)$no_urut + 1;
    } else {
        $no_urut = 1;
    }
    $kode_final = $prefix . str_pad($no_urut, 3, "0", STR_PAD_LEFT);

    // 2. Ambil Input & Proteksi
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $tgl    = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);

    // 3. Query Simpan
    $query = "INSERT INTO barang (kode_barang, kategori, nama_barang, stok, satuan, tgl_masuk) 
              VALUES ('$kode_final', 'ATK', '$nama', '$stok', '$satuan', '$tgl')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/atk.php?status=sukses");
        exit();
    }
}

// ==========================================
// LOGIKA KHUSUS TAMBAH KEBERSIHAN
// ==========================================
if (isset($_POST['tambah_kebersihan'])) {
    // 1. Logika Kode Otomatis Kebersihan (Prefix KBR)
    $prefix = "KBR-" . date('y') . "-"; 
    $sql_cari = "SELECT kode_barang FROM barang WHERE kode_barang LIKE '$prefix%' ORDER BY kode_barang DESC LIMIT 1";
    $query_cari = mysqli_query($conn, $sql_cari);
    $data = mysqli_fetch_assoc($query_cari);

    if ($data) {
        $no_urut = substr($data['kode_barang'], -3);
        $no_urut = (int)$no_urut + 1;
    } else {
        $no_urut = 1;
    }
    $kode_final = $prefix . str_pad($no_urut, 3, "0", STR_PAD_LEFT);

    // 2. Ambil Input & Proteksi (Wajib didefinisikan ulang di sini)
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $tgl    = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);

    // 3. Query Simpan dengan Kategori 'Kebersihan'
    $query = "INSERT INTO barang (kode_barang, kategori, nama_barang, stok, satuan, tgl_masuk) 
              VALUES ('$kode_final', 'Kebersihan', '$nama', '$stok', '$satuan', '$tgl')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/kebersihan.php?status=sukses");
        exit();
    }
}


// ==========================================
// LOGIKA KHUSUS TAMBAH BAHAN PRAKTEK (PUSAT)
// ==========================================
if (isset($_POST['tambah_praktek_pusat'])) {
    // 1. Generate Kode Otomatis (Prefix BPR-26-XXX)
    $prefix = "BPR-" . date('y') . "-";
    $sql_cari = "SELECT kode_bahan FROM bahan_praktek WHERE kode_bahan LIKE '$prefix%' ORDER BY kode_bahan DESC LIMIT 1";
    $query_cari = mysqli_query($conn, $sql_cari);
    $data = mysqli_fetch_assoc($query_cari);

    // Hitung nomor urut berikutnya
    $no_urut = ($data) ? (int)substr($data['kode_bahan'], -3) + 1 : 1;
    $kode_final = $prefix . str_pad($no_urut, 3, "0", STR_PAD_LEFT);

    // 2. Ambil Input & Proteksi Keamanan
    $nama        = mysqli_real_escape_string($conn, $_POST['nama_bahan']);
    $spesifikasi = mysqli_real_escape_string($conn, $_POST['spesifikasi']); // Baru
    $stok        = (int)$_POST['stok'];
    $kondisi     = mysqli_real_escape_string($conn, $_POST['kondisi']);   // Baru
    $satuan      = mysqli_real_escape_string($conn, $_POST['satuan']);
    $tgl_masuk   = date('Y-m-d'); // Otomatis tanggal hari ini

    // 3. Query Simpan ke tabel bahan_praktek
    // Perhatikan urutan kolom: kode_bahan, nama_bahan, spesifikasi, stok, kondisi, satuan, tgl_masuk
    $query = "INSERT INTO bahan_praktek (kode_bahan, nama_bahan, spesifikasi, stok, kondisi, satuan, tgl_masuk) 
              VALUES ('$kode_final', '$nama', '$spesifikasi', '$stok', '$kondisi', '$satuan', '$tgl_masuk')";

    if (mysqli_query($conn, $query)) {
        // Redirect ke halaman index bahan-praktek dengan status sukses
        header("Location: ../gudang/bahan-praktek.php?status=sukses");
        exit();
    } else {
        // Jika terjadi error pada database
        header("Location: ../gudang/bahan-praktek.php?status=gagal");
        exit();
    }
}


// 2. PROSES TAMBAH JURUSAN
if (isset($_POST['tambah_jurusan'])) {
    $nama_jurusan = mysqli_real_escape_string($conn, $_POST['nama_jurusan']);
    
    $query = "INSERT INTO jurusan (nama_jurusan) VALUES ('$nama_jurusan')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../bahan-praktek/jurusan.php?status=sukses");
    } else {
        header("Location: ../bahan-praktek/jurusan.php?status=gagal");
    }
    exit();
}

// 3. PROSES TAMBAH LAB
if (isset($_POST['tambah_lab'])) {
    $id_jurusan = mysqli_real_escape_string($conn, $_POST['id_jurusan']);
    $nama_lab   = mysqli_real_escape_string($conn, $_POST['nama_lab']);
    
    $query = "INSERT INTO lab (id_jurusan, nama_lab) VALUES ('$id_jurusan', '$nama_lab')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../bahan-praktek/jurusan.php?status=sukses");
    } else {
        header("Location: ../bahan-praktek/jurusan.php?status=gagal");
    }
    exit();
}

// 4. PROSES TAMBAH KEPALA LAB (DENGAN USERNAME & PASSWORD)
if (isset($_POST['tambah_kepala'])) {
    $id_lab   = mysqli_real_escape_string($conn, $_POST['id_lab']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_kepala']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nip      = mysqli_real_escape_string($conn, $_POST['nip']);
    $kontak   = mysqli_real_escape_string($conn, $_POST['kontak']);
    
    // Ambil password asli dari input
    $password_asli = $_POST['password']; 
    
    // 1. Enkripsi password untuk keamanan login (Hash)
    $password_hash = password_hash($password_asli, PASSWORD_DEFAULT);
    
    // 2. Simpan juga teks aslinya ke variabel terpisah (untuk password_plain)
    $password_plain = mysqli_real_escape_string($conn, $password_asli);

    // Masukkan ke kolom 'password' (hash) DAN 'password_plain' (teks biasa)
    $query = "INSERT INTO kepala_lab (id_lab, nama_kepala, username, password, password_plain, nip, kontak, role) 
              VALUES ('$id_lab', '$nama', '$username', '$password_hash', '$password_plain', '$nip', '$kontak', 'kepala_lab')";
    
    if (mysqli_query($conn, $query)) {
        // Redirect dengan status sukses untuk memicu SweetAlert di halaman utama
        header("Location: ../bahan-praktek/kepala-lab.php?status=sukses");
    } else {
        // Jika gagal (misal: username kembar), kirim status gagal
        header("Location: ../bahan-praktek/kepala-lab.php?status=gagal");
    }
    exit();
}

// ==========================================
// 1. LOGIKA DISTRIBUSI BAHAN (TAMBAHAN BARU)
// ==========================================
if (isset($_POST['simpan_distribusi'])) {
    $id_praktek     = mysqli_real_escape_string($conn, $_POST['id_praktek'] ?? '');
    $id_lab         = mysqli_real_escape_string($conn, $_POST['id_lab'] ?? ''); 
    $id_permintaan  = mysqli_real_escape_string($conn, $_POST['id_permintaan'] ?? '');
    $kode           = mysqli_real_escape_string($conn, $_POST['kode_distribusi'] ?? '');
    $jumlah         = (int)($_POST['jumlah'] ?? 0);
    $tanggal        = $_POST['tanggal_distribusi'] ?? date('Y-m-d');

    // VALIDASI KRUSIAL
    if (empty($id_lab) || $id_lab == 'undefined') {
        // Jika ID Lab kosong, kembalikan ke index dengan status error
        header("Location: ../distribusi/index.php?status=error_id");
        exit();
    }

    // 1. Cek stok di gudang pusat
    $cek = mysqli_query($conn, "SELECT stok FROM bahan_praktek WHERE id_praktek = '$id_praktek'");
    $dt = mysqli_fetch_assoc($cek);
    if ($dt['stok'] >= $jumlah) {
        mysqli_begin_transaction($conn);
        
        try {
            // 2. Simpan ke tabel distribusi_lab
            mysqli_query($conn, "INSERT INTO distribusi_lab (id_praktek, id_lab, kode_distribusi, jumlah, tanggal_distribusi, status) 
                                 VALUES ('$id_praktek', '$id_lab', '$kode', '$jumlah', '$tanggal', 'dikirim')");
            
            // 3. Kurangi stok di gudang pusat
            mysqli_query($conn, "UPDATE bahan_praktek SET stok = stok - $jumlah WHERE id_praktek = '$id_praktek'");

            // 4. Update status permintaan barang (Jika berasal dari permintaan Kepala Lab)
            if (!empty($id_permintaan)) {
                mysqli_query($conn, "UPDATE permintaan_barang SET status = 'disetujui' WHERE id_permintaan = '$id_permintaan'");
            }

            mysqli_commit($conn);
            header("Location: ../distribusi/index.php?id_lab=$id_lab&status=sukses");
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: ../distribusi/index.php?id_lab=$id_lab&status=gagal");
        }
    } else {
        header("Location: ../distribusi/index.php?id_lab=$id_lab&status=stok_kurang");
    }
    exit();
}