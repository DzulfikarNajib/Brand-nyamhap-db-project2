<?php

session_start();
include '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['FOUNDER', 'SPROD'];

if (!in_array($staff_role, $allowed_roles)) {
    include '../layout/header.php';
    echo '<h2>Akses Ditolak</h2>';
    echo '<p style="color: red;">Anda tidak memiliki izin untuk menambah data Menu.</p>';
    include '../layout/footer.php';
    exit();
}

$pesan_status = '';

function generateMenuID($koneksi)
{
    $result = pg_query($koneksi, "SELECT menuid FROM menu ORDER BY menuid DESC LIMIT 1");

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $lastID = $row['menuid'];  
        $num = intval(substr($lastID, 1));
        $num++;
        return "M" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "M001";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $menuid = generateMenuID($koneksi);
    $nama = pg_escape_string($koneksi, $_POST['nama_menu']);
    $deskripsi = pg_escape_string($koneksi, $_POST['deskripsi']);
    $harga = (int)$_POST['harga'];

    $query = "INSERT INTO menu (menuid, namamenu, deskripsi, harga)
              VALUES ($1, $2, $3, $4)";
    $result = pg_query_params($koneksi, $query, array($menuid, $nama, $deskripsi, $harga));

    if ($result) {
        $_SESSION['pesan_status'] = "
            <div class='alert alert-success'>
                Menu <b>$menuid</b> berhasil ditambahkan!
            </div>
        ";
        header("Location: /NYAMHAP/menu/index.php");
        exit();
    } else {
        $_SESSION['pesan_status'] = "
            <div class='alert alert-danger'>
                Gagal menambah menu: ".pg_last_error($koneksi)."
            </div>
        ";
        header("Location: /NYAMHAP/menu/index.php");
        exit();
    }
}

include '../layout/header.php';


if (isset($_SESSION['pesan_status'])) {
    echo $_SESSION['pesan_status'];
    unset($_SESSION['pesan_status']);
}

?>
<style>
    form {
        width: 95%;
        background: #ffffff;
        padding: 25px;
        margin-top: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .form-group input,
    .form-group textarea {
        width: 98%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
    }

    textarea {
        height: 90px;
        resize: vertical;
    }

    button.btn-primary {
        background: #ff7a00;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    button.btn-primary:hover {
        background: #e86c00;
    }

    .btn-danger {
        margin-left: 10px;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
    }
</style>


<h2>Tambah Menu Baru</h2>

<?= $pesan_status ?>

<form method="POST" action="">
    <div class="form-group">
        <label for="nama_menu">Nama Menu:</label>
        <input type="text" id="nama_menu" name="nama_menu" required>
    </div>

    <div class="form-group">
        <label for="deskripsi">Deskripsi:</label>
        <textarea id="deskripsi" name="deskripsi" required></textarea>
    </div>

    <div class="form-group">
        <label for="harga">Harga (Rp):</label>
        <input type="number" id="harga" name="harga" min="0" required>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Menu</button>
    <a href="index.php" class="btn btn-danger">Batal</a>
</form>

<?php
include '../layout/footer.php';
pg_close($koneksi);
?>
