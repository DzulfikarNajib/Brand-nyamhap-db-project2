<?php
// FILE: /NYAMHAP/pesanan/delete.php

include '../config/db.php'; // Ambil koneksi

// Pastikan ada ID pesanan yang dikirim melalui GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?status=error&msg=" . urlencode("ID Pesanan tidak ditemukan."));
    exit();
}

$pesanan_id = pg_escape_string($koneksi, $_GET['id']);

// 1. Mulai Transaksi PostgreSQL
pg_query($koneksi, "BEGIN");
$success = true; // Flag keberhasilan transaksi

try {
    // A. Hapus data di tabel Pembayaran (Paling luar)
    $query_pembayaran = "DELETE FROM Pembayaran WHERE PesananID = '$pesanan_id'";
    $res_pembayaran = pg_query($koneksi, $query_pembayaran);

    if (!$res_pembayaran) {
        $success = false;
        throw new Exception("Gagal menghapus data Pembayaran: " . pg_last_error($koneksi));
    }

    // B. Hapus data di tabel DetailPesanan
    $query_detail = "DELETE FROM DetailPesanan WHERE PesananID = '$pesanan_id'";
    $res_detail = pg_query($koneksi, $query_detail);

    if (!$res_detail) {
        $success = false;
        throw new Exception("Gagal menghapus data Detail Pesanan: " . pg_last_error($koneksi));
    }
    
    // C. Hapus data di tabel Pesanan (Paling dalam/Induk)
    $query_pesanan = "DELETE FROM Pesanan WHERE PesananID = '$pesanan_id'";
    $res_pesanan = pg_query($koneksi, $query_pesanan);

    // Cek apakah ada baris yang terpengaruh (ID ditemukan)
    if (!$res_pesanan || pg_affected_rows($res_pesanan) === 0) {
        $success = false;
        throw new Exception("ID Pesanan tidak ditemukan atau gagal dihapus: " . pg_last_error($koneksi));
    }

    // 2. Commit Transaksi
    if ($success) {
        pg_query($koneksi, "COMMIT");
        // Redirect ke halaman daftar pesanan (sukses)
        header("Location: index.php?status=delete_success");
        exit();
    }

} catch (Exception $e) {
    // 3. Rollback Transaksi jika ada error
    pg_query($koneksi, "ROLLBACK");
    // Redirect ke halaman daftar pesanan dengan pesan error
    header("Location: index.php?status=error&msg=" . urlencode("Penghapusan gagal: " . $e->getMessage()));
    exit();
}

// Tutup koneksi (Opsional)
pg_close($koneksi);
?>