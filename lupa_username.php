<?php
session_start();
include "config/database.php"; 

$pesan_sistem = ""; 
$alert_class = "";
$icon = "";

if (isset($_POST['cek_username'])) {
    $role_input = $_POST['role'];
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $pin = mysqli_real_escape_string($conn, $_POST['pin']);

    if ($role_input == 'admin' || $role_input == 'admin-acc') {
        $query = mysqli_query($conn, "SELECT username, nama_lengkap FROM users WHERE nip='$nip' AND pin_pemulihan='$pin' AND role='$role_input'");
        $nama_key = 'nama_lengkap';
    } else {
        $query = mysqli_query($conn, "SELECT username, nama_kepala FROM kepala_lab WHERE nip='$nip' AND pin_pemulihan='$pin'");
        $nama_key = 'nama_kepala';
    }

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $username_ditemukan = $data['username'];
        $nama_pemilik = $data[$nama_key];
        
        // Pesan Sukses Menampilkan Username
        $pesan_sistem = "Data ditemukan! Halo <b>$nama_pemilik</b>,<br>Username Anda adalah:<br><br>
                         <span class='badge bg-success fs-5 mt-2 shadow-sm border border-light text-white p-3'>$username_ditemukan</span>";
        $alert_class = "alert-success";
        $icon = "bi-check-circle-fill";
    } else {
        $pesan_sistem = "Gagal! NIP atau PIN Pemulihan Anda salah.";
        $alert_class = "alert-danger";
        $icon = "bi-x-circle";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Username | SIM Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --poltek-navy: #001f3f; --poltek-gold: #FFD700; --poltek-gold-dark: #b8860b; }
        body { background: radial-gradient(circle at center, #003366 0%, #001f3f 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .login-container { max-width: 950px; width: 95%; background: white; border-radius: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; display: flex; border: 1px solid rgba(255, 215, 0, 0.3); }
        .login-visual { background: linear-gradient(135deg, rgba(0, 31, 63, 0.9), rgba(0, 51, 102, 0.8)), url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=1986&auto=format&fit=crop'); background-size: cover; background-position: center; width: 45%; padding: 40px; color: white; display: flex; flex-direction: column; justify-content: center; text-align: center; position: relative; }
        .login-visual::after { content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 10px; background: var(--poltek-gold); }
        .login-visual img { width: 120px; margin: 0 auto 25px; filter: drop-shadow(0 0 10px rgba(255,215,0,0.5)); }
        .login-form { width: 55%; padding: 50px 60px; background: #fff; }
        .welcome-title { color: var(--poltek-navy); font-weight: 800; font-size: 1.8rem; }
        .role-selector { display: flex; gap: 10px; margin-bottom: 25px; }
        .role-option { flex: 1; position: relative; }
        .role-option input { position: absolute; opacity: 0; cursor: pointer; }
        .role-card { display: flex; flex-direction: column; align-items: center; padding: 12px 5px; border: 2px solid #eee; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; text-align: center; height: 100%; }
        .role-card i { font-size: 1.3rem; color: #ccc; margin-bottom: 5px; }
        .role-card span { font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; line-height: 1.1; }
        .role-option input:checked + .role-card { border-color: var(--poltek-gold); background: rgba(255, 215, 0, 0.05); }
        .role-option input:checked + .role-card i, .role-option input:checked + .role-card span { color: var(--poltek-navy); }
        .form-label { font-weight: 700; color: var(--poltek-navy); font-size: 0.85rem; margin-bottom: 5px; }
        .input-group-text { background: transparent; border-right: none; color: var(--poltek-gold-dark); }
        .form-control { border-left: none; padding: 12px; border-radius: 0 10px 10px 0; }
        .form-control:focus { border-color: #dee2e6; box-shadow: none; }
        .btn-login { background: var(--poltek-navy); color: var(--poltek-gold); border: 2px solid var(--poltek-gold); padding: 15px; border-radius: 12px; font-weight: 800; width: 100%; transition: 0.4s; letter-spacing: 1px; margin-top: 15px; }
        .btn-login:hover { background: var(--poltek-gold); color: var(--poltek-navy); box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3); }
        @media (max-width: 768px) { .login-visual { display: none; } .login-form { width: 100%; padding: 40px 30px; } }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-visual">
        <img src="images/logo.png" alt="Logo" onerror="this.src='https://upload.wikimedia.org/wikipedia/id/0/05/Logo_Politeknik_ATI_Makassar.png'">
        <h2 class="fw-bold text-white mb-2">INVENTARIS</h2>
        <p class="text-white-50 small px-4">Sistem Informasi Manajemen Laboratorium & Bahan Praktek Terpadu</p>
    </div>

    <div class="login-form">
        <div class="mb-4 text-center">
            <h2 class="welcome-title mb-1">Cari Username</h2>
            <p class="text-muted small mt-2">Lupa username Anda? Lacak akun menggunakan NIP dan PIN Pemulihan.</p>
        </div>

        <?php if($pesan_sistem != ""): ?>
            <div class="alert <?= $alert_class; ?> border-0 small py-3 mb-4 d-flex align-items-start rounded-3">
                <i class="bi <?= $icon; ?> me-3 fs-4"></i> 
                <div><?= $pesan_sistem; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="text-center mb-2">
                <label class="form-label text-uppercase mb-0">Peran Akun Anda</label>
            </div>
            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="admin" required checked>
                    <div class="role-card"><i class="bi bi-shield-lock"></i><span>Admin</span></div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="admin-acc" required>
                    <div class="role-card"><i class="bi bi-person-gear"></i><span>Admin ACC</span></div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="kepala_lab" required>
                    <div class="role-card"><i class="bi bi-person-workspace"></i><span>Kepala Lab</span></div>
                </label>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label text-uppercase">NIP Anda</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="nip" class="form-control" placeholder="1980..." required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-uppercase text-danger">PIN Pemulihan</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-123"></i></span>
                        <input type="password" name="pin" class="form-control" placeholder="6 Digit" maxlength="6" required>
                    </div>
                </div>
            </div>

            <button type="submit" name="cek_username" class="btn btn-login">
                Lacak Username <i class="bi bi-search ms-2"></i>
            </button>
            
            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none text-muted small fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke halaman Login
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>