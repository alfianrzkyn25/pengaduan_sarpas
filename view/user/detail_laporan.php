<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Detail Laporan</title><link rel="stylesheet" href="../assets/admin.css"></head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar"><div class="sidebar-header">Pengaduan Sarpras</div><div class="sidebar-sub">Portal Siswa</div><ul>
<li><a href="../controller/c_user_dashboard.php" class="">Beranda</a></li>
<li><a href="../controller/c_user_tambah.php" class="">Buat Pengaduan</a></li>
<li><a href="../controller/c_user_laporan.php" class="active">Laporan Saya</a></li>
<li><a href="../logout.php">Keluar</a></li>
</ul><div class="admin-user"><strong><?=e($user["nama"]??"Siswa")?></strong>Siswa</div></aside>
<label for="menuToggle" class="sidebar-overlay"></label>
<div class="main-content">
<header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label><h1>Detail Laporan</h1></header>
<main class="content-area">
<?php if($saved):?><div class="alert success">Pengaduan berhasil dikirim.</div><?php endif;?>
<?php if(!$a):?><div class="alert error">Laporan tidak ditemukan.</div><a class="btn" href="../controller/c_user_laporan.php">Kembali</a><?php else:?>
<div class="heading"><div><p class="eyebrow">Laporan #<?=(int)$a['id_aspirasi']?></p><h1><?=e($a['nama_kategori'])?></h1><p class="muted"><?=e($a['tanggal'])?></p></div><span class="badge <?=strtolower($a['status'])?>"><?=e($a['status'])?></span></div>
<div class="detail">
<section class="panel"><h2>Informasi Pengaduan</h2><p><b>Pelapor</b><?=e($a['nama'])?> · <?=e($a['kelas'])?></p><p><b>Lokasi</b><?=e($a['lokasi'])?></p><p><b>Keterangan</b><?=e($a['keterangan'])?></p>
<?php if($fotoPengaduan):?><div style="margin-top:10px"><b>Foto Pengaduan</b><div class="lampiran-grid"><?php foreach($fotoPengaduan as $lf):?><a href="../<?=e($lf['url_file'])?>" target="_blank"><img src="../<?=e($lf['url_file'])?>" alt="<?=e($lf['nama_file'])?>"></a><?php endforeach;?></div></div><?php endif;?>
</section>
<section class="panel"><h2>Perkembangan</h2><div class="timeline"><?php foreach($hist as $h):?><div class="timeline-item"><b><?=e($h['status_baru'])?></b><small><?=e($h['waktu_ubah'])?> · <?=e($h['diubah_oleh'])?></small><div><?=e($h['catatan']?:'-')?></div></div><?php endforeach;?></div></section>
</div>
<section class="panel">
<h2>Feedback dari Admin</h2>
<?php if($a['feedback']):?>
<div class="alert <?=strtolower($a['status'])==='selesai'?'success':'info'?>" style="margin-bottom:<?=$fotoFeedback?'14px':'0'?>"><?=e($a['feedback'])?></div>
<?php else: ?>
<p class="muted" style="margin:0 0 <?=$fotoFeedback?'14px':'0'?>">Belum ada feedback dari admin.</p>
<?php endif;?>
<?php if($fotoFeedback):?><div><b>Foto Bukti Perbaikan</b><div class="lampiran-grid"><?php foreach($fotoFeedback as $lf):?><a href="../<?=e($lf['url_file'])?>" target="_blank"><img src="../<?=e($lf['url_file'])?>" alt="<?=e($lf['nama_file'])?>"></a><?php endforeach;?></div></div><?php endif;?>
</section>
<a class="btn secondary" href="../controller/c_user_laporan.php">← Kembali</a>
<?php endif;?>
</main></div>
</body></html>
