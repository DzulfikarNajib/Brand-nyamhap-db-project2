<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pemesanan</title>
    <style>

    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background: #f0f0f0;
    }
    
    .container { 
        background: white; 
        padding: 0; 
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        max-width: 1200px; 
        margin: 50px auto; 
        overflow: hidden; 
    }

    .main-navbar {

        background: white; 
        padding: 10px 30px; 
        display: flex;
        justify-content: space-between; 
        align-items: center;
        border-bottom: 1px solid #e0e0e0; 
    }

    .header-branding {
        display: flex;
        align-items: center;
        padding: 0; 
    }

    .header-branding img {
        height: 40px; 
        margin-right: 2px; 
        
    }

    .header-branding h1 {
        font-size: 28px; 
        margin: 0; 
        color: #FF6700; 
        font-weight: 800;
        letter-spacing: 0.5px;
    }
    
    nav { 
        padding: 0; 
        display: flex;
        align-items: center;
        gap: 33px; 
    }
    
    nav a { 
        color: #4a4a4a; 
        text-decoration: none; 
        font-weight: 600;
        transition: color 0.3s ease;
        padding: 5px 0;
        position: relative; 
    }

    nav a:hover, nav a.active { 
        color: #FF6700;
    }

    nav a:hover::after, nav a.active::after {
        content: '';
        display: block;
        width: 100%;
        height: 3px;
        background: #FF6700;
        position: absolute;
        bottom: -10px; 
        left: 0;
        border-radius: 2px;
    }

    .nav-links img {
        display: none;
    }
    
    .action-button {

        display: inline-block;
        background: #dc3545; 
        color: white; 
        text-decoration: none; 
        padding: 6px 20px;
        border-radius: 6px;
        font-weight: 500;
        transition: background-color 0.3s;
    }

    .action-button:hover {
        background: #c82333;
    }

    .content-section {
        padding: 20px;
        margin-top: 0;
    }

    table { width: 100%; border-collapse: collapse; margin-top: 1px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #FF8C00; color: white; }
    .btn { padding: 10px 10px; text-decoration: none; color: white; border-radius: 6px; font-size: 14px; }
    .btn-primary { background: #FF6700; } 
    .btn-danger { background: #dc3545; } 
    .btn-warning { background: #ffc107; color: black; } 

    </style>
</head>
<body>
    <div class="container">
        
        <header class="main-navbar">
            
            <div class="header-branding">
                <img src="/NYAMHAP/assests/img/4K-removebg-preview.png" alt="Logo NYAMHAP"> 
                <h1>NYAMHAP</h1>
            </div>
            
            <nav class="nav-links">
                <?php
                $path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
                function is_active($targets) {
                    global $path;
                    foreach ((array)$targets as $t) {
                        $t = rtrim($t, '/');
                        if ($t === '') {
                            if ($path === '' || $path === '/NYAMHAP' || $path === '/NYAMHAP/index.php') return 'active';
                            continue;
                        }
                        if ($path === $t || strpos($path, $t . '/') === 0) return 'active';
                    }
                    return '';
                }
                ?>
                <a href="/NYAMHAP/index.php" class="<?php echo ($path === '/NYAMHAP' || $path === '/NYAMHAP/index.php' || $path === '') ? 'active' : ''; ?>">Home</a>
                <a href="/NYAMHAP/menu/index.php" class="<?php echo is_active(['/NYAMHAP/menu', '/NYAMHAP/menu/index.php']); ?>">Menu</a>
                <a href="/NYAMHAP/pembayaran/index.php" class="<?php echo is_active(['/NYAMHAP/pembayaran', '/NYAMHAP/pembayaran/index.php']); ?>">Data Pembayaran</a>
                <a href="/NYAMHAP/staff/index.php" class="<?php echo is_active(['/NYAMHAP/staff', '/NYAMHAP/staff/index.php']); ?>">Data Staff</a>
                <a href="/NYAMHAP/bahan_baku/index.php" class="<?php echo is_active(['/NYAMHAP/bahan_baku', '/NYAMHAP/bahan_baku/index.php']); ?>">Data Bahan Baku</a>
                <a href="/NYAMHAP/periklanan/index.php" class="<?php echo is_active(['/NYAMHAP/periklanan', '/NYAMHAP/periklanan/index.php']); ?>">Data Periklanan</a>
                <a href="/NYAMHAP/laporan/index.php" class="<?php echo is_active(['/NYAMHAP/laporan', '/NYAMHAP/laporan/index.php']); ?>">Laporan</a>
                </nav>
            
            <a href="/NYAMHAP/logout.php" class="action-button">Logout</a>
            
        </header>
        
        <div class="content-section">
