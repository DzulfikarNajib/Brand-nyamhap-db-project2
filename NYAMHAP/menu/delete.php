<?php

session_start(); 

include '../config/db.php'; 

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['FOUNDER', 'SPROD']; 

if (!in_array($staff_role, $allowed_roles)) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?status=error&msg=" . urlencode("ID Menu tidak ditemukan."));
    exit();
}

$menu_id = pg_escape_string($koneksi, $_GET['id']);

$query = "DELETE FROM menu WHERE menuid = '$menu_id'";

$result = pg_query($koneksi, $query);

if ($result) {
    if (pg_affected_rows($result) > 0) {
        header("Location: index.php?status=delete_success");
    } else {
        header("Location: index.php?status=error&msg=" . urlencode("Menu tidak ditemukan."));
    }
} else {
    $error = pg_last_error($koneksi);
    if (strpos($error, 'foreign key constraint') !== false) {
        $msg = "Gagal menghapus! Menu ini sudah digunakan dalam data detail pesanan.";
    } else {
        $msg = "Gagal menghapus menu: " . $error;
    }
    header("Location: index.php?status=error&msg=" . urlencode($msg));
}

exit();
?>