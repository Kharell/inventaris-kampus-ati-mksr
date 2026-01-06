<?php
session_start();
// Cek Login
if(!isset($_SESSION['role'])){
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="main-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-area w-100">
        
        <?php include '../includes/header.php'; ?>

        <main class="p-4">
            <div class="container-fluid">
                <h2 class="fw-bold mb-4">Ringkasan Inventaris</h2>
                
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-3 bg-primary text-white">
                            <small>Total Barang</small>
                            <h3 class="fw-bold">1,250</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-3 bg-success text-white">
                            <small>Barang Masuk</small>
                            <h3 class="fw-bold">45</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-3 bg-warning text-dark">
                            <small>Stok Menipis</small>
                            <h3 class="fw-bold">12</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-3 bg-danger text-white">
                            <small>Barang Rusak</small>
                            <h3 class="fw-bold">5</h3>
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