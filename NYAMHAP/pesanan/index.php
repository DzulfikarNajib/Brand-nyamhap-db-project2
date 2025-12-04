<?php
// FILE: /NYAMHAP/pesanan/index.php

include '../config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$is_sales_staff = ($staff_role === 'SPEN');

include '../layout/header.php';

// Tampilkan pesan status (selaras dengan pelanggan)
$status = $_GET['status'] ?? '';
$error_msg = $_GET['msg'] ?? '';

if ($status === 'success_delete' || $status === 'delete_success') {
    echo '<div class="notice notice-success"> Pesanan berhasil dihapus.</div>';
} elseif ($status === 'success_create' || $status === 'create_success') {
    echo '<div class="notice notice-success"> Pesanan baru berhasil dibuat.</div>';
} elseif ($status === 'success_edit' || $status === 'edit_success') {
    echo '<div class="notice notice-success"> Pesanan berhasil diperbarui.</div>';
} elseif ($status === 'error' && $error_msg) {
    echo '<div class="notice notice-error"> Error: ' . htmlspecialchars(urldecode($error_msg)) . '</div>';
}
?>

<style>
.table-container { width:100%; overflow-x:auto; }
.actions-top { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:12px; }
.actions-top .left, .actions-top .right { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.search-form { display:flex; gap:8px; align-items:center; }
.search-input { padding:6px 8px; border:1px solid #ccc; border-radius:4px; }
.pagination-bar { display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; margin-top:12px; }
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

<h2>Daftar Pesanan</h2>

<div class="actions-top">
    <div class="left">
        <?php if ($is_sales_staff) : ?>
            <a href="create.php" class="btn btn-primary">+ Tambah Pesanan Baru</a>
        <?php else : ?>
            <span style="color:grey;">* Hanya dapat dikelola oleh Staff Penjualan.</span>
        <?php endif; ?>

        <!-- SEARCH FORM -->
        <?php
        // Ambil nilai query dan pagination dari GET
        $q_raw = trim($_GET['q'] ?? '');
        $q_escaped_html = htmlspecialchars($q_raw, ENT_QUOTES);
        ?>
        <form method="get" class="search-form" style="margin-left:8px;">
            <input type="hidden" name="per_page" value="<?= htmlspecialchars($_GET['per_page'] ?? '10') ?>">
            <input type="hidden" name="page" value="1">
            <input type="text" name="q" class="search-input" placeholder="Cari ID, nama pelanggan, tanggal..." value="<?= $q_escaped_html ?>">
            <button type="submit" class="btn btn-light">Cari</button>
            <?php if ($q_raw !== ''): ?>
                <a href="<?= htmlspecialchars('?' . http_build_query(['per_page' => ($_GET['per_page'] ?? 10), 'page' => 1])) ?>" class="btn btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="right">
        <a href="/NYAMHAP/index.php" class="btn btn-light" style="white-space:nowrap;">« Kembali ke Dasbor</a>
        <a href="/NYAMHAP/pembayaran/index.php" class="btn btn-light" style="white-space:nowrap;">Next Pembayaran »</a>
    </div>
</div>

<div class="table-container">
<table border="1" class="table table-bordered" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>ID Pesanan</th>
            <th>Nama Pelanggan</th>
            <th>Tanggal</th>
            <th>Total Harga</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // --- Pagination setup ---
        $per_page_param = $_GET['per_page'] ?? '10';
        $per_page_all = ($per_page_param === 'all');
        $per_page = $per_page_all ? null : max(1, (int)$per_page_param);
        $page = max(1, (int)($_GET['page'] ?? 1));

        // Build WHERE clause if search present
        $where_sql = '';
        if ($q_raw !== '') {
            // Escape untuk query Postgres
            $esc_q = pg_escape_string($koneksi, $q_raw);
            // Cari di pesananid (cast to text), nama pelanggan, dan tanggal
            $where_sql = " WHERE (pesanan.pesananid::text ILIKE '%{$esc_q}%' OR pelanggan.namap ILIKE '%{$esc_q}%' OR pesanan.tanggal::text ILIKE '%{$esc_q}%')";
        }

        // Count total rows (join pelanggan so nama bisa dicari)
        $count_query = "SELECT COUNT(*) AS cnt FROM pesanan JOIN pelanggan ON pesanan.pelangganid = pelanggan.pelangganid" . $where_sql;
        $count_res = pg_query($koneksi, $count_query);
        $total_rows = 0;
        if ($count_res) {
            $r = pg_fetch_assoc($count_res);
            $total_rows = (int)$r['cnt'];
        }

        if ($per_page_all) {
            $query = "SELECT pesanan.pesananid, pesanan.pelangganid, pelanggan.namap, pesanan.tanggal, pesanan.totalharga 
                      FROM pesanan JOIN pelanggan ON pesanan.pelangganid = pelanggan.pelangganid
                      {$where_sql}
                      ORDER BY pesanan.tanggal DESC";
            $result = pg_query($koneksi, $query);
            $total_pages = 1;
        } else {
            $total_pages = (int)ceil(max(1, $total_rows) / $per_page);
            if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
            $offset = ($page - 1) * $per_page;
            // Note: casting LIMIT and OFFSET safe because they are integers
            $query = "SELECT pesanan.pesananid, pesanan.pelangganid, pelanggan.namap, pesanan.tanggal, pesanan.totalharga 
                      FROM pesanan JOIN pelanggan ON pesanan.pelangganid = pelanggan.pelangganid
                      {$where_sql}
                      ORDER BY pesanan.tanggal DESC
                      LIMIT {$per_page} OFFSET {$offset}";
            $result = pg_query($koneksi, $query);
        }

        $colspan = $is_sales_staff ? 5 : 4;

        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $pesanan_id = htmlspecialchars($row['pesananid']);
                $pesanan_id_url = urlencode($row['pesananid']);
                $nama_p = htmlspecialchars($row['namap']);
                $tanggal = htmlspecialchars($row['tanggal']);
                $total_harga = $row['totalharga'];
                $pelangganid = urlencode($row['pelangganid'] ?? '');

                echo "<tr>";
                echo "<td>{$pesanan_id}</td>";
                echo "<td>" . htmlspecialchars($row['namap']) . "</td>";
                echo "<td>{$tanggal}</td>";
                echo "<td>Rp " . number_format($total_harga, 0, ',', '.') . "</td>";
                    echo "<td>";
                    echo "<a href='edit.php?id={$pesanan_id_url}' class='btn btn-warning btn-sm'>Ubah</a> ";
                    echo "<a href='delete.php?id={$pesanan_id_url}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin hapus pesanan ini?\")'>Hapus</a>";
                    echo "</td>";
                

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='{$colspan}' style='text-align:center'>Belum ada data pesanan.</td></tr>";
        }
        ?>
    </tbody>
</table>
</div>

<!-- Pagination & action bar -->
<div class="pagination-bar">
    <div class="pagination-left" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <?php
        // Helper untuk membangun query string sambil mempertahankan q dan per_page
        $build_link = function($p, $per) use ($q_raw) {
            $qs = [];
            if ($per === 'all') $qs['per_page'] = 'all';
            else $qs['per_page'] = (int)$per;
            $qs['page'] = (int)$p;
            if ($q_raw !== '') $qs['q'] = $q_raw;
            return '?' . http_build_query($qs);
        };

        $curr_per = $per_page_all ? 'all' : $per_page;
        if ($curr_per !== 'all') {
            echo "<a href='".htmlspecialchars($build_link(1, 'all'))."' class='btn btn-outline-secondary'>Lihat Semua</a> ";
        } else {
            echo "<a href='".htmlspecialchars($build_link(1, 10))."' class='btn btn-outline-secondary'>Per 10</a> ";
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