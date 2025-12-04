<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$report_data = [];
$report_type = $_GET['type'] ?? 'penjualan'; 
$start_date = $_GET['start_date'] ?? date('Y-m-01'); 
$end_date = $_GET['end_date'] ?? date('Y-m-d'); 
$report_title = '';

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

if ($report_type == 'penjualan') {
    $report_title = 'Laporan Penjualan (Lunas) Berdasarkan Tanggal Pembayaran';
    
    $query = "
        SELECT 
            P.PesananID, 
            Pg.NamaP AS NamaPelanggan,
            B.TanggalPembayaran,
            P.TotalHarga,
            B.MetodePembayaran
        FROM Pembayaran B
        JOIN Pesanan P ON B.PesananID = P.PesananID
        JOIN Pelanggan Pg ON P.PelangganID = Pg.PelangganID
        WHERE B.StatusPembayaran = 'Lunas' 
          AND B.TanggalPembayaran BETWEEN $1 AND $2
        ORDER BY B.TanggalPembayaran ASC
    ";
    $params = array($start_date, $end_date);
    
} elseif ($report_type == 'biaya_iklan') {
    $report_title = 'Laporan Biaya Periklanan Aktif';
    
    $query = "
        SELECT 
            PeriklananID,
            MediaPeriklanan,
            TanggalMulai,
            TanggalSelesai,
            Biaya,
            S.NamaS AS NamaStaff
        FROM Periklanan Pr
        JOIN Staff S ON Pr.StaffID = S.StaffID
        WHERE (TanggalMulai <= $2 AND TanggalSelesai >= $1) 
        ORDER BY TanggalMulai ASC
    ";
    $params = array($start_date, $end_date);

} elseif ($report_type == 'penggunaan_bahan') {
    $report_title = 'Laporan Total Penggunaan Bahan Baku (Berdasarkan Pesanan Lunas)';
    
    $query = "
        SELECT 
            BB.BahanID,
            BB.NamaBahan,
            BB.Satuan,
            BB.HargaPerUnit,
            SUM(DP.JumlahMenu * R.JumlahBahan) AS TotalKuantitasDigunakan,
            SUM(DP.JumlahMenu * R.JumlahBahan * BB.HargaPerUnit) AS TotalBiayaBahan
        FROM Pembayaran B
        JOIN Pesanan P ON B.PesananID = P.PesananID
        JOIN DetailPesanan DP ON P.PesananID = DP.PesananID
        JOIN Resep R ON DP.MenuID = R.MenuID
        JOIN BahanBaku BB ON R.BahanID = BB.BahanID
        WHERE B.StatusPembayaran = 'Lunas' 
          AND B.TanggalPembayaran BETWEEN $1 AND $2
        GROUP BY BB.BahanID, BB.NamaBahan, BB.Satuan, BB.HargaPerUnit
        ORDER BY BB.NamaBahan ASC
    ";
    $params = array($start_date, $end_date);

} elseif ($report_type == 'laba_rugi' || $report_type == 'laba_rugi_detail') {

    $report_title = ($report_type == 'laba_rugi') ? 'Laporan Ringkasan Pemasukan & Pengeluaran (Laba Rugi)' : 'Laporan Laba Rugi Lengkap (Pemasukan vs Pengeluaran)';
  
    $query_pemasukan = "
        SELECT SUM(P.TotalHarga) AS total_pemasukan
        FROM Pembayaran B
        JOIN Pesanan P ON B.PesananID = P.PesananID
        WHERE B.StatusPembayaran = 'Lunas' 
          AND B.TanggalPembayaran BETWEEN $1 AND $2
    ";
    $result_pemasukan = pg_query_params($koneksi, $query_pemasukan, array($start_date, $end_date));
    $pemasukan = pg_fetch_assoc($result_pemasukan)['total_pemasukan'] ?? 0;
    
    $query_pemasukan_rincian = "
        SELECT 
            P.PesananID, 
            Pg.NamaP AS NamaPelanggan,
            B.TanggalPembayaran,
            P.TotalHarga,
            B.MetodePembayaran
        FROM Pembayaran B
        JOIN Pesanan P ON B.PesananID = P.PesananID
        JOIN Pelanggan Pg ON P.PelangganID = Pg.PelangganID
        WHERE B.StatusPembayaran = 'Lunas' 
          AND B.TanggalPembayaran BETWEEN $1 AND $2
        ORDER BY B.TanggalPembayaran ASC
    ";
    $result_pemasukan_rincian = pg_query_params($koneksi, $query_pemasukan_rincian, array($start_date, $end_date));
    $pemasukan_rincian = pg_fetch_all($result_pemasukan_rincian) ?: [];

    $query_bahan_rincian = "
        SELECT 
            BB.NamaBahan,
            BB.Satuan,
            SUM(DP.JumlahMenu * R.JumlahBahan) AS TotalKuantitasDigunakan,
            SUM(DP.JumlahMenu * R.JumlahBahan * BB.HargaPerUnit) AS TotalBiaya
        FROM Pembayaran B
        JOIN Pesanan P ON B.PesananID = P.PesananID
        JOIN DetailPesanan DP ON P.PesananID = DP.PesananID
        JOIN Resep R ON DP.MenuID = R.MenuID
        JOIN BahanBaku BB ON R.BahanID = BB.BahanID
        WHERE B.StatusPembayaran = 'Lunas' 
          AND B.TanggalPembayaran BETWEEN $1 AND $2
        GROUP BY BB.NamaBahan, BB.Satuan
        ORDER BY TotalBiaya DESC
    ";
    $result_bahan = pg_query_params($koneksi, $query_bahan_rincian, array($start_date, $end_date));
    $biaya_bahan_rincian = pg_fetch_all($result_bahan) ?: [];
    $total_biaya_bahan = array_sum(array_column($biaya_bahan_rincian, 'totalbiaya'));

    $query_iklan_rincian = "
        SELECT 
            MediaPeriklanan,
            TanggalMulai,
            TanggalSelesai,
            Biaya
        FROM Periklanan
        WHERE (TanggalMulai <= $2 AND TanggalSelesai >= $1)
        ORDER BY TanggalMulai ASC
    ";
    $result_iklan = pg_query_params($koneksi, $query_iklan_rincian, array($start_date, $end_date));
    $biaya_iklan_rincian = pg_fetch_all($result_iklan) ?: [];
    $total_biaya_iklan = array_sum(array_column($biaya_iklan_rincian, 'biaya'));

    $report_data = [
        'pemasukan' => (float)$pemasukan,
        'pemasukan_rincian' => $pemasukan_rincian, 
        'biaya_bahan_rincian' => $biaya_bahan_rincian,
        'biaya_iklan_rincian' => $biaya_iklan_rincian,
        'total_biaya_bahan' => (float)$total_biaya_bahan,
        'total_biaya_iklan' => (float)$total_biaya_iklan,
        'total_pengeluaran' => (float)$total_biaya_bahan + (float)$total_biaya_iklan,
        'laba_kotor' => (float)$pemasukan - ((float)$total_biaya_bahan + (float)$total_biaya_iklan)
    ];

    unset($query); 
}

