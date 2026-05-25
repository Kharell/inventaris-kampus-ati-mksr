<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../config/database.php";
include "../../config/auth.php";
checkAccess(['admin', 'admin-acc']);

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

// ==========================================
// 1. KIRIM ULANG (SISA/KEKURANGAN)
// ==========================================
if ($aksi == 'kirim_ulang') {
    // Sanitasi data
    $id_distribusi = mysqli_real_escape_string($conn, $_POST['id']);
    $jumlah_sisa   = (int)$_POST['jumlah'];

    // Ambil data lama
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
            // Buat record PENGIRIMAN BARU untuk sisa barang
            $keterangan_baru = "Kirim kekurangan/ulang (" . $jumlah_sisa . " unit)";
            $sql_insert = "INSERT INTO distribusi_lab 
                            (id_praktek, id_lab, kode_distribusi, jumlah, tanggal_distribusi, spesifikasi, kondisi, status, keterangan) 
                           VALUES 
                            ('$id_praktek', '$id_lab', '$kode', '$jumlah_sisa', NOW(), '$spek', '$kondisi', 'dikirim', '$keterangan_baru')";
            mysqli_query($conn, $sql_insert);

            // Tutup record lama
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
// 2. TAMBAH BAHAN PRAKTEK (MANUAL OLEH ADMIN)
// ==========================================
if (isset($_POST['tambah_bahan_lab'])) {
    $id_lab      = mysqli_real_escape_string($conn, $_POST['id_lab']);
    $id_jurusan  = mysqli_real_escape_string($conn, $_POST['id_jurusan']);
    
    $kode_bahan  = mysqli_real_escape_string($conn, $_POST['kode_bahan']);
    $nama        = mysqli_real_escape_string($conn, $_POST['nama_bahan']);
    $spesifikasi = mysqli_real_escape_string($conn, $_POST['spesifikasi']);
    $kondisi     = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $satuan      = mysqli_real_escape_string($conn, $_POST['satuan']);
    
    $stok        = 0; // Default stok 0
    $tgl_masuk   = date('Y-m-d'); 

    $query = "INSERT INTO bahan_praktek (id_lab, id_jurusan, kode_bahan, nama_bahan, spesifikasi, stok, kondisi, satuan, tgl_masuk) 
              VALUES ('$id_lab', '$id_jurusan', '$kode_bahan', '$nama', '$spesifikasi', '$stok', '$kondisi', '$satuan', '$tgl_masuk')";

    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab&id_jurusan=$id_jurusan&status=sukses");
    } else {
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab&id_jurusan=$id_jurusan&status=gagal");
    }
    exit();
}

// ==========================================
// 3. SETUJUI (ACC) PERMINTAAN KEPALA LAB (STOK AWAL/TAMBAHAN)
// ==========================================
if (isset($_POST['setujui'])) {
    $id_permintaan = mysqli_real_escape_string($conn, $_POST['id_permintaan']);
    $id_barang     = mysqli_real_escape_string($conn, $_POST['id_barang']); // id_praktek
    
    // Ini adalah STOK AKUMULASI (Stok Lama + Baru) yang dikirim dari form sebelumnya
    $stok_akumulasi = (int)$_POST['stok_baru']; 
    
    $id_lab_back   = mysqli_real_escape_string($conn, $_POST['id_lab_back']);
    $id_j_back     = mysqli_real_escape_string($conn, $_POST['id_j_back']);

    // Ambil data detail bahan
    $query_bahan = mysqli_query($conn, "SELECT kode_bahan, spesifikasi, kondisi FROM bahan_praktek WHERE id_praktek = '$id_barang'");
    $data_bahan  = mysqli_fetch_assoc($query_bahan);
    
    $kode_bahan_asal = $data_bahan['kode_bahan']; 
    $spesifikasi     = mysqli_real_escape_string($conn, $data_bahan['spesifikasi']);
    $kondisi_asal    = $data_bahan['kondisi'];

    mysqli_begin_transaction($conn);

    try {
        // 1. UPDATE stok di tabel bahan_praktek menjadi STOK AKUMULASI
        $q1 = "UPDATE bahan_praktek SET stok = '$stok_akumulasi' WHERE id_praktek = '$id_barang'";
        mysqli_query($conn, $q1);

        // 2. UPDATE status permintaan menjadi disetujui (approved)
        $q2 = "UPDATE permintaan_bahan SET status = 'approved' WHERE id_permintaan = '$id_permintaan'";
        mysqli_query($conn, $q2);

        // 3. MENCARI TAHU BERAPA JUMLAH BARANG YANG BARU MASUK SAAT INI
        // (Kita perlu tahu selisihnya untuk dicatat di Histori / Distribusi Lab)
        // Ambil stok_saat_ini (tambahan yang diajukan) dari tabel permintaan_bahan
        $q_req = mysqli_query($conn, "SELECT stok_saat_ini FROM permintaan_bahan WHERE id_permintaan = '$id_permintaan'");
        $d_req = mysqli_fetch_assoc($q_req);
        $tambahan_baru = (int)$d_req['stok_saat_ini'];

        $tanggal_sekarang = date('Y-m-d');
        
        // 4. INSERT ke tabel distribusi_lab sebagai LOG Mutasi Masuk
        // Yang diinsert adalah $tambahan_baru, BUKAN total akumulasinya!
        $q4 = "INSERT INTO distribusi_lab (
                id_praktek, id_lab, kode_distribusi, jumlah, jumlah_diterima, 
                tanggal_distribusi, tanggal_diterima, spesifikasi, kondisi, status, keterangan
              ) VALUES (
                '$id_barang', '$id_lab_back', '$kode_bahan_asal', '$tambahan_baru', '$tambahan_baru', 
                '$tanggal_sekarang', NOW(), '$spesifikasi', '$kondisi_asal', 'diterima', 'Pencatatan Stok Oleh Kepala Lab (Di-ACC Admin)'
              )";
        mysqli_query($conn, $q4);

        mysqli_commit($conn);
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=success&msg=Berhasil disetujui.");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
} 
// LOGIKA TOLAK PERMINTAAN
elseif (isset($_POST['tolak'])) {
    $id_permintaan = mysqli_real_escape_string($conn, $_POST['id_permintaan']);
    $id_lab_back   = $_POST['id_lab_back'];
    $id_j_back     = $_POST['id_j_back'];

    try {
        $q3 = "UPDATE permintaan_bahan SET status = 'rejected' WHERE id_permintaan = '$id_permintaan'";
        mysqli_query($conn, $q3);
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=success&msg=Data ditolak");
    } catch (Exception $e) {
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=error&msg=" . urlencode($e->getMessage()));
    }
    exit();
}

