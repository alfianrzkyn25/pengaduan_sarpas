<?php
require_once __DIR__ . '/../../config/database.php';
require_role('siswa');
$user=current_user();
$stats=['total'=>0,'menunggu'=>0,'proses'=>0,'selesai'=>0,'ditolak'=>0];
$st=mysqli_prepare($conn,'SELECT status,COUNT(*) jumlah FROM aspirasi WHERE nis=? GROUP BY status'); mysqli_stmt_bind_param($st,'s',$user['nis']); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st); while($x=mysqli_fetch_assoc($r)){ $stats['total']+=(int)$x['jumlah']; $k=strtolower($x['status']); if(isset($stats[$k]))$stats[$k]=(int)$x['jumlah']; } mysqli_stmt_close($st);
$st=mysqli_prepare($conn,'SELECT a.id_aspirasi,a.lokasi,a.keterangan,a.status,a.tanggal,k.nama_kategori FROM aspirasi a LEFT JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.nis=? ORDER BY a.tanggal DESC LIMIT 5'); mysqli_stmt_bind_param($st,'s',$user['nis']); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard Siswa</title><link rel="stylesheet" href="../../assets/admin.css"></head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar"><div class="sidebar-header">Pengaduan Sarpras</div><div class="sidebar-sub">Portal Siswa</div><ul>
<li><a href="dashboard.php" class="active">Beranda</a></li>
<li><a href="buat_pengaduan.php" class="">Buat Pengaduan</a></li>
<li><a href="laporan_saya.php" class="">Laporan Saya</a></li>
<li><a href="../../logout.php">Keluar</a></li>
</ul><div class="admin-user"><strong><?=e($user["nama"]??"Siswa")?></strong>Siswa</div></aside>
<label for="menuToggle" class="sidebar-overlay"></label>
<div class="main-content">
<header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label><h1>Dashboard Siswa</h1></header>
<main class="content-area">
<div class="heading"><div><p class="eyebrow">Portal Siswa</p><h1>Halo, <?=e($user['nama'])?> 👋</h1><p class="muted">Kelola pengaduan sarana dan lihat perkembangan laporanmu.</p></div><a class="btn" href="buat_pengaduan.php">+ Buat Pengaduan</a></div>
<div class="grid">
<div class="card stat"><span>Total Laporan</span><strong><?=$stats['total']?></strong></div>
<div class="card stat"><span>Menunggu</span><strong><?=$stats['menunggu']?></strong></div>
<div class="card stat"><span>Diproses</span><strong><?=$stats['proses']?></strong></div>
<div class="card stat"><span>Selesai</span><strong><?=$stats['selesai']?></strong></div>
<div class="card stat"><span>Ditolak</span><strong><?=$stats['ditolak']?></strong></div>
</div>
<section class="panel"><div class="panel-head"><h2>Laporan Terbaru</h2><a href="laporan_saya.php">Lihat semua</a></div>
<div class="list"><?php while($a=mysqli_fetch_assoc($r)): ?><a class="item" href="detail_laporan.php?id=<?=(int)$a['id_aspirasi']?>"><div><b>#<?=(int)$a['id_aspirasi']?> · <?=e($a['nama_kategori'])?></b><span><?=e($a['lokasi'])?></span><small><?=e($a['keterangan'])?></small></div><span class="badge <?=strtolower($a['status'])?>"><?=e($a['status'])?></span></a><?php endwhile; mysqli_stmt_close($st); ?></div>
</section>
</main></div>
</body></html>
