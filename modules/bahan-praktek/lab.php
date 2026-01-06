<?php
include "../../config/database.php";
include "../../config/auth.php";
checkLogin();

if (isset($_POST['tambah'])) {
    $id_jurusan = $_POST['id_jurusan'];
    $nama_lab = $_POST['nama_lab'];
    mysqli_query($conn, "INSERT INTO lab (id_jurusan, nama_lab) VALUES ('$id_jurusan', '$nama_lab')");
}

include "../../includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include "../../includes/sidebar.php"; ?>
        <main class="col-md-10 p-4">
            <h2>Kelola Data Lab</h2>
            <form method="POST" class="mb-4 row g-2">
                <div class="col-md-3">
                    <select name="id_jurusan" class="form-select" required>
                        <option value="">-- Pilih Jurusan --</option>
                        <?php
                        $jur = mysqli_query($conn, "SELECT * FROM jurusan");
                        while($j = mysqli_fetch_assoc($jur)) echo "<option value='".$j['id_jurusan']."'>".$j['nama_jurusan']."</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="nama_lab" class="form-control" placeholder="Nama Lab" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="tambah" class="btn btn-custom-blue">Simpan Lab</button>
                </div>
            </form>

            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Jurusan</th>
                        <th>Nama Lab</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT lab.*, jurusan.nama_jurusan FROM lab JOIN jurusan ON lab.id_jurusan = jurusan.id_jurusan");
                    while($l = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td><?= $l['nama_jurusan']; ?></td>
                        <td><?= $l['nama_lab']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</div>