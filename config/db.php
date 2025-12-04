<?php
// Konfigurasi koneksi database
$host     = 'localhost';
$dbname   = 'NYAMHAP';   // Nama database yang ada di DBeaver/ToolsSQL lainnya
$user     = 'postgres';
$password = 'isi_password'; // Ganti dengan password PostgreSQL kamu

// Membuat koneksi
$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");

// Validasi koneksi
if (!$conn) {
    die("Koneksi database gagal: " . pg_last_error());
}

// Set schema default
pg_query($conn, "SET search_path TO public");

// Variabel global koneksi
$koneksi = $conn;
?>
