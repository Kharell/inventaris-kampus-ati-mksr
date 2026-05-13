<?php
session_start();
include "../../config/database.php"; 

$id_praktek = isset($_GET['id_praktek']) ? mysqli_real_escape_string($conn, $_GET['id_praktek']) : '';
$id_lab_user = $_SESSION['id_lab'] ?? '';

if (empty($id_praktek)) {
    echo "<div class='alert alert-danger'>ID Bahan tidak valid.</div>";
    exit;
}

<<<<<<< HEAD
// ==============================================================
// INI ADALAH KODE YANG SEMPAT HILANG ($query_timeline)
// ==============================================================
=======
// QUERY UNION: Tambahkan pemanggilan kolom 'jumlah' (sebagai jumlah_asal) untuk perbandingan
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
$sql_timeline = "
    SELECT 
        'masuk' as jenis_transaksi,
        COALESCE(tanggal_diterima, tanggal_distribusi) as tgl_transaksi,
        jumlah_diterima as jumlah,
<<<<<<< HEAD
        jumlah as jumlah_asal,
        keterangan,
        kode_distribusi as referensi
    FROM distribusi_lab 
    WHERE id_praktek = '$id_praktek' 
    AND id_lab = '$id_lab_user' 
    AND status = 'diterima'
    AND jumlah_diterima > 0 
=======
        jumlah as jumlah_asal, -- Membaca jumlah asli yang dikirim admin
        keterangan,
        kode_distribusi as referensi
    FROM distribusi_lab 
    WHERE id_praktek = '$id_praktek' AND id_lab = '$id_lab_user' AND status = 'diterima'
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
    
    UNION ALL
    
    SELECT 
        'keluar' as jenis_transaksi,
        tgl_pakai as tgl_transaksi,
        jumlah_pakai as jumlah,
<<<<<<< HEAD
        jumlah_pakai as jumlah_asal,
        'Digunakan untuk praktek' as keterangan,
        kode_distribusi as referensi
    FROM pemakaian_lab 
    WHERE id_praktek = '$id_praktek' 
    AND id_lab = '$id_lab_user'
=======
        jumlah_pakai as jumlah_asal, -- Samakan strukturnya
        'Digunakan untuk praktek' as keterangan,
        kode_distribusi as referensi
    FROM pemakaian_lab 
    WHERE id_praktek = '$id_praktek' AND id_lab = '$id_lab_user'
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
    
    ORDER BY tgl_transaksi DESC
";

<<<<<<< HEAD
// Eksekusi Kueri
$query_timeline = mysqli_query($conn, $sql_timeline);

// Ambil Nama Bahan untuk ditaruh di Kop Cetakan PDF
$q_nama = mysqli_query($conn, "SELECT nama_bahan, kode_bahan FROM bahan_praktek WHERE id_praktek = '$id_praktek'");
$r_nama = mysqli_fetch_assoc($q_nama);
$nama_bahan_cetak = $r_nama['nama_bahan'] ?? 'Material';
$kode_bahan_cetak = $r_nama['kode_bahan'] ?? '-';
// ==============================================================


// 1. Deteksi Protokol & URL Dinamis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$current_path = $_SERVER['SCRIPT_NAME']; 
$parts = explode('/', trim($current_path, '/'));
$project_folder = $parts[0]; 
$base_url = $protocol . $host . "/" . $project_folder . "/";
$logo_url = $base_url . "images/images.png";
?>

<!-- Tombol Cetak PDF -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Transaksi</h6>
    <button onclick="cetakMutasiPDF()" class="btn btn-warning fw-bold shadow-sm rounded-3 text-dark px-4 border border-warning">
        <i class="bi bi-printer-fill me-2"></i>Cetak Laporan PDF
    </button>
</div>

