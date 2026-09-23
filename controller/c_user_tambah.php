<?php
require_once __DIR__ . '/../config/database.php'; require_role('siswa'); $user = current_user(); $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idk = (int)($_POST['id_kategori'] ?? 0);
    $lokasi = trim($_POST['lokasi'] ?? '');
    $ket = trim($_POST['keterangan'] ?? '');

    $allowedExt = ['jpg' => 1, 'jpeg' => 1, 'png' => 1, 'gif' => 1, 'webp' => 1];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $hasFile = !empty($_FILES['foto']['name']);
    $fotoExt = $hasFile ? strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)) : '';

    if (!$idk || $lokasi === '' || $ket === '') {
        $error = 'Semua data wajib diisi.';
    } elseif ($hasFile && $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Gagal mengunggah foto. Silakan coba lagi.';
    } elseif ($hasFile && !isset($allowedExt[$fotoExt])) {
        $error = 'Format foto tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.';
    } elseif ($hasFile && $_FILES['foto']['size'] > $maxSize) {
        $error = 'Ukuran foto maksimal 5MB.';
    } elseif ($hasFile && !@getimagesize($_FILES['foto']['tmp_name'])) {
        $error = 'File yang diunggah bukan gambar yang valid.';
    } else {
        $admin = default_admin_id();
        if (!$admin) {
            $error = 'Belum ada data admin di database.';
        } else {
            mysqli_begin_transaction($conn);
            $st = mysqli_prepare($conn, "INSERT INTO aspirasi (nis,id_kategori,id_admin,lokasi,keterangan,status) VALUES (?,?,?,?,?,'Menunggu')");
            mysqli_stmt_bind_param($st, 'siiss', $user['nis'], $idk, $admin, $lokasi, $ket);
            $ok = mysqli_stmt_execute($st);
            $id = mysqli_insert_id($conn);
            mysqli_stmt_close($st);

            if ($ok) {
                $hs = mysqli_prepare($conn, "INSERT INTO histori (id_aspirasi,id_admin,status_lama,status_baru,catatan,diubah_oleh) VALUES (?, ?, NULL,'Menunggu','Aspirasi baru masuk.','siswa')");
                mysqli_stmt_bind_param($hs, 'ii', $id, $admin);
                $ok = mysqli_stmt_execute($hs);
                mysqli_stmt_close($hs);
            }

            if ($ok) {
                mysqli_commit($conn);
                if ($hasFile) {
                    $dir = __DIR__ . '/../assets/uploads/aspirasi/' . $id;
                    if (!is_dir($dir)) mkdir($dir, 0777, true);
                    $filename = 'foto-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.' . $fotoExt;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . '/' . $filename)) {
                        $urlFile = 'assets/uploads/aspirasi/' . $id . '/' . $filename;
                        $origName = $_FILES['foto']['name'];
                        $lp = mysqli_prepare($conn, 'INSERT INTO lampiran (id_aspirasi, url_file, nama_file) VALUES (?,?,?)');
                        mysqli_stmt_bind_param($lp, 'iss', $id, $urlFile, $origName);
                        mysqli_stmt_execute($lp);
                        mysqli_stmt_close($lp);
                    }
                }
                redirect('c_user_detail.php?id=' . $id . '&saved=1');
            }
            mysqli_rollback($conn);
            $error = 'Pengaduan gagal disimpan: ' . mysqli_error($conn);
        }
    }
}

$cats = mysqli_query($conn, 'SELECT id_kategori,nama_kategori,ket_kategori FROM kategori ORDER BY nama_kategori ASC');
include __DIR__ . '/../view/user/buat_pengaduan.php';
