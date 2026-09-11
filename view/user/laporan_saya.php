<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Laporan Saya</title>
<link rel="stylesheet" href="../assets/admin.css">
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
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  text-decoration: none;
  white-space: nowrap;
  transition: background .15s;
}
.btn-edit-user:hover { background: #dbeafe; }

.btn-hapus-user {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
  border-radius: 6px;
  cursor: pointer;
  white-space: nowrap;
  transition: background .15s;
}
.btn-hapus-user:hover { background: #fca5a5; }

.alert-success {
  background: #d1fae5; border: 1px solid #6ee7b7;
  color: #065f46; padding: 10px 16px;
  border-radius: 8px; margin-bottom: 16px; font-size: 14px;
}
.alert-error {
  background: #fee2e2; border: 1px solid #fca5a5;
  color: #b91c1c; padding: 10px 16px;
  border-radius: 8px; margin-bottom: 16px; font-size: 14px;
}

.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,.45); z-index: 999;
  align-items: center; justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-box {
  background: #fff; border-radius: 12px;
  padding: 28px 32px; max-width: 380px; width: 90%;
  box-shadow: 0 8px 32px rgba(0,0,0,.18); text-align: center;
}
.modal-box h3 { margin: 0 0 8px; font-size: 18px; color: #111; }
.modal-box p  { margin: 0 0 6px; font-size: 14px; color: #555; }
.modal-box small { display: block; font-size: 12px; color: #ef4444; margin-bottom: 18px; }
.modal-actions { display: flex; gap: 10px; justify-content: center; }
.modal-actions .btn-batal {
  padding: 8px 20px; border: 1px solid #d1d5db;
  border-radius: 8px; background: #fff; cursor: pointer; font-size: 14px;
}
.modal-actions .btn-konfirm {
  padding: 8px 20px; border: none; border-radius: 8px;
  background: #dc2626; color: #fff; cursor: pointer;
  font-size: 14px; font-weight: 600;
}
</style>
</head><body>

<input type="checkbox" id="menuToggle" class="menu-toggle">
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">Pengaduan Sarpras</div>
  <div class="sidebar-sub">Portal Siswa</div>
  <ul>
    <li><a href="../controller/c_user_dashboard.php">Beranda</a></li>
    <li><a href="../controller/c_user_tambah.php">Buat Pengaduan</a></li>
    <li><a href="../controller/c_user_laporan.php" class="active">Laporan Saya</a></li>
    <li><a href="../logout.php">Keluar</a></li>
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
  <a class="btn" href="../controller/c_user_tambah.php">+ Buat Pengaduan</a>
</div>

<section class="panel"><div class="list">

<?php if(mysqli_num_rows($r) === 0): ?>
  <p class="muted">Belum ada pengaduan.</p>
<?php endif; ?>

<?php while($a = mysqli_fetch_assoc($r)): ?>
<div class="item-wrap">
  <a class="item has-action"
     href="../controller/c_user_detail.php?id=<?=(int)$a['id_aspirasi']?>">
    <div>
      <b>#<?=(int)$a['id_aspirasi']?> &middot; <?=e($a['nama_kategori'])?></b>
      <span><?=e($a['lokasi'])?></span>
      <small><?=e(date('d-m-Y H:i', strtotime($a['tanggal'])))?> &middot; <?=e($a['keterangan'])?></small>
    </div>
    <span class="badge <?=strtolower($a['status'])?>"><?=e($a['status'])?></span>
  </a>

  <div class="item-actions">
    <a class="btn-edit-user"
       href="../controller/c_user_edit.php?id=<?=(int)$a['id_aspirasi']?>">
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
      <form method="POST" action="../controller/c_hapus_laporan.php" style="display:inline">
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