// ==========================================
// 4. LOGIKA TAMBAH ATK & KEBERSIHAN (KATEGORI)
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

// ==============================================================
// LOGIKA TAMBAH DATA GUDANG PERSEDIAAN BARU
// ==============================================================
if (isset($_POST['jenis_form']) && $_POST['jenis_form'] == 'persediaan_baru') {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $stok_awal = (int)$_POST['stok_awal'];
    // Nilai awal pengajuan & pemakaian diset 0
    $pengajuan = 0; 
    $pemakaian = 0; 

    // Ingat: Jangan masukkan kolom `stok_akhir` karena sudah GENERATED secara otomatis oleh Database
    $query = "INSERT INTO gudang_persediaan (nama_barang, satuan, stok_awal, pengajuan_barang, pemakaian_barang) 
              VALUES ('$nama_barang', '$satuan', '$stok_awal', '$pengajuan', '$pemakaian')";
    
    if (mysqli_query($conn, $query)) {
        // Sesuaikan URL kembalinya dengan letak file gudang persediaan Anda
        header("Location: ../gudang/persediaan.php?status=sukses");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit();
}


// ==========================================
// 6. PROSES TAMBAH JURUSAN & LAB & KEPALA
// ==========================================
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

if (isset($_POST['tambah_kepala'])) {
    $id_lab   = mysqli_real_escape_string($conn, $_POST['id_lab']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_kepala']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nip      = mysqli_real_escape_string($conn, $_POST['nip']);
    $kontak   = mysqli_real_escape_string($conn, $_POST['kontak']);
    
    $password_asli = $_POST['password']; 
    $password_hash = password_hash($password_asli, PASSWORD_DEFAULT);
    $password_plain = mysqli_real_escape_string($conn, $password_asli);

    $query = "INSERT INTO kepala_lab (id_lab, nama_kepala, username, password, password_plain, nip, kontak, role) 
              VALUES ('$id_lab', '$nama', '$username', '$password_hash', '$password_plain', '$nip', '$kontak', 'kepala_lab')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../bahan-praktek/kepala-lab.php?status=sukses");
    } else {
        header("Location: ../bahan-praktek/kepala-lab.php?status=gagal");
    }
    exit();
}

// ==========================================
// 7. SIMPAN DISTRIBUSI (DARI MENU DISTRIBUSI)
// ==========================================
if (isset($_POST['simpan_distribusi'])) {
    $id_req   = mysqli_real_escape_string($conn, $_POST['id_permintaan']);
    $id_lab   = mysqli_real_escape_string($conn, $_POST['id_lab']);
    $id_prak  = mysqli_real_escape_string($conn, $_POST['id_barang']); 
    $jumlah   = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $kode     = mysqli_real_escape_string($conn, $_POST['kode_distribusi']);
    $kondisi  = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $tgl_now  = date('Y-m-d');

    $q_spek = mysqli_query($conn, "SELECT spesifikasi FROM bahan_praktek WHERE id_praktek = '$id_prak'");
    $d_spek = mysqli_fetch_assoc($q_spek);
    $spesifikasi = mysqli_real_escape_string($conn, $d_spek['spesifikasi'] ?? '-');

    mysqli_begin_transaction($conn);
    try {
        $q1 = "UPDATE permintaan_barang SET status='disetujui', tgl_proses=NOW(), jumlah_disetujui='$jumlah' WHERE id_permintaan='$id_req'";
        mysqli_query($conn, $q1);

        $q2 = "INSERT INTO distribusi_lab (id_praktek, id_lab, kode_distribusi, jumlah, tanggal_distribusi, spesifikasi, kondisi, status) 
               VALUES ('$id_prak', '$id_lab', '$kode', '$jumlah', '$tgl_now', '$spesifikasi', '$kondisi', 'dikirim')";
        mysqli_query($conn, $q2);

        mysqli_commit($conn);
        echo "success"; 
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Gagal: " . $e->getMessage();
    }
    exit;
}
?>