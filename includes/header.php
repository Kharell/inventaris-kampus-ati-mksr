<header class="topbar">
    <div class="container-fluid d-flex align-items-center justify-content-between px-0">
        
        <div class="d-flex align-items-center">
            <div class="status-indicator d-flex align-items-center bg-light px-3 py-2 rounded-pill">
                <span class="status-dot pulse me-2"></span>
                <small class="fw-bold text-dark" style="font-size: 0.75rem;">Sistem Online</small>
                <div class="vr mx-3 opacity-25" style="height: 20px;"></div>
                <i class="bi bi-cpu text-muted me-2"></i>
                <small class="text-muted" style="font-size: 0.75rem;">Server: Aktif</small>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <div class="me-4 position-relative" role="button">
                <i class="bi bi-bell fs-5 text-muted"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                    <?= $stok_menipis ?? '0'; ?>
                </span>
            </div>

            <div class="dropdown">
                <div class="d-flex align-items-center dropdown-toggle" role="button" data-bs-toggle="dropdown">
                    <div class="text-end me-3 d-none d-sm-block">
                        <p class="mb-0 fw-bold text-dark" style="font-size: 0.85rem; line-height: 1;">
                            <?= $_SESSION['nama'] ?? 'User Admin'; ?>
                        </p>
                        <small class="text-warning fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">
                            <?= $_SESSION['role'] ?? 'Administrator'; ?>
                        </small>
                    </div>
                    <div class="avatar-box shadow-sm" style="background: #001f3f; color: #FFD700; border: 2px solid #FFD700;">
                        <?= substr($_SESSION['nama'] ?? 'A', 0, 1); ?>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate slideIn">
                    <li><h6 class="dropdown-header">Pengaturan Akun</h6></li>
                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalKeamanan">
                        <i class="bi bi-shield-lock me-2 text-primary"></i>Keamanan & Password
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="prosesLogout()">
                        <i class="bi bi-box-arrow-right me-2"></i>Keluar Aplikasi
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<div class="modal fade" id="modalKeamanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: #001f3f; color: #FFD700;">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2"></i>Pengaturan Keamanan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $base_url; ?>modules/auth/update_security.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">USERNAME SAAT INI</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control border-0 bg-light" value="<?= $_SESSION['username']; ?>" required>
                        </div>
                    </div>
                    
                    <hr class="my-4 opacity-10">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-key"></i></span>
                            <input type="password" id="new_password" name="new_password" class="form-control border-0 bg-light" placeholder="Isi untuk ganti">
                            <button class="btn btn-light border-0" type="button" onclick="togglePassword('new_password')">
                                <i class="bi bi-eye" id="eye_new_password"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-check2-circle"></i></span>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control border-0 bg-light" placeholder="Ulangi password">
                            <button class="btn btn-light border-0" type="button" onclick="togglePassword('confirm_password')">
                                <i class="bi bi-eye" id="eye_confirm_password"></i>
                            </button>
                        </div>
                    </div>

                    <script>
                    // Fungsi untuk sembunyi/tampilkan password
                    function togglePassword(id) {
                        const input = document.getElementById(id);
                        const icon = document.getElementById('eye_' + id);
                        if (input.type === "password") {
                            input.type = "text";
                            icon.classList.replace('bi-eye', 'bi-eye-slash');
                        } else {
                            input.type = "password";
                            icon.classList.replace('bi-eye-slash', 'bi-eye');
                        }
                    }
                    </script>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_security" class="btn btn-sm btn-navy rounded-pill px-4" style="background: #001f3f; color: #FFD700;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* CSS agar tampilan Header lebih modern */
.status-dot {
    height: 8px;
    width: 8px;
    background-color: #28a745;
    border-radius: 50%;
    display: inline-block;
}

.pulse {
    animation: pulse-animation 2s infinite;
}

@keyframes pulse-animation {
    0% { box-shadow: 0 0 0 0px rgba(40, 167, 69, 0.4); }
    100% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
}

.avatar-box {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.topbar {
    padding: 0.75rem 1.5rem;
    background: #ffffff;
    border-bottom: 1px solid #edf2f9;
}
</style>