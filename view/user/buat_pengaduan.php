<?php
require_once __DIR__ . '/../../config/database.php';
require_role('siswa');
$user = current_user();
$error = '';

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
                    $dir = __DIR__ . '/../../assets/uploads/aspirasi/' . $id;
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
                redirect('detail_laporan.php?id=' . $id . '&saved=1');
            }
            mysqli_rollback($conn);
            $error = 'Pengaduan gagal disimpan: ' . mysqli_error($conn);
        }
    }
}

$cats = mysqli_query($conn, 'SELECT id_kategori,nama_kategori,ket_kategori FROM kategori ORDER BY nama_kategori ASC');
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Buat Pengaduan</title>
    <link rel="stylesheet" href="../../assets/admin.css">
</head>

<body>
    <input type="checkbox" id="menuToggle" class="menu-toggle">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Pengaduan Sarpras</div>
        <div class="sidebar-sub">Portal Siswa</div>
        <ul>
            <li><a href="dashboard.php" class="">Beranda</a></li>
            <li><a href="buat_pengaduan.php" class="active">Buat Pengaduan</a></li>
            <li><a href="laporan_saya.php" class="">Laporan Saya</a></li>
            <li><a href="../../logout.php">Keluar</a></li>
        </ul>
        <div class="admin-user"><strong><?= e($user["nama"] ?? "Siswa") ?></strong>Siswa</div>
    </aside>
    <label for="menuToggle" class="sidebar-overlay"></label>
    <div class="main-content">
        <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
            <h1>Buat Pengaduan</h1>
        </header>
        <main class="content-area">
            <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
            <section class="panel">
                <h2 style="margin-top:0">Formulir Pengaduan Baru</h2>
                <form method="post" class="admin-form" enctype="multipart/form-data">
                    <label>Kategori<select name="id_kategori" required>
                            <option value="">Pilih kategori</option><?php while ($c = mysqli_fetch_assoc($cats)): ?><option value="<?= $c['id_kategori'] ?>"><?= e($c['nama_kategori']) ?> — <?= e($c['ket_kategori']) ?></option><?php endwhile; ?>
                        </select></label>
                    <label>Lokasi<input name="lokasi" maxlength="50" required placeholder="Contoh: Ruang XII RPL 1"></label>
                    <label>Keterangan<textarea name="keterangan" maxlength="255" required placeholder="Jelaskan kerusakan atau masalah..."></textarea></label>
                    <label>Foto Lampiran (opsional)<input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp"><small style="color:#6b7280;display:block;margin-top:4px">Format JPG/PNG/GIF/WEBP, maksimal 5MB.</small></label>
                    <div><button class="btn" type="submit">Kirim Pengaduan</button> <a class="btn secondary" href="dashboard.php">Batal</a></div>
                </form>
            </section>
        </main>
    </div>
</body>

</html>