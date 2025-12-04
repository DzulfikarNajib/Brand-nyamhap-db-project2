<?php
// FILE: /NYAMHAP/periklanan/edit.php

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
    // Jika BUKAN Founder atau SMKT, tolak akses
    include '../layout/header.php';
    echo '<h2>❌ Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk **mengubah** data Periklanan. Fitur ini hanya untuk Founder dan Staff Marketing (Role: ' . implode(', ', $allowed_roles) . ').</p>';
    include '../layout/footer.php';
    exit(); 
}
// --- END OTORISASI ---

$message = '';
$periklanan_data = null; 
// Ambil ID target dari GET atau POST
$target_id = $_GET['id'] ?? $_POST['periklananid'] ?? ''; 

if (empty($target_id)) {
    // Jika tidak ada ID yang diberikan, arahkan kembali
    header("Location: index.php");
    exit();
}

// ------------------------------------------------------------------
// 1. PROSES POST (UPDATE DATA)
// ------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data yang dikirim dari formulir
    $periklananid = trim($_POST['periklananid']); 
    $mediaperiklanan = trim($_POST['mediaperiklanan']); 
    $biaya = (int)$_POST['biaya'];
    $tanggalmulai = trim($_POST['tanggalmulai']);
    $tanggalselesai = trim($_POST['tanggalselesai']);
    
    // Siapkan Query UPDATE
    $query_update = "UPDATE periklanan SET 
                         mediaperiklanan = $1, 
                         biaya = $2, 
                         tanggalmulai = $3, 
                         tanggalselesai = $4 
                       WHERE periklananid = $5";
                     
    $params = array($mediaperiklanan, $biaya, $tanggalmulai, $tanggalselesai, $periklananid);
    
    // Jalankan Query
    $result = pg_query_params($koneksi, $query_update, $params);

    if ($result) {
        // Redirect ke index dengan status sukses
        header("Location: index.php?status=success_edit");
        exit;
    } else {
        $error_detail = pg_last_error($koneksi);
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Gagal memperbarui Iklan. Error: ' . htmlspecialchars($error_detail) . '</div>';
    }
} 
// ------------------------------------------------------------------
// 2. PROSES GET (TAMPILKAN FORM)
// ------------------------------------------------------------------
// Ambil data Iklan berdasarkan ID
$query_select = "SELECT periklananid, staffid, mediaperiklanan, biaya, tanggalmulai, tanggalselesai FROM periklanan WHERE periklananid = $1";
$result_select = pg_query_params($koneksi, $query_select, array($target_id));

if ($result_select && pg_num_rows($result_select) > 0) {
    $periklanan_data = pg_fetch_assoc($result_select);
} else {
    $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Data Iklan tidak ditemukan.</div>';
}


include '../layout/header.php'; 
?>

<h2>✏️ Edit Kampanye Iklan: <?= htmlspecialchars($periklanan_data['mediaperiklanan'] ?? 'Data Tidak Ada'); ?></h2>

<?= $message; ?>

<?php if ($periklanan_data) : ?>
<form method="POST" action="edit.php">
    <input type="hidden" name="periklananid" value="<?= htmlspecialchars($periklanan_data['periklananid']); ?>">
    
    <p style="color: grey; margin-bottom: 20px;">ID Iklan: <b><?= htmlspecialchars($periklanan_data['periklananid']); ?></b> (Dibuat oleh Staff ID: <?= htmlspecialchars($periklanan_data['staffid']); ?>)</p>

    <label for="mediaperiklanan">Nama Kampanye / Media</label><br>
    <input type="text" id="mediaperiklanan" name="mediaperiklanan" value="<?= htmlspecialchars($periklanan_data['mediaperiklanan']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="biaya">Biaya (Rp)</label><br>
    <input type="number" id="biaya" name="biaya" value="<?= htmlspecialchars($periklanan_data['biaya']); ?>" required min="0" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="tanggalmulai">Tanggal Mulai</label><br>
    <input type="date" id="tanggalmulai" name="tanggalmulai" value="<?= htmlspecialchars($periklanan_data['tanggalmulai']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>
    
    <label for="tanggalselesai">Tanggal Berakhir</label><br>
    <input type="date" id="tanggalselesai" name="tanggalselesai" value="<?= htmlspecialchars($periklanan_data['tanggalselesai']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Perbarui Data</button>
    <a href="index.php" class="btn btn-warning" style="color: black;">Batal</a>
</form>
<?php endif; ?>

<?php
include '../layout/footer.php';
pg_close($koneksi);
?>