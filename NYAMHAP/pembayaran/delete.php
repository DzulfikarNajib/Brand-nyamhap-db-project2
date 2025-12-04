<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
if ($staff_role !== 'FOUNDER') {
    $_SESSION['payment_message'] = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Akses Ditolak! Hanya FOUNDER yang memiliki izin untuk menghapus data Pembayaran.</div>';
    header("Location: index.php");
    exit();
}

$target_id = $_GET['id'] ?? null;

if (empty($target_id)) {
    $_SESSION['payment_message'] = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Gagal: ID Pembayaran tidak ditemukan.</div>';
    header("Location: index.php");
    exit();
}

$pembayaran_id_esc = pg_escape_string($koneksi, $target_id);

$query = "DELETE FROM Pembayaran WHERE PembayaranID = $1";
$result = pg_query_params($koneksi, $query, array($pembayaran_id_esc));

if ($result) {
    if (pg_affected_rows($result) > 0) {
        $_SESSION['payment_message'] = '<div class="btn-primary" style="padding: 10px; margin-bottom: 15px;">Pembayaran ID ' . htmlspecialchars($target_id) . ' berhasil DIHAPUS! (Hanya Founder yang dapat melakukan aksi ini).</div>';
    } else {
        $_SESSION['payment_message'] = '<div class="btn-warning" style="padding: 10px; margin-bottom: 15px;">Peringatan: Data Pembayaran ID ' . htmlspecialchars($target_id) . ' tidak ditemukan atau sudah terhapus.</div>';
    }
} else {
    $error_detail = pg_last_error($koneksi);
    $_SESSION['payment_message'] = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Gagal menghapus Pembayaran. Error: ' . htmlspecialchars($error_detail) . '</div>';
}

header("Location: index.php");
exit();
?>