<!-- Area yang akan dicetak -->
<div id="areaCetakMutasi">
    
    <!-- KOP SURAT (Hanya muncul saat dicetak ke PDF) -->
    <div class="cetak-kop d-none">
        <div style="display: flex; align-items: center; border-bottom: 4px solid #001f3f; padding-bottom: 15px; margin-bottom: 25px;">
            <td class="logo-container" style="width: 150px; text-align: left; vertical-align: middle;">
                <img src="<?= $logo_url ?>" width="110" alt="Logo">
            </td>
            
            <div style="text-align: center; flex-grow: 1;">
                <h5 style="margin: 0; font-weight: bold; color: #000; letter-spacing: 1px; font-size: 16px;">KEMENTERIAN PERINDUSTRIAN REPUBLIK INDONESIA</h5>
                <h3 style="margin: 5px 0; font-weight: 800; color: #001f3f; letter-spacing: 2px; font-size: 22px;">POLITEKNIK ATI MAKASSAR</h3>
                <p style="margin: 0; font-size: 13px; color: #333;">Jl. Sunu No.220, Suangga, Kec. Tallo, Kota Makassar, Sulawesi Selatan 90211</p>
            </div>
        </div>
        <div style="text-align: center; margin-bottom: 25px;">
            <h4 style="font-weight: bold; text-decoration: underline; margin-bottom: 5px; color: #000; font-size: 18px;">LAPORAN RIWAYAT PEMBELIAN MATERIAL</h4>
            <p style="margin: 0; font-weight: bold; color: #555; font-size: 14px;">Bahan: <?= htmlspecialchars($nama_bahan_cetak) ?> (<?= htmlspecialchars($kode_bahan_cetak) ?>)</p>
            <p style="margin: 0; font-size: 12px; color: #666;">Dicetak pada: <?= date('d F Y, H:i') ?> WITA</p>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="table-responsive">
        <table id="tabelMutasi" class="table table-bordered table-hover align-middle w-100" style="font-size: 0.9rem;">
            <thead class="text-center text-uppercase" style="background-color: #f1f5f9; color: #475569; font-weight: bold;">
                <tr>
                    <th width="5%" class="py-3">No</th>
                    <th width="15%" class="py-3">Tanggal</th>
                    <th width="15%" class="py-3">Referensi</th>
                    <th width="15%" class="py-3">Status</th>
                    <th width="35%" class="py-3">Keterangan</th>
                    <th width="15%" class="py-3">QTY</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                // Sekarang variabel $query_timeline ini sudah ada isinya dari atas!
                if ($query_timeline && mysqli_num_rows($query_timeline) > 0) :
                    while ($row = mysqli_fetch_assoc($query_timeline)) : 
                        $is_masuk = ($row['jenis_transaksi'] == 'masuk');
                        $jml = (int)$row['jumlah'];
                        $jml_asal = (int)$row['jumlah_asal'];

                        $is_ditolak = ($is_masuk && $jml == 0); 
                        $is_sebagian = ($is_masuk && $jml > 0 && $jml < $jml_asal); 

                        $row_class = '';
                        if ($is_ditolak) {
                            $row_class = 'table-danger'; 
                            $badge_class = 'bg-danger text-white border-danger';
                            $text_class = 'text-danger';
                            $icon = '<i class="bi bi-x-octagon-fill me-1"></i> DITOLAK';
                            $sign = '';
                        } elseif ($is_sebagian) {
                            $row_class = 'table-warning'; 
                            $badge_class = 'bg-warning text-dark border-warning';
                            $text_class = 'text-dark';
                            $icon = '<i class="bi bi-exclamation-triangle-fill me-1"></i> SEBAGIAN';
                            $sign = '+';
                        } elseif ($is_masuk) {
                            $badge_class = 'bg-success text-white border-success';
                            $text_class = 'text-success';
                            $icon = '<i class="bi bi-box-arrow-in-down me-1"></i> FULL';
                            $sign = '+';
                        } else {
                            $badge_class = 'bg-danger text-white border-danger';
                            $text_class = 'text-danger';
                            $icon = '<i class="bi bi-box-arrow-up me-1"></i> KELUAR';
                            $sign = '-';
                        }
                        
                        $waktu = !empty($row['tgl_transaksi']) ? strtotime($row['tgl_transaksi']) : time();
                ?>
                    <tr class="<?= $row_class ?>">
                        <td class="text-center text-muted fw-bold"><?= $no++ ?></td>
                        <td class="text-center">
                            <span class="d-none"><?= $row['tgl_transaksi'] ?></span>
                            <div class="fw-bold text-dark"><?= date('d M Y', $waktu) ?></div>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', $waktu) ?></small>
                        </td>
                        <td class="text-center">
                            <code class="text-primary bg-light px-2 py-1 rounded border shadow-sm" style="font-size: 0.85rem;"><?= htmlspecialchars($row['referensi'] ?? '-') ?></code>
                        </td>
                        <td class="text-center">
                            <span class="badge border <?= $badge_class ?> rounded-pill px-3 py-2 w-100 shadow-sm" style="letter-spacing: 0.5px;">
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
                else:
                ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted fw-bold">Belum ada riwayat transaksi material.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SCRIPT CETAK PDF SUPER BERSIH -->
