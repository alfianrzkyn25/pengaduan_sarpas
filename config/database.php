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

// $base = path absolut ke root project (mis. "/pengaduan_sarpas"), dihitung dari
// DOCUMENT_ROOT sehingga SELALU benar dari file manapun (view/admin, view/user,
// auth, controller) dan tidak peduli apakah URL sedang "dipercantik" oleh .htaccess
// atau diakses lewat path file aslinya.
function base_url() {
    $root = str_replace('\\', '/', dirname(__DIR__)); // .../pengaduan_sarpas
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/')) : '';
    $rel = ($docRoot !== '' && strpos($root, $docRoot) === 0) ? substr($root, strlen($docRoot)) : '';
    $rel = '/' . trim($rel, '/');
    return $rel === '/' ? '' : $rel;
}
$base = base_url();

// Bikin URL "cantik" (tanpa .php, tanpa folder view/) berbasis $base.
// Contoh: url('admin/dashboard') -> /pengaduan_sarpas/admin/dashboard
function url($path) {
    global $base;
    return $base . '/' . ltrim($path, '/');
}

function login_redirect_path() { return url('auth/login'); }
function require_login() { if (empty($_SESSION['role'])) redirect(login_redirect_path()); }
function require_role($role) { require_login(); if ($_SESSION['role'] !== $role) redirect(login_redirect_path()); }
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
