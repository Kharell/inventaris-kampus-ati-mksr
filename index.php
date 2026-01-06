<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
} else {
    if ($_SESSION['role'] == 'admin') {
        header("Location: views/admin/index.php");
    } else {
        header("Location: views/kepala-lab/index.php");
    }
}
?>