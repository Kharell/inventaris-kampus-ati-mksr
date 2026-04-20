

<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// 1. UPDATE ATK
if (isset($_POST['update_atk'])) {
    $id     = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);

    $query = "UPDATE barang SET nama_barang='$nama', stok='$stok', satuan='$satuan' WHERE id_barang='$id'";
    mysqli_query($conn, $query);
    header("Location: ../gudang/atk.php?status=update_sukses");
    exit();
}

// 2. UPDATE KEBERSIHAN
if (isset($_POST['update_kebersihan'])) {
    $id     = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $stok   = mysqli_real_escape_string($conn, $_POST['stok']);
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);

    $query = "UPDATE barang SET nama_barang='$nama', stok='$stok', satuan='$satuan' WHERE id_barang='$id'";
    mysqli_query($conn, $query);
    header("Location: ../gudang/kebersihan.php?status=update_sukses");
    exit();
}

// 3. UPDATE BAHAN PRAKTEK (LAB) - ELSE DIHAPUS AGAR TIDAK MENJEGAL
if (isset($_POST['update_bahan_lab'])) {
    $id_praktek  = mysqli_real_escape_string($conn, $_POST['id_praktek']);
    $id_lab_back = mysqli_real_escape_string($conn, $_POST['id_lab_back']);
    $id_j_back   = mysqli_real_escape_string($conn, $_POST['id_j_back']);
    $kode_bahan  = mysqli_real_escape_string($conn, $_POST['kode_bahan']);
    $nama        = mysqli_real_escape_string($conn, $_POST['nama_bahan']);
    $spesifikasi = mysqli_real_escape_string($conn, $_POST['spesifikasi']);
    $kondisi     = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $satuan      = mysqli_real_escape_string($conn, $_POST['satuan']);
    
    $sql = "UPDATE bahan_praktek SET kode_bahan='$kode_bahan', nama_bahan='$nama', spesifikasi='$spesifikasi', kondisi='$kondisi', satuan='$satuan' WHERE id_praktek='$id_praktek'";
    mysqli_query($conn, $sql);
    header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=sukses_edit");
    exit();
}

// 4. EDIT PERSEDIAAN GUDANG (Punya Anda sekarang Aman)
if (isset($_POST['id_persediaan'])) {
    $id        = mysqli_real_escape_string($conn, $_POST['id_persediaan']);
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $satuan    = mysqli_real_escape_string($conn, $_POST['satuan']);
    $awal      = (int)$_POST['stok_awal'];
    $pengajuan = (int)$_POST['pengajuan_barang'];
    $pemakaian = (int)$_POST['pemakaian_barang'];

    $sql = "UPDATE gudang_persediaan SET nama_barang='$nama', satuan='$satuan', stok_awal='$awal', pengajuan_barang='$pengajuan', pemakaian_barang='$pemakaian' WHERE id_persediaan='$id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../gudang/persediaan.php?status=sukses");
    } else {
        header("Location: ../gudang/persediaan.php?status=gagal");
    }
    exit();
}

// 5. UPDATE JURUSAN / LAB / KEPALA LAB / DISTRIBUSI (Gunakan pola yang sama)
if (isset($_POST['update_jurusan'])) {
    $id = $_POST['id_jurusan'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_jurusan']);
    mysqli_query($conn, "UPDATE jurusan SET nama_jurusan='$nama' WHERE id_jurusan='$id'");
    header("Location: ../bahan-praktek/jurusan.php?status=update_sukses");
    exit();
}

if (isset($_POST['update_lab'])) {
    $id = $_POST['id_lab'];
    $id_jur = $_POST['id_jurusan'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lab']);
    mysqli_query($conn, "UPDATE lab SET id_jurusan='$id_jur', nama_lab='$nama' WHERE id_lab='$id'");
    header("Location: ../bahan-praktek/jurusan.php?status=update_sukses");
    exit();
}

// PENGAMAN TERAKHIR: Jika tidak ada data POST yang cocok
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}
?>

<!-- 
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

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
// LOGIKA UPDATE BAHAN PRAKTEK (LAB)
// ==========================================
if (isset($_POST['update_bahan_lab'])) {
    // 1. Tangkap Parameter ID & Redirect Back
    $id_praktek  = mysqli_real_escape_string($conn, $_POST['id_praktek']);
    $id_lab_back = mysqli_real_escape_string($conn, $_POST['id_lab_back']);
    $id_j_back   = mysqli_real_escape_string($conn, $_POST['id_j_back']);
    
    // 2. Tangkap Input Data Baru
    $kode_bahan  = mysqli_real_escape_string($conn, $_POST['kode_bahan']);
    $nama        = mysqli_real_escape_string($conn, $_POST['nama_bahan']);
    $spesifikasi = mysqli_real_escape_string($conn, $_POST['spesifikasi']);
    $kondisi     = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $satuan      = mysqli_real_escape_string($conn, $_POST['satuan']);
    
    // 3. Query Update
    $sql = "UPDATE bahan_praktek SET 
            kode_bahan  = '$kode_bahan',
            nama_bahan  = '$nama',
            spesifikasi = '$spesifikasi',
            kondisi     = '$kondisi',
            satuan      = '$satuan'
            WHERE id_praktek = '$id_praktek'";

    if (mysqli_query($conn, $sql)) {
        // Berhasil: Kembali ke lab yang sama dengan parameter url agar tidak hilang
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=sukses_edit");
        exit();
    } else {
        // Gagal: Kembali dengan parameter url dan status gagal
        header("Location: ../gudang/bahan-praktek.php?id_lab=$id_lab_back&id_jurusan=$id_j_back&status=gagal_edit");
        exit();
    }
} else {
    // Jika diakses ilegal
    header("Location: ../gudang/bahan-praktek.php");
    exit();
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
    
}


// EDIT PERSEDIAAN GUDANG
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan 'id_persediaan' sesuai dengan name="id_persediaan" di input hidden modal
    $id = mysqli_real_escape_string($conn, $_POST['id_persediaan']);
    
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $satuan    = mysqli_real_escape_string($conn, $_POST['satuan']);
    $awal      = (int)$_POST['stok_awal'];
    $pengajuan = (int)$_POST['pengajuan_barang'];
    $pemakaian = (int)$_POST['pemakaian_barang'];

    $sql = "UPDATE gudang_persediaan SET 
            nama_barang = '$nama', 
            satuan = '$satuan', 
            stok_awal = '$awal', 
            pengajuan_barang = '$pengajuan', 
            pemakaian_barang = '$pemakaian' 
            WHERE id_persediaan = '$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../gudang/persediaan.php?status=sukses");
        exit();
    } else {
        // Cek error di sini jika masih gagal
        die("Error database: " . mysqli_error($conn));
    }
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
} -->