if (isset($query)) {
    $result = pg_query_params($koneksi, $query, $params);
    if (!$result) {
        die("Query Error: " . pg_last_error($koneksi));
    }
    $report_data = pg_fetch_all($result) ?: [];
}

include '../layout/header.php';
?>

<h2>📈 <?= $report_title; ?></h2>

<div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
    <form method="GET" action="index.php">
        
        <label for="type">Pilih Jenis Laporan:</label>
        <select name="type" onchange="this.form.submit()" style="padding: 8px;">
            <option value="penjualan" <?= ($report_type == 'penjualan' ? 'selected' : ''); ?>>Laporan Penjualan</option>
            <option value="biaya_iklan" <?= ($report_type == 'biaya_iklan' ? 'selected' : ''); ?>>Laporan Biaya Iklan</option>
            <option value="penggunaan_bahan" <?= ($report_type == 'penggunaan_bahan' ? 'selected' : ''); ?>>Laporan Bahan Baku</option>
            <option value="laba_rugi" <?= ($report_type == 'laba_rugi' ? 'selected' : ''); ?>>Laporan Laba Rugi (Ringkasan)</option>
            <option value="laba_rugi_detail" <?= ($report_type == 'laba_rugi_detail' ? 'selected' : ''); ?>>Laporan Laba Rugi (Detail)</option>
        </select>
        
        <br><br>
        
        <label for="start_date">Dari Tanggal:</label>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date); ?>" required style="padding: 8px; margin-right: 15px;">
        
        <label for="end_date">Sampai Tanggal:</label>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date); ?>" required style="padding: 8px; margin-right: 15px;">
        
        <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
    </form>
