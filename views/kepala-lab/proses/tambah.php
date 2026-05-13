<?php
session_start();
include "../../../config/database.php";

date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];

    if ($aksi == 'terima_barang') {
        $id_distribusi = mysqli_real_escape_string($conn, $_POST['id']);
        $jumlah_fisik = (int) $_POST['jumlah']; // Jumlah nyata yang diterima
        $tipe = mysqli_real_escape_string($conn, $_POST['tipe']);
        
        // TAMBAHAN: Tangkap alasan yang dikirim dari Javascript
        $alasan_input = isset($_POST['keterangan']) ? mysqli_real_escape_string($conn, $_POST['keterangan']) : '';

        mysqli_begin_transaction($conn);

        try {
            // 1. Ambil data distribusi
            $get_data = mysqli_query($conn, "SELECT id_praktek, jumlah FROM distribusi_lab WHERE id_distribusi = '$id_distribusi' FOR UPDATE");
            $data = mysqli_fetch_assoc($get_data);

            if (!$data)
                throw new Exception("Data distribusi tidak ditemukan.");

            $id_praktek = $data['id_praktek'];
            $jumlah_dikirim = (int) $data['jumlah'];

            // 2. Tentukan keterangan (PERBAIKAN LOGIKA ALASAN)
            $keterangan = "Diterima sesuai dokumen";
            if ($tipe == 'kurang' || $tipe == 'rusak') {
                // Gabungkan teks default dengan alasan dari Kepala Lab
                $keterangan = "Terima sebagian (Fisik: $jumlah_fisik). Alasan: " . $alasan_input;
            }

            // 3. Update Status Distribusi
            // Simpan jumlah_fisik ke kolom jumlah_diterima
            $update_dist = mysqli_query($conn, "UPDATE distribusi_lab SET 
                status = 'diterima', 
                jumlah_diterima = '$jumlah_fisik', 
                keterangan = '$keterangan',
                tanggal_diterima = NOW()
                WHERE id_distribusi = '$id_distribusi'");

            // 4. Update Stok (HANYA TAMBAH SEJUMLAH FISIK YANG DITERIMA)
            // Logika: Saat barang dikirim, stok di bahan_praktek biasanya sudah dipotong full.
            // Jadi kita kembalikan/tambah hanya yang benar-benar diterima.
            $update_stok = mysqli_query($conn, "UPDATE bahan_praktek SET 
                stok = stok + $jumlah_fisik 
                WHERE id_praktek = '$id_praktek'");


            if ($update_dist && $update_stok) {
                mysqli_commit($conn);
                echo "success";
            } else {
                throw new Exception("Gagal update database.");
            }

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "Error: " . $e->getMessage();
        }
        exit;
        
    } elseif ($aksi == 'tolak') {
        $id_distribusi = mysqli_real_escape_string($conn, $_POST['id']);
        $alasan = mysqli_real_escape_string($conn, $_POST['keterangan']);

        mysqli_begin_transaction($conn);

        try {
            // 1. Ambil data distribusi untuk cek ketersediaan data
            $get_data = mysqli_query($conn, "SELECT id_praktek, jumlah FROM distribusi_lab WHERE id_distribusi = '$id_distribusi' FOR UPDATE");
            $data = mysqli_fetch_assoc($get_data);

            if (!$data)
                throw new Exception("Data distribusi tidak ditemukan.");

            // HAPUS LOGIKA UPDATE STOK (KEMBALIKAN STOK) DI SINI
            // Karena stok dari awal tidak pernah dipotong oleh Admin, maka tidak perlu ada yang dikembalikan.

            // 2. Update status distribusi menjadi ditolak
            $update_status = mysqli_query($conn, "UPDATE distribusi_lab SET 
                status = 'ditolak', 
                keterangan = '$alasan',
                tanggal_distribusi = NOW() 
                WHERE id_distribusi = '$id_distribusi'");

            if ($update_status) { // Hanya cek update_status
                mysqli_commit($conn);
                echo "success";
            } else {
                throw new Exception("Gagal memperbarui data penolakan.");
            }

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "Error: " . $e->getMessage();
        }
        exit;
    }
}
// --- 3. LOGIKA KIRIM PERMINTAAN BARANG BARU (KE PUSAT) ---
if (isset($_POST['kirim_permintaan'])) {
    $id_kepala     = $_SESSION['id_user'];
    $id_barang     = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $jumlah_minta  = (int)$_POST['jumlah_minta'];
    $stok_awal     = (int)$_POST['stok_awal'];
    $keterangan    = mysqli_real_escape_string($conn, $_POST['keterangan_kepala']);

    // Ambil data pendukung dari database bahan
    $query_cek = mysqli_query($conn, "SELECT spesifikasi, kondisi FROM bahan_praktek WHERE id_praktek = '$id_barang'");
    $data_cek  = mysqli_fetch_assoc($query_cek);

    $spesifikasi = mysqli_real_escape_string($conn, $data_cek['spesifikasi'] ?? '-');
    $kondisi     = mysqli_real_escape_string($conn, $data_cek['kondisi'] ?? 'Baik');

    $sql_tambah = "INSERT INTO permintaan_barang 
                   (id_kepala, id_barang, stok_awal, spesifikasi, jumlah_minta, kondisi, status, tgl_permintaan, keterangan_kepala) 
                   VALUES 
                   ('$id_kepala', '$id_barang', '$stok_awal', '$spesifikasi', '$jumlah_minta', '$kondisi', 'pending', NOW(), '$keterangan')";
    
    if (mysqli_query($conn, $sql_tambah)) {
        header("Location: ../lab/kebutuhan.php?status=success&msg=Permintaan dikirim");
    } else {
        header("Location: ../lab/kebutuhan.php?status=error&msg=" . urlencode(mysqli_error($conn)));
    }
    exit;
}

// --- 4. LOGIKA LAPOR PEMAKAIAN ---
if (isset($_POST['lapor_pakai'])) {
    // 1. Tangkap input dari form (name="id_praktek")
    // Formatnya adalah "id_praktek|kode_bahan" (contoh: "12|ELK-001")
    $data_input = explode('|', $_POST['id_praktek']);
    
    // 2. Pisahkan data
    $id_praktek = mysqli_real_escape_string($conn, $data_input[0]);
    $kode_bahan = mysqli_real_escape_string($conn, $data_input[1]);
    
    $jumlah_pakai = (int)$_POST['jumlah_pakai'];
    $id_lab = $_SESSION['id_lab'];

    mysqli_begin_transaction($conn);
    
    try {
        // 3. Simpan riwayat pemakaian
        // id_distribusi kita isi '0' karena pemakaian kini memotong stok global, bukan batch tertentu
        $sql_pakai = "INSERT INTO pemakaian_lab (id_distribusi, kode_distribusi, id_praktek, id_lab, jumlah_pakai, tgl_pakai) 
                      VALUES ('0', '$kode_bahan', '$id_praktek', '$id_lab', '$jumlah_pakai', NOW())";
        
        // 4. Potong Stok langsung di tabel bahan_praktek
        $sql_stok = "UPDATE bahan_praktek SET stok = stok - $jumlah_pakai WHERE id_praktek = '$id_praktek'";
        
        if (mysqli_query($conn, $sql_pakai) && mysqli_query($conn, $sql_stok)) {
            mysqli_commit($conn);
            $_SESSION['alert'] = 'sukses_pakai';
            header("Location: ../lab/pemakaian.php?status=success");
        } else {
            throw new Exception("Gagal update stok pemakaian: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: ../lab/pemakaian.php?status=error");
    }
    exit;
}
// --- 5. HAPUS PEMAKAIAN ---
// --- 5. HAPUS PEMAKAIAN ---
if (isset($_GET['hapus_pakai'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus_pakai']);

    mysqli_begin_transaction($conn);
    try {
        // 1. Ambil data pemakaian untuk tahu berapa jumlah yang harus dikembalikan
        $q_pakai = mysqli_query($conn, "SELECT id_praktek, jumlah_pakai FROM pemakaian_lab WHERE id_pemakaian = '$id' FOR UPDATE");
        $d_pakai = mysqli_fetch_assoc($q_pakai);
        
        if ($d_pakai) {
            $id_praktek_batal = $d_pakai['id_praktek'];
            $jumlah_batal = $d_pakai['jumlah_pakai'];

            // 2. Kembalikan stok ke bahan_praktek (Ditambahkan kembali)
            $sql_kembalikan_stok = "UPDATE bahan_praktek SET stok = stok + $jumlah_batal WHERE id_praktek = '$id_praktek_batal'";
            mysqli_query($conn, $sql_kembalikan_stok);
            
            // 3. Hapus log pemakaian dari riwayat
            $sql_hapus = "DELETE FROM pemakaian_lab WHERE id_pemakaian = '$id'";
            mysqli_query($conn, $sql_hapus);
            
            mysqli_commit($conn);
            $_SESSION['alert'] = 'sukses_hapus';
        } else {
            throw new Exception("Data pemakaian tidak ditemukan.");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['alert'] = 'gagal_hapus';
    }
    
    // Kembali ke halaman pemakaian
    header("Location: ../lab/pemakaian.php");
    exit;
}

// --- 6. TAMBAH STOK AWAL (MASSAL) ---
if (isset($_POST['kirimm'])) {
    $id_barang_array = $_POST['id_barang'] ?? [];
    $stok_fisik_array = $_POST['stok_fisik_lab'] ?? [];
    $id_user = $_SESSION['id_user']; 
    $tgl = date('Y-m-d H:i:s');

    if (empty($id_barang_array)) {
        header("Location: ../lab/stok.php?status=empty");
        exit;
    }

    foreach ($id_barang_array as $key => $id_barang_raw) {
        $id_barang = mysqli_real_escape_string($conn, $id_barang_raw);
        $stok_fisik = (int)($stok_fisik_array[$key] ?? 0);

        $query = "INSERT INTO permintaan_bahan (id_barang, id_user, stok_saat_ini, tgl_permintaan, status) 
                  VALUES ('$id_barang', '$id_user', '$stok_fisik', '$tgl', 'pending')";
        
        mysqli_query($conn, $query);
    }

    header("Location: ../lab/stok.php?status=success");
    exit;
}

// --- 6. LOGIKA MENGUNCI PEMAKAIAN ---
if (isset($_GET['kunci_pakai'])) {
    $id = mysqli_real_escape_string($conn, $_GET['kunci_pakai']);
    
    // Update status menjadi terkunci (1)
    if (mysqli_query($conn, "UPDATE pemakaian_lab SET status_kunci = '1' WHERE id_pemakaian = '$id'")) {
        $_SESSION['alert'] = 'sukses_kunci'; // Pastikan Anda punya alert ini di UI depan jika mau memunculkan notif
    } else {
        $_SESSION['alert'] = 'gagal_kunci';
    }
    
    header("Location: ../lab/pemakaian.php");
    exit;
}
?>