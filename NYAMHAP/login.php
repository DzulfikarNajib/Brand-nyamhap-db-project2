<?php

include 'config/db.php';
session_start();

if (isset($_SESSION['staff_id'])) { 
    header("Location: index.php"); 
    exit(); 
}

$error_message = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $input_staffid = trim($_POST['staff_id']);
    $input_password = $_POST['password']; 

    $query = "SELECT staffid, namas, passwordhash, rolekode FROM staff WHERE staffid = $1";

    $result = pg_query_params($koneksi, $query, array($input_staffid));

    if ($result) { 
        $staff = pg_fetch_assoc($result); 
        if ($staff && $staff['passwordhash'] == $input_password) { 
          
            $_SESSION['staff_id'] = $staff['staffid']; 
            $_SESSION['staff_nama'] = $staff['namas'];
            $_SESSION['staff_role'] = $staff['rolekode']; 

            header("Location: index.php");
            exit();

        } else {
            $error_message = "Staff ID atau Password salah.";
        }
    } else {
        $error_message = "Terjadi kesalahan query database: " . pg_last_error($koneksi); 
    }
}
?>

<!DOCTYPE html> 
<html lang="id"> 
<head>
    <meta charset="UTF-8">
    <title>Login - NYAMHAP App</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 350px; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-login { background-color: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn-login:hover { background-color: #0056b3; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
    </style> 
</head>
<body>
    <div class="login-container">
        <h2>NYAMHAP - Login Staff</h2>
        
        <?php if ($error_message) : ?>
            <p class="error"><?= $error_message; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="staff_id">Staff ID</label>
            <input type="text" id="staff_id" name="staff_id" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-login">Masuk</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">
            *Gunakan StaffID (misal: S001) dan password Anda (misal: 123)
        </p>
    </div>
</body>
</html>
