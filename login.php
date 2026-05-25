<?php
session_start();
include "config/database.php"; 

$error = ""; 

// 1. Tangkap pesan dari URL
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'sesi_habis') {
        $error = "Sesi Anda telah berakhir, Silakan login kembali.";
        $alert_class = "alert-warning"; 
        $icon = "bi-clock-history";
    } else if ($_GET['pesan'] == 'gagal') {
        $error = "Username atau Password salah!";
        $alert_class = "alert-danger"; 
        $icon = "bi-x-circle";
    } else if ($_GET['pesan'] == 'wajib_login') {
        $error = "Anda harus login untuk mengakses sistem.";
        $alert_class = "alert-info"; 
        $icon = "bi-info-circle";
    } else if ($_GET['pesan'] == 'logout_berhasil') {
        $error = "Anda telah berhasil keluar sistem.";
        $alert_class = "alert-success"; 
        $icon = "bi-check-circle";
    } else if ($_GET['pesan'] == 'reset_sukses') {
        // --- TAMBAHAN BARU UNTUK NOTIFIKASI RESET PASSWORD ---
        $error = "Password berhasil direset! Silakan login dengan password baru.";
        $alert_class = "alert-success"; 
        $icon = "bi-check-circle-fill";
    } else if ($_GET['pesan'] == 'registrasi_sukses') {
        $error = "Registrasi berhasil! Silakan login menggunakan akun baru Anda.";
        $alert_class = "alert-success"; 
        $icon = "bi-check-circle-fill";
    }
}

