<?php
// FILE: /NYAMHAP/periklanan/delete.php

// Panggil konfigurasi dan koneksi
include '../config/db.php';
session_start();

// --- PENGECEKAN AUTENTIKASI (Wajib Login) ---
if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

// --- OTORISASI BARU: HANYA FOUNDER ATAU STAFF MARKETING (SMKT) ---
$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['FOUNDER', 'SMAR']; 

if (!in_array($staff_role, $allowed_roles)) {
    // Jika BUKAN Founder atau SMKT, tolak akses dan arahkan kembali
    header("Location: index.php");
    exit();
}
// --- END OTORISASI ---

// Mengambil ID target dari parameter URL 'id'
$periklananid_to_delete = $_GET['id'] ?? null;

if (empty($periklananid_to_delete)) {
    header("Location: index.php?status=fail_delete");
    exit();
}

// ------------------------------------------------------------------
// 1. Ambil data Iklan untuk validasi
// ------------------------------------------------------------------
$query_select = "SELECT periklananid, mediaperiklanan FROM periklanan WHERE periklananid = $1";
$result_select = pg_query_params($koneksi, $query_select, array($periklananid_to_delete));

if (!$result_select || pg_num_rows($result_select) == 0) {
    header("Location: index.php?status=fail_delete");
    exit();
}

// ------------------------------------------------------------------
// 2. PROSES PENGHAPUSAN
// ------------------------------------------------------------------
$query_delete = "DELETE FROM periklanan WHERE periklananid = $1";
$result = pg_query_params($koneksi, $query_delete, array($periklananid_to_delete));

if ($result) {
    // Jika penghapusan berhasil
    header("Location: index.php?status=success_delete");
    exit();
} else {
    // Jika terjadi error saat menghapus (misal, Foreign Key Constraint)
    $error_msg = urlencode("Gagal menghapus iklan. Error: " . pg_last_error($koneksi));
    header("Location: index.php?status=error&msg={$error_msg}");
    exit();
}
?>