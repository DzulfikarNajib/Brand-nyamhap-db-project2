<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_login_id = $_SESSION['staff_id'];
$founder_id = 'S001'; 

if ($staff_login_id !== $founder_id) {
    include '../layout/header.php';
    echo '<h2>Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk menambah data Staff. Fitur ini hanya untuk Founder (ID: ' . $founder_id . ').</p>';
    include '../layout/footer.php';
    exit(); 
}

$generated_staffid = 'S001';
$res_max = pg_query($koneksi, "SELECT MAX((substring(staffid FROM 2))::int) AS maxid FROM staff");
if ($res_max) {
    $row = pg_fetch_assoc($res_max);
    $maxid = isset($row['maxid']) && $row['maxid'] !== null ? intval($row['maxid']) : 0;
    $nextnum = $maxid + 1;
    $generated_staffid = 'S' . str_pad($nextnum, 3, '0', STR_PAD_LEFT);
}

$message = '';
$selected_role = 'SPem'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staffid = $generated_staffid;
    $nama = trim($_POST['nama']);
    $nohp = trim($_POST['nohp']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_hash_input = $password; 
    $selected_role = trim($_POST['rolekode'] ?? 'SPem');
    $allowed_roles = ['SPem', 'SPen', 'SKeu', 'SProd', 'SMar'];
    if (!in_array($selected_role, $allowed_roles)) {
        $selected_role = 'SPem';
    }

    if (empty($nama) || empty($nohp) || empty($email) || empty($password)) {
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Semua field wajib diisi!</div>';
    } else {
        $query = "INSERT INTO staff (staffid, namas, nohandphones, email, passwordhash, rolekode) 
                  VALUES ($1, $2, $3, $4, $5, $6)";
        
        $params = array($staffid, $nama, $nohp, $email, $password_hash_input, $selected_role);
        
        $result = pg_query_params($koneksi, $query, $params);

        if ($result) {
            $_SESSION['pesan_status'] = "
            <div class='alert alert-success' style='padding:10px; margin-bottom:10px;'>
                Menu <b>$staffid</b> berhasil ditambahkan!
            </div>
            ";
            $res_max = pg_query($koneksi, "SELECT MAX((substring(staffid FROM 2))::int) AS maxid FROM staff");
            if ($res_max) {
                $row = pg_fetch_assoc($res_max);
                $maxid = isset($row['maxid']) && $row['maxid'] !== null ? intval($row['maxid']) : 0;
                $nextnum = $maxid + 1;
                $generated_staffid = 'S' . str_pad($nextnum, 3, '0', STR_PAD_LEFT);
                header("Location: /NYAMHAP/staff/index.php");
                exit();

            }
        } else {
            $error_detail = pg_last_error($koneksi);
            if (strpos($error_detail, 'duplicate key value') !== false) {
                 $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Gagal Menambahkan karena Staff ID, Email, atau No. HP sudah terdaftar.</div>';
            } else {
                 $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Gagal menambahkan Staff. Error: ' . htmlspecialchars($error_detail) . '</div>';
            }
        }
    }
}

include '../layout/header.php'; 
?>

<h2>Tambah Staff Baru</h2>

<?= $message; ?>

<form method="POST" action="create.php">
    <label for="staffid">Staff ID (otomatis)</label><br>
    <input type="text" id="staffid" name="staffid" value="<?= htmlspecialchars($generated_staffid); ?>" readonly style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="nama">Nama Staff</label><br>
    <input type="text" id="nama" name="nama" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="nohp">No. Handphone</label><br>
    <input type="text" id="nohp" name="nohp" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="email">Email</label><br>
    <input type="email" id="email" name="email" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br><br>

    <label for="rolekode">Role</label><br>
    <select id="rolekode" name="rolekode" required style="width: 310px; padding: 8px; margin-bottom:10px;">
        <option value="SPem" <?= $selected_role === 'SPem' ? 'selected' : '' ?>>Staff Pemasok</option>
        <option value="SPen" <?= $selected_role === 'SPen' ? 'selected' : '' ?>>Staff Penjualan</option>
        <option value="SKeu" <?= $selected_role === 'SKeu' ? 'selected' : '' ?>>Staff Keuangan</option>
        <option value="SProd" <?= $selected_role === 'SProd' ? 'selected' : '' ?>>Staff Produksi</option>
        <option value="SMar" <?= $selected_role === 'SMar' ? 'selected' : '' ?>>Staff Marketing</option>
    </select><br>
    </select><br>

    <label for="password">Password (Default: untuk login)</label><br>
    <input type="password" id="password" name="password" required style="width: 300px; padding: 8px; margin-bottom: 10px;"><br>

    <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Simpan Staff</button>
    <a href="index.php" class="btn btn-warning" style="color: black;">Batal</a>
</form>

<?php
include '../layout/footer.php';
?>
