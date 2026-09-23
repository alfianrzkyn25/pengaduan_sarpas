<?php
require_once __DIR__ . '/../../config/database.php';
require_role('siswa');
$user=current_user();
$st=mysqli_prepare($conn,'SELECT a.id_aspirasi,a.lokasi,a.keterangan,a.status,a.tanggal,k.nama_kategori FROM aspirasi a LEFT JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.nis=? ORDER BY a.tanggal DESC,a.id_aspirasi DESC'); mysqli_stmt_bind_param($st,'s',$user['nis']); mysqli_stmt_execute($st); $r=mysqli_stmt_get_result($st);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Laporan Saya</title>
<link rel="stylesheet" href="../../assets/admin.css">
<style>
.item-wrap { position: relative; }
.item.has-action { padding-right: 175px; }

.item-actions {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  gap: 6px;
  z-index: 2;
}

.btn-edit-user {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 13px;
  font-size: 12px;
  font-weight: 600;
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  text-decoration: none;
  white-space: nowrap;
  transition: background .15s ease, transform .15s ease;
}
.btn-edit-user:hover { background: #dbeafe; transform: translateY(-1px); }

.btn-hapus-user {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 13px;
  font-size: 12px;
  font-weight: 600;
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
  border-radius: 8px;
  cursor: pointer;
  white-space: nowrap;
  transition: background .15s ease, transform .15s ease;
}
.btn-hapus-user:hover { background: #fecaca; transform: translateY(-1px); }

.alert-success {
  background: #f0fdf4; border-left: 4px solid #16a34a;
  color: #166534; padding: 12px 16px;
  border-radius: 12px; margin-bottom: 16px; font-size: 14px;
}
.alert-error {
  background: #fef2f2; border-left: 4px solid #dc2626;
  color: #b91c1c; padding: 12px 16px;
  border-radius: 12px; margin-bottom: 16px; font-size: 14px;
}

.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(15,23,42,.5); backdrop-filter: blur(2px); z-index: 999;
  align-items: center; justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-box {
  background: #fff; border-radius: 18px;
  padding: 30px 32px; max-width: 380px; width: 90%;
  box-shadow: 0 24px 48px -12px rgba(15,23,42,.35); text-align: center;
  animation: modalIn .22s cubic-bezier(.4,0,.2,1);
}
@keyframes modalIn{from{opacity:0;transform:translateY(10px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.modal-box h3 { margin: 0 0 8px; font-size: 18px; color: #111827; font-weight: 800; }
.modal-box p  { margin: 0 0 6px; font-size: 14px; color: #667085; }
.modal-box small { display: block; font-size: 12px; color: #ef4444; margin-bottom: 18px; }
.modal-actions { display: flex; gap: 10px; justify-content: center; }
.modal-actions .btn-batal {
  padding: 9px 20px; border: 1.5px solid #e2e6ee;
  border-radius: 10px; background: #fff; cursor: pointer; font-size: 14px; font-weight: 600; color: #374151;
  transition: background .15s ease;
}
.modal-actions .btn-batal:hover { background: #f8fafc; }
.modal-actions .btn-konfirm {
  padding: 9px 20px; border: none; border-radius: 10px;
  background: linear-gradient(135deg,#ef4444,#b91c1c); color: #fff; cursor: pointer;
  font-size: 14px; font-weight: 700; box-shadow: 0 8px 18px -8px rgba(220,38,38,.5);
  transition: transform .15s ease, filter .15s ease;
}
.modal-actions .btn-konfirm:hover { transform: translateY(-1px); filter: brightness(1.05); }
</style>
</head><body>

<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">Pengaduan Sarpras</div>
  <div class="sidebar-sub">Portal Siswa</div>
  <ul>
    <li><a href="dashboard.php">Beranda</a></li>
    <li><a href="buat_pengaduan.php">Buat Pengaduan</a></li>
    <li><a href="laporan_saya.php" class="active">Laporan Saya</a></li>
    <li><a href="../../logout.php">Keluar</a></li>
  </ul>
  <div class="admin-user"><strong><?=e($user['nama']??'Siswa')?></strong>Siswa</div>
</aside>
<label for="menuToggle" class="sidebar-overlay"></label>

<div class="main-content">
<header class="topbar">
  <label for="menuToggle" class="menu-icon">&#9776;</label>
  <h1>Laporan Saya</h1>
</header>
<main class="content-area">

<?php if(isset($_GET['deleted'])): ?>
  <div class="alert-success">&#10003; Laporan berhasil dihapus.</div>
<?php elseif(isset($_GET['edited'])): ?>
  <div class="alert-success">&#10003; Laporan berhasil diperbarui.</div>
<?php elseif(isset($_GET['error'])): ?>
  <div class="alert-error">&#9888; Laporan tidak dapat dihapus. Hanya laporan berstatus <strong>Menunggu</strong> yang bisa dihapus.</div>
<?php elseif(isset($_GET['error_edit'])): ?>
  <div class="alert-error">&#9888; Laporan tidak dapat diedit. Hanya laporan berstatus <strong>Menunggu</strong> yang bisa diedit.</div>
<?php endif; ?>

<div class="heading">
  <div>
    <p class="eyebrow">Riwayat</p>
    <h1>Laporan Saya</h1>
    <p class="muted">Semua pengaduan yang kamu kirim.</p>
  </div>
  <a class="btn" href="buat_pengaduan.php">+ Buat Pengaduan</a>
</div>

<section class="panel"><div class="list">

<?php if(mysqli_num_rows($r) === 0): ?>
  <p class="muted">Belum ada pengaduan.</p>
<?php endif; ?>

<?php while($a = mysqli_fetch_assoc($r)): ?>
<div class="item-wrap">
  <a class="item has-action"
     href="detail_laporan.php?id=<?=(int)$a['id_aspirasi']?>">
    <div>
      <b>#<?=(int)$a['id_aspirasi']?> &middot; <?=e($a['nama_kategori'])?></b>
      <span><?=e($a['lokasi'])?></span>
      <small><?=e(date('d-m-Y H:i', strtotime($a['tanggal'])))?> &middot; <?=e($a['keterangan'])?></small>
    </div>
    <span class="badge <?=strtolower($a['status'])?>"><?=e($a['status'])?></span>
  </a>

  <div class="item-actions">
    <a class="btn-edit-user"
       href="edit_laporan.php?id=<?=(int)$a['id_aspirasi']?>">
      &#9998; Edit
    </a>
    <button class="btn-hapus-user"
      onclick="konfirmasiHapus(<?=(int)$a['id_aspirasi']?>,'<?=e(addslashes('#'.(int)$a['id_aspirasi'].' · '.$a['nama_kategori']))?>', '<?=e($a['status'])?>')">
      &#128465; Hapus
    </button>
  </div>
</div>
<?php endwhile; mysqli_stmt_close($st); ?>

</div></section>
</main></div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-overlay" id="modalHapus">
  <div class="modal-box">
    <h3>Hapus Laporan?</h3>
    <p id="modalDesc">Laporan ini akan dihapus permanen.</p>
    <small id="modalNote"></small>
    <div class="modal-actions">
      <button class="btn-batal" onclick="tutupModal()">Batal</button>
      <form method="POST" action="../../controller/c_hapus_laporan.php" style="display:inline">
        <input type="hidden" name="id_aspirasi" id="inputIdAspirasi" value="">
        <button type="submit" class="btn-konfirm">Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
function konfirmasiHapus(id, label, status) {
  document.getElementById('inputIdAspirasi').value = id;
  document.getElementById('modalDesc').textContent =
    'Laporan ' + label + ' akan dihapus permanen dan tidak dapat dikembalikan.';
  var note = document.getElementById('modalNote');
  if (status !== 'Menunggu') {
    note.textContent = '⚠ Laporan berstatus "' + status + '" tidak dapat dihapus.';
    note.style.display = 'block';
  } else {
    note.textContent = '';
    note.style.display = 'none';
  }
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
