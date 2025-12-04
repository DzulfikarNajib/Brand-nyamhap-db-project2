<?php
// FILE: /NYAMHAP/staff/edit.php

// Panggil konfigurasi dan koneksi
include '../config/db.php';
session_start();

// --- PENGECEKAN AUTENTIKASI ---
if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

// Inisialisasi variabel
$message = '';
$staff_data = null; 
$staff_login_id = $_SESSION['staff_id'];
$founder_id = 'S001'; 
$is_founder = ($staff_login_id === $founder_id);

// Ambil ID yang mau diedit, dari GET (saat pertama kali buka) atau POST (saat submit form)
$target_staff_id = $_GET['id'] ?? $_POST['staffid'] ?? '';

// --- FITUR OTORISASI DUA LAPIS ---
// Akses DISETUJUI jika:
// 1. Dia adalah Founder (S001)
// ATAU
// 2. ID yang akan diedit SAMA dengan ID yang sedang login
$is_authorized = $is_founder || ($staff_login_id === $target_staff_id && !empty($target_staff_id));

if (!$is_authorized) {
    // Jika tidak memenuhi kedua kondisi di atas (Akses Ditolak)
    include '../layout/header.php';
    echo '<h2>Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk mengedit data Staff ini. Anda hanya dapat mengedit data Anda sendiri, atau jika Anda adalah Founder (ID: ' . $founder_id . ').</p>';
    include '../layout/footer.php';
    exit(); // Hentikan script
}
// --- END OTORISASI ---

$message = '';
// ------------------------------------------------------------------
// 1. PROSES POST (UPDATE DATA)
// ------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Data sudah dipastikan hanya bisa di-POST oleh Founder atau Staff itu sendiri
    $staffid = $target_staff_id; // Sudah diset dari awal
    $nama = trim($_POST['nama']);
    $nohp = trim($_POST['nohp']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Siapkan Query UPDATE
    $query_update = "UPDATE staff SET namas = $1, nohandphones = $2, email = $3";
    $params = array($nama, $nohp, $email);
    $param_index = 4;

    if (!empty($password)) {
        $query_update .= ", passwordhash = $" . $param_index;
        $params[] = $password; 
        $param_index++;
    }

    $query_update .= " WHERE staffid = $" . $param_index;
    $params[] = $staffid; 

    // Jalankan Query
    $result = pg_query_params($koneksi, $query_update, $params);

   
    if ($query_update) {

        // Simpan pesan ke session
        $_SESSION['pesan_status'] = "
            <div class='alert alert-success' style='padding:10px; margin-bottom:10px;'>
                Menu <b>$staffid</b> berhasil diperbarui!
            </div>
        ";

        header("Location: /NYAMHAP/staff/index.php");
        exit();
    } else {

        $message = "
            <div class='alert alert-danger' style='padding:10px; margin-bottom:10px;'>
                Gagal memperbarui menu: " . pg_last_error($koneksi) . "
            </div>
        ";

    }
} 
// ------------------------------------------------------------------
// 2. PROSES GET (TAMPILKAN FORM)
// ------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "GET" || ($staff_data === null && !empty($target_staff_id))) {
    
    if (empty($target_staff_id)) {
        // Jika ID kosong (walaupun sudah dicek di otorisasi, ini jaga-jaga)
        header("Location: index.php");
        exit();
    }
    
    // Ambil data Staff berdasarkan ID
    $query_select = "SELECT staffid, namas, nohandphones, email FROM staff WHERE staffid = $1";
    $result_select = pg_query_params($koneksi, $query_select, array($target_staff_id));
    
    if ($result_select && pg_num_rows($result_select) > 0) {
        $staff_data = pg_fetch_assoc($result_select);
    } else {
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Data Staff tidak ditemukan.</div>';
    }
}


include '../layout/header.php'; 
?>

<h2>Edit Data Staff: <?= htmlspecialchars($staff_data['staffid'] ?? 'ID Tidak Ada'); ?></h2>

<?= $message; ?>

<?php if ($staff_data) : ?>
<form method="POST" action="edit.php">
    <input type="hidden" name="staffid" value="<?= htmlspecialchars($staff_data['staffid']); ?>">

    <label for="nama">Nama Staff</label><br>
    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($staff_data['namas']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="nohp">No. Handphone</label><br>
    <input type="text" id="nohp" name="nohp" value="<?= htmlspecialchars($staff_data['nohandphones']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="email">Email</label><br>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($staff_data['email']); ?>" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="password">Password Baru</label><br>
    <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password" style="width: 300px; padding: 8px; margin-bottom: 10px;"><br>
    <small style="color: grey;">*Password hanya diubah jika field ini diisi.</small><br><br>

    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Perbarui Data</button>
    <a href="index.php" class="btn btn-warning" style="color: black;">Kembali</a>
</form>
<?php endif; ?>

<?php
include '../layout/footer.php';
?>