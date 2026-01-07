<?php
include "config/database.php"; 

$pesan_error = "";
$pendaftaran_sukses = false;

if (isset($_POST['daftar'])) {
    // 1. Sanitasi input untuk mencegah SQL Injection
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    
    // 2. Enkripsi password menggunakan BCRYPT
    $password     = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role         = "admin"; 

    // 3. Validasi: Cek apakah username sudah terdaftar
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan_error = "Username sudah digunakan!";
    } else {
        // 4. Proses Insert ke Database
        $query = "INSERT INTO users (username, password, role, nama_lengkap) 
                  VALUES ('$username', '$password', '$role', '$nama_lengkap')";
        
        if (mysqli_query($conn, $query)) {
            $pendaftaran_sukses = true;
        } else {
            $pesan_error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin | Inventory Lab</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body { 
            background: #f4f7fa; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            font-family: 'Inter', sans-serif; 
        }
        .card-register { 
            border: none; 
            border-radius: 1.5rem; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.08); 
            background: #fff; 
        }
        .btn-navy { 
            background: #001f3f; 
            color: #fff; 
            border: none; 
            padding: 12px; 
            border-radius: 10px; 
            font-weight: bold; 
            transition: 0.3s; 
        }
        .btn-navy:hover { 
            background: #003366; 
            color: #fff; 
            transform: translateY(-2px); 
        }
        .input-group-text { 
            background: #f8f9fa; 
            border-right: none; 
            color: #6c757d; 
        }
        .form-control { 
            border-left: none; 
            border-radius: 0 10px 10px 0; 
            padding: 12px; 
            border-color: #dee2e6; 
        }
        .toggle-password { 
            cursor: pointer; 
            background: #fff; 
            border-left: none; 
            border-radius: 0 10px 10px 0; 
            border: 1px solid #dee2e6; 
            padding: 12px; 
            color: #6c757d; 
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-register p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-badge-fill text-primary mb-3" style="font-size: 3rem; color: #001f3f !important;"></i>
                    <h3 class="fw-bold" style="color: #001f3f;">Registrasi Admin</h3>
                    <p class="text-muted small">Buat akun untuk akses administrator</p>
                </div>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Admin" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="User ID" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" id="passInput" name="password" class="form-control border-end-0" placeholder="••••••••" required>
                            <span class="toggle-password" onclick="togglePass()">
                                <i class="bi bi-eye-slash" id="eyeIcon"></i>
                            </span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="daftar" class="btn btn-navy shadow-sm text-uppercase">
                            Daftar Akun <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <span class="small text-muted">Sudah punya akun? </span>
                        <a href="login.php" class="small fw-bold text-decoration-none" style="color: #001f3f;">Masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Fungsi Toggle Show/Hide Password
    function togglePass() {
        const passInput = document.getElementById('passInput');
        const eyeIcon = document.getElementById('eyeIcon');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    }

  // Pop-up Berhasil + Auto Redirect + SATU Loading Animation
    <?php if ($pendaftaran_sukses): ?>
    Swal.fire({
        title: 'Berhasil!',
        html: `
            <p class="mb-3">Akun Admin telah dibuat. Mengalihkan ke halaman masuk...</p>
            <div class="spinner-border text-primary" role="status" style="color: #001f3f !important; width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        `,
        icon: 'success',
        timer: 3000, 
        timerProgressBar: true, 
        showConfirmButton: false,
        allowOutsideClick: false,
        willClose: () => {
            window.location.href = 'login.php';
        }
    });
    <?php endif; ?>

    // Penanganan SweetAlert untuk Gagal
    <?php if ($pesan_error != ""): ?>
    Swal.fire({
        title: 'Gagal!',
        text: '<?= $pesan_error; ?>',
        icon: 'error',
        confirmButtonColor: '#001f3f'
    });
    <?php endif; ?>
</script>

</body>
</html>