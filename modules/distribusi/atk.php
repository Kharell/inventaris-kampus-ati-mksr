<?php
include "../../config/database.php";
include "../../config/auth.php";
checkAccess('admin');

// Kita tetap biarkan logic PHP di atas jika suatu saat Anda ingin mengaktifkan fiturnya kembali
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon - Inventaris ATK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    
    <style>
        :root { --navy: #001f3f; --gold: #FFD700; }
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; overflow-x: hidden; }

        /* Animasi Gear */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .coming-soon-icon {
            font-size: 5rem;
            color: var(--navy);
            display: inline-block;
            animation: spin 6s linear infinite;
        }
        
        .cs-card {
            border: none;
            border-radius: 20px;
            background: white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }

        .btn-navy {
            background-color: var(--navy);
            color: var(--gold);
            font-weight: bold;
            border: 2px solid var(--gold);
            padding: 10px 25px;
            border-radius: 10px;
            transition: 0.3s;
        }
        .btn-navy:hover {
            background-color: #003366;
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include "../../includes/sidebar.php"; ?>

    <div class="main-content w-100"> 
        <?php include "../../includes/header.php"; ?>

        <main class="p-4 d-flex align-items-center justify-content-center" style="margin-top: 70px; min-height: calc(100vh - 70px);">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-7 text-center">
                        <div class="cs-card p-5">
                            <div class="mb-4">
                                <i class="bi bi-gear-wide-connected coming-soon-icon"></i>
                            </div>
                            <h1 class="fw-bold text-dark mb-3">Segera Hadir!</h1>
                            <p class="text-muted fs-5 mb-4">
                                Modul <strong>Distribusi ATK</strong> sedang dalam proses sinkronisasi database. Kami akan segera kembali dengan fitur manajemen distribusi yang lebih lengkap.
                            </p>
                            
                            <div class="progress mb-4" style="height: 12px; border-radius: 20px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 70%; background-color: var(--navy);"></div>
                            </div>
                            
                            <div class="d-flex justify-content-center gap-3">
                                <a href="../../index.php" class="btn btn-navy shadow-sm">
                                    <i class="bi bi-house-door me-2"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>