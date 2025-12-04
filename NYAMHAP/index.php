<?php

include 'config/db.php';
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$staff_nama = $_SESSION['staff_nama'];

include 'layout/header.php'; 
?>

<style>

    .dashboard-container {
        width: 100%;
        padding: 1 10px; 
        font-family: 'Inter', sans-serif;
    }

    .welcome-box {
        margin-top: 0px;
        background-color: #ffe0b2;
        border: 1px solid #ffcc80;
        padding: 20px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-left: 6px solid #FF8A00; 
        margin-bottom: 30px;
        color: #4a4a4a;
    }
    .welcome-box h2 {
        color: #FF6700;
        margin: 0 0 5px 0;
        font-weight: 700;
    }

    .flow-wrapper {
        display: flex;
        gap: 25px; 
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    .action-card {
        background: #ffffff;
        border: 1px solid #e0e0e0; 
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
        flex: 1; 
        min-width: 320px;
        transition: all 0.3s ease;
        
        display: flex;
        flex-direction: column;
        justify-content: space-between; 
    }
    
    .action-card:hover {
        box-shadow: 0 8px 20px rgba(255, 103, 0, 0.2); 
        border-color: #FF6700; 
    }

    .action-card h3 {
        color: #FF6700; 
        font-size: 25px;
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #fff0e5; 
    }
    
    .action-card p:not(.next-step-hint) {
        flex-grow: 1; 
    }

    .action-card .btn {
        display: inline-block;
        padding: 12px 25px;
        text-decoration: none;
        color: white;
        border-radius: 6px;
        font-weight: 700;
        margin-top: 15px;
        transition: background-color 0.3s;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-sizing: border-box; 
        
        height: 50px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        
        align-self: flex-start; 
    }

    .action-card .btn-primary { 
        background: #007bff; 
    } 
    .action-card .btn-warning { 
        background: #FF8A00; 
        color: white;
    } 

    .next-step-hint {
        color: #999;
        margin-top: 15px;
        font-style: italic;
        font-size: 13px;
        
        margin-bottom: 0;
    }

</style>

<div class="dashboard-container">

    <div class="welcome-box">
        <h2>👋 Selamat Datang, <?= htmlspecialchars($staff_nama); ?>!</h2>
        <p>
            Anda login sebagai <strong>Staff ID: <?= htmlspecialchars($staff_id); ?></strong>.  
            Selamat Bekerja
        </p>
        <p class="next-step-hint" style="margin-top:8px; color:#555;">
            <?php
            $role = $_SESSION['staff_role'] ?? $_SESSION['role'] ?? 'staff';

            $desc = $access_map[$role] ?? $access_map['staff'] ?? 'Staff memiliki akses terbatas sesuai tugas masing-masing.';
            ?>
            <small><?= htmlspecialchars($desc); ?></small>
        </p>
    </div>
            
    <div class="flow-wrapper">
        
        <div class="action-card primary-action">
            <h3>Data Pelanggan</h3>
            <p>
                Menambahkan atau memilih pelanggan yang akan melakukan pemesanan.
                Pastikan data pelanggan sudah lengkap dan akurat sebelum melanjutkan ke langkah berikutnya.
            </p>
            <a href="/NYAMHAP/pelanggan/index.php" class="btn btn-primary">
                Pelanggan
            </a>
            <p class="next-step-hint">
                <small>Setelah data pelanggan siap, Anda bisa Lanjut ke Pesanan.</small>
            </p>
        </div>

        <div class="action-card secondary-action">
            <h3>Pesanan Baru</h3>
            <p>
                Setelah memilih pelanggan, lanjutkan ke Pesanan untuk membuat pesanan baru.
                Pilih menu, tentukan jumlah, dan selesaikan proses pemesanan.
            </p>
            <a href="/NYAMHAP/pesanan/index.php" class="btn btn-warning">
                Lanjutkan ke Pesanan
            </a>
            <p class="next-step-hint">
                <small>Masukkan data pesanan dengan teliti.</small>
            </p>
        </div>

    </div>
    
</div>

<?php
include 'layout/footer.php';
?>
