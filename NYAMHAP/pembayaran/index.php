<?php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['FOUNDER', 'SKEU', 'SPEN'];
$is_allowed = in_array($staff_role, $allowed_roles); 
$can_edit = in_array($staff_role, ['FOUNDER', 'SKEU']); 
$can_delete = ($staff_role === 'FOUNDER'); 

include '../layout/header.php';

if (isset($_SESSION['payment_message'])) {
    echo '<div class="notice notice-success">' . $_SESSION['payment_message'] . '</div>';
    unset($_SESSION['payment_message']);
}
?>

<style>

.table-container { width:100%; overflow-x:auto; }
.actions-top {
    display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:12px;
}
.actions-top .left, .actions-top .right { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

.pagination-bar {
    display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; margin-top:12px;
}
.pagination-controls { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.pagination-scroll { display:inline-block; max-width:100%; overflow-x:auto; white-space:nowrap; }

.btn { padding:6px 10px; border-radius:4px; text-decoration:none; display:inline-block; }
.btn-primary { background:#d7a10bff; color:#fff; border:1px solid #d7a10bff; }
.btn-warning { background:#ffc107; color:#212529; border:1px solid #ffca2c; }
.btn-danger { background:#dc3545; color:#fff; border:1px solid #c82333; }
.btn-light { background:#f8f9fa; color:#212529; border:1px solid #ddd; }
.btn-outline-secondary { background:transparent; color:#6c757d; border:1px solid #6c757d; }

.notice { padding:10px; margin-bottom:15px; border-radius:4px; }
.notice-success { background:#e6ffed; color:#056e3b; border:1px solid #d1f7dc; }
.notice-error { background:#ffe6e6; color:#8a1f1f; border:1px solid #f5c2c2; }

@media (min-width:720px) { .pagination-scroll { max-width:600px; } }
</style>

<h2>Daftar Pembayaran Transaksi</h2>

<div class="actions-top">
    <div class="left">
        <?php if (in_array($staff_role, ['FOUNDER', 'SPEN', 'SKEU'])) : ?>
            <a href="input.php" class="btn btn-primary">Input/Update Pembayaran</a>
        <?php else : ?>
            <span style="color: grey;">* Hanya dapat dikelola oleh Founder, Staff Penjualan, dan Keuangan.</span>
        <?php endif; ?>
    </div>

    <div class="right">
        <a href="/NYAMHAP/index.php" class="btn btn-light" style="white-space:nowrap;">« Kembali ke Dasbor</a>
    </div>
</div>

<div class="table-container">
<?php
$per_page_param = $_GET['per_page'] ?? '10';
$per_page_all = ($per_page_param === 'all');
$per_page = $per_page_all ? null : max(1, (int)$per_page_param);
$page = max(1, (int)($_GET['page'] ?? 1));

$count_res = pg_query($koneksi, "SELECT COUNT(*) AS cnt FROM Pembayaran");
$total_rows = 0;
if ($count_res) {
    $r = pg_fetch_assoc($count_res);
    $total_rows = (int)$r['cnt'];
}

$colspan = 7; 
if ($can_edit || $can_delete) {
    $colspan = 8; 
}


if ($per_page_all) {
    $query = "
        SELECT 
            B.PembayaranID,
            B.PesananID,
            B.TanggalPembayaran,
            B.MetodePembayaran,
            B.StatusPembayaran,
            P.TotalHarga,
            Pg.NamaP
        FROM Pembayaran B
        JOIN Pesanan P ON B.PesananID = P.PesananID
        JOIN Pelanggan Pg ON P.PelangganID = Pg.PelangganID
        ORDER BY B.TanggalPembayaran DESC, B.PembayaranID DESC
    ";
    $result = pg_query($koneksi, $query);
    $total_pages = 1;
} else {
    $total_pages = ($per_page > 0) ? (int)ceil($total_rows / $per_page) : 1;
    if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
    $offset = ($page - 1) * $per_page;

    $query = "
        SELECT 
            B.PembayaranID,
            B.PesananID,
            B.TanggalPembayaran,
            B.MetodePembayaran,
            B.StatusPembayaran,
            P.TotalHarga,
            Pg.NamaP
        FROM Pembayaran B
        JOIN Pesanan P ON B.PesananID = P.PesananID
        JOIN Pelanggan Pg ON P.PelangganID = Pg.PelangganID
        ORDER BY B.TanggalPembayaran DESC, B.PembayaranID DESC
        LIMIT $per_page OFFSET $offset
    ";
    $result = pg_query($koneksi, $query);
}
?>

<table class="table table-bordered" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>ID Pembayaran</th>
            <th>ID Pesanan</th>
            <th>Pelanggan</th>
            <th>Total Bayar</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Tgl Pembayaran</th>
            <?php if ($can_edit || $can_delete) : ?><th>Aksi</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result && pg_num_rows($result) > 0) :
            while ($row = pg_fetch_assoc($result)) :
                $pid = htmlspecialchars($row['pembayaranid']);
                $pesananid = htmlspecialchars($row['pesananid']);
                $nama = htmlspecialchars($row['namap']);
                $total = number_format($row['totalharga'] ?? 0, 0, ',', '.');
                $metode = htmlspecialchars($row['metodepembayaran']);
                $status = htmlspecialchars($row['statuspembayaran']);
                $tgl = htmlspecialchars($row['tanggalpembayaran']);

                $status_class = '';
                if ($row['statuspembayaran'] == 'Lunas') $status_class = 'btn-success';
                elseif ($row['statuspembayaran'] == 'Pending') $status_class = 'btn-warning';
                elseif ($row['statuspembayaran'] == 'Gagal') $status_class = 'btn-danger';
        ?>
            <tr>
                <td><?= $pid; ?></td>
                <td><?= $pesananid; ?></td>
                <td><?= $nama; ?></td>
                <td>Rp <?= $total; ?></td>
                <td><?= $metode; ?></td>
                <td><span class="btn btn-sm <?= $status_class; ?>"><?= $status; ?></span></td>
                <td><?= $tgl; ?></td>
                
                <?php if ($can_edit || $can_delete) : ?>
                    <td style="white-space:nowrap;">
                        <?php if ($can_edit) : ?>
                            <a href="edit.php?id=<?= urlencode($row['pembayaranid']); ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php endif; ?>
                        
                        <?php if ($can_delete) :  ?>
                            <a href="delete.php?id=<?= urlencode($row['pembayaranid']); ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('⚠️ PERINGATAN: Anda yakin ingin menghapus Pembayaran ID <?= $pid; ?>? Aksi ini tidak dapat dibatalkan. (Hanya Founder yang dapat melakukan ini)');">
                                Hapus
                            </a>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php
            endwhile;
        else:
            echo "<tr><td colspan='{$colspan}' style='text-align:center'>Belum ada data pembayaran.</td></tr>";
        endif;
        ?>
    </tbody>
</table>
</div>

<div class="pagination-bar">
    <div class="pagination-left" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <?php
        $build_link = function($p, $per) {
            $qs = [];
            if ($per === 'all') $qs['per_page'] = 'all';
            else $qs['per_page'] = (int)$per;
            $qs['page'] = (int)$p;
            return '?' . http_build_query($qs);
        };

        $curr_per = $per_page_all ? 'all' : $per_page;
        if ($curr_per !== 'all') {
            echo "<a href='".htmlspecialchars('?'.http_build_query(['per_page'=>'all']))."' class='btn btn-outline-secondary'>Lihat Semua</a> ";
        } else {
            echo "<a href='".htmlspecialchars('?'.http_build_query(['per_page'=>10,'page'=>1]))."' class='btn btn-outline-secondary'>Per 10</a> ";
        }
        ?>
        <span style="color:gray; margin-left:6px;">(Total: <?= $total_rows ?>)</span>
    </div>

    <div class="pagination-controls">
        <?php if (!$per_page_all && $total_rows > 0): ?>
            <div class="pagination-scroll">
                <?php
                if ($page > 1) {
                    echo "<a href='".htmlspecialchars($build_link($page-1, $per_page))."' class='btn btn-light'>‹ Prev</a> ";
                } else {
                    echo "<span class='btn btn-light' style='opacity:0.5'>‹ Prev</span> ";
                }

                $start = max(1, $page - 3);
                $end = min($total_pages, $page + 3);
                if ($start > 1) {
                    echo "<a href='".htmlspecialchars($build_link(1, $per_page))."' class='btn btn-light'>1</a> ";
                    if ($start > 2) echo "<span class='btn btn-light' style='pointer-events:none'>...</span> ";
                }
                for ($i = $start; $i <= $end; $i++) {
                    if ($i == $page) {
                        echo "<span class='btn btn-primary' style='font-weight:bold'>{$i}</span> ";
                    } else {
                        echo "<a href='".htmlspecialchars($build_link($i, $per_page))."' class='btn btn-light'>{$i}</a> ";
                    }
                }
                if ($end < $total_pages) {
                    if ($end < $total_pages - 1) echo "<span class='btn btn-light' style='pointer-events:none'>...</span> ";
                    echo "<a href='".htmlspecialchars($build_link($total_pages, $per_page))."' class='btn btn-light'>{$total_pages}</a> ";
                }

                if ($page < $total_pages) {
                    echo "<a href='".htmlspecialchars($build_link($page+1, $per_page))."' class='btn btn-light'>Next ›</a> ";
                } else {
                    echo "<span class='btn btn-light' style='opacity:0.5'>Next ›</span> ";
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include '../layout/footer.php';
?>