<?php
// FILE: /NYAMHAP/periklanan/create.php

// Panggil konfigurasi dan koneksi
include '../config/db.php';
session_start();

// --- PENGECEKAN AUTENTIKASI ---
if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

// --- OTORISASI BARU: HANYA FOUNDER ATAU STAFF MARKETING (SMKT) ---
$staff_login_id = $_SESSION['staff_id'];
$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['FOUNDER', 'SMKT']; 

if (!in_array($staff_role, $allowed_roles)) {
    include '../layout/header.php';
    echo '<h2>❌ Akses Ditolak</h2>';
    echo '<p style="color: red;">Fitur ini hanya untuk Founder dan Staff Marketing (Role: ' . implode(', ', $allowed_roles) . ').</p>';
    include '../layout/footer.php';
    exit();
}
// --- END OTORISASI ---

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan data dari formulir
    $periklananid = strtoupper(trim($_POST['periklananid'])); 
    $mediaperiklanan = trim($_POST['mediaperiklanan']); 
    $biaya = (int)$_POST['biaya'];
    $tanggalmulai = trim($_POST['tanggalmulai']);
    $tanggalselesai = trim($_POST['tanggalselesai']);
    
    // Asumsi: staffid yang membuat iklan adalah staff yang sedang login
    $staffid_input = $staff_login_id; 

    // Validasi Data Sederhana
    if (empty($periklananid) || empty($mediaperiklanan) || empty($tanggalmulai) || empty($tanggalselesai) || $biaya === null) {
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Semua field wajib diisi!</div>';
    } else {
        
        // Query INSERT 
        $query = "INSERT INTO periklanan (periklananid, staffid, mediaperiklanan, biaya, tanggalmulai, tanggalselesai) 
                  VALUES ($1, $2, $3, $4, $5, $6)";
        
        $params = array($periklananid, $staffid_input, $mediaperiklanan, $biaya, $tanggalmulai, $tanggalselesai);
        
        $result = pg_query_params($koneksi, $query, $params);

        if ($result) {
            // Redirect ke index dengan status sukses
            header("Location: index.php?status=success_create");
            exit();
        } else {
            $error_detail = pg_last_error($koneksi);
            if (strpos($error_detail, 'duplicate key value') !== false) {
                 $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Gagal: ID Iklan sudah terdaftar.</div>';
            } else {
                 $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Gagal menambahkan Iklan. Error: ' . htmlspecialchars($error_detail) . '</div>';
            }
        }
    }
}

include '../layout/header.php'; 
?>

<h2>➕ Tambah Kampanye Iklan Baru</h2>

<?= $message; ?>

<form method="POST" action="create.php">
    <label for="periklananid">ID Iklan (Contoh: AD006)</label><br>
    <input type="text" id="periklananid" name="periklananid" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="mediaperiklanan">Nama Kampanye / Media</label><br>
    <input type="text" id="mediaperiklanan" name="mediaperiklanan" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="biaya">Biaya (Rp)</label><br>
    <input type="number" id="biaya" name="biaya" required min="0" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="tanggalmulai">Tanggal Mulai</label><br>
    <input type="date" id="tanggalmulai" name="tanggalmulai" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>
    
    <label for="tanggalselesai">Tanggal Berakhir</label><br>
    <input type="date" id="tanggalselesai" name="tanggalselesai" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>
    
    <p style="color: gray; font-size: small;">*ID Staff yang tercatat sebagai pembuat: <?= htmlspecialchars($staff_login_id); ?></p>

    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Simpan Kampanye</button>
    <a href="index.php" class="btn btn-warning" style="color: black;">Batal</a>
</form>

<?php
include '../layout/footer.php';
?>
