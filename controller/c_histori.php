<?php
require_once __DIR__ . '/../config/database.php'; require_role('admin'); $user=current_user();
$stats=['menunggu'=>0,'proses'=>0,'selesai'=>0];
$rs=mysqli_query($conn,"SELECT status_baru,COUNT(*) jumlah FROM histori GROUP BY status_baru");
while($x=mysqli_fetch_assoc($rs)){ $key=strtolower($x['status_baru']); if(isset($stats[$key])) $stats[$key]=(int)$x['jumlah']; }
$r=mysqli_query($conn,"SELECT h.*,s.nama,s.nis,COALESCE(ad.nama,h.diubah_oleh) diubah_oleh FROM histori h LEFT JOIN aspirasi a ON a.id_aspirasi=h.id_aspirasi LEFT JOIN siswa s ON s.nis=a.nis LEFT JOIN admin ad ON ad.id_admin=h.id_admin ORDER BY h.waktu_ubah DESC,h.id_histori DESC");
include __DIR__.'/../view/admin/histori.php';
