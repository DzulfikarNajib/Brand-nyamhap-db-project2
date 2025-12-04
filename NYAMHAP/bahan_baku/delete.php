<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$is_founder = ($staff_role === 'FOUNDER');

if (!$is_founder) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$bahanid = pg_escape_string($koneksi, $_GET['id']);

$query = "DELETE FROM bahanbaku WHERE bahanid = $1";
$result = pg_query_params($koneksi, $query, array($bahanid));

if ($result) {
    header("Location: index.php?status=success_delete");
} else {
    $error_detail = pg_last_error($koneksi);
    if (strpos($error_detail, 'violates foreign key constraint') !== false) {
        header("Location: index.php?status=fail_delete&error=fk");
    } else {
        header("Location: index.php?status=fail_delete");
    }
}

exit();
?>