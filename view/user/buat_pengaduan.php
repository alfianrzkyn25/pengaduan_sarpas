<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Buat Pengaduan</title><link rel="stylesheet" href="../assets/admin.css"></head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar"><div class="sidebar-header">Pengaduan Sarpras</div><div class="sidebar-sub">Portal Siswa</div><ul>
<li><a href="../controller/c_user_dashboard.php" class="">Beranda</a></li>
<li><a href="../controller/c_user_tambah.php" class="active">Buat Pengaduan</a></li>
<li><a href="../controller/c_user_laporan.php" class="">Laporan Saya</a></li>
<li><a href="../logout.php">Keluar</a></li>
</ul><div class="admin-user"><strong><?=e($user["nama"]??"Siswa")?></strong>Siswa</div></aside>
<label for="menuToggle" class="sidebar-overlay"></label>
<div class="main-content">
<header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label><h1>Buat Pengaduan</h1></header>
<main class="content-area">
<?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<section class="panel"><h2 style="margin-top:0">Formulir Pengaduan Baru</h2>
<form method="post" class="admin-form" enctype="multipart/form-data">
<label>Kategori<select name="id_kategori" required><option value="">Pilih kategori</option><?php while($c=mysqli_fetch_assoc($cats)): ?><option value="<?=$c['id_kategori']?>"><?=e($c['nama_kategori'])?> — <?=e($c['ket_kategori'])?></option><?php endwhile;?></select></label>
<label>Lokasi<input name="lokasi" maxlength="50" required placeholder="Contoh: Ruang XII RPL 1"></label>
<label>Keterangan<textarea name="keterangan" maxlength="255" required placeholder="Jelaskan kerusakan atau masalah..."></textarea></label>
<label>Foto Lampiran (opsional)<input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp"><small style="color:#6b7280;display:block;margin-top:4px">Format JPG/PNG/GIF/WEBP, maksimal 5MB.</small></label>
<div><button class="btn" type="submit">Kirim Pengaduan</button> <a class="btn secondary" href="../controller/c_user_dashboard.php">Batal</a></div>
</form></section>
</main></div>
</body></html>
