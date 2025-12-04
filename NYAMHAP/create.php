<?php

include '../config/db.php'; 
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_role = 'SPEN';

if ($staff_role !== $allowed_role) {
    include '../layout/header.php';
    echo '<h2>Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk menambah pelanggan.</p>';
    include '../layout/footer.php';
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $pelanggan_id = generateNewPelangganID($koneksi);
    $nama = $_POST['nama_pelanggan'];
    $telepon = $_POST['telepon'];

    if (!empty($nama) && !empty($telepon)) {

        $query = "INSERT INTO pelanggan (pelangganid, namap, nohandphonep)
                  VALUES ($1, $2, $3)";

        $result = pg_query_params($koneksi, $query, array($pelanggan_id, $nama, $telepon));

        if ($result) {

            $_SESSION['pesan_status'] = "
                <div class='alert alert-success' style='padding:10px; margin-bottom:10px;'>
                    Pelanggan <b>$pelanggan_id</b> berhasil ditambahkan!
                </div>
            ";

            header("Location: /NYAMHAP/pelanggan/index.php");
            exit();
        } 
        else {
            $error_message = "Gagal menambahkan pelanggan: " . pg_last_error($koneksi);

            if (strpos($error_message, 'duplicate key value') !== false) {
                $error_message = "Nomor Handphone sudah terdaftar.";
            }
        }
    } else {
        $error_message = "Nama dan Nomor Handphone wajib diisi.";
    }
}

include '../layout/header.php';

function generateNewPelangganID($koneksi) {
    $query = "SELECT MAX(pelangganid) AS max_id FROM pelanggan WHERE pelangganid LIKE 'P%'";
    $result = pg_query($koneksi, $query);

    if (!$result) return "P001";

    $row = pg_fetch_assoc($result);
    $max_id = $row['max_id'];

    if ($max_id) {
        $angka_terakhir = (int) substr($max_id, 1);
        $angka_baru = $angka_terakhir + 1;
        return "P" . sprintf("%03d", $angka_baru);
    } else {
        return "P001";
    }
}
?>

<div style="width: 400px; max-width: 100%; margin: 0 auto;">

    <h2 style="text-align: center; margin-bottom: 25px;">Pelanggan Baru</h2>

    <?php 
    if (isset($error_message)) {
        echo '<div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 15px;">' . htmlspecialchars($error_message) . '</div>';
    }
    ?>
    
    <form method="POST" action="create.php">

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">ID Pelanggan Baru:</label>
            <span style="font-weight: bold; color: #333;"><?php echo generateNewPelangganID($koneksi); ?></span>
            <small style="display: block; color: gray; margin-top: 5px;">(ID dibuat otomatis oleh sistem)</small>
        </div>
        
        <hr>

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="nama_pelanggan" style="display: block; font-weight: bold; margin-bottom: 5px;">Nama Pelanggan:</label>
            <input type="text" id="nama_pelanggan" name="nama_pelanggan" maxlength="100" required 
                    style="width: 100%; padding: 8px; box-sizing: border-box;"
                    value="<?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?>">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="telepon" style="display: block; font-weight: bold; margin-bottom: 5px;">No. Handphone:</label>
            <input type="tel" id="telepon" name="telepon" maxlength="15" required 
                    style="width: 100%; padding: 8px; box-sizing: border-box;"
                    value="<?php echo isset($telepon) ? htmlspecialchars($telepon) : ''; ?>">
        </div>
        
        <hr>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
            <a href="index.php" class="btn btn-danger">Batal</a>
        </div>

    </form>

</div>
<?php
include '../layout/footer.php';
pg_close($koneksi);
?>