<?php
require_once __DIR__ . '/../../config/database.php';
require_role('siswa');
$user = current_user();

$id   = (int)($_GET['id'] ?? $_POST['id_aspirasi'] ?? 0);
$error = '';

// Ambil data aspirasi milik siswa ini, hanya boleh edit jika Menunggu
$st = mysqli_prepare($conn, 'SELECT a.*,k.nama_kategori FROM aspirasi a LEFT JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.id_aspirasi=? AND a.nis=?');
mysqli_stmt_bind_param($st, 'is', $id, $user['nis']);
mysqli_stmt_execute($st);
$a = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
mysqli_stmt_close($st);

if (!$a) {
  redirect(url('user/laporan_saya'));
}
if ($a['status'] !== 'Menunggu') {
  redirect(url('user/laporan_saya') . '?error_edit=1');
}

// Handle POST (simpan perubahan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idk    = (int)($_POST['id_kategori'] ?? 0);
  $lokasi = trim($_POST['lokasi'] ?? '');
  $ket    = trim($_POST['keterangan'] ?? '');

  $allowedExt = ['jpg' => 1, 'jpeg' => 1, 'png' => 1, 'gif' => 1, 'webp' => 1];
  $maxSize    = 5 * 1024 * 1024;
  $hasFile    = !empty($_FILES['foto']['name']);
  $fotoExt    = $hasFile ? strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)) : '';

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
    $st = mysqli_prepare($conn, 'UPDATE aspirasi SET id_kategori=?,lokasi=?,keterangan=? WHERE id_aspirasi=? AND nis=? AND status="Menunggu"');
    mysqli_stmt_bind_param($st, 'issis', $idk, $lokasi, $ket, $id, $user['nis']);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    if ($ok) {
      if ($hasFile) {
        $dir = __DIR__ . '/../../assets/uploads/aspirasi/' . $id;
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $filename = 'foto-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.' . $fotoExt;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . '/' . $filename)) {
          $urlFile  = 'assets/uploads/aspirasi/' . $id . '/' . $filename;
          $origName = $_FILES['foto']['name'];
          $lp = mysqli_prepare($conn, 'INSERT INTO lampiran (id_aspirasi,url_file,nama_file) VALUES (?,?,?)');
          mysqli_stmt_bind_param($lp, 'iss', $id, $urlFile, $origName);
          mysqli_stmt_execute($lp);
          mysqli_stmt_close($lp);
        }
      }
      redirect(url('user/laporan_saya') . '?edited=1');
    }
    $error = 'Gagal menyimpan perubahan: ' . mysqli_error($conn);
  }

  // Refresh $a setelah gagal supaya form tetap terisi
  $st = mysqli_prepare($conn, 'SELECT a.*,k.nama_kategori FROM aspirasi a LEFT JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.id_aspirasi=? AND a.nis=?');
  mysqli_stmt_bind_param($st, 'is', $id, $user['nis']);
  mysqli_stmt_execute($st);
  $a = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
  mysqli_stmt_close($st);
}

// Ambil daftar kategori
$cats = mysqli_query($conn, 'SELECT id_kategori,nama_kategori,ket_kategori FROM kategori ORDER BY nama_kategori ASC');

