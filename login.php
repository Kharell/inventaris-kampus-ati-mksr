<?php
session_start();
include "config/database.php"; 

$error = ""; 

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $role_input = $_POST['role']; 

    if ($role_input == 'admin') {
        $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        $data = mysqli_fetch_assoc($query);
        $id_key = 'id_admin'; 
        $nama_key = 'username'; 
    } else {
        $query = mysqli_query($conn, "SELECT * FROM kepala_lab WHERE username='$username'");
        $data = mysqli_fetch_assoc($query);
        $id_key = 'id_kepala';
        $nama_key = 'nama_kepala';
    }

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['id_user']    = $data[$id_key];
        $_SESSION['username']   = $data['username'];
        $_SESSION['role']       = $role_input;
        $_SESSION['nama']       = $data[$nama_key];
        
        if($role_input == 'kepala_lab') {
            $_SESSION['id_lab'] = $data['id_lab'];
        }

        if ($role_input == 'admin') {
            header("Location: views/admin/index.php");
        } else {
            header("Location: views/kepala-lab/index.php");
        }
        exit();
    } else {
        $error = "Akses ditolak! Periksa kembali Username & Password anda.";
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
        .login-form { width: 55%; padding: 60px; background: #fff; }

        .welcome-title { color: var(--poltek-navy); font-weight: 800; font-size: 2rem; }

        /* Custom Role Selector (Card Style) */
        .role-selector { display: flex; gap: 15px; margin-bottom: 25px; }
        
        .role-option {
            flex: 1;
            position: relative;
        }

        .role-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .role-card i { font-size: 1.5rem; color: #ccc; margin-bottom: 5px; }
        .role-card span { font-size: 0.8rem; font-weight: 700; color: #888; text-transform: uppercase; }

        .role-option input:checked + .role-card {
            border-color: var(--poltek-gold);
            background: rgba(255, 215, 0, 0.05);
        }

        .role-option input:checked + .role-card i { color: var(--poltek-navy); }
        .role-option input:checked + .role-card span { color: var(--poltek-navy); }

        /* Input Styles */
        .form-label { font-weight: 700; color: var(--poltek-navy); font-size: 0.85rem; }
        .input-group-text { background: transparent; border-right: none; color: var(--poltek-gold-dark); }
        .form-control { border-left: none; padding: 12px; border-radius: 0 10px 10px 0; }
        .form-control:focus { border-color: #dee2e6; box-shadow: none; }

        .btn-login {
            background: var(--poltek-navy);
            color: var(--poltek-gold);
            border: 2px solid var(--poltek-gold);
            padding: 15px;
            border-radius: 12px;
            font-weight: 800;
            width: 100%;
            transition: 0.4s;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            background: var(--poltek-gold);
            color: var(--poltek-navy);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }

        @media (max-width: 768px) {
            .login-visual { display: none; }
            .login-form { width: 100%; padding: 40px 30px; }
        }
        :root {
            --poltek-navy: #001f3f;
            --poltek-gold: #FFD700;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-visual">
        <img src="images/logo.png" alt="Logo Politeknik ATI Makassar" onerror="this.src='https://upload.wikimedia.org/wikipedia/id/0/05/Logo_Politeknik_ATI_Makassar.png'">
        
        <h2 class="fw-bold text-white mb-2">INVENTARIS</h2>
        <p class="text-white-50 small px-4">Sistem Informasi Manajemen Laboratorium & Bahan Praktek Terpadu</p>
        
        <div class="mt-4 pt-4 border-top border-white border-opacity-10">
            <span class="badge rounded-pill px-3 py-2" style="background: var(--poltek-gold); color: var(--poltek-navy);">
                KEMENTERIAN PERINDUSTRIAN RI
            </span>
        </div>
    </div>

    <div class="login-form">
        <div class="mb-4">
            <h2 class="welcome-title mb-1">Login</h2>
            <p class="text-muted">Gunakan akun anda untuk mengakses dashboard.</p>
        </div>

        <?php if($error != ""): ?>
            <div class="alert alert-danger border-0 small py-2 mb-4">
                <i class="bi bi-x-circle-fill me-2"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="form-label text-uppercase mb-2">Pilih Akses</label>
            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="admin" required checked>
                    <div class="role-card">
                        <i class="bi bi-person-gear"></i>
                        <span>Admin</span>
                    </div>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="kepala_lab">
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
                <!-- <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div> -->

                 <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                    
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="form-control" 
                        placeholder="••••••••" 
                        required
                    >

                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye-fill" id="toggleIcon"></i>
                    </span>
                </div>

            </div>

            <button type="submit" name="login" class="btn btn-login">
                MASUK SEKARANG <i class="bi bi-arrow-right ms-2"></i>
            </button>
            
            <p class="text-center mt-5 text-muted small">
                &copy; 2024 Politeknik ATI Makassar
            </p>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Konfirmasi Keluar',
        text: "Apakah Anda yakin ingin mengakhiri sesi ini, Politeknik ATI Makassar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#001f3f', // Navy
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal',
        background: '#ffffff',
        color: '#001f3f',
        borderRadius: '20px',
        customClass: {
            popup: 'border-gold-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Jika klik Ya, arahkan ke file logout.php
            window.location.href = "../../logout.php"; 
        }
    })
}

function togglePassword() {
    const passwordInput = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("bi-eye-fill");
        icon.classList.add("bi-eye-slash-fill");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("bi-eye-slash-fill");
        icon.classList.add("bi-eye-fill");
    }
}
</script>

<style>
    /* Tambahan agar pop-up punya aksen gold di atasnya */
    .border-gold-popup {
        border-top: 8px solid #FFD700 !important;
    }
</style>


</body>
</html>