<script>
function cetakMutasiPDF() {
    var printContents = document.getElementById("areaCetakMutasi").innerHTML;
    var printWindow = window.open('', '', 'height=800,width=1000');
    
    printWindow.document.write('<!DOCTYPE html><html><head><title>Cetak Mutasi Material</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">');
    
    printWindow.document.write('<style>');
    printWindow.document.write(`
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        /* Reset Body untuk Kertas Putih Bersih */
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            padding: 30px; 
            color: #000; 
            background-color: #fff !important;
        }
        
        /* Tampilkan Kop Surat yang sebelumnya d-none */
        .cetak-kop { display: block !important; } 
        
        /* ========================================================
           KUNCI UTAMA: HILANGKAN SEMUA ELEMEN UI YANG MENGGANGGU
           ======================================================== */
        .dataTables_wrapper .dataTables_filter,  /* Kotak Pencarian DataTables */
        .dataTables_wrapper .dataTables_length,  /* Dropdown Limit DataTables */
        .dataTables_wrapper .dataTables_info,    /* Teks Info "Showing 1 to 10..." */
        .dataTables_wrapper .dataTables_paginate,/* Tombol Prev/Next DataTables */
        .pagination,                             /* Pagination Bawaan Bootstrap */
        .no-print,                               /* Class Khusus (jika ada) */
        button,                                  /* Semua tombol */
        input[type="search"]                     /* Semua input pencarian */
        { 
            display: none !important; 
        }

        /* Rapikan Tabel agar muat di kertas A4 */
        .table-responsive { overflow: visible !important; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
        .table th, .table td { border: 1px solid #000 !important; padding: 10px; vertical-align: middle; }
        .table th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; color: #000 !important;}
        
        /* Pertahankan Warna Status Baris Tabel */
        .table-danger, .table-danger td { background-color: #fee2e2 !important; -webkit-print-color-adjust: exact; }
        .table-warning, .table-warning td { background-color: #fef3c7 !important; -webkit-print-color-adjust: exact; }
        
        /* Penyesuaian Badge dan Ikon */
        .badge { border: 1px solid #000 !important; font-size: 11px; color: #000 !important; background: transparent !important; padding: 5px 10px; border-radius: 50px; }
        .text-success { color: #15803d !important; }
        .text-danger { color: #b91c1c !important; }
        .text-dark { color: #000 !important; }
        .shadow-sm { box-shadow: none !important; } /* Hilangkan bayangan/shadow */
    `);
    printWindow.document.write('</style></head><body>');
    
    printWindow.document.write(printContents);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    // Waktu jeda agar CSS termuat sempurna sebelum dialog Print ditarik
    setTimeout(function() {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }, 800);
}
</script>
=======
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
>>>>>>> 75ac65b1f3ece3b3423fc23e593fd1379ad3113e