// Lampiran yang sudah ada
$lampiran = [];
$st = mysqli_prepare($conn, 'SELECT id_lampiran,url_file,nama_file FROM lampiran WHERE id_aspirasi=? ORDER BY id_lampiran ASC');
mysqli_stmt_bind_param($st, 'i', $id);
mysqli_stmt_execute($st);
$lr = mysqli_stmt_get_result($st);
while ($lf = mysqli_fetch_assoc($lr)) {
  if (strpos(basename($lf['url_file']), 'feedback-') !== 0) $lampiran[] = $lf;
}
mysqli_stmt_close($st);
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Laporan #<?= (int)$a['id_aspirasi'] ?></title>
  <link rel="stylesheet" href="<?= e($base) ?>/assets/admin.css">
  <style>
    .alert.error {
      background: #fef2f2;
      border-left: 4px solid #dc2626;
      color: #b91c1c;
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 16px;
      font-size: 14px;
    }

    .lampiran-list {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 8px;
    }

    .lampiran-item {
      position: relative;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f8fafc;
      border: 1px solid #e7eaf2;
      border-radius: 10px;
      padding: 7px 12px;
      font-size: 13px;
      transition: background .15s ease;
    }

    .lampiran-item:hover {
      background: #f1f4f9;
    }

    .lampiran-item a {
      color: #2563eb;
      text-decoration: none;
      max-width: 180px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .lampiran-item a:hover {
      text-decoration: underline;
    }

    .btn-hapus-foto {
      background: none;
      border: none;
      color: #dc2626;
      cursor: pointer;
      font-size: 16px;
      line-height: 1;
      padding: 0 2px;
      flex-shrink: 0;
      transition: transform .15s ease;
    }

    .btn-hapus-foto:hover {
      color: #991b1b;
      transform: scale(1.15);
    }

    .info-note {
      background: #eff6ff;
      border-left: 4px solid #2563eb;
      color: #1e40af;
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 13px;
      margin-bottom: 16px;
    }
  </style>
</head>

<body>
  <input type="checkbox" id="menuToggle" class="menu-toggle">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">Pengaduan Sarpras</div>
    <div class="sidebar-sub">Portal Siswa</div>
    <ul>
      <li><a href="<?= e(url('user/dashboard')) ?>">Beranda</a></li>
      <li><a href="<?= e(url('user/buat_pengaduan')) ?>">Buat Pengaduan</a></li>
      <li><a href="<?= e(url('user/laporan_saya')) ?>" class="active">Laporan Saya</a></li>
      <li><a href="<?= e(url('auth/logout')) ?>">Keluar</a></li>
    </ul>
    <div class="admin-user"><strong><?= e($user['nama'] ?? 'Siswa') ?></strong>Siswa</div>
  </aside>
  <label for="menuToggle" class="sidebar-overlay"></label>

  <div class="main-content">
    <header class="topbar">
      <label for="menuToggle" class="menu-icon">&#9776;</label>
      <h1>Edit Laporan #<?= (int)$a['id_aspirasi'] ?></h1>
    </header>
    <main class="content-area">

      <div class="info-note">&#9432; Hanya laporan berstatus <strong>Menunggu</strong> yang dapat diubah.</div>

      <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

      <section class="panel">
        <h2 style="margin-top:0">Edit Pengaduan</h2>
        <form method="POST" class="admin-form" enctype="multipart/form-data">
          <input type="hidden" name="id_aspirasi" value="<?= (int)$a['id_aspirasi'] ?>">

          <label>Kategori
            <select name="id_kategori" required>
              <option value="">Pilih kategori</option>
              <?php while ($c = mysqli_fetch_assoc($cats)): ?>
                <option value="<?= $c['id_kategori'] ?>" <?= (int)$c['id_kategori'] === (int)$a['id_kategori'] ? 'selected' : '' ?>>
                  <?= e($c['nama_kategori']) ?> — <?= e($c['ket_kategori']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </label>

          <label>Lokasi
            <input name="lokasi" maxlength="50" required placeholder="Contoh: Ruang XII RPL 1"
              value="<?= e($a['lokasi']) ?>">
          </label>

          <label>Keterangan
            <textarea name="keterangan" maxlength="255" required
              placeholder="Jelaskan kerusakan atau masalah..."><?= e($a['keterangan']) ?></textarea>
          </label>

          <?php if (!empty($lampiran)): ?>
            <label>Foto Terlampir Saat Ini</label>
            <div class="lampiran-list">
              <?php foreach ($lampiran as $lf): ?>
                <div class="lampiran-item">
                  <a href="<?= e($base) ?>/<?= e($lf['url_file']) ?>" target="_blank" title="<?= e($lf['nama_file']) ?>">
                    &#128247; <?= e($lf['nama_file']) ?>
                  </a>
                  <form method="POST" action="<?= e($base) ?>/controller/c_hapus_lampiran.php" style="display:inline">
                    <input type="hidden" name="id_lampiran" value="<?= (int)$lf['id_lampiran'] ?>">
                    <input type="hidden" name="id_aspirasi" value="<?= (int)$a['id_aspirasi'] ?>">
                    <button type="submit" class="btn-hapus-foto" title="Hapus foto ini">&times;</button>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <label>Tambah Foto Baru (opsional)
            <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp">
            <small style="color:#6b7280;display:block;margin-top:4px">Format JPG/PNG/GIF/WEBP, maksimal 5MB.</small>
          </label>

          <div>
            <button class="btn" type="submit">&#10003; Simpan Perubahan</button>
            <a class="btn secondary" href="<?= e(url('user/laporan_saya')) ?>">Batal</a>
          </div>
        </form>
      </section>

    </main>
  </div>
</body>

</html>