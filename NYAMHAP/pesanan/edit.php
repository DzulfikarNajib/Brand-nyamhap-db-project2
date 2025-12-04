<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_role_edit = 'SPEN'; 
$is_sales_staff = ($staff_role === $allowed_role_edit);
$is_view_mode = !$is_sales_staff;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Pesanan tidak ditemukan.");
}

$pesanan_id = pg_escape_string($koneksi, $_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($is_view_mode) {
        die("Akses ditolak. Anda tidak memiliki izin untuk menyimpan perubahan.");
    }

    if (isset($_POST['jumlah']) && is_array($_POST['jumlah'])) {
        foreach ($_POST['jumlah'] as $menuid => $jumlah_baru) {

            $menuid = pg_escape_string($koneksi, $menuid);
            $jumlah_baru = (int)$jumlah_baru;

            if ($jumlah_baru < 1) { 
                $jumlah_baru = 1;
            }

            $update = "
                UPDATE detailpesanan
                SET jumlahmenu = $jumlah_baru
                WHERE pesananid = '$pesanan_id' AND menuid = '$menuid'
            ";
            pg_query($koneksi, $update);
        }

        $update_total = "
            UPDATE pesanan
            SET totalharga = (
                SELECT SUM(dp.jumlahmenu * m.harga)
                FROM detailpesanan dp
                JOIN menu m ON dp.menuid = m.menuid
                WHERE dp.pesananid = '$pesanan_id'
            )
            WHERE pesananid = '$pesanan_id'
        ";
        pg_query($koneksi, $update_total);

        header("Location: index.php?status=success_edit");
        exit();
    }
}


$query_header = "
    SELECT p.pesananid, p.tanggal, p.totalharga,
           COALESCE(pl.namap, 'Pelanggan Umum') AS namap,
           pb.statuspembayaran
    FROM pesanan p
    LEFT JOIN pelanggan pl ON p.pelangganid = pl.pelangganid
    JOIN pembayaran pb ON p.pesananid = pb.pesananid
    WHERE p.pesananid = '$pesanan_id'
";
$res_header = pg_query($koneksi, $query_header);
$header = pg_fetch_assoc($res_header);

if (!$header) {
    die("Pesanan tidak ditemukan.");
}

$query_detail = "
    SELECT dp.pesananid, dp.menuid, dp.jumlahmenu,
           m.namamenu, m.harga,
           (dp.jumlahmenu * m.harga) AS subtotal
    FROM detailpesanan dp
    JOIN menu m ON dp.menuid = m.menuid
    WHERE dp.pesananid = '$pesanan_id'
";
$res_detail = pg_query($koneksi, $query_detail);

include '../layout/header.php';
?>

<h2>
    <?php echo $is_view_mode ? 'Lihat Detail' : 'Edit'; ?> Pesanan #<?php echo htmlspecialchars($pesanan_id); ?>
    <?php if ($is_view_mode) echo '<span style="color: orange; font-size: 0.8em;">(Mode Lihat Saja)</span>'; ?>
</h2>

<p>
    <strong>Pelanggan:</strong> <?php echo htmlspecialchars($header['namap']); ?><br>
    <strong>Tanggal:</strong> <?php echo htmlspecialchars($header['tanggal']); ?><br>
    <strong>Status Pembayaran:</strong> <span style="font-weight: bold; color: <?php echo $header['statuspembayaran'] == 'Lunas' ? 'green' : 'red'; ?>;"><?php echo htmlspecialchars($header['statuspembayaran']); ?></span><br>
    <strong>Total Harga:</strong> Rp <?php echo number_format($header['totalharga'], 0, ',', '.'); ?>
</p>

<form method="POST">
    <table border="1" width="100%" cellpadding="8" style="border-collapse: collapse;">
        <thead style="background: #333; color: #fff;">
            <tr>
                <th>Menu</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php while($row = pg_fetch_assoc($res_detail)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['namamenu']); ?></td>

                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>

                <td>
                    <input 
                        type="number" 
                        name="jumlah[<?php echo htmlspecialchars($row['menuid']); ?>]" 
                        value="<?php echo htmlspecialchars($row['jumlahmenu']); ?>" 
                        min="1"
                        style="width: 70px;"
                        <?php echo $is_view_mode ? 'readonly' : ''; ?> 
                        >
                </td>

                <td>Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?></td>

                <td>
                    <?php if (!$is_view_mode) : ?>
                        <a href="hapus_item.php?menu_id=<?php echo htmlspecialchars($row['menuid']); ?>&pesanan=<?php echo htmlspecialchars($pesanan_id); ?>"
                            onclick="return confirm('Hapus menu ini?');"
                            class="btn btn-danger btn-sm">
                            ❌ Hapus
                        </a>
                    <?php else : ?>
                        <span style="color: grey;">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>

    <?php if (!$is_view_mode) : ?>
        <button type="submit" class="btn btn-primary">
            Simpan Perubahan Jumlah
        </button>
    <?php else : ?>
        <p style="color: orange; font-weight: bold;">Anda tidak memiliki izin untuk menyimpan perubahan.</p>
    <?php endif; ?>

    <a href="index.php" class="btn btn-warning">Kembali</a>
</form>

<?php include '../layout/footer.php'; ?>