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
    echo "<h2>Akses Ditolak</h2>";
    echo "<p style='color:red;'>Anda tidak memiliki izin untuk mengubah data pelanggan.</p>";
    include '../layout/footer.php';
    exit();
}

$pelanggan_id = $_GET['id'] ?? $_POST['pelangganid'] ?? '';

if (empty($pelanggan_id)) {
    header("Location: index.php?status=error&msg=" . urlencode("ID Pelanggan tidak ditemukan"));
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST['nama_pelanggan']);
    $telepon = trim($_POST['telepon']);

    if (!empty($nama) && !empty($telepon)) {

        $query_update = "
            UPDATE pelanggan
            SET namap = $1, nohandphonep = $2
            WHERE pelangganid = $3
        ";

        $update = pg_query_params($koneksi, $query_update, [$nama, $telepon, $pelanggan_id]);

        if ($update) {
            header("Location: index.php?status=edit_success");
            exit();
        } else {
            $pg_error = pg_last_error($koneksi);

            if (strpos($pg_error, 'duplicate key') !== false) {
                $message = "Nomor Handphone sudah terdaftar.";
            } else {
                $message = "Gagal mengupdate data: " . $pg_error;
            }
        }

    } else {
        $message = "Nama dan Nomor Handphone wajib diisi.";
    }
}

$query = "SELECT pelangganid, namap, nohandphonep FROM pelanggan WHERE pelangganid = $1";
$result = pg_query_params($koneksi, $query, [$pelanggan_id]);

$pelanggan = pg_fetch_assoc($result);

if (!$pelanggan) {
    header("Location: index.php?status=error&msg=" . urlencode("Pelanggan tidak ditemukan"));
    exit();
}

include '../layout/header.php';
?>

<div style="width: 400px; margin: 0 auto;">

    <h2 style="text-align:center; margin-bottom: 25px;">Edit Pelanggan</h2>

    <?php if (!empty($message)): ?>
        <div style="color:red; padding:10px; border:1px solid red; margin-bottom:15px;">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit.php">
        <input type="hidden" name="pelangganid" value="<?= htmlspecialchars($pelanggan_id); ?>">

        <label>ID Pelanggan:</label><br>
        <b><?= htmlspecialchars($pelanggan['pelangganid']); ?></b>
        <hr>

        <label>Nama Pelanggan:</label>
        <input type="text" name="nama_pelanggan" required
               value="<?= htmlspecialchars($pelanggan['namap']); ?>"
               style="width:100%; padding:8px; margin-bottom:10px;">

        <label>No. Handphone:</label>
        <input type="tel" name="telepon" required maxlength="15"
               value="<?= htmlspecialchars($pelanggan['nohandphonep']); ?>"
               style="width:100%; padding:8px; margin-bottom:10px;">

        <hr>

        <button type="submit" class="btn btn-primary">Update Pelanggan</button>
        <a href="index.php" class="btn btn-danger">Batal</a>
    </form>
</div>

<?php
include '../layout/footer.php';
pg_close($koneksi);
?>
