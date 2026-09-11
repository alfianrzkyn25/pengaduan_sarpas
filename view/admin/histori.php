<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Histori Laporan</title><link rel="stylesheet" href="../assets/admin.css">
<style>
.btn-hapus{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;font-size:12px;background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:6px;cursor:pointer;font-weight:600;transition:background .15s;}
.btn-hapus:hover{background:#fca5a5;}
.alert-success{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal-box{background:#fff;border-radius:12px;padding:28px 32px;max-width:380px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.18);text-align:center;}
.modal-box h3{margin:0 0 8px;font-size:18px;color:#111;}
.modal-box p{margin:0 0 20px;font-size:14px;color:#555;}
.modal-actions{display:flex;gap:10px;justify-content:center;}
.modal-actions .btn-batal{padding:8px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:14px;}
.modal-actions .btn-konfirm{padding:8px 20px;border:none;border-radius:8px;background:#dc2626;color:#fff;cursor:pointer;font-size:14px;font-weight:600;}
</style>
</head><body>
<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar"><div class="sidebar-header">Pengaduan Sarpras</div><ul>
<li><a href="../controller/c_dashboard.php" class="">Dashboard</a></li>
<li><a href="../controller/c_aspirasi.php" class="">Data Aspirasi</a></li>
<li><a href="../controller/c_tambah_aspirasi.php" class="">form aspirasi</a></li>
<li><a href="../controller/c_histori.php" class="active">Histori Laporan</a></li>
<li><a href="../controller/c_kategori.php" class="">Kategori Sarpras</a></li>
<li><a href="../logout.php">Keluar</a></li>
</ul><div class="admin-user"><strong><?=e($user["nama"]??"Admin")?></strong>Administrator</div></aside>
<label for="menuToggle" class="sidebar-overlay"></label>
<div class="main-content">
<header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label><h1>Histori Laporan</h1></header>
<main class="content-area">

<?php if(isset($_GET['deleted'])): ?>
<div class="alert-success">&#10003; Histori berhasil dihapus.</div>
<?php endif; ?>

<div class="grid-3">
<div class="card stat"><span>Menunggu</span><strong><?=$stats['menunggu']?></strong></div>
<div class="card stat"><span>Proses</span><strong><?=$stats['proses']?></strong></div>
<div class="card stat"><span>Selesai</span><strong><?=$stats['selesai']?></strong></div>
</div>
<section class="panel"><h2 style="margin-top:0">Riwayat Perubahan Status</h2>
<div style="overflow:auto"><table class="table">
<tr><th>ID</th><th>Aspirasi</th><th>Status Lama</th><th>Status Baru</th><th>Catatan</th><th>Oleh</th><th>Waktu</th><th>Aksi</th></tr>
<?php while($h=mysqli_fetch_assoc($r)):?>
<tr>
  <td><?=$h['id_histori']?></td>
  <td><a class="btn secondary" style="padding:4px 8px" href="../controller/c_detail_aspirasi.php?id=<?=$h['id_aspirasi']?>">#<?=$h['id_aspirasi']?></a></td>
  <td><?=e($h['status_lama']?:'-')?></td>
  <td><span class="badge <?=strtolower($h['status_baru'])?>"><?=e($h['status_baru'])?></span></td>
  <td><?=e($h['catatan']?:'-')?></td>
  <td><?=e($h['diubah_oleh'])?></td>
  <td><?=e(date('d-m-Y H:i',strtotime($h['waktu_ubah'])))?></td>
  <td>
    <button class="btn-hapus" onclick="konfirmasiHapus(<?=(int)$h['id_histori']?>)">
      &#128465; Hapus
    </button>
  </td>
</tr>
<?php endwhile;?>
</table></div></section>
</main></div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-overlay" id="modalHapus">
  <div class="modal-box">
    <h3>Hapus Histori?</h3>
    <p>Entri histori ini akan dihapus permanen dan tidak dapat dikembalikan.</p>
    <div class="modal-actions">
      <button class="btn-batal" onclick="tutupModal()">Batal</button>
      <form method="POST" action="../controller/c_hapus_histori.php" style="display:inline">
        <input type="hidden" name="id_histori" id="inputIdHistori" value="">
        <input type="hidden" name="back" value="../controller/c_histori.php">
        <button type="submit" class="btn-konfirm">Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
function konfirmasiHapus(id) {
  document.getElementById('inputIdHistori').value = id;
  document.getElementById('modalHapus').classList.add('active');
}
function tutupModal() {
  document.getElementById('modalHapus').classList.remove('active');
}
document.getElementById('modalHapus').addEventListener('click', function(e) {
  if (e.target === this) tutupModal();
});
</script>
</body></html>
