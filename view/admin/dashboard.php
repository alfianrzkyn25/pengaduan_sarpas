<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard Utama</title><link rel="stylesheet" href="../assets/admin.css"></head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar"><div class="sidebar-header">Pengaduan Sarpras</div><ul>
<li><a href="../controller/c_dashboard.php" class="active">Dashboard</a></li>
<li><a href="../controller/c_aspirasi.php" class="">Data Aspirasi</a></li>
<li><a href="../controller/c_tambah_aspirasi.php" class="">form aspirasi</a></li>
<li><a href="../controller/c_histori.php" class="">Histori Laporan</a></li>
<li><a href="../controller/c_kategori.php" class="">Kategori Sarpras</a></li>
<li><a href="../logout.php">Keluar</a></li>
</ul><div class="admin-user"><strong><?=e($user["nama"]??"Admin")?></strong>Administrator</div></aside>
<label for="menuToggle" class="sidebar-overlay"></label>
<div class="main-content">
<header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label><h1>Dashboard Utama</h1></header>
<main class="content-area">
<div class="grid">
<div class="card stat"><span>Total Pengaduan</span><strong><?=$stats['total']?></strong></div>
<div class="card stat"><span>Menunggu</span><strong><?=$stats['menunggu']?></strong></div>
<div class="card stat"><span>Proses</span><strong><?=$stats['proses']?></strong></div>
<div class="card stat"><span>Selesai</span><strong><?=$stats['selesai']?></strong></div>
</div>
<section class="panel"><div class="panel-head"><h2>5 Aspirasi Terbaru</h2><a href="../controller/c_aspirasi.php">Kelola semua</a></div>
<div style="overflow:auto"><table class="table">
<tr><th>ID</th><th>Siswa</th><th>Kategori</th><th>Lokasi</th><th>Laporan</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
<?php while($a=mysqli_fetch_assoc($r)):?>
<tr><td>#<?=$a['id_aspirasi']?></td><td><?=e($a['nama'])?></td><td><?=e($a['nama_kategori'])?></td><td><?=e($a['lokasi'])?></td><td><?=e($a['keterangan'])?></td><td><span class="badge <?=strtolower($a['status'])?>"><?=e($a['status'])?></span></td><td><?=e(date('d-m-Y H:i',strtotime($a['tanggal'])))?></td><td><a class="btn" href="../controller/c_detail_aspirasi.php?id=<?=$a['id_aspirasi']?>">Beri Feedback</a></td></tr>
<?php endwhile;?>
</table></div></section>
</main></div>
</body></html>
