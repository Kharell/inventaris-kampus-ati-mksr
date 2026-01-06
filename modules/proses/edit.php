<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

// ==========================================
// LOGIKA UPDATE KHUSUS ATK
// ==========================================
if (isset($_POST['update_atk'])) {
    $id     = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);

    $query = "UPDATE barang SET 
                nama_barang = '$nama', 
                stok = '$stok', 
                satuan = '$satuan' 
              WHERE id_barang = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/atk.php?status=update_sukses");
        exit();
    } else {
        header("Location: ../gudang/atk.php?status=gagal");
        exit();
    }
}

// ==========================================
// LOGIKA UPDATE KHUSUS KEBERSIHAN
// ==========================================
if (isset($_POST['update_kebersihan'])) {
    // 1. Ambil Data dan Proteksi (Security)
    $id     = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);

    // 2. Query Update
    $query = "UPDATE barang SET 
                nama_barang = '$nama', 
                stok = '$stok', 
                satuan = '$satuan' 
              WHERE id_barang = '$id'";
    
    // 3. Eksekusi dan Redirect
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/kebersihan.php?status=update_sukses");
        exit();
    } else {
        header("Location: ../gudang/kebersihan.php?status=gagal");
        exit();
    }
}

// ==========================================
// LOGIKA UPDATE KHUSUS BAHAN PRAKTEK
// ==========================================
if (isset($_POST['update_praktek_pusat'])) {
    $id     = mysqli_real_escape_string($conn, $_POST['id_praktek']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_bahan']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);

    // Update ke tabel bahan_praktek
    $query = "UPDATE bahan_praktek SET 
                nama_bahan = '$nama', 
                stok = '$stok', 
                satuan = '$satuan' 
              WHERE id_praktek = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../gudang/bahan-praktek.php?status=update_sukses");
        exit();
    } else {
        header("Location: ../gudang/bahan-praktek.php?status=gagal");
        exit();
    }
}



// 1. UPDATE JURUSAN
if (isset($_POST['update_jurusan'])) {
    $id = $_POST['id_jurusan'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_jurusan']);
    
    $query = "UPDATE jurusan SET nama_jurusan='$nama' WHERE id_jurusan='$id'";
    if (mysqli_query($conn, $query)) {
        header("Location: ../bahan-praktek/jurusan.php?status=update_sukses");
    } else {
        header("Location: ../bahan-praktek/jurusan.php?status=gagal");
    }
    exit();
}

// 2. UPDATE LAB
if (isset($_POST['update_lab'])) {
    $id = $_POST['id_lab'];
    $id_jur = $_POST['id_jurusan'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lab']);
    
    $query = "UPDATE lab SET id_jurusan='$id_jur', nama_lab='$nama' WHERE id_lab='$id'";
    if (mysqli_query($conn, $query)) {
        header("Location: ../bahan-praktek/jurusan.php?status=update_sukses");
    } else {
        header("Location: ../bahan-praktek/jurusan.php?status=gagal");
    }
    exit();
}

// 3. UPDATE KEPALA LAB
if (isset($_POST['update_kepala'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_kepala']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kepala']);
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    // Logika update password: hanya diupdate jika input password tidak kosong
    if (!empty($_POST['password'])) {
        $pw_asli = $_POST['password'];
        $password_hash = password_hash($pw_asli, PASSWORD_DEFAULT);
        $password_plain = mysqli_real_escape_string($conn, $pw_asli);

        $query = "UPDATE kepala_lab SET 
                    nama_kepala='$nama', 
                    username='$username', 
                    password='$password_hash', 
                    password_plain='$password_plain', 
                    nip='$nip', 
                    kontak='$kontak' 
                  WHERE id_kepala='$id'";
    } else {
        // Jika password kosong, jangan update kolom password dan password_plain
        $query = "UPDATE kepala_lab SET 
                    nama_kepala='$nama', 
                    username='$username', 
                    nip='$nip', 
                    kontak='$kontak' 
                  WHERE id_kepala='$id'";
    }

    if (mysqli_query($conn, $query)) {
        // Redirect dengan status update_sukses agar SweetAlert muncul
        header("Location: ../bahan-praktek/kepala-lab.php?status=update_sukses");
    } else {
        header("Location: ../bahan-praktek/kepala-lab.php?status=gagal");
    }
    exit();
}

// EDIT DISTRIBUSI
if (isset($_POST['update_distribusi'])) {
    $id_distribusi = mysqli_real_escape_string($conn, $_POST['id_distribusi']);
    $jumlah_baru = (int)$_POST['jumlah'];

    // 1. Ambil data distribusi lama & ID barang
    $query_lama = mysqli_query($conn, "SELECT id_praktek, jumlah FROM distribusi_lab WHERE id_distribusi = '$id_distribusi'");
    $data_lama = mysqli_fetch_assoc($query_lama);
    
    $id_praktek = $data_lama['id_praktek'];
    $jumlah_lama = (int)$data_lama['jumlah'];

    // 2. Hitung selisih
    // Jika baru > lama, stok gudang harus dikurangi lagi
    // Jika baru < lama, stok gudang harus ditambah kembali
    $selisih = $jumlah_baru - $jumlah_lama;

    // 3. Cek apakah stok di gudang mencukupi jika ada penambahan (selisih positif)
    $query_stok = mysqli_query($conn, "SELECT stok FROM bahan_praktek WHERE id_praktek = '$id_praktek'");
    $data_stok = mysqli_fetch_assoc($query_stok);
    $stok_gudang = (int)$data_stok['stok'];

    if ($selisih > $stok_gudang) {
        header("Location: ../../modules/distribusi/index.php?status=stok_kurang");
        exit;
    }

    // 4. Update stok gudang pusat
    $update_stok = mysqli_query($conn, "UPDATE bahan_praktek SET stok = stok - ($selisih) WHERE id_praktek = '$id_praktek'");

    if ($update_stok) {
        // 5. Update jumlah di tabel distribusi
        $update_dist = mysqli_query($conn, "UPDATE distribusi_lab SET jumlah = '$jumlah_baru' WHERE id_distribusi = '$id_distribusi'");
        
        if ($update_dist) {
            header("Location: ../../modules/distribusi/index.php?status=edit_sukses");
        } else {
            header("Location: ../../modules/distribusi/index.php?status=gagal");
        }
    }
}
?>