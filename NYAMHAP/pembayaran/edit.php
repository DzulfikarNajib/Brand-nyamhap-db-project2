<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$is_allowed = in_array($staff_role, ['FOUNDER', 'SKEU']); 
$can_edit = ($staff_role === 'SKEU'); 

$message = '';
$pembayaran_data = null; 

$status_options = ['Lunas', 'Pending', 'Gagal'];

$target_id = $_GET['id'] ?? $_POST['pembayaranid'] ?? ''; 

if (empty($target_id)) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$can_edit) {
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Anda tidak memiliki izin untuk memperbarui status. Hanya staff keuangan yang boleh melakukan aksi ini.</div>';
    } else {
        $pembayaranid = trim($_POST['pembayaranid']); 
        $statuspembayaran = trim($_POST['statuspembayaran']);

        $query_update = "UPDATE Pembayaran SET 
                            StatusPembayaran = $1,
                            TanggalPembayaran = CURRENT_DATE 
                         WHERE PembayaranID = $2";
                         
        $params = array($statuspembayaran, $pembayaranid);
        
        $result = pg_query_params($koneksi, $query_update, $params);

        if ($result) {
            $_SESSION['payment_message'] = '<div class="btn-primary" style="padding: 10px; margin-bottom: 15px;">✅ Status Pembayaran ID **' . htmlspecialchars($pembayaranid) . '** berhasil diperbarui menjadi **' . htmlspecialchars($statuspembayaran) . '** (Tanggal Otomatis Hari Ini).</div>';
            header("Location: index.php");
            exit();
        } else {
            $error_detail = pg_last_error($koneksi);
            $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Gagal memperbarui Pembayaran. Error: ' . htmlspecialchars($error_detail) . '</div>';
        }
    }
} 

$query_select = "
    SELECT 
        B.PembayaranID, B.PesananID, B.TanggalPembayaran, B.MetodePembayaran, B.StatusPembayaran,
        P.TotalHarga, Pg.NamaP
    FROM Pembayaran B
    JOIN Pesanan P ON B.PesananID = P.PesananID
    JOIN Pelanggan Pg ON P.PelangganID = Pg.PelangganID
    WHERE B.PembayaranID = $1
";

$result_select = pg_query_params($koneksi, $query_select, array($target_id));

if ($result_select && pg_num_rows($result_select) > 0) {
    $pembayaran_data = pg_fetch_assoc($result_select);
} else {
    $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">❌ Data Pembayaran tidak ditemukan.</div>';
}

include '../layout/header.php'; 
?>

<h2>Update Status Pembayaran Transaksi</h2>

<?= $message; ?>

<?php if ($pembayaran_data) : ?>
    <p style="color: grey; margin-bottom: 20px;">
        ID Pembayaran: <b><?= htmlspecialchars($pembayaran_data['pembayaranid']); ?></b><br>
        Pesanan ID: <b><?= htmlspecialchars($pembayaran_data['pesananid']); ?></b> (Pelanggan: <?= htmlspecialchars($pembayaran_data['namap']); ?>, Total: Rp <?= number_format($pembayaran_data['totalharga'], 0, ',', '.'); ?>)<br>
        Metode Awal: <b><?= htmlspecialchars($pembayaran_data['metodepembayaran']); ?></b><br>
        Tanggal Terakhir Dicatat: <b><?= htmlspecialchars($pembayaran_data['tanggalpembayaran']); ?></b>
    </p>

    <?php if ($can_edit) : ?>
        <form method="POST" action="edit.php">
            <input type="hidden" name="pembayaranid" value="<?= htmlspecialchars($pembayaran_data['pembayaranid']); ?>">
            
            <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                <label for="statuspembayaran">Status Pembayaran</label><br>
                <select id="statuspembayaran" name="statuspembayaran" required style="width: 300px; padding: 8px; margin-bottom: 10px;">
                    <?php foreach ($status_options as $status) : ?>
                        <option value="<?= htmlspecialchars($status); ?>" <?= ($pembayaran_data['statuspembayaran'] == $status) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="color: red; font-weight: bold; margin-top: 10px;">PERHATIAN: Tanggal Pembayaran akan otomatis diperbarui ke tanggal hari ini saat Anda klik 'Perbarui Status'.</p>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Perbarui Status</button>
            <a href="index.php" class="btn btn-warning" style="color: black;">Batal</a>
        </form>
    <?php else: ?>
        <div class="btn-warning" style="padding:10px; margin-top:10px;">
            Anda tidak memiliki izin untuk mengubah status. Hanya staff keuangan yang dapat memperbarui status pembayaran.
        </div>
        <a href="index.php" class="btn btn-secondary" style="margin-top:10px; display:inline-block;">Kembali</a>
    <?php endif; ?>
<?php endif; ?>

<?php
include '../layout/footer.php';
?>