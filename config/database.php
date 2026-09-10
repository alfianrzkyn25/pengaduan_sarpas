<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$server = 'localhost';
$username = 'root';
$pass = '';
$database = 'pengaduan_sarpas';

$conn = mysqli_connect($server, $username, $pass, $database);
if (!$conn) die('Koneksi database gagal: ' . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8mb4');

function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($path) { header('Location: ' . $path); exit; }
function require_login() { if (empty($_SESSION['role'])) redirect('../login.php'); }
function require_role($role) { require_login(); if ($_SESSION['role'] !== $role) redirect('../login.php'); }
function current_user() {
    global $conn;
    if (empty($_SESSION['role'])) return null;
    if ($_SESSION['role'] === 'siswa' && !empty($_SESSION['nis'])) {
        $st=mysqli_prepare($conn,'SELECT nis,nama,kelas FROM siswa WHERE nis=?'); mysqli_stmt_bind_param($st,'s',$_SESSION['nis']); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st); $u=mysqli_fetch_assoc($r); mysqli_stmt_close($st); return $u;
    }
    if ($_SESSION['role'] === 'admin' && !empty($_SESSION['id_admin'])) {
        $id=(int)$_SESSION['id_admin']; $st=mysqli_prepare($conn,'SELECT id_admin,username,nama FROM admin WHERE id_admin=?'); mysqli_stmt_bind_param($st,'i',$id); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st); $u=mysqli_fetch_assoc($r); mysqli_stmt_close($st); return $u;
    }
    return null;
}
function default_admin_id() {
    global $conn;
    $r=mysqli_query($conn,'SELECT id_admin FROM admin ORDER BY id_admin ASC LIMIT 1');
    $row=$r?mysqli_fetch_assoc($r):null;
    return $row ? (int)$row['id_admin'] : 0;
}
