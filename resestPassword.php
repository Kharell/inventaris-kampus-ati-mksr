<?php
session_start();
include "config/database.php"; 

$pesan_error = "";
$reset_sukses = false;

if (isset($_POST['reset'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_baru = $_POST['password'];
    $konfirmasi_pass = $_POST['konfirmasi_password'];
    $kode_input = $_POST['kode_aktivasi'];

    // 1. Ambil Master Key dari Database
    $query_key = mysqli_query($conn, "SELECT kode_rahasia FROM master_key WHERE id = 1");
    $data_key  = mysqli_fetch_assoc($query_key);
    $master_key_db = $data_key['kode_rahasia'];

    // 2. Validasi Input
    if ($kode_input !== $master_key_db) {
        $pesan_error = "KODE AKTIVASI SALAH! Reset ditolak.";
    } elseif ($pass_baru !== $konfirmasi_pass) {
        $pesan_error = "Konfirmasi Password tidak cocok!";
    } elseif (strlen($pass_baru) < 8) {
        $pesan_error = "Password baru minimal harus 8 karakter!";
    } else {
        // 3. Cek apakah Username benar-benar ada
        $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$user'");
        if (mysqli_num_rows($cek_user) == 0) {
            $pesan_error = "Username tidak ditemukan!";
        } else {
            // 4. Update Password (Hash Password Baru)
            $pass_hashed = password_hash($pass_baru, PASSWORD_BCRYPT);
            $query_update = "UPDATE users SET password = '$pass_hashed' WHERE username = '$user'";
            
            if (mysqli_query($conn, $query_update)) {
                $reset_sukses = true;
            } else {
                $pesan_error = "Gagal memperbarui database: " . mysqli_error($conn);
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
    <title>Reset Password Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7fa; min-height: 100vh; display: flex; align-items: center; font-family: 'Inter', sans-serif; }
        .card-register { border: none; border-radius: 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,0.08); background: #fff; }
        .btn-navy { background: #001f3f; color: #fff; border: none; padding: 12px; border-radius: 10px; font-weight: bold; }
        .btn-navy:hover { background: #003366; color: #fff; }
        .form-control:focus { box-shadow: none; border-color: #001f3f; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center my-5">
        <div class="col-md-5">
            <div class="card card-register p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem; color: #001f3f !important;"></i>
                    <h3 class="fw-bold">Reset Password Admin</h3>
                    <p class="text-muted small">Silakan reset ulang password anda</p>
                </div>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="Masukkan Username Anda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="pass1" class="form-control" required placeholder="••••••••">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleView('pass1', 'icon1')">
                                <i class="bi bi-eye-slash" id="icon1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Konfirmasi New Password</label>
                        <div class="input-group">
                            <input type="password" name="konfirmasi_password" id="pass2" class="form-control" required placeholder="••••••••">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleView('pass2', 'icon2')">
                                <i class="bi bi-eye-slash" id="icon2"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-danger">KODE AKTIVASI (Master Key)</label>
                        <input type="password" name="kode_aktivasi" id="masterKey" class="form-control border-danger" required placeholder="••••••••">
                        <div class="form-text mt-2" style="font-size: 11px;">
                            *Hanya Admin utama yang mengetahui kode ini untuk mereset password.
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" name="reset" class="btn btn-navy">RESET SEKARANG</button>
                    </div>

                    <div class="text-center">
                        <p class="small text-muted">Lupa segalanya? <a href="daftarAdmin.php" class="text-decoration-none fw-bold" style="color: #001f3f;">Daftar ulang akun</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi Toggle Password
    function toggleView(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    }

    // SweetAlert Sukses
    <?php if ($reset_sukses): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: 'Password Anda telah diperbarui. Silakan login kembali.',
        icon: 'success',
        confirmButtonColor: '#001f3f'
    }).then(() => {
        window.location.href = 'login.php';
    });
    <?php endif; ?>

    // SweetAlert Gagal
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