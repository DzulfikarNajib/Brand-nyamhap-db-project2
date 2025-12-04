<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? ''); 

$allowed_roles = ['SPRO','SPEM']; 


if (!in_array($staff_role, $allowed_roles)) {
    include '../layout/header.php';
    echo '<h2>Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk menambah data Menu.</p>';
    include '../layout/footer.php';
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bahanid = strtoupper(trim($_POST['bahanid'])); 
    $namabahan = trim($_POST['namabahan']); 
    $stok = (int)$_POST['stok'];
    $satuan = trim($_POST['satuan']);
    $hargaperunit = (int)$_POST['hargaperunit'];

    if (empty($bahanid) || empty($namabahan) || empty($satuan) || $stok === null || $hargaperunit === null) {
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Semua field wajib diisi!</div>';
    } else {

        $query = "INSERT INTO bahanbaku (bahanid, namabahan, stok, satuan, hargaperunit) 
                  VALUES ($1, $2, $3, $4, $5)";
        
        $params = array($bahanid, $namabahan, $stok, $satuan, $hargaperunit);
        
        $result = pg_query_params($koneksi, $query, $params);

        if ($result) {
            $message = '<div class="btn-primary" style="padding: 10px; margin-bottom: 15px;">✅ Bahan Baku **' . htmlspecialchars($namabahan) . '** berhasil ditambahkan!</div>';
            
        } else {
            $error_detail = pg_last_error($koneksi);
            if (strpos($error_detail, 'duplicate key value') !== false) {
                 $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Gagal: ID Bahan Baku atau Nama Bahan sudah terdaftar.</div>';
            } else {
                 $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Gagal menambahkan Bahan Baku. Error: ' . htmlspecialchars($error_detail) . '</div>';
            }
        }
    }
}

include '../layout/header.php'; 
?>

<h2>Tambah Bahan Baku Baru</h2>

<?= $message; ?>

<form method="POST" action="create.php">
    <label for="bahanid">ID Bahan Baku (Contoh: B011)</label><br>
    <input type="text" id="bahanid" name="bahanid" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="namabahan">Nama Bahan Baku</label><br>
    <input type="text" id="namabahan" name="namabahan" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="stok">Jumlah Stok Awal</label><br>
    <input type="number" id="stok" name="stok" required min="0" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>
    
    <label for="satuan">Satuan</label><br>
    <input type="text" id="satuan" name="satuan" required placeholder="Contoh: Kg, Pack, Buah" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="hargaperunit">Harga Per Unit (Rp)</label><br>
    <input type="number" id="hargaperunit" name="hargaperunit" required min="0" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Simpan Bahan Baku</button>
    <a href="index.php" class="btn btn-warning" style="color: black;">Batal</a>
</form>

<?php
include '../layout/footer.php';
?>
