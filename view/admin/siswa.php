<?php $base = $base ?? rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/'); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Siswa</title>
<link rel="stylesheet" href="<?=e($base)?>/assets/admin.css">
</head>
<body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">Pengaduan Sarpras</div>
  <ul>
    <li><a href="<?=e($base)?>/view/admin/dashboard.php">Dashboard</a></li>
    <li><a href="<?=e($base)?>/view/admin/aspirasi.php">Data Aspirasi</a></li>
    <li><a href="<?=e($base)?>/view/admin/form_aspirasi.php">Form Aspirasi</a></li>
    <li><a href="<?=e($base)?>/view/admin/histori.php">Histori Laporan</a></li>
    <li><a href="<?=e($base)?>/view/admin/kategori.php">Kategori Sarpras</a></li>
    <li><a href="<?=e($base)?>/controller/c_siswa.php" class="active">Kelola Siswa</a></li>
    <li><a href="<?=e($base)?>/logout.php">Keluar</a></li>
  </ul>
  <div class="admin-user"><strong><?=e($user['nama'] ?? 'Admin')?></strong>Administrator</div>
</aside>
<label for="menuToggle" class="sidebar-overlay"></label>

<div class="main-content">
<header class="topbar">
  <label for="menuToggle" class="menu-icon">&#9776;</label>
  <h1>Kelola Siswa</h1>
</header>

<main class="content-area">
  <div class="heading">
    <div>
      <p class="eyebrow">Manajemen pengguna</p>
      <h1>Data Siswa</h1>
      <p class="muted">Tambah, ubah, dan hapus akun siswa dari satu halaman.</p>
    </div>
    <a class="btn" href="<?=e($base)?>/controller/c_siswa.php?action=baru">+ Tambah Siswa</a>
  </div>

  <?php if ($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?=e($success)?></div><?php endif; ?>

  <?php if ($editing !== null || $action === 'tambah' || $action === 'edit' || ($_GET['action'] ?? '') === 'baru'): ?>
  <section class="panel siswa-form-panel">
    <div class="panel-head">
      <h2><?= $editing ? 'Edit Data Siswa' : 'Tambah Siswa' ?></h2>
      <a href="<?=e($base)?>/controller/c_siswa.php">Tutup</a>
    </div>
    <form method="post" class="admin-form">
      <input type="hidden" name="csrf" value="<?=e($csrf)?>">
      <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'tambah' ?>">
      
      <div class="detail form-grid-2">
        <label>NIS
          <input name="nis" required maxlength="10" inputmode="numeric" pattern="[0-9]{4,10}" value="<?=e($editing['nis'] ?? $old['nis'])?>" placeholder="Contoh: 2425001" <?= $editing ? 'readonly' : '' ?>>
          <?php if ($editing): ?><input type="hidden" name="old_nis" value="<?=e($editing['old_nis'] ?? $editing['nis'])?>"><small class="form-help">NIS merupakan identitas siswa dan tidak diubah saat edit.</small><?php endif; ?>
        </label>
        <label>Nama Lengkap
          <input name="nama" required maxlength="50" value="<?=e($editing['nama'] ?? $old['nama'])?>" placeholder="Nama siswa">
        </label>
        <label>Kelas
          <input name="kelas" required maxlength="10" value="<?=e($editing['kelas'] ?? $old['kelas'])?>" placeholder="Contoh: XII RPL 2">
        </label>
        <label>Password <?= $editing ? '<span class="muted-inline">(kosongkan jika tidak diubah)</span>' : '' ?>
          <input type="password" name="password" <?= $editing ? '' : 'required' ?> minlength="4" autocomplete="new-password" placeholder="Minimal 4 karakter">
        </label>
      </div>
      <div class="form-actions">
        <a class="btn secondary" href="<?=e($base)?>/controller/c_siswa.php">Batal</a>
        <button class="btn" type="submit"><?= $editing ? 'Simpan Perubahan' : 'Tambah Siswa' ?></button>
      </div>
    </form>
  </section>
  <?php endif; ?>

  <section class="panel">
    <div class="toolbar">
      <form class="search" method="get">
        <input name="q" value="<?=e($q)?>" placeholder="Cari NIS, nama, atau kelas...">
        <button class="btn" type="submit">Cari</button>
        <?php if ($q !== ''): ?><a class="btn secondary" href="<?=e($base)?>/controller/c_siswa.php">Reset</a><?php endif; ?>
      </form>
      <div class="siswa-total"><strong><?=count($rows)?></strong><span>siswa ditampilkan</span></div>
    </div>

    <div class="table-title-row">
      <div>
        <h2>Daftar Siswa</h2>
        <p class="muted">Akun siswa yang dapat digunakan untuk masuk ke sistem pengaduan.</p>
      </div>
    </div>

    <div style="overflow:auto">
      <table class="table siswa-table">
        <thead>
          <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Jumlah Pengaduan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="empty-state">Belum ada data siswa.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $i => $s): ?>
          <tr>
            <td><?=($i+1)?></td>
            <td><strong><?=e($s['nis'])?></strong></td>
            <td><?=e($s['nama'])?></td>
            <td><span class="class-pill"><?=e($s['kelas'])?></span></td>
            <td><span class="count-pill"><?=e($s['jumlah_laporan'])?></span></td>
            <td>
              <div class="action-group">
                <a class="btn small" href="<?=e($base)?>/controller/c_siswa.php?edit=<?=urlencode($s['nis'])?>">Edit</a>
                <form method="post" action="<?=e($base)?>/controller/c_siswa.php" onsubmit="return confirm('Yakin ingin menghapus siswa <?=e($s['nama'])?> (<?=e($s['nis'])?>)?');">
                  <input type="hidden" name="csrf" value="<?=e($csrf)?>">
                  <input type="hidden" name="action" value="hapus">
                  <input type="hidden" name="nis" value="<?=e($s['nis'])?>">
                  <button class="btn danger small" type="submit">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</div>
</body>
</html>
