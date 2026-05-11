<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

if ($aksi == 'kirim_ulang') {
    // Sanitasi data
    $id_distribusi = mysqli_real_escape_string($conn, $_POST['id']);
    $jumlah_sisa   = (int)$_POST['jumlah'];

    // 1. Ambil data lama
    $cek = mysqli_query($conn, "SELECT * FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'");
    $data_lama = mysqli_fetch_assoc($cek);

    if ($data_lama) {
        $id_praktek = $data_lama['id_praktek'];
        $id_lab     = $data_lama['id_lab'];
        $kode       = $data_lama['kode_distribusi'];
        $spek       = mysqli_real_escape_string($conn, $data_lama['spesifikasi']);
        $kondisi    = $data_lama['kondisi'];

        mysqli_begin_transaction($conn);
        try {
            // 2. Buat record PENGIRIMAN BARU untuk sisa barang (INSERT)
            $keterangan_baru = "Kirim kekurangan/ulang (" . $jumlah_sisa . " unit)";
            
            $sql_insert = "INSERT INTO distribusi_lab 
                            (id_praktek, id_lab, kode_distribusi, jumlah, tanggal_distribusi, spesifikasi, kondisi, status, keterangan) 
                           VALUES 
                            ('$id_praktek', '$id_lab', '$kode', '$jumlah_sisa', NOW(), '$spek', '$kondisi', 'dikirim', '$keterangan_baru')";
            mysqli_query($conn, $sql_insert);

            // 3. TUTUP record/histori lama agar masuk ke tab "Selesai" (TIDAK ADA LAGI ERROR ENUM)
            // Kita set jumlah = jumlah_diterima (jika ditolak nilainya 0), dan status diubah jadi 'diterima'
            $sql_update_lama = "UPDATE distribusi_lab SET 
                                jumlah = COALESCE(jumlah_diterima, 0), 
                                status = 'diterima' 
                                WHERE id_distribusi = '$id_distribusi'";
            mysqli_query($conn, $sql_update_lama);

            mysqli_commit($conn);
            echo "success";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "Error Database: " . $e->getMessage();
        }
    } else {
        echo "Data distribusi tidak ditemukan.";
    }
    exit;
}

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


