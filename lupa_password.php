<?php
session_start();
include "config/database.php"; 

$error = ""; 
$alert_class = "";
$icon = "";

if (isset($_POST['reset'])) {
    $role_input = $_POST['role'];
    $username   = mysqli_real_escape_string($conn, $_POST['username']);
    $pin        = mysqli_real_escape_string($conn, $_POST['pin']);
    $pass_baru  = $_POST['password_baru'];
    $pass_konf  = $_POST['konfirmasi_password'];

    // 1. Cek apakah password baru dan konfirmasi cocok
    if ($pass_baru !== $pass_konf) {
        $error = "Konfirmasi password tidak cocok dengan password baru!";
        $alert_class = "alert-warning";
        $icon = "bi-exclamation-triangle";
    } else {
        // 2. Cek kecocokan Username & PIN di Database
        $tabel = "";
        $kolom_id = "";

        if ($role_input == 'admin' || $role_input == 'admin-acc') {
            $tabel = "users";
            $kolom_id = "id_user";
            $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND pin_pemulihan='$pin' AND role='$role_input'");
        } else {
            $tabel = "kepala_lab";
            $kolom_id = "id_kepala";
            $query = mysqli_query($conn, "SELECT * FROM kepala_lab WHERE username='$username' AND pin_pemulihan='$pin'");
        }

        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            $id_target = $data[$kolom_id];

            // 3. Hash Password Baru dan Simpan ke Database
            $hashed_password = password_hash($pass_baru, PASSWORD_DEFAULT);
            $update = mysqli_query($conn, "UPDATE $tabel SET password='$hashed_password' WHERE $kolom_id='$id_target'");

            if ($update) {
                // Arahkan ke halaman login dengan notifikasi sukses
                header("Location: login.php?pesan=reset_sukses");
                exit();
            } else {
                $error = "Terjadi kesalahan sistem saat memperbarui password.";
                $alert_class = "alert-danger";
                $icon = "bi-x-circle";
            }
        } else {
            $error = "Gagal! Username tidak ditemukan atau PIN Pemulihan salah.";
            $alert_class = "alert-danger";
            $icon = "bi-shield-x";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | SIM Inventaris</title>
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
        .login-form { width: 55%; padding: 40px 60px; background: #fff; }
        .welcome-title { color: var(--poltek-navy); font-weight: 800; font-size: 1.8rem; }
        .role-selector { display: flex; gap: 10px; margin-bottom: 20px; }
        .role-option { flex: 1; position: relative; }
        .role-option input { position: absolute; opacity: 0; cursor: pointer; }
        .role-card { display: flex; flex-direction: column; align-items: center; padding: 10px 5px; border: 2px solid #eee; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; text-align: center; height: 100%; }
        .role-card i { font-size: 1.2rem; color: #ccc; margin-bottom: 5px; }
        .role-card span { font-size: 0.7rem; font-weight: 700; color: #888; text-transform: uppercase; line-height: 1.1; }
        .role-option input:checked + .role-card { border-color: var(--poltek-gold); background: rgba(255, 215, 0, 0.05); }
        .role-option input:checked + .role-card i, .role-option input:checked + .role-card span { color: var(--poltek-navy); }
        .form-label { font-weight: 700; color: var(--poltek-navy); font-size: 0.8rem; margin-bottom: 5px; }
        .input-group-text { background: transparent; border-right: none; color: var(--poltek-gold-dark); }
        .form-control { border-left: none; padding: 10px; border-radius: 0 10px 10px 0; font-size: 0.9rem;}
        .form-control:focus { border-color: #dee2e6; box-shadow: none; }
        .btn-login { background: var(--poltek-navy); color: var(--poltek-gold); border: 2px solid var(--poltek-gold); padding: 12px; border-radius: 12px; font-weight: 800; width: 100%; transition: 0.4s; letter-spacing: 1px; margin-top: 10px; }
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
            <h2 class="welcome-title mb-1">Reset Password</h2>
            <p class="text-muted small mt-2">Masukkan Username dan PIN Keamanan Anda untuk mereset sandi.</p>
        </div>

        <?php if($error != ""): ?>
            <div class="alert <?= $alert_class; ?> border-0 small py-2 mb-4 d-flex align-items-center rounded-3">
                <i class="bi <?= $icon; ?> me-2 fs-5"></i> 
                <div><?= $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
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
                    <label class="form-label text-uppercase">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
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

            <div class="mb-3">
                <label class="form-label text-uppercase">Password Baru</label>
                 <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>    
                    <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="••••••••" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('password_baru', 'toggleIcon1')" style="cursor: pointer;">
                        <i class="bi bi-eye-fill" id="toggleIcon1"></i>
                    </span>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-uppercase">Konfirmasi Password</label>
                 <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-check2-all"></i></span>    
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="••••••••" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('konfirmasi_password', 'toggleIcon2')" style="cursor: pointer;">
                        <i class="bi bi-eye-fill" id="toggleIcon2"></i>
                    </span>
                </div>
            </div>

            <button type="submit" name="reset" class="btn btn-login">
                SIMPAN PASSWORD BARU <i class="bi bi-save ms-2"></i>
            </button>
            
            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none text-muted small fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke halaman Login
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
    } else {
        passwordInput.type = "password";
        icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
    }
}
</script>

</body>
</html>