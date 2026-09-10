<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Data Aspirasi</title><link rel="stylesheet" href="../assets/admin.css"></head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar"><div class="sidebar-header">Pengaduan Sarpras</div><ul>
<li><a href="../controller/c_dashboard.php" class="">Dashboard</a></li>
<li><a href="../controller/c_aspirasi.php" class="active">Data Aspirasi</a></li>
<li><a href="../controller/c_tambah_aspirasi.php" class="">form aspirasi</a></li>
<li><a href="../controller/c_histori.php" class="">Histori Laporan</a></li>
<li><a href="../controller/c_kategori.php" class="">Kategori Sarpras</a></li>
<li><a href="../logout.php">Keluar</a></li>
</ul><div class="admin-user"><strong><?=e($user["nama"]??"Admin")?></strong>Administrator</div></aside>
<label for="menuToggle" class="sidebar-overlay"></label>
<div class="main-content">
<header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label><h1>Data Aspirasi</h1></header>
<main class="content-area">
<section class="panel">
<div class="toolbar">
<form class="search" method="get"><input name="q" value="<?=e($q)?>" placeholder="Cari NIS, nama, lokasi, status..."><button class="btn" type="submit">Cari</button></form>
<a class="btn" href="../controller/c_tambah_aspirasi.php">+ Tambah Aspirasi</a>
</div>
<h2 style="margin-top:0">Daftar Aspirasi</h2>
<div style="overflow:auto"><table class="table">
<tr><th>ID</th><th>NIS</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Laporan</th><th>Status</th><th>Feedback Admin</th><th>Aksi</th></tr>
<?php foreach($rows as $a):?>
<tr><td>#<?=$a['id_aspirasi']?></td><td><?=e($a['nis'])?></td><td><?=e($a['nama'])?></td><td><?=e($a['nama_kategori'])?></td><td><?=e($a['lokasi'])?></td><td><?=e($a['keterangan'])?></td><td><span class="badge <?=strtolower($a['status'])?>"><?=e($a['status'])?></span></td><td><?=e($a['feedback']?:'-')?></td><td><a class="btn" href="../controller/c_detail_aspirasi.php?id=<?=$a['id_aspirasi']?>">Beri Feedback</a></td></tr>
<?php endforeach;?>
</table></div></section>
</main></div>
</body></html>
