<?php
session_start();
include "../../config/database.php"; 

$id_praktek = isset($_GET['id_praktek']) ? mysqli_real_escape_string($conn, $_GET['id_praktek']) : '';
$id_lab_user = $_SESSION['id_lab'] ?? '';

if (empty($id_praktek)) {
    echo "<div class='alert alert-danger'>ID Bahan tidak valid.</div>";
    exit;
}

// QUERY UNION: Tambahkan pemanggilan kolom 'jumlah' (sebagai jumlah_asal) untuk perbandingan
$sql_timeline = "
    SELECT 
        'masuk' as jenis_transaksi,
        COALESCE(tanggal_diterima, tanggal_distribusi) as tgl_transaksi,
        jumlah_diterima as jumlah,
        jumlah as jumlah_asal, -- Membaca jumlah asli yang dikirim admin
        keterangan,
        kode_distribusi as referensi
    FROM distribusi_lab 
    WHERE id_praktek = '$id_praktek' AND id_lab = '$id_lab_user' AND status = 'diterima'
    
    UNION ALL
    
    SELECT 
        'keluar' as jenis_transaksi,
        tgl_pakai as tgl_transaksi,
        jumlah_pakai as jumlah,
        jumlah_pakai as jumlah_asal, -- Samakan strukturnya
        'Digunakan untuk praktek' as keterangan,
        kode_distribusi as referensi
    FROM pemakaian_lab 
    WHERE id_praktek = '$id_praktek' AND id_lab = '$id_lab_user'
    
    ORDER BY tgl_transaksi DESC
";

$query_timeline = mysqli_query($conn, $sql_timeline);
?>

<div class="table-responsive">
    <table id="tabelMutasi" class="table table-bordered table-hover align-middle w-100" style="font-size: 0.9rem;">
        <thead class="table-light text-center text-uppercase text-muted">
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Referensi</th>
                <th width="15%">Status</th>
                <th width="35%">Keterangan</th>
                <th width="15%">QTY</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($query_timeline) > 0) :
                while ($row = mysqli_fetch_assoc($query_timeline)) : 
                    $is_masuk = ($row['jenis_transaksi'] == 'masuk');
                    
                    // Konversi string ke angka
                    $jml = (int)$row['jumlah'];
                    $jml_asal = (int)$row['jumlah_asal'];

                    // Logika Deteksi Status
                    $is_ditolak = ($is_masuk && $jml == 0); 
                    $is_sebagian = ($is_masuk && $jml > 0 && $jml < $jml_asal); 

                    // Pewarnaan Baris & Teks
                    $row_class = '';
                    if ($is_ditolak) {
                        $row_class = 'table-danger'; // Merah (Ditolak)
                        $badge_class = 'bg-danger text-white border-danger';
                        $text_class = 'text-danger';
                        $icon = '<i class="bi bi-x-octagon-fill me-1"></i> DITOLAK';
                        $sign = '';
                    } elseif ($is_sebagian) {
                        $row_class = 'table-warning'; // Kuning (Sebagian)
                        $badge_class = 'bg-warning text-dark border-warning';
                        $text_class = 'text-dark';
                        $icon = '<i class="bi bi-exclamation-triangle-fill me-1"></i> SEBAGIAN';
                        $sign = '+';
                    } elseif ($is_masuk) {
                        $badge_class = 'bg-success-subtle text-success border-success';
                        $text_class = 'text-success';
                        $icon = '<i class="bi bi-box-arrow-in-down me-1"></i> FULL';
                        $sign = '+';
                    } else {
                        $badge_class = 'bg-danger-subtle text-danger border-danger';
                        $text_class = 'text-danger';
                        $icon = '<i class="bi bi-box-arrow-up me-1"></i> KELUAR';
                        $sign = '-';
                    }
                    
                    $waktu = !empty($row['tgl_transaksi']) ? strtotime($row['tgl_transaksi']) : time();
            ?>
                <tr class="<?= $row_class ?>">
                    <!-- Biarkan kosong, akan diisi angka urut otomatis oleh DataTables JS -->
                    <td class="text-center text-muted fw-bold"></td>
                    
                    <td class="text-center">
                        <span class="d-none"><?= $row['tgl_transaksi'] ?></span>
                        <div class="fw-bold text-dark"><?= date('d M Y', $waktu) ?></div>
                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', $waktu) ?></small>
                    </td>
                    <td class="text-center">
                        <code class="text-navy bg-light px-2 py-1 rounded border shadow-sm"><?= htmlspecialchars($row['referensi'] ?? '-') ?></code>
                    </td>
                    <td class="text-center">
                        <span class="badge border <?= $badge_class ?> rounded-pill px-3 py-2 w-100">
                            <?= $icon ?>
                        </span>
                    </td>
                    <td class="text-wrap" style="line-height: 1.4;">
                        <?php 
                            $ket = htmlspecialchars($row['keterangan'] ?? '');
                            echo !empty($ket) ? $ket : '-';
                        ?>
                    </td>
                    <td class="text-center fw-bold fs-5 <?= $text_class ?>">
                        <?= $sign ?> <?= $jml ?>
                    </td>
                </tr>
            <?php 
                endwhile; 
            endif;
            ?>
        </tbody>
    </table>
</div>