if (isset($_POST['tambah_bahan_lab'])) {
    // 1. Tangkap Parameter
    $id_lab      = mysqli_real_escape_string($conn, $_POST['id_lab']);
    $id_jurusan  = mysqli_real_escape_string($conn, $_POST['id_jurusan']);
    
    // 2. Tangkap Input Manual
    $kode_bahan  = mysqli_real_escape_string($conn, $_POST['kode_bahan']); // Manual
    $nama        = mysqli_real_escape_string($conn, $_POST['nama_bahan']);
    $spesifikasi = mysqli_real_escape_string($conn, $_POST['spesifikasi']);
    $kondisi     = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $satuan      = mysqli_real_escape_string($conn, $_POST['satuan']);
    
    $stok        = 0; // Default stok 0 (diisi lewat menu masuk/keluar)
    $tgl_masuk   = date('Y-m-d'); 

    // 3. Query Simpan
    // Kolom disesuaikan dengan struktur DB kamu: id_lab, id_jurusan, kode_bahan, dst.
    $query = "INSERT INTO bahan_praktek (id_lab, id_jurusan, kode_bahan, nama_bahan, spesifikasi, stok, kondisi, satuan, tgl_masuk) 
              VALUES ('$id_lab', '$id_jurusan', '$kode_bahan', '$nama', '$spesifikasi', '$stok', '$kondisi', '$satuan', '$tgl_masuk')";

    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab&id_jurusan=$id_jurusan&status=sukses");
        exit();
    } else {
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab&id_jurusan=$id_jurusan&status=gagal");
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
// 1. LOGIKA DISTRIBUSI BAHAN (FIXED & LENGKAP)
// ==========================================


if (isset($_POST['simpan_distribusi'])) {
    $id_req   = mysqli_real_escape_string($conn, $_POST['id_permintaan']);
    $id_lab   = mysqli_real_escape_string($conn, $_POST['id_lab']);
    $id_prak  = mysqli_real_escape_string($conn, $_POST['id_barang']); 
    $jumlah   = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $kode     = mysqli_real_escape_string($conn, $_POST['kode_distribusi']);
    $kondisi  = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $tgl_now  = date('Y-m-d');

    // Ambil spek dari tabel asal
    $q_spek = mysqli_query($conn, "SELECT spesifikasi FROM bahan_praktek WHERE id_praktek = '$id_prak'");
    $d_spek = mysqli_fetch_assoc($q_spek);
    $spesifikasi = mysqli_real_escape_string($conn, $d_spek['spesifikasi'] ?? '-');

    mysqli_begin_transaction($conn);
    try {
        // 1. Update status di permintaan_barang
        $q1 = "UPDATE permintaan_barang SET status='disetujui', tgl_proses=NOW(), jumlah_disetujui='$jumlah' WHERE id_permintaan='$id_req'";
        mysqli_query($conn, $q1);

        // 2. Simpan ke distribusi_lab
        $q2 = "INSERT INTO distribusi_lab (id_praktek, id_lab, kode_distribusi, jumlah, tanggal_distribusi, spesifikasi, kondisi, status) 
               VALUES ('$id_prak', '$id_lab', '$kode', '$jumlah', '$tgl_now', '$spesifikasi', '$kondisi', 'dikirim')";
        mysqli_query($conn, $q2);

        mysqli_commit($conn);
        echo "success"; // Respon untuk SweetAlert
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Gagal: " . $e->getMessage();
    }
    exit;
}




// ==========================================
// TAMBAH PERSEDIAAN (DIPERBAIKI AGAR TIDAK BENTROK)
// ==========================================
// Gunakan isset($_POST['...']) BUKAN $_SERVER['REQUEST_METHOD']
if (isset($_POST['simpan_persediaan'])) { 
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $satuan    = mysqli_real_escape_string($conn, $_POST['satuan']);
    $awal      = (int)$_POST['stok_awal'];
    $pengajuan = (int)$_POST['pengajuan_barang'];
    $pemakaian = (int)$_POST['pemakaian_barang'];

    $sql = "INSERT INTO gudang_persediaan (nama_barang, satuan, stok_awal, pengajuan_barang, pemakaian_barang) 
            VALUES ('$nama', '$satuan', '$awal', '$pengajuan', '$pemakaian')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../gudang/persediaan.php?status=sukses");
        exit(); // Wajib pakai exit setelah header
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}


// Aktifkan laporan error database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_POST['setujui'])) {
    $id_permintaan = mysqli_real_escape_string($conn, $_POST['id_permintaan']);
    $id_barang     = mysqli_real_escape_string($conn, $_POST['id_barang']); // Ini adalah id_praktek
    $stok_baru     = mysqli_real_escape_string($conn, $_POST['stok_baru']);
    
    $id_lab_back   = $_POST['id_lab_back'];
    $id_j_back     = $_POST['id_j_back'];

    // 1. Ambil kode_bahan dan spesifikasi dari tabel bahan_praktek
    $query_bahan = mysqli_query($conn, "SELECT kode_bahan, spesifikasi, kondisi FROM bahan_praktek WHERE id_praktek = '$id_barang'");
    $data_bahan  = mysqli_fetch_assoc($query_bahan);
    
    $kode_bahan_asal = $data_bahan['kode_bahan']; // Ini yang akan jadi kode_distribusi
    $spesifikasi     = mysqli_real_escape_string($conn, $data_bahan['spesifikasi']);
    $kondisi_asal    = $data_bahan['kondisi'];

    mysqli_begin_transaction($conn);

    try {
        // 2. UPDATE stok di tabel bahan_praktek
        $q1 = "UPDATE bahan_praktek SET stok = '$stok_baru' WHERE id_praktek = '$id_barang'";
        mysqli_query($conn, $q1);

        // 3. UPDATE status permintaan
        $q2 = "UPDATE permintaan_bahan SET status = 'approved' WHERE id_permintaan = '$id_permintaan'";
        mysqli_query($conn, $q2);

        // 4. INSERT ke tabel distribusi_lab
        // Menggunakan $kode_bahan_asal sebagai isi dari kolom kode_distribusi
        $tanggal_sekarang = date('Y-m-d');
        
        // 4. INSERT ke tabel distribusi_lab
        // Kita tambahkan 'jumlah_diterima' agar langsung dianggap SELESAI dan tidak masuk tab Masalah
        $tanggal_sekarang = date('Y-m-d');
        
        $q4 = "INSERT INTO distribusi_lab (
                id_praktek, 
                id_lab, 
                kode_distribusi, 
                jumlah, 
                jumlah_diterima, /* <--- TAMBAHAN BARU */
                tanggal_distribusi, 
                spesifikasi, 
                kondisi, 
                status, 
                keterangan
              ) VALUES (
                '$id_barang', 
                '$id_lab_back', 
                '$kode_bahan_asal', 
                '$stok_baru', 
                '$stok_baru', /* <--- ISI DENGAN NILAI YANG SAMA (FULL) */
                '$tanggal_sekarang', 
                '$spesifikasi', 
                '$kondisi_asal', 
                'diterima', 
                'Pencatatan stok awal (Otomatis)'
              )";
        mysqli_query($conn, $q4);

        mysqli_commit($conn);

        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=success&msg=Berhasil disetujui. Kode Distribusi: $kode_bahan_asal");
        exit();

    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conn);
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }

} elseif (isset($_POST['tolak'])) {
    // ... kode tolak tetap sama ...
    $id_permintaan = mysqli_real_escape_string($conn, $_POST['id_permintaan']);
    $id_lab_back   = $_POST['id_lab_back'];
    $id_j_back     = $_POST['id_j_back'];

    try {
        $q3 = "UPDATE permintaan_bahan SET status = 'rejected' WHERE id_permintaan = '$id_permintaan'";
        mysqli_query($conn, $q3);
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=success&msg=Data ditolak");
    } catch (mysqli_sql_exception $e) {
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=error&msg=" . urlencode($e->getMessage()));
    }
    exit();
}




