<?php
session_start();
include "config/database.php"; 

$error = ""; 
$success = "";

// Ambil daftar Lab untuk form pilihan (Dropdown)
$query_lab = mysqli_query($conn, "SELECT * FROM lab ORDER BY nama_lab ASC");

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_kepala']);
    $nip      = mysqli_real_escape_string($conn, $_POST['nip']);
    $kontak    = mysqli_real_escape_string($conn, $_POST['kontak']); // <-- Tangkap No HP
    $id_lab   = mysqli_real_escape_string($conn, $_POST['id_lab']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $konf_pass= $_POST['konfirmasi_password'];
    $pin      = $_POST['pin_pemulihan'];

    // 1. Validasi Password
    if ($password !== $konf_pass) {
        $error = "Konfirmasi password tidak cocok!";
    } 
    // 2. Validasi PIN Pemulihan (Wajib 6 Angka)
    else if (!preg_match('/^[0-9]{6}$/', $pin)) {
        $error = "PIN Pemulihan wajib terdiri dari 6 digit angka!";
    } 
    else {
        // 3. Cek apakah Username sudah dipakai
        $cek_user = mysqli_query($conn, "SELECT username FROM kepala_lab WHERE username='$username'");
        if (mysqli_num_rows($cek_user) > 0) {
            $error = "Username sudah digunakan, silakan cari username lain.";
        } else {
            // 4. Cek apakah Lab tersebut sudah memiliki Kepala Lab
            $cek_lab = mysqli_query($conn, "SELECT id_kepala FROM kepala_lab WHERE id_lab='$id_lab'");
            if (mysqli_num_rows($cek_lab) > 0) {
                $error = "Laboratorium tersebut sudah memiliki Kepala Lab yang terdaftar!";
            } else {
                // 5. Hash Password & Simpan Data (Termasuk No HP)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Pastikan kolom kontak ada di tabel database Anda
                $insert = mysqli_query($conn, "INSERT INTO kepala_lab (nama_kepala, nip, kontak, username, password, pin_pemulihan, id_lab) 
                                               VALUES ('$nama', '$nip', '$kontak', '$username', '$hashed_password', '$pin', '$id_lab')");
                
                if ($insert) {
                    // Redirect ke login jika sukses
                    header("Location: login.php?pesan=registrasi_sukses");
                    exit();
                } else {
                    $error = "Terjadi kesalahan sistem saat menyimpan data: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Kepala Lab | SIM Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --poltek-navy: #001f3f; --poltek-gold: #FFD700; --poltek-gold-dark: #b8860b; }
        body {
            background: radial-gradient(circle at center, #003366 0%, #001f3f 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; padding: 30px 0;
        }
        .login-container {
            max-width: 1000px; width: 95%; background: white; border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; display: flex; border: 1px solid rgba(255, 215, 0, 0.3);
        }
        .login-visual {
            background: linear-gradient(135deg, rgba(0, 31, 63, 0.9), rgba(0, 51, 102, 0.8)), url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=1986&auto=format&fit=crop'); 
            background-size: cover; background-position: center; width: 40%; padding: 40px; color: white;
            display: flex; flex-direction: column; justify-content: center; text-align: center; position: relative;
        }
        .login-visual::after { content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 10px; background: var(--poltek-gold); }
        .login-visual img { width: 120px; margin: 0 auto 25px; filter: drop-shadow(0 0 10px rgba(255,215,0,0.5)); }
        
        .login-form { width: 60%; padding: 40px 50px; background: #fff; max-height: 90vh; overflow-y: auto;}
        .welcome-title { color: var(--poltek-navy); font-weight: 800; font-size: 1.8rem; }
        
        .form-label { font-weight: 700; color: var(--poltek-navy); font-size: 0.8rem; margin-bottom: 5px; text-transform: uppercase;}
        .input-group-text { background: transparent; border-right: none; color: var(--poltek-gold-dark); }
        .form-control, .form-select { border-left: none; padding: 10px; border-radius: 0 10px 10px 0; font-size: 0.9rem;}
        .form-control:focus, .form-select:focus { border-color: #dee2e6; box-shadow: none; }
        
        .btn-login {
            background: var(--poltek-navy); color: var(--poltek-gold); border: 2px solid var(--poltek-gold);
            padding: 12px; border-radius: 12px; font-weight: 800; width: 100%; transition: 0.4s; letter-spacing: 1px; margin-top: 10px;
        }
        .btn-login:hover { background: var(--poltek-gold); color: var(--poltek-navy); box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3); }

        @media (max-width: 768px) {
            .login-visual { display: none; }
            .login-form { width: 100%; padding: 30px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-visual">
        <img src="images/logo.png" alt="Logo Politeknik ATI Makassar" onerror="this.src='https://upload.wikimedia.org/wikipedia/id/0/05/Logo_Politeknik_ATI_Makassar.png'">
        <h2 class="fw-bold text-white mb-2">INVENTARIS</h2>
        <p class="text-white-50 small px-4">Sistem Informasi Manajemen Laboratorium & Bahan Praktek Terpadu</p>
    </div>

    <div class="login-form custom-scrollbar">
        <div class="mb-4 text-center">
            <h2 class="welcome-title mb-1">Registrasi Kepala Lab</h2>
            <p class="text-muted small mt-2">Daftarkan diri Anda untuk mengelola inventaris laboratorium.</p>
        </div>

        <?php if($error != ""): ?>
            <div class="alert alert-danger border-0 small py-2 mb-4 d-flex align-items-center rounded-3">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <div><?= $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap & Gelar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                    <input type="text" name="nama_kepala" class="form-control" placeholder="Contoh: Dr. Budi, M.T" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">NIP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="nip" class="form-control" placeholder="NIP Anda" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor WhatsApp / HP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                        <input type="text" name="kontak" class="form-control" placeholder="0812..." required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Laboratorium yang Dipimpin</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                    <select name="id_lab" class="form-select border-start-0" required>
                        <option value="" selected disabled>-- Pilih Laboratorium --</option>
                        <?php while($row_lab = mysqli_fetch_assoc($query_lab)): ?>
                            <option value="<?= $row_lab['id_lab'] ?>"><?= htmlspecialchars($row_lab['nama_lab']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <hr class="text-muted my-4">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Username login" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-danger">PIN Pemulihan (6 Angka)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-123"></i></span>
                        <input type="password" name="pin_pemulihan" class="form-control" placeholder="Contoh: 123456" pattern="[0-9]{6}" title="Masukkan tepat 6 digit angka" required>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">*Simpan PIN ini baik-baik untuk reset password.</small>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>    
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        <span class="input-group-text" onclick="togglePassword('password', 'iconPass1')" style="cursor: pointer;">
                            <i class="bi bi-eye-fill" id="iconPass1"></i>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-check2-all"></i></span>    
                        <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="••••••••" required>
                        <span class="input-group-text" onclick="togglePassword('konfirmasi_password', 'iconPass2')" style="cursor: pointer;">
                            <i class="bi bi-eye-fill" id="iconPass2"></i>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" name="register" class="btn btn-login">
                BUAT AKUN SEKARANG <i class="bi bi-person-plus ms-2"></i>
            </button>
            
            <div class="text-center mt-3">
                <p class="text-muted small">
                    Sudah punya akun? 
                    <a href="login.php" class="text-decoration-none fw-bold" style="color: #001f3f;">Kembali ke Login</a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
    } else {
        input.type = "password";
        icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
    }
}
</script>

</body>
</html>