</div>

<h3>Hasil untuk Periode: <?= htmlspecialchars($start_date); ?> s/d <?= htmlspecialchars($end_date); ?></h3>

<?php if (empty($report_data) && $report_type != 'laba_rugi' && $report_type != 'laba_rugi_detail') : ?>
    <div class="btn-warning" style="padding: 10px;">
        Data tidak ditemukan untuk periode dan jenis laporan yang dipilih.
    </div>
<?php elseif ($report_type == 'laba_rugi' || $report_type == 'laba_rugi_detail') : ?>

    <?php 
        $laba_class = $report_data['laba_kotor'] >= 0 ? 'btn-success' : 'btn-danger';
        $width = ($report_type == 'laba_rugi') ? '50%' : '70%'; 
    ?>
    
    <div style="width: <?= $width; ?>; margin: 0 auto 30px auto;">
        <table class="table table-bordered">
            <thead>
                <tr class="btn-primary">
                    <th colspan="2">Pemasukan dan Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: bold; width: 70%;">Pemasukan</td>
                    <td style="text-align: right; color: green; font-weight: bold;"><?= formatRupiah($report_data['pemasukan']); ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: bold; background-color: #eee;">Pengeluaran:</td>
                </tr>
                <tr>
                    <td>- Biaya Bahan Baku (HPP)</td>
                    <td style="text-align: right;"><?= formatRupiah($report_data['total_biaya_bahan']); ?></td>
                </tr>
                <tr>
                    <td>- Biaya Periklanan</td>
                    <td style="text-align: right;"><?= formatRupiah($report_data['total_biaya_iklan']); ?></td>
                </tr>
                <tr class="btn-danger">
                    <td style="font-weight: bold;">Total Pengeluaran</td>
                    <td style="text-align: right; font-weight: bold;"><?= formatRupiah($report_data['total_pengeluaran']); ?></td>
                </tr>
                <tr class="<?= $laba_class; ?>">
                    <td style="font-weight: bold;">LABA KOTOR (Pemasukan - Pengeluaran)</td>
                    <td style="text-align: right; font-weight: bold;"><?= formatRupiah($report_data['laba_kotor']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <?php if ($report_type == 'laba_rugi_detail') : ?>
        <hr>
        
        <h4 style="margin-top: 30px;">RINCIAN PEMASUKAN - Penjualan Lunas</h4>
        <?php if (empty($report_data['pemasukan_rincian'])) : ?>
            <p>Tidak ada rincian penjualan lunas tercatat untuk periode ini.</p>
        <?php else : ?>
            <table class="table table-bordered">
                <thead>
                    <tr class="btn-success">
                        <th>ID Pesanan</th>
                        <th>Tgl Pembayaran</th>
                        <th>Pelanggan</th>
                        <th>Metode Bayar</th>
                        <th style="text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data['pemasukan_rincian'] as $row) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['pesananid']); ?></td>
                            <td><?= htmlspecialchars($row['tanggalpembayaran']); ?></td>
                            <td><?= htmlspecialchars($row['namapelanggan']); ?></td>
                            <td><?= htmlspecialchars($row['metodepembayaran']); ?></td>
                            <td style="text-align: right;"><?= formatRupiah($row['totalharga'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="btn-success">
                        <td colspan="4" style="font-weight: bold; text-align: right;">TOTAL PEMASUKAN</td>
                        <td style="font-weight: bold; text-align: right;"><?= formatRupiah($report_data['pemasukan']); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
        
        <hr>

        <h4 style="margin-top: 30px;">RINCIAN PENGELUARAN - Biaya Bahan Baku (HPP)</h4>
        <?php if (empty($report_data['biaya_bahan_rincian'])) : ?>
            <p>Tidak ada biaya bahan baku tercatat untuk periode ini.</p>
        <?php else : ?>
            <table class="table table-bordered">
                <thead>
                    <tr class="btn-info">
                        <th>Nama Bahan</th>
                        <th style="text-align: right;">Total Kuantitas Digunakan</th>
                        <th>Satuan</th>
                        <th style="text-align: right;">Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data['biaya_bahan_rincian'] as $row) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['namabahan']); ?></td>
                            <td style="text-align: right;"><?= number_format($row['totalkuantitasdigunakan'], 2, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($row['satuan']); ?></td>
                            <td style="text-align: right;"><?= formatRupiah($row['totalbiaya'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="btn-info">
                        <td colspan="3" style="font-weight: bold; text-align: right;">SUBTOTAL BIAYA BAHAN BAKU</td>
                        <td style="font-weight: bold; text-align: right;"><?= formatRupiah($report_data['total_biaya_bahan']); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <hr>

        <h4 style="margin-top: 30px;">RINCIAN PENGELUARAN - Biaya Periklanan</h4>
        <?php if (empty($report_data['biaya_iklan_rincian'])) : ?>
            <p>Tidak ada biaya periklanan tercatat untuk periode ini.</p>
        <?php else : ?>
            <table class="table table-bordered">
                <thead>
                    <tr class="btn-info">
                        <th>Media Kampanye</th>
                        <th>Tgl Mulai</th>
                        <th>Tgl Selesai</th>
                        <th style="text-align: right;">Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data['biaya_iklan_rincian'] as $row) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['mediaperiklanan']); ?></td>
                            <td><?= htmlspecialchars($row['tanggalmulai']); ?></td>
                            <td><?= htmlspecialchars($row['tanggalselesai']); ?></td>
                            <td style="text-align: right;"><?= formatRupiah($row['biaya'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="btn-info">
                        <td colspan="3" style="font-weight: bold; text-align: right;">SUBTOTAL BIAYA IKLAN</td>
                        <td style="font-weight: bold; text-align: right;"><?= formatRupiah($report_data['total_biaya_iklan']); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
    
<?php elseif ($report_type == 'penjualan') : ?>
    
    <?php 
        $total_penjualan = array_sum(array_column($report_data, 'totalharga'));
    ?>
    <p style="font-size: 1.1em; font-weight: bold;">
        Total Penjualan Lunas dalam Periode: <?= formatRupiah($total_penjualan); ?>
    </p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Tgl Pembayaran</th>
                <th>Pelanggan</th>
                <th>Metode Bayar</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data as $row) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['pesananid']); ?></td>
                    <td><?= htmlspecialchars($row['tanggalpembayaran']); ?></td>
                    <td><?= htmlspecialchars($row['namapelanggan']); ?></td>
                    <td><?= htmlspecialchars($row['metodepembayaran']); ?></td>
                    <td><?= formatRupiah($row['totalharga'] ?? 0); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($report_type == 'biaya_iklan') : ?>
    
    <?php 
        $total_biaya = array_sum(array_column($report_data, 'biaya'));
    ?>
    <p style="font-size: 1.1em; font-weight: bold;">
        Total Biaya Iklan Aktif dalam Periode: <?= formatRupiah($total_biaya); ?>
    </p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Iklan</th>
                <th>Media Kampanye</th>
                <th>Staff Bertanggung Jawab</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data as $row) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['periklananid']); ?></td>
                    <td><?= htmlspecialchars($row['mediaperiklanan']); ?></td>
                    <td><?= htmlspecialchars($row['namastaff']); ?></td>
                    <td><?= htmlspecialchars($row['tanggalmulai']); ?></td>
                    <td><?= htmlspecialchars($row['tanggalselesai']); ?></td>
                    <td><?= formatRupiah($row['biaya'] ?? 0); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($report_type == 'penggunaan_bahan') : ?>
    
    <?php 
        $total_biaya_bahan = array_sum(array_column($report_data, 'totalbiayabahan'));
    ?>
    <p style="font-size: 1.1em; font-weight: bold;">
        Total Biaya Bahan Baku Terpakai: <?= formatRupiah($total_biaya_bahan); ?>
    </p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Bahan</th>
                <th>Nama Bahan</th>
                <th>Harga Per Unit</th>
                <th>Total Kuantitas Digunakan</th>
                <th>Satuan</th>
                <th>Total Biaya Bahan Terpakai</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data as $row) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['bahanid']); ?></td>
                    <td><?= htmlspecialchars($row['namabahan']); ?></td>
                    <td><?= formatRupiah($row['hargaperunit'] ?? 0); ?></td>
                    <td><?= number_format($row['totalkuantitasdigunakan'], 2, ',', '.'); ?></td>
                    <td><?= htmlspecialchars($row['satuan']); ?></td>
                    <td><?= formatRupiah($row['totalbiayabahan'] ?? 0); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<?php
include '../layout/footer.php';
?>