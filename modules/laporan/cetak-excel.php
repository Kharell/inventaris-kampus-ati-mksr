<?php
include "../../config/database.php";

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Inventaris_".date('Ymd').".xls");
?>

<h3>Laporan Persediaan Barang Kampus</h3>
<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th>Satuan</th>
            <th>Tanggal Masuk</th>
            <th>Lab/Lokasi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $query = mysqli_query($conn, "SELECT barang.*, lab.nama_lab FROM barang 
                                      LEFT JOIN lab ON barang.id_lab = lab.id_lab");
        while($d = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $d['nama_barang']; ?></td>
            <td><?= $d['kategori']; ?></td>
            <td><?= $d['stok']; ?></td>
            <td><?= $d['satuan']; ?></td>
            <td><?= $d['tgl_masuk']; ?></td>
            <td><?= ($d['nama_lab']) ? $d['nama_lab'] : 'Gudang Umum'; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>