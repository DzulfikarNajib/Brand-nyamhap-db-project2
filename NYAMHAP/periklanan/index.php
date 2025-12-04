<?php

session_start();
include '../config/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: /NYAMHAP/login.php");
    exit();
}

$staff_role = strtoupper($_SESSION['staff_role'] ?? '');
$allowed_roles = ['FOUNDER', 'SMAR']; 
$is_allowed = in_array($staff_role, $allowed_roles);

include '../layout/header.php';

$status = $_GET['status'] ?? '';
$error_msg = $_GET['msg'] ?? '';
$new_id = $_GET['id'] ?? '';

if ($status === 'success_delete') {
    echo '<div class="notice notice-success">Kampanye iklan berhasil dihapus.</div>';
} elseif ($status === 'fail_delete') {
    echo '<div class="notice notice-error">Gagal menghapus kampanye iklan. ' . ($error_msg ? htmlspecialchars(urldecode($error_msg)) : '') . '</div>';
} elseif ($status === 'success_create') {
    echo '<div class="notice notice-success">Kampanye iklan berhasil ditambahkan! ID: ' . htmlspecialchars($new_id) . '</div>';
} elseif ($status === 'success_edit') {
    echo '<div class="notice notice-success">Kampanye iklan berhasil diperbarui.</div>';
} elseif ($status === 'error' && $error_msg) {
    echo '<div class="notice notice-error">Error: ' . htmlspecialchars(urldecode($error_msg)) . '</div>';
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

<h2>Manajemen Periklanan</h2>

<div class="actions-top">
    <div class="left">
        <?php if ($is_allowed) : ?>
            <a href="create.php" class="btn btn-primary">+ Tambah Kampanye Iklan</a>
        <?php else : ?>
            <span style="color: grey;">* Periklanan hanya dapat dikelola oleh Founder dan Staff Marketing.</span>
        <?php endif; ?>
    </div>

    <div class="right">
        <a href="/NYAMHAP/index.php" class="btn btn-light" style="white-space:nowrap;">« Kembali ke Dasbor</a>
    </div>
</div>

<div class="table-container">
<table border="1" class="table table-bordered" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>ID Iklan</th>
            <th>Staff ID</th>
            <th>Nama/Media Kampanye</th>
            <th>Biaya</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Berakhir</th>
            <?php if ($is_allowed) : ?><th>Aksi</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $per_page_param = $_GET['per_page'] ?? '10';
        $per_page_all = ($per_page_param === 'all');
        $per_page = $per_page_all ? null : max(1, (int)$per_page_param);
        $page = max(1, (int)($_GET['page'] ?? 1));

        $count_res = pg_query($koneksi, "SELECT COUNT(*) AS cnt FROM periklanan");
        $total_rows = 0;
        if ($count_res) {
            $r = pg_fetch_assoc($count_res);
            $total_rows = (int)$r['cnt'];
        }

        if ($per_page_all) {
            $query = "SELECT periklananid, mediaperiklanan, biaya, tanggalmulai, tanggalselesai, staffid FROM periklanan ORDER BY periklananid ASC";
            $result = pg_query($koneksi, $query);
            $total_pages = 1;
        } else {
            $total_pages = ($per_page > 0) ? (int)ceil($total_rows / $per_page) : 1;
            if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
            $offset = ($page - 1) * $per_page;
            $query = "SELECT periklananid, mediaperiklanan, biaya, tanggalmulai, tanggalselesai, staffid FROM periklanan ORDER BY periklananid ASC LIMIT $per_page OFFSET $offset";
            $result = pg_query($koneksi, $query);
        }

        $colspan = $is_allowed ? 7 : 6;

        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $id = htmlspecialchars($row['periklananid']);
                $id_url = urlencode($row['periklananid']);
                $staffid = htmlspecialchars($row['staffid']);
                $media = htmlspecialchars($row['mediaperiklanan']);
                $biaya = $row['biaya'];
                $tglmulai = htmlspecialchars($row['tanggalmulai']);
                $tglselesai = htmlspecialchars($row['tanggalselesai']);

                echo "<tr>";
                echo "<td>{$id}</td>";
                echo "<td>{$staffid}</td>";
                echo "<td>{$media}</td>";
                echo "<td>Rp " . number_format((float)($biaya ?? 0), 0, ',', '.') . "</td>";
                echo "<td>{$tglmulai}</td>";
                echo "<td>{$tglselesai}</td>";

                if ($is_allowed) {
                    echo "<td>";
                    echo "<a href='edit.php?id=" . htmlspecialchars($id_url) . "' class='btn btn-warning btn-sm'>Edit</a> ";
                    echo "<a href='delete.php?id=" . htmlspecialchars($id_url) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus kampanye " . addslashes($media) . "?\")'>Hapus</a>";
                    echo "</td>";
                }

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='{$colspan}' style='text-align:center'>Belum ada data periklanan.</td></tr>";
        }
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
            echo "<a href='" . htmlspecialchars('?' . http_build_query(['per_page' => 'all'])) . "' class='btn btn-outline-secondary'>Lihat Semua</a> ";
        } else {
            echo "<a href='" . htmlspecialchars('?' . http_build_query(['per_page' => 10, 'page' => 1])) . "' class='btn btn-outline-secondary'>Per 10</a> ";
        }
        ?>
        <span style="color:gray; margin-left:6px;">(Total: <?= $total_rows ?>)</span>
    </div>

    <div class="pagination-controls">
        <?php if (!$per_page_all && $total_rows > 0): ?>
            <div class="pagination-scroll">
                <?php

                if ($page > 1) {
                    echo "<a href='" . htmlspecialchars($build_link($page - 1, $per_page)) . "' class='btn btn-light'>‹ Prev</a> ";
                } else {
                    echo "<span class='btn btn-light' style='opacity:0.5'>‹ Prev</span> ";
                }

                $start = max(1, $page - 3);
                $end = min($total_pages, $page + 3);
                if ($start > 1) {
                    echo "<a href='" . htmlspecialchars($build_link(1, $per_page)) . "' class='btn btn-light'>1</a> ";
                    if ($start > 2) echo "<span class='btn btn-light' style='pointer-events:none'>...</span> ";
                }
                for ($i = $start; $i <= $end; $i++) {
                    if ($i == $page) {
                        echo "<span class='btn btn-primary' style='font-weight:bold'>{$i}</span> ";
                    } else {
                        echo "<a href='" . htmlspecialchars($build_link($i, $per_page)) . "' class='btn btn-light'>{$i}</a> ";
                    }
                }
                if ($end < $total_pages) {
                    if ($end < $total_pages - 1) echo "<span class='btn btn-light' style='pointer-events:none'>...</span> ";
                    echo "<a href='" . htmlspecialchars($build_link($total_pages, $per_page)) . "' class='btn btn-light'>{$total_pages}</a> ";
                }

                if ($page < $total_pages) {
                    echo "<a href='" . htmlspecialchars($build_link($page + 1, $per_page)) . "' class='btn btn-light'>Next ›</a> ";
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