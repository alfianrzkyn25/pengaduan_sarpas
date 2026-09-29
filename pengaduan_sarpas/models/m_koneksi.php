<?php
$server = 'localhost';
$username = 'root';
$pass = ''; // biasanya kosong jika menggunakan XAMPP
$database = 'pengaduan_sarpas';

$conn = mysqli_connect($server, $username, $pass, $database);

if (mysqli_connect_error()) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
