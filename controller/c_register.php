<?php
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['role'])) {
    redirect($_SESSION['role'] === 'admin' ? 'controller/c_dashboard.php' : 'controller/c_user_dashboard.php');
}

$error = '';
$old = ['nis' => '', 'nama' => '', 'kelas' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis         = trim($_POST['nis'] ?? '');
    $nama        = trim($_POST['nama'] ?? '');
    $kelas       = trim($_POST['kelas'] ?? '');
    $password    = $_POST['password'] ?? '';
    $konfirmasi  = $_POST['konfirmasi'] ?? '';
    $old = ['nis' => $nis, 'nama' => $nama, 'kelas' => $kelas];

    if ($nis === '' || $nama === '' || $kelas === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua data wajib diisi.';
    } elseif (!preg_match('/^[0-9]{4,10}$/', $nis)) {
        $error = 'NIS harus berupa angka (4-10 digit).';
    } elseif (mb_strlen($nama) < 3) {
        $error = 'Nama lengkap minimal 3 karakter.';
    } elseif (strlen($password) < 4) {
        $error = 'Password minimal 4 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama dengan password.';
    } else {
        $st = mysqli_prepare($conn, 'SELECT nis FROM siswa WHERE nis=? LIMIT 1');
        mysqli_stmt_bind_param($st, 's', $nis);
        mysqli_stmt_execute($st);
        mysqli_stmt_store_result($st);
        $exists = mysqli_stmt_num_rows($st) > 0;
        mysqli_stmt_close($st);

        $adminClash = false;
        if (!$exists) {
            $sa = mysqli_prepare($conn, 'SELECT id_admin FROM admin WHERE username=? LIMIT 1');
            mysqli_stmt_bind_param($sa, 's', $nis);
            mysqli_stmt_execute($sa);
            mysqli_stmt_store_result($sa);
            $adminClash = mysqli_stmt_num_rows($sa) > 0;
            mysqli_stmt_close($sa);
        }

        if ($exists) {
            $error = 'NIS ini sudah terdaftar. Silakan login.';
        } elseif ($adminClash) {
            $error = 'NIS ini tidak dapat digunakan. Silakan hubungi admin.';
        } else {
            $stmt = mysqli_prepare($conn, 'INSERT INTO siswa (nis, nama, kelas, password) VALUES (?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'ssss', $nis, $nama, $kelas, $password);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($ok) {
                redirect('login.php?registered=1');
            }
            $error = 'Registrasi gagal disimpan: ' . mysqli_error($conn);
        }
    }
}

include __DIR__ . '/../view/auth/register.php';
