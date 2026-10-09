<?php
$server = 'sql211.infinityfree.com';
$username = 'if0_43078283';
$pass = 'cRMeygqIWD1';
$database = 'if0_43078283_pengaduan_sarpas';

$conn = mysqli_connect($server, $username, $pass, $database);

if (mysqli_connect_error()) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
