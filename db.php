<?php

$host     = 'localhost';
$dbname   = 'NYAMHAP';
$user     = 'postgres';
$password = 'Najib2202';

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");

if (!$conn) {
    die(" Koneksi database gagal: " . pg_last_error());
}

pg_query($conn, "SET search_path TO public");


$koneksi = $conn;

?>
