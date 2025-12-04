<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_login_id = $_SESSION['staff_id'];
$founder_id = 'S001'; 

if ($staff_login_id !== $founder_id) {
    include '../layout/header.php';
    echo '<h2>Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk menghapus data Staff. Fitur ini hanya untuk Founder (ID: ' . $founder_id . ').</p>';
    include '../layout/footer.php';
    exit(); 
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $staffid_to_delete = $_GET['id'];

    if ($staffid_to_delete === $founder_id) {
        header("Location: index.php?status=cannot_delete_founder");
        exit();
    }

    $query = "DELETE FROM staff WHERE staffid = $1";
    
    $result = pg_query_params($koneksi, $query, array($staffid_to_delete));

    if ($result) {
        $status_message = "success";
    } else {
        $status_message = "fail_db";
    }
    
    header("Location: index.php?status=" . $status_message);
    exit();
    
} else {
    header("Location: index.php?status=no_id");
    exit();
}
?>