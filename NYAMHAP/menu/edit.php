<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['FOUNDER', 'SPRO']; 

if (!in_array($staff_role, $allowed_roles)) {
    include '../layout/header.php';
    echo "<h2>Akses Ditolak</h2>";
    echo "<p style='color:red;'>Anda tidak memiliki izin untuk mengedit menu.</p>";
    include '../layout/footer.php';
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID menu tidak ditemukan.");
}

$menu_id = $_GET['id'];

$query = "SELECT menuid, namamenu, deskripsi, harga FROM menu WHERE menuid = $1";
$result = pg_query_params($koneksi, $query, array($menu_id));
$menu = pg_fetch_assoc($result);

if (!$menu) {
    die("Menu tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = pg_escape_string($koneksi, $_POST['nama_menu']);
    $deskripsi = pg_escape_string($koneksi, $_POST['deskripsi']);
    $harga = (int) $_POST['harga'];

    $update_query = "
        UPDATE menu
        SET namamenu = $1,
            deskripsi = $2,
            harga = $3
        WHERE menuid = $4
    ";

    $update_result = pg_query_params(
        $koneksi,
        $update_query,
        array($nama, $deskripsi, $harga, $menu_id)
    );

    if ($update_result) {

        $_SESSION['pesan_status'] = "
            <div class='alert alert-success' style='padding:10px; margin-bottom:10px;'>
                Menu <b>$menu_id</b> berhasil diperbarui!
            </div>
        ";

        header("Location: /NYAMHAP/menu/index.php");
        exit();
    } else {

        $_SESSION['pesan_status'] = "
            <div class='alert alert-danger' style='padding:10px; margin-bottom:10px;'>
                Gagal memperbarui menu: " . pg_last_error($koneksi) . "
            </div>
        ";

        header("Location: /NYAMHAP/menu/index.php");
        exit();
    }
}

include '../layout/header.php';
?>

<h2>Edit Menu</h2>

<form method="POST" action="edit.php?id=<?php echo $menu_id; ?>">

    <div class="form-group">
        <label>ID Menu:</label>
        <strong><?php echo htmlspecialchars($menu['menuid']); ?></strong>
    </div>

    <div class="form-group">
        <label for="nama_menu">Nama Menu:</label>
        <input type="text" id="nama_menu" name="nama_menu" required
            value="<?php echo htmlspecialchars($menu['namamenu']); ?>">
    </div>

    <div class="form-group">
        <label for="deskripsi">Deskripsi:</label>
        <textarea id="deskripsi" name="deskripsi" required><?php echo htmlspecialchars($menu['deskripsi']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="harga">Harga (Rp):</label>
        <input type="number" id="harga" name="harga" min="0" required
            value="<?php echo htmlspecialchars($menu['harga']); ?>">
    </div>

    <button type="submit" class="btn btn-primary">Update Menu</button>
    <a href="index.php" class="btn btn-danger">Batal</a>

</form>

<?php
include '../layout/footer.php';
pg_close($koneksi);
?>