// ==========================================
// 2. LOGIKA TAMBAH ATK & KEBERSIHAN (KATEGORI)
// ==========================================
if (isset($_POST['tambah_atk']) || isset($_POST['tambah_kebersihan'])) {
    $is_atk = isset($_POST['tambah_atk']);
    $prefix = $is_atk ? "ATK-" . date('y') . "-" : "KBR-" . date('y') . "-";
    $kategori = $is_atk ? "ATK" : "Kebersihan";
    $redirect = $is_atk ? "atk.php" : "kebersihan.php";

    // Generate Kode Otomatis
    $sql_cari = "SELECT kode_barang FROM barang WHERE kode_barang LIKE '$prefix%' ORDER BY kode_barang DESC LIMIT 1";
    $res_cari = mysqli_query($conn, $sql_cari);
    $data = mysqli_fetch_assoc($res_cari);
    $no_urut = ($data) ? (int)substr($data['kode_barang'], -3) + 1 : 1;
    $kode_final = $prefix . str_pad($no_urut, 3, "0", STR_PAD_LEFT);

    $nama   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $tgl    = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);

    $query = "INSERT INTO barang (kode_barang, kategori, nama_barang, stok, satuan, tgl_masuk) 
              VALUES ('$kode_final', '$kategori', '$nama', '$stok', '$satuan', '$tgl')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/$redirect?status=sukses");
    } else {
        header("Location: ../gudang/$redirect?status=gagal");
    }
    exit();


}



