<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$supplier_role = 'SPEM';
$is_founder = ($staff_role === 'FOUNDER'); 

$can_edit = $is_founder || $staff_role === $supplier_role;

if (!$can_edit) {
    include '../layout/header.php';
    echo '<h2>Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk **mengubah** data Bahan Baku. Fitur ini hanya untuk Founder atau Staff Pemasok.</p>';
    include '../layout/footer.php';
    exit(); 
}

$message = '';
$bahanbaku_data = null; 
$target_bahan_id = $_GET['id'] ?? $_POST['bahanid'] ?? ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bahanid = trim($_POST['bahanid']); 
    $namabahan = trim($_POST['namabahan']);
    $stok = (int)$_POST['stok'];
    $satuan = trim($_POST['satuan']);
    $hargaperunit = (int)$_POST['hargaperunit'];
    
    $query_update = "UPDATE bahanbaku SET namabahan = $1, stok = $2, satuan = $3, hargaperunit = $4 WHERE bahanid = $5";
    $params = array($namabahan, $stok, $satuan, $hargaperunit, $bahanid);

    $result = pg_query_params($koneksi, $query_update, $params);

    if ($result) {
        header("Location: index.php?status=success_edit");
        exit();

    } else {
        $error_detail = pg_last_error($koneksi);
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Gagal memperbarui Bahan Baku. Error: ' . htmlspecialchars($error_detail) . '</div>';
 
        $query_select = "SELECT bahanid, namabahan, stok, satuan, hargaperunit FROM bahanbaku WHERE bahanid = $1";
        $result_select = pg_query_params($koneksi, $query_select, array($bahanid));
        $bahanbaku_data = pg_fetch_assoc($result_select);
    }
} 

if ($_SERVER["REQUEST_METHOD"] == "GET" || $bahanbaku_data === null) {
    
    if (empty($target_bahan_id)) {
        header("Location: index.php");
        exit();
    }

    $query_select = "SELECT bahanid, namabahan, stok, satuan, hargaperunit FROM bahanbaku WHERE bahanid = $1";
    $result_select = pg_query_params($koneksi, $query_select, array($target_bahan_id));
    
    if ($result_select && pg_num_rows($result_select) > 0) {
        $bahanbaku_data = pg_fetch_assoc($result_select);
    } else {
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Data Bahan Baku tidak ditemukan.</div>';
    }
}


include '../layout/header.php'; 
?>

<h2>Edit Bahan Baku: <?= htmlspecialchars($bahanbaku_data['namabahan'] ?? 'Data Tidak Ada'); ?></h2>

<?= $message; ?>

<?php if ($bahanbaku_data) : ?>
<form method="POST" action="edit.php">
    <input type="hidden" name="bahanid" value="<?= htmlspecialchars($bahanbaku_data['bahanid']); ?>">

    <label for="namabahan">Nama Bahan Baku</label><br>
    <input type="text" id="namabahan" name="namabahan" value="<?= htmlspecialchars($bahanbaku_data['namabahan']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="stok">Jumlah Stok</label><br>
    <input type="number" id="stok" name="stok" value="<?= htmlspecialchars($bahanbaku_data['stok']); ?>" required min="0" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="satuan">Satuan</label><br>
    <input type="text" id="satuan" name="satuan" value="<?= htmlspecialchars($bahanbaku_data['satuan']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="hargaperunit">Harga Per Unit (Rp)</label><br>
    <input type="number" id="hargaperunit" name="hargaperunit" value="<?= htmlspecialchars($bahanbaku_data['hargaperunit']); ?>" required min="0" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Perbarui Data</button>
    <a href="index.php" class="btn btn-warning" style="color: black;">Batal</a>
</form>
<?php endif; ?>

<?php
include '../layout/footer.php';
?>