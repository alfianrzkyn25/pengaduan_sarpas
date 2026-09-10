<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Kategori Sarpras</title><link rel="stylesheet" href="../assets/admin.css"></head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar"><div class="sidebar-header">Pengaduan Sarpras</div><ul>
<li><a href="../controller/c_dashboard.php" class="">Dashboard</a></li>
<li><a href="../controller/c_aspirasi.php" class="">Data Aspirasi</a></li>
<li><a href="../controller/c_tambah_aspirasi.php" class="">form aspirasi</a></li>
<li><a href="../controller/c_histori.php" class="">Histori Laporan</a></li>
<li><a href="../controller/c_kategori.php" class="active">Kategori Sarpras</a></li>
<li><a href="../logout.php">Keluar</a></li>
</ul><div class="admin-user"><strong><?=e($user["nama"]??"Admin")?></strong>Administrator</div></aside>
<label for="menuToggle" class="sidebar-overlay"></label>
<div class="main-content">
<header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label><h1>Kategori Sarpras</h1></header>
<main class="content-area">
<section class="panel"><h2 style="margin-top:0">Daftar Kategori & Jumlah Aspirasi</h2>
<div style="overflow:auto"><table class="table">
<tr><th>ID</th><th>Kategori</th><th>Jumlah Aspirasi</th><th>Aksi</th></tr>
<?php while($k=mysqli_fetch_assoc($r)):?>
<tr><td><?=$k['id_kategori']?></td><td><?=e($k['nama_kategori'])?></td><td><?=$k['jumlah']?></td><td><a class="btn" href="../controller/c_aspirasi.php?q=<?=urlencode($k['nama_kategori'])?>">Lihat Aspirasi</a></td></tr>
<?php endwhile;?>
</table></div></section>
</main></div>
</body></html>
