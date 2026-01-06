<?php
include "../../config/database.php";

// 1. Ambil Parameter
$id_lab  = isset($_GET['id_lab']) ? mysqli_real_escape_string($conn, $_GET['id_lab']) : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit   = 5; 
$offset  = ($page - 1) * $limit;

if (empty($id_lab)) {
    echo '<div class="text-center p-5 text-danger fw-bold">ID Lab tidak ditemukan.</div>';
    exit;
}

// 2. Query Filter & Pagination
$where_clause = "WHERE d.id_lab = '$id_lab'";
if ($keyword != '') {
    $where_clause .= " AND (b.nama_bahan LIKE '%$keyword%' OR d.kode_distribusi LIKE '%$keyword%')";
}

$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM distribusi_lab d JOIN bahan_praktek b ON d.id_praktek = b.id_praktek $where_clause");
$total_data  = mysqli_fetch_assoc($total_query)['total'];
$total_page  = ceil($total_data / $limit);

// 3. Query Data (Pastikan kolom status ditarik)
$sql = "SELECT d.*, b.nama_bahan, b.satuan 
        FROM distribusi_lab d 
        JOIN bahan_praktek b ON d.id_praktek = b.id_praktek 
        $where_clause 
        ORDER BY d.id_distribusi DESC 
        LIMIT $limit OFFSET $offset";
$query = mysqli_query($conn, $sql);

// 4. Render Output
if (mysqli_num_rows($query) > 0) {
    echo '
    <div class="table-responsive rounded-3 overflow-hidden border">
        <table class="table table-hover align-middle mb-0 custom-table">
            <thead>
                <tr>
                    <th class="text-center py-3" style="width: 50px;">#</th>
                    <th>DETAIL MATERIAL</th>
                    <th>KODE DISTRIBUSI</th>
                    <th>KUANTITAS</th>
                    <th>STATUS & WAKTU</th>
                    <th class="text-center">AKSI</th>
                </tr>
            </thead>
            <tbody>';
    
    $no = $offset + 1;
    while ($row = mysqli_fetch_assoc($query)) {
        $tgl = date('d M, Y', strtotime($row['tanggal_distribusi']));
        $nama_js = addslashes($row['nama_bahan']);
        $initial = strtoupper(substr($row['nama_bahan'], 0, 1));

        // LOGIKA STATUS DINAMIS
        if ($row['status'] == 'diterima') {
            $status_badge = "<span class='badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill' style='font-size: 0.65rem;'><i class='bi bi-check-circle-fill me-1'></i>Diterima Lab</span>";
            $status_desc = "<div class='text-success fw-bold' style='font-size: 0.7rem;'><i class='bi bi-check-all'></i> Konfirmasi Selesai</div>";
        } else {
            $status_badge = "<span class='badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill' style='font-size: 0.65rem;'><i class='bi bi-truck me-1'></i>Proses Kirim</span>";
            $status_desc = "<div class='text-muted' style='font-size: 0.7rem;'><i class='bi bi-clock-history'></i> Berhasil Dikirim</div>";
        }
        
        echo "
        <tr>
            <td class='text-center'><span class='text-muted small'>{$no}</span></td>
            <td>
                <div class='d-flex align-items-center'>
                    <div class='avatar-sm-table me-3'>{$initial}</div>
                    <div>
                        <div class='fw-bold text-navy'>{$row['nama_bahan']}</div>
                        {$status_badge}
                    </div>
                </div>
            </td>
            <td>
                <code class='kode-label'>{$row['kode_distribusi']}</code>
            </td>
            <td>
                <span class='badge bg-navy-subtle text-navy px-3 py-2 rounded-pill'>
                    <strong>{$row['jumlah']}</strong> {$row['satuan']}
                </span>
            </td>
            <td>
                <div class='small fw-semibold text-dark'>{$tgl}</div>
                {$status_desc}
            </td>
            <td class='text-center'>
                <div class='d-flex justify-content-center gap-2'>";
                
                // Jika status sudah diterima, Admin tidak bisa hapus/edit (opsional, disarankan)
                if ($row['status'] != 'diterima') {
                    echo "
                    <button class='btn btn-icon btn-edit' title='Ubah Data'
                            onclick='openEditDist(\"{$row['id_distribusi']}\", \"{$nama_js}\", \"{$row['jumlah']}\")'>
                        <i class='bi bi-pencil-square'></i>
                    </button>
                    <button class='btn btn-icon btn-delete' title='Hapus Data'
                            onclick='hapusDistribusi({$row['id_distribusi']})'>
                        <i class='bi bi-trash3-fill'></i>
                    </button>";
                } else {
                    echo "<span class='text-success small fw-bold'><i class='bi bi-lock-fill'></i> Locked</span>";
                }

        echo "
                </div>
            </td>
        </tr>";
        $no++;
    }
    echo '</tbody></table></div>';

    // 5. Pagination Modern
    if ($total_page > 1) {
        echo '<nav class="mt-4"><ul class="pagination pagination-sm justify-content-end gap-2">';
        for ($i = 1; $i <= $total_page; $i++) {
            $activeClass = ($i == $page) ? 'active' : '';
            echo "<li class='page-item $activeClass'>
                    <a class='page-link border-0 rounded-circle shadow-sm text-center' 
                       style='width: 35px; height: 35px; line-height: 25px;'
                       href='javascript:void(0)' 
                       onclick='loadDistribusi(\"$id_lab\", $i, \"$keyword\")'>$i</a>
                  </li>";
        }
        echo '</ul></nav>';
    }
} else {
    echo '
    <div class="text-center py-5 bg-light rounded-4 border border-dashed">
        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" style="width: 80px; opacity: 0.5;">
        <p class="text-muted mt-3 fw-semibold">Tidak ada data distribusi ditemukan untuk pencarian ini.</p>
    </div>';
}
?>

<style>
    /* Tambahan warna untuk status */
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
    .text-success { color: #198754 !important; }
    .text-warning { color: #ffc107 !important; }

    :root {
        --navy: #002b5c;
        --gold: #FFD700;
        --soft-bg: #f8fafc;
    }

    .custom-table thead {
        background-color: var(--navy);
        color: white;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .custom-table tbody tr:hover {
        background-color: #f1f5f9 !important;
        transform: scale(1.002);
    }

    .avatar-sm-table {
        width: 35px;
        height: 35px;
        background: var(--navy);
        color: var(--gold);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .kode-label {
        background: #f8f9fa;
        color: #475569;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
    }

    .bg-navy-subtle {
        background-color: rgba(0, 43, 92, 0.1);
        color: var(--navy);
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: 0.2s;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .btn-edit { color: #d97706; border: 1px solid #fcd34d; }
    .btn-edit:hover { background: #d97706; color: white; }

    .btn-delete { color: #dc2626; border: 1px solid #fecaca; }
    .btn-delete:hover { background: #dc2626; color: white; }

    .pagination .page-item.active .page-link {
        background-color: var(--navy) !important;
        color: var(--gold) !important;
    }
</style>