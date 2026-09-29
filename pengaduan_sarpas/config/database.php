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
        $st=mysqli_prepare($conn,'SELECT nis,nama,kelas,foto FROM siswa WHERE nis=?'); mysqli_stmt_bind_param($st,'s',$_SESSION['nis']); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st); $u=mysqli_fetch_assoc($r); mysqli_stmt_close($st); return $u;
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

// ---------------------------------------------------------------------------
// FOTO PROFIL SISWA
// ---------------------------------------------------------------------------

// Pastikan kolom siswa.foto ada (sudah ada di pengaduan_sarpas.sql). Kalau database
// lama belum punya kolomnya, otomatis ditambahkan sekali saja.
function ensure_siswa_foto_column() {
    global $conn;
    $ada = false;
    try {
        $r = mysqli_query($conn, "SHOW COLUMNS FROM `siswa` LIKE 'foto'");
        if ($r && mysqli_num_rows($r) === 0) {
            mysqli_query($conn, "ALTER TABLE `siswa` ADD COLUMN `foto` VARCHAR(255) NULL DEFAULT NULL");
            $r = mysqli_query($conn, "SHOW COLUMNS FROM `siswa` LIKE 'foto'");
        }
        $ada = $r && mysqli_num_rows($r) > 0;
    } catch (Throwable $ex) {
        $ada = false;
    }
    if (!$ada) die('Kolom "foto" di tabel siswa belum ada dan gagal dibuat otomatis. Import ulang pengaduan_sarpas.sql atau jalankan: ALTER TABLE siswa ADD COLUMN foto VARCHAR(255) NULL;');
}
ensure_siswa_foto_column();

// Inisial nama untuk avatar cadangan (mis. "Fajar Nur" -> "FN").
function avatar_initials($nama) {
    $nama = trim((string)$nama);
    if ($nama === '') return '?';
    $parts = preg_split('/\s+/u', $nama);
    $ini = mb_substr($parts[0], 0, 1, 'UTF-8');
    if (count($parts) > 1) $ini .= mb_substr(end($parts), 0, 1, 'UTF-8');
    return mb_strtoupper($ini, 'UTF-8');
}

// URL foto profil, atau string kosong kalau belum ada / filenya hilang.
function foto_profil_url($foto) {
    global $base;
    $foto = ltrim((string)$foto, '/');
    if ($foto === '' || strpos($foto, '..') !== false) return '';
    if (!is_file(dirname(__DIR__) . '/' . $foto)) return '';
    return $base . '/' . $foto;
}

// HTML avatar bulat: foto profil kalau ada, kalau tidak inisial nama.
// $size: sm (32px) | md (42px) | lg (104px) | xl (112px). Butuh assets/profil.css.
function avatar($foto, $nama, $size = 'md') {
    $src = foto_profil_url($foto);
    $cls = 'avatar avatar-' . preg_replace('/[^a-z]/', '', (string)$size);
    if ($src !== '') {
        return '<span class="' . $cls . '"><img src="' . e($src) . '" alt="Foto ' . e($nama) . '"></span>';
    }
    return '<span class="' . $cls . ' avatar-fallback" title="' . e($nama) . '">' . e(avatar_initials($nama)) . '</span>';
}
