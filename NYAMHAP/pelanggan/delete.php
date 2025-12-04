<?php

include '../config/db.php'; 
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_role = 'SPEN'; 

if ($staff_role !== $allowed_role) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?status=error&msg=" . urlencode("ID Pelanggan tidak ditemukan."));
    exit();
}

$pelanggan_id = pg_escape_string($koneksi, $_GET['id']);

$query = "DELETE FROM pelanggan WHERE pelangganid = '$pelanggan_id'";

$result = pg_query($koneksi, $query);

if ($result) {
    if (pg_affected_rows($result) > 0) {
        header("Location: index.php?status=delete_success");
    } else {
        header("Location: index.php?status=error&msg=" . urlencode("Pelanggan tidak ditemukan."));
    }
} else {
    $error = pg_last_error($koneksi);
    if (strpos($error, 'foreign key constraint') !== false) {
        $msg = "Gagal menghapus! Pelanggan ini sudah memiliki data Pesanan terkait. Hapus dulu semua pesanan dari pelanggan ini.";
    } else {
        $msg = "Gagal menghapus pelanggan: " . $error;
    }
    header("Location: index.php?status=error&msg=" . urlencode($msg));
}

exit();
?>