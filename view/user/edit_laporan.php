<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Laporan #<?=(int)$a['id_aspirasi']?></title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.alert.error{background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;}
.lampiran-list{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;}
.lampiran-item{position:relative;display:inline-flex;align-items:center;gap:6px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:13px;}
.lampiran-item a{color:#2563eb;text-decoration:none;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.lampiran-item a:hover{text-decoration:underline;}
.btn-hapus-foto{background:none;border:none;color:#dc2626;cursor:pointer;font-size:16px;line-height:1;padding:0 2px;flex-shrink:0;}
.btn-hapus-foto:hover{color:#991b1b;}
.info-note{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:10px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;}
</style>
</head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">Pengaduan Sarpras</div>
  <div class="sidebar-sub">Portal Siswa</div>
  <ul>
    <li><a href="../controller/c_user_dashboard.php">Beranda</a></li>
    <li><a href="../controller/c_user_tambah.php">Buat Pengaduan</a></li>
    <li><a href="../controller/c_user_laporan.php" class="active">Laporan Saya</a></li>
    <li><a href="../logout.php">Keluar</a></li>
  </ul>
  <div class="admin-user"><strong><?=e($user['nama']??'Siswa')?></strong>Siswa</div>
</aside>
<label for="menuToggle" class="sidebar-overlay"></label>

<div class="main-content">
<header class="topbar">
  <label for="menuToggle" class="menu-icon">&#9776;</label>
  <h1>Edit Laporan #<?=(int)$a['id_aspirasi']?></h1>
</header>
<main class="content-area">

<div class="info-note">&#9432; Hanya laporan berstatus <strong>Menunggu</strong> yang dapat diubah.</div>

<?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>

<section class="panel">
  <h2 style="margin-top:0">Edit Pengaduan</h2>
  <form method="POST" action="../controller/c_user_edit.php" class="admin-form" enctype="multipart/form-data">
    <input type="hidden" name="id_aspirasi" value="<?=(int)$a['id_aspirasi']?>">

    <label>Kategori
      <select name="id_kategori" required>
        <option value="">Pilih kategori</option>
        <?php while($c=mysqli_fetch_assoc($cats)): ?>
        <option value="<?=$c['id_kategori']?>" <?=(int)$c['id_kategori']===(int)$a['id_kategori']?'selected':'?>>
          <?=e($c['nama_kategori'])?> — <?=e($c['ket_kategori'])?>
        </option>
        <?php endwhile;?>
      </select>
    </label>

    <label>Lokasi
      <input name="lokasi" maxlength="50" required placeholder="Contoh: Ruang XII RPL 1"
             value="<?=e($a['lokasi'])?>">
    </label>

    <label>Keterangan
      <textarea name="keterangan" maxlength="255" required
                placeholder="Jelaskan kerusakan atau masalah..."><?=e($a['keterangan'])?></textarea>
    </label>

    <?php if(!empty($lampiran)): ?>
    <label>Foto Terlampir Saat Ini</label>
    <div class="lampiran-list">
      <?php foreach($lampiran as $lf): ?>
      <div class="lampiran-item">
        <a href="../<?=e($lf['url_file'])?>" target="_blank" title="<?=e($lf['nama_file'])?>">
          &#128247; <?=e($lf['nama_file'])?>
        </a>
        <form method="POST" action="../controller/c_hapus_lampiran.php" style="display:inline">
          <input type="hidden" name="id_lampiran"  value="<?=(int)$lf['id_lampiran']?>">
          <input type="hidden" name="id_aspirasi"  value="<?=(int)$a['id_aspirasi']?>">
          <button type="submit" class="btn-hapus-foto" title="Hapus foto ini">&times;</button>
        </form>
      </div>
      <?php endforeach;?>
    </div>
    <?php endif;?>

    <label>Tambah Foto Baru (opsional)
      <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp">
      <small style="color:#6b7280;display:block;margin-top:4px">Format JPG/PNG/GIF/WEBP, maksimal 5MB.</small>
    </label>

    <div>
      <button class="btn" type="submit">&#10003; Simpan Perubahan</button>
      <a class="btn secondary" href="../controller/c_user_laporan.php">Batal</a>
    </div>
  </form>
</section>

</main></div>
</body></html>
