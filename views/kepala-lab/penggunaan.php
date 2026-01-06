<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

if (isset($_POST['lapor_pakai'])) {
    $id_dist = $_POST['id_distribusi'];
    $pakai = $_POST['jumlah_pakai'];
    
    // Update status distribusi menjadi 'digunakan' dan catat jumlahnya
    // Dalam sistem nyata, Anda mungkin ingin membuat tabel 'pemakaian' terpisah
    mysqli_query($conn, "UPDATE distribusi SET status='digunakan', jumlah = jumlah - $pakai WHERE id_distribusi = '$id_dist'");
    echo "<script>alert('Pemakaian berhasil dicatat!'); window.location='index.php';</script>";
}

include "../../includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include "../../includes/sidebar.php"; ?>
        <main class="col-md-10 p-4">
            <h3>Input Penggunaan Bahan</h3>
            <div class="col-md-5">
                <form method="POST" class="card p-4 shadow-sm">
                    <input type="hidden" name="id_distribusi" value="<?= $_GET['id']; ?>">
                    <div class="mb-3">
                        <label>Jumlah yang Digunakan</label>
                        <input type="number" name="jumlah_pakai" class="form-control" required>
                    </div>
                    <button type="submit" name="lapor_pakai" class="btn btn-custom-blue">Kirim Laporan Penggunaan</button>
                </form>
            </div>
        </main>
    </div>
</div>