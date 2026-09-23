<?php
require_once __DIR__ . '/../config/database.php'; require_role('admin'); $user=current_user(); $error='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = trim($_POST['nis'] ?? '');
    $idk = (int)($_POST['id_kategori'] ?? 0);
    $lokasi = trim($_POST['lokasi'] ?? '');
    $ket = trim($_POST['keterangan'] ?? '');
    $admin = (int)$user['id_admin'];

    $allowedExt = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $hasFile = !empty($_FILES['foto']['name']);
    $fotoExt = $hasFile ? strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)) : '';

    if ($nis === '' || !$idk || $lokasi === '' || $ket === '') {
        $error = 'Semua data pengaduan wajib diisi.';
    } elseif ($hasFile && $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Gagal mengunggah foto. Silakan coba lagi.';
    } elseif ($hasFile && !isset($allowedExt[$fotoExt])) {
        $error = 'Format foto tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.';
    } elseif ($hasFile && $_FILES['foto']['size'] > $maxSize) {
        $error = 'Ukuran foto maksimal 5MB.';
    } elseif ($hasFile && !@getimagesize($_FILES['foto']['tmp_name'])) {
        $error = 'File yang diunggah bukan gambar yang valid.';
    } else {
        $st = mysqli_prepare($conn, "INSERT INTO aspirasi (nis,id_kategori,id_admin,lokasi,keterangan,status) VALUES (?,?,?,?,?,'Menunggu')");
        mysqli_stmt_bind_param($st, 'siiss', $nis, $idk, $admin, $lokasi, $ket);
        if (mysqli_stmt_execute($st)) {
            $id = mysqli_insert_id($conn);
            mysqli_stmt_close($st);

            $hs = mysqli_prepare($conn, "INSERT INTO histori (id_aspirasi,id_admin,status_lama,status_baru,catatan,diubah_oleh) VALUES (?, ?, NULL,'Menunggu','Aspirasi baru masuk.',?)");
            $by = $user['nama'] ?? 'admin';
            mysqli_stmt_bind_param($hs, 'iis', $id, $admin, $by);
            mysqli_stmt_execute($hs);
            mysqli_stmt_close($hs);

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

            redirect('c_detail_aspirasi.php?id=' . $id . '&saved=1');
        }
        $error = 'Data gagal disimpan: ' . mysqli_error($conn);
    }
}
$students = mysqli_query($conn, 'SELECT nis,nama,kelas FROM siswa ORDER BY nama ASC');
$categories = mysqli_query($conn, 'SELECT id_kategori,nama_kategori,ket_kategori FROM kategori ORDER BY nama_kategori ASC');
include __DIR__ . '/../view/admin/form_aspirasi.php';