// 2. Proses Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $role_input = $_POST['role']; 

    // Pisahkan pengecekan tabel berdasarkan Role
    if ($role_input == 'admin' || $role_input == 'admin-acc') {
        $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND role='$role_input'");
        $data = mysqli_fetch_assoc($query);
        $id_key = 'id_user'; 
        $nama_key = 'nama_lengkap'; 
    } else {
        $query = mysqli_query($conn, "SELECT * FROM kepala_lab WHERE username='$username'");
        $data = mysqli_fetch_assoc($query);
        $id_key = 'id_kepala';
        $nama_key = 'nama_kepala';
    }

    // Jika username cocok dan password valid
    if ($data && password_verify($password, $data['password'])) {
        session_regenerate_id(true); 
        $_SESSION['id_user']  = $data[$id_key];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $role_input;
        $_SESSION['nama']     = $data[$nama_key] ?? $data['username']; 
        
        // --- DATA UNTUK KEBUTUHAN CETAK LAPORAN ---
        if ($role_input == 'admin' || $role_input == 'admin-acc') {
            $_SESSION['nama_lengkap'] = $data['nama_lengkap']; 
            $_SESSION['nip']          = $data['nip'];          
        } else {
            $_SESSION['nama_lengkap'] = $data['nama_kepala'];  
            $_SESSION['nip']          = $data['nip'] ?? '..........................'; 
            $_SESSION['id_lab']       = $data['id_lab']; 
        }

        // --- SISTEM KEAMANAN SESI ---
        $_SESSION['last_activity'] = time(); 
        $_SESSION['secure_fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);

        // --- ARAHKAN KE DASHBOARD MASING-MASING ---
        if ($role_input == 'admin') {
            $redirect = "views/admin/index.php";
        } else if ($role_input == 'admin-acc') {
            $redirect = "views/admin-acc/index.php";
        } else {
            $redirect = "views/kepala-lab/index.php";
        }
        
        header("Location: " . $redirect);
        exit();
    } else {
        header("Location: login.php?pesan=gagal"); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIM Inventaris Politeknik ATI Makassar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --poltek-navy: #001f3f;
            --poltek-gold: #FFD700;
            --poltek-gold-dark: #b8860b;
        }

        body {
            background: radial-gradient(circle at center, #003366 0%, #001f3f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            max-width: 950px;
            width: 95%;
            background: white;
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            overflow: hidden;
            display: flex;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        /* Sisi Kiri (Visual) */
        .login-visual {
            background: linear-gradient(135deg, rgba(0, 31, 63, 0.9), rgba(0, 51, 102, 0.8)), 
                        url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=1986&auto=format&fit=crop'); 
            background-size: cover;
            background-position: center;
            width: 45%;
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .login-visual::after {
            content: "";
            position: absolute;
            bottom: 0; left: 0; right: 0; height: 10px;
            background: var(--poltek-gold);
        }

        .login-visual img {
            width: 120px;
            margin: 0 auto 25px;
            filter: drop-shadow(0 0 10px rgba(255,215,0,0.5));
        }

        /* Sisi Kanan (Form) */
        .login-form { width: 55%; padding: 50px 60px; background: #fff; }
        .welcome-title { color: var(--poltek-navy); font-weight: 800; font-size: 2rem; }

        /* Custom Role Selector */
        .role-selector { display: flex; gap: 10px; margin-bottom: 25px; }
        .role-option { flex: 1; position: relative; }
        .role-option input { position: absolute; opacity: 0; cursor: pointer; }
        
        .role-card {
            display: flex; flex-direction: column; align-items: center; padding: 12px 5px;
            border: 2px solid #eee; border-radius: 12px; cursor: pointer;
            transition: all 0.3s ease; text-align: center; height: 100%;
        }

        .role-card i { font-size: 1.3rem; color: #ccc; margin-bottom: 5px; }
        .role-card span { font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; line-height: 1.1; }

        .role-option input:checked + .role-card { border-color: var(--poltek-gold); background: rgba(255, 215, 0, 0.05); }
        .role-option input:checked + .role-card i, .role-option input:checked + .role-card span { color: var(--poltek-navy); }

        /* Input Styles */
        .form-label { font-weight: 700; color: var(--poltek-navy); font-size: 0.85rem; }
        .input-group-text { background: transparent; border-right: none; color: var(--poltek-gold-dark); }
        .form-control { border-left: none; padding: 12px; border-radius: 0 10px 10px 0; }
        .form-control:focus { border-color: #dee2e6; box-shadow: none; }

        .btn-login {
            background: var(--poltek-navy); color: var(--poltek-gold); border: 2px solid var(--poltek-gold);
            padding: 15px; border-radius: 12px; font-weight: 800; width: 100%; transition: 0.4s; letter-spacing: 1px;
        }

        .btn-login:hover { background: var(--poltek-gold); color: var(--poltek-navy); box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3); }

        @media (max-width: 768px) {
            .login-visual { display: none; }
            .login-form { width: 100%; padding: 40px 30px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-visual">
        <img src="images/logo.png" alt="Logo Politeknik ATI Makassar" onerror="this.src='https://upload.wikimedia.org/wikipedia/id/0/05/Logo_Politeknik_ATI_Makassar.png'">
        <h2 class="fw-bold text-white mb-2">INVENTARIS</h2>
        <p class="text-white-50 small px-4">Sistem Informasi Manajemen Laboratorium & Bahan Praktek Terpadu <br>
          KEMENTERIAN PERINDUSTRIAN RI </p>
        <div class="mt-4 pt-4 border-top border-white border-opacity-10">
            <span class="badge rounded-pill px-3 py-2" style="background: var(--poltek-gold); color: var(--poltek-navy);">
                POLITEKNIK ATI MAKASSAR
            </span>
        </div>
    </div>

    <div class="login-form">
        <div class="mb-4 text-center">
            <h2 class="welcome-title mb-1">Login</h2>
            <p class="text-muted small mt-2">Pilih peran dan gunakan akun Anda untuk mengakses sistem.</p>
        </div>

        <?php if($error != ""): ?>
            <div class="alert <?= $alert_class; ?> border-0 small py-2 mb-4 d-flex align-items-center rounded-3">
                <i class="bi <?= $icon; ?> me-2 fs-5"></i> 
                <div><?= $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="text-center mb-2">
                <label class="form-label text-uppercase mb-0">Pilih Akses Login</label>
            </div>
            
            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="admin" required checked>
                    <div class="role-card">
                        <i class="bi bi-shield-lock"></i>
                        <span>Admin Utama</span>
                    </div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="admin-acc" required>
                    <div class="role-card">
                        <i class="bi bi-person-gear"></i>
                        <span>Admin ACC</span>
                    </div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="kepala_lab" required>
                    <div class="role-card">
                        <i class="bi bi-person-workspace"></i>
                        <span>Kepala Lab</span>
                    </div>
                </label>
            </div>

            <div class="mb-3">
                <label class="form-label text-uppercase">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="ID Pegawai / Username" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-uppercase">Password</label>
                 <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>    
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword()" style="cursor: pointer;">
                        <i class="bi bi-eye-fill" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login">
                MASUK SEKARANG <i class="bi bi-arrow-right ms-2"></i>
            </button>
            
            <div class="text-center mt-4">
                <p class="text-muted small mb-1">
                    Kepala Lab baru? 
                    <a href="daftar_kepala_lab.php" class="text-decoration-none fw-bold" style="color: #001f3f;">Daftar Akun Lab</a>
                </p>
                
                <div class="d-flex justify-content-center gap-3 mt-2">
                    <a href="lupa_username.php" class="text-decoration-none fw-bold small" style="color: #b8860b;">
                        <i class="bi bi-person-badge"></i> Lupa Username?
                    </a>
                    <span class="text-muted">|</span>
                    <a href="lupa_password.php" class="text-decoration-none fw-bold small" style="color: #001f3f;">
                        <i class="bi bi-key"></i> Lupa Password?
                    </a>
                </div>
            </div>

            <p class="text-center mt-4 text-muted small" style="font-size: 0.7rem;">
                &copy; 2026 Politeknik ATI Makassar
            </p>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
    } else {
        passwordInput.type = "password";
        icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
    }
}

// Mencegah user kembali ke dashboard tanpa login ulang
window.history.pushState(null, null, window.location.href);
window.onpopstate = function () {
    window.history.go(1);
};
</script>

</body>
</html>