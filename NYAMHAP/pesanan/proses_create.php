<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id']) || strtoupper($_SESSION['staff_role'] ?? '') !== 'SPEN') {
    header("Location: /NYAMHAP/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: create.php");
    exit();
}

pg_query($koneksi, "BEGIN");

try {
    $pelanggan_id = $_POST['pelanggan_id'] ?? null;
    $total_harga_final = (int)($_POST['total_harga_final'] ?? 0);
    $tanggal_pesanan = date('Y-m-d'); 

    if (empty($pelanggan_id) || $total_harga_final <= 0) {
        throw new Exception("Data pelanggan atau total harga tidak valid.");
    }

    $q_last_p = pg_query($koneksi, "SELECT PesananID FROM Pesanan ORDER BY PesananID DESC LIMIT 1");
    $last_p_row = pg_fetch_assoc($q_last_p);
    
    if ($last_p_row) {
        $last_num = (int)substr($last_p_row['pesananid'], 1);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }
    $new_pesanan_id = 'O' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

    $pelanggan_id_esc = pg_escape_string($koneksi, $pelanggan_id);
    $tanggal_esc = pg_escape_string($koneksi, $tanggal_pesanan);

    $query_pesanan = "INSERT INTO Pesanan (PesananID, PelangganID, Tanggal, TotalHarga) 
                      VALUES ('{$new_pesanan_id}', '{$pelanggan_id_esc}', '{$tanggal_esc}', {$total_harga_final})";
    
    if (!pg_query($koneksi, $query_pesanan)) {
        throw new Exception("Gagal menyimpan Pesanan: " . pg_last_error($koneksi));
    }

    $menu_ids = $_POST['menu_id'] ?? [];
    $jumlahs = $_POST['jumlah'] ?? [];
    
    foreach ($menu_ids as $index => $menu_id) {
        $jumlah = (int)($jumlahs[$index] ?? 0);
  
        if (empty($menu_id) || $jumlah <= 0) continue;

        $menu_id_esc = pg_escape_string($koneksi, $menu_id);
        
        $query_detail = "INSERT INTO DetailPesanan (PesananID, MenuID, PilihanMenu, JumlahMenu) 
                         VALUES ('{$new_pesanan_id}', '{$menu_id_esc}', 'Original', {$jumlah})";
        
        if (!pg_query($koneksi, $query_detail)) {
            throw new Exception("Gagal menyimpan Detail Pesanan (ID: $menu_id): " . pg_last_error($koneksi));
        }
    }
    
    $q_last_b = pg_query($koneksi, "SELECT PembayaranID FROM Pembayaran ORDER BY PembayaranID DESC LIMIT 1");
    $last_b_row = pg_fetch_assoc($q_last_b);
    
    if ($last_b_row) {
        $last_b_num = (int)substr($last_b_row['pembayaranid'], 3); 
        $next_b_num = $last_b_num + 1;
    } else {
        $next_b_num = 1;
    }
    $new_pembayaran_id = 'BYR' . str_pad($next_b_num, 2, '0', STR_PAD_LEFT);
    
    $query_pembayaran = "INSERT INTO Pembayaran (PembayaranID, PesananID, TanggalPembayaran, MetodePembayaran, StatusPembayaran) 
                         VALUES ('{$new_pembayaran_id}', '{$new_pesanan_id}', '{$tanggal_esc}', 'Cash', 'Pending')"; 
                         
    if (!pg_query($koneksi, $query_pembayaran)) {
        throw new Exception("Gagal membuat Pembayaran Pending: " . pg_last_error($koneksi));
    }

    pg_query($koneksi, "COMMIT");

    header("Location: index.php?status=success_create");
    exit();

} catch (Exception $e) {
    pg_query($koneksi, "ROLLBACK");
    $error_msg = urlencode($e->getMessage());
    header("Location: index.php?status=error&msg={$error_msg}");
    exit();
}
?>