<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');
$user = current_user();
$stats=['total'=>0,'menunggu'=>0,'proses'=>0,'selesai'=>0];
$r=mysqli_query($conn,"SELECT status,COUNT(*) jumlah FROM aspirasi GROUP BY status");
while($x=mysqli_fetch_assoc($r)){ $stats['total']+=(int)$x['jumlah']; $key=strtolower($x['status']); if(isset($stats[$key])) $stats[$key]=(int)$x['jumlah']; }
$r=mysqli_query($conn,"SELECT a.id_aspirasi,a.nis,s.nama,k.nama_kategori,a.lokasi,a.keterangan,a.status,a.tanggal FROM aspirasi a LEFT JOIN siswa s ON s.nis=a.nis LEFT JOIN kategori k ON k.id_kategori=a.id_kategori ORDER BY a.tanggal DESC,a.id_aspirasi DESC LIMIT 5");
include __DIR__.'/../view/admin/dashboard.php';
