<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['SPEN', 'SKEU']; 
if (!in_array($staff_role, $allowed_roles)) {
    $_SESSION['payment_message'] = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Akses ditolak: Hanya staf penjualan/keuangan yang dapat mengakses.</div>';
    header("Location: index.php");
    exit();
}

$message = '';

$metode_options = ['Cash', 'Transfer', 'QRIS', 'E-Wallet'];
$status_options = ['Lunas', 'Pending', 'Gagal'];

$query_pesanan = "
    SELECT 
        P.PesananID, P.TotalHarga, Pg.NamaP, B.PembayaranID
    FROM Pesanan P
    JOIN Pelanggan Pg ON P.PelangganID = Pg.PelangganID
    -- Cek Pembayaran yang statusnya BUKAN Lunas, dan hanya ambil 1 baris Pembayaran per Pesanan.
    JOIN Pembayaran B ON P.PesananID = B.PesananID
    WHERE B.StatusPembayaran != 'Lunas' -- Ambil yang statusnya masih Pending atau Gagal (belum final)
    ORDER BY P.Tanggal DESC
";
$result_pesanan = pg_query($koneksi, $query_pesanan);

if (!$result_pesanan) {
    die("Error mengambil data pesanan: " . pg_last_error($koneksi));
}
$pesanan_list = pg_fetch_all($result_pesanan) ?: [];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $pembayaranid = trim($_POST['pembayaranid_update']); 
    $metodepembayaran = trim($_POST['metodepembayaran']);
    $statuspembayaran = trim($_POST['statuspembayaran']); 
    
    if (empty($pembayaranid) || empty($metodepembayaran) || empty($statuspembayaran)) {
        $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Semua field wajib diisi!</div>';
    } else {
        
        $query = "UPDATE Pembayaran SET 
                    TanggalPembayaran = CURRENT_DATE,
                    MetodePembayaran = $1, 
                    StatusPembayaran = $2
                  WHERE PembayaranID = $3";
        
        $params = array($metodepembayaran, $statuspembayaran, $pembayaranid);
        
        $result = pg_query_params($koneksi, $query, $params);

        if ($result) {
            $_SESSION['payment_message'] = '<div class="btn-primary" style="padding: 10px; margin-bottom: 15px;">Pembayaran ID ' . htmlspecialchars($pembayaranid) . ' berhasil di-UPDATE menjadi **' . htmlspecialchars($statuspembayaran) . '**!</div>';
            header("Location: index.php");
            exit();
        } else {
            $error_detail = pg_last_error($koneksi);
            $message = '<div class="btn-danger" style="padding: 10px; margin-bottom: 15px;">Gagal memperbarui Pembayaran. Error: ' . htmlspecialchars($error_detail) . '</div>';
        }
    }
}

include '../layout/header.php'; 
?>

<h2>Update Pembayaran Transaksi Belum Lunas</h2>

<?= $message; ?>

<form method="POST" action="input.php">
    <label for="pesananid">Pilih Pesanan (yang Status Pembayaran Awalnya Belum Lunas)</label><br>
    <select id="pesanan-select" name="pesananid_dummy" required style="width: 300px; padding: 8px; margin-bottom: 10px;">
        <option value="">-- Pilih Pesanan --</option>
        <?php foreach ($pesanan_list as $pesanan) : ?>
            <option value="<?= htmlspecialchars($pesanan['pembayaranid']); ?>" 
                    data-pesananid="<?= htmlspecialchars($pesanan['pesananid']); ?>">
                <?= htmlspecialchars($pesanan['pesananid']); ?> - <?= htmlspecialchars($pesanan['namap']); ?> (Rp <?= number_format($pesanan['totalharga'], 0, ',', '.'); ?>)
            </option>
        <?php endforeach; ?>
    </select><br>

    <input type="hidden" id="pembayaranid_update_field" name="pembayaranid_update" value="">
    
    <label for="pembayaranid_display">ID Pembayaran yang Akan Diperbarui</label><br>
    <input type="text" id="pembayaranid_display" disabled style="width: 300px; padding: 8px; margin-bottom: 10px; background-color: #eee;" value=""><br><br>

    <?php if (empty($pesanan_list)) : ?>
        <p style="color: green;">Semua Pesanan yang tercatat sudah Lunas atau sudah final.</p>
    <?php endif; ?>

    <p style="color: red; margin-bottom: 15px;">Tanggal Pembayaran akan otomatis dicatat sebagai tanggal hari ini!!.</p>

    <label for="metodepembayaran">Metode Pembayaran</label><br>
    <select id="metodepembayaran" name="metodepembayaran" required style="width: 300px; padding: 8px; margin-bottom: 10px;">
        <?php foreach ($metode_options as $metode) : ?>
            <option value="<?= $metode; ?>" <?= (($_POST['metodepembayaran'] ?? '') == $metode) ? 'selected' : ''; ?>>
                <?= $metode; ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>
    
    <label for="statuspembayaran">Status Pembayaran</label><br>
    <select id="statuspembayaran" name="statuspembayaran" required style="width: 300px; padding: 8px; margin-bottom: 10px;">
        <?php foreach ($status_options as $status) : ?>
            <option value="<?= $status; ?>" <?= ($status == 'Lunas') ? 'selected' : ''; ?>>
                <?= $status; ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit" class="btn btn-primary" style="margin-top: 15px;" <?= empty($pesanan_list) ? 'disabled' : ''; ?>>Perbarui Pembayaran</button>
    <a href="index.php" class="btn btn-warning" style="color: black;">Kembali ke Daftar</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('pesanan-select');
    const displayField = document.getElementById('pembayaranid_display');
    const updateField = document.getElementById('pembayaranid_update_field');
    
    function updatePaymentIdFields() {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            displayField.value = selectedOption.value;
            updateField.value = selectedOption.value;
        } else {
            displayField.value = '';
            updateField.value = '';
        }
    }

    updatePaymentIdFields(); 

    select.addEventListener('change', updatePaymentIdFields);
});
</script>

<?php
include '../layout/footer.php';
?>