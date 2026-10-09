<?php
require_once __DIR__ . '/../../config/database.php';
require_role('siswa');
$user = current_user();
$st = mysqli_prepare($conn, 'SELECT a.id_aspirasi,a.lokasi,a.keterangan,a.status,a.tanggal,k.nama_kategori FROM aspirasi a LEFT JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.nis=? ORDER BY a.tanggal DESC,a.id_aspirasi DESC');
mysqli_stmt_bind_param($st, 's', $user['nis']);
mysqli_stmt_execute($st);
$r = mysqli_stmt_get_result($st);
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Laporan Saya</title>
  <link rel="stylesheet" href="<?= e($base) ?>/assets/admin.css">
  <link rel="stylesheet" href="<?= e($base) ?>/assets/profil.css">
  <script src="<?= e($base) ?>/assets/ui.js"></script>
</head>

<body>

  <input type="checkbox" id="menuToggle" class="menu-toggle">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">Pengaduan Sarpras</div>
    <div class="sidebar-sub">Portal Siswa</div>
    <ul>
      <li><a href="<?= e(url('user/dashboard')) ?>">Beranda</a></li>
      <li><a href="<?= e(url('user/buat_pengaduan')) ?>">Buat Pengaduan</a></li>
      <li><a href="<?= e(url('user/laporan_saya')) ?>" class="active">Laporan Saya</a></li>
      <li><a href="<?= e(url('user/profil')) ?>">Profil Saya</a></li>
      <li><a href="<?= e(url('auth/logout')) ?>">Keluar</a></li>
    </ul>
    <div class="admin-user has-avatar">
        <a class="user-chip" href="<?= e(url('user/profil')) ?>" title="Lihat profil">
            <?= avatar($user['foto'] ?? null, $user['nama'] ?? 'Siswa', 'md') ?>
            <div class="who"><strong><?= e($user['nama'] ?? 'Siswa') ?></strong>Siswa · <?= e($user['kelas'] ?? '') ?></div>
        </a>
    </div>
  </aside>
  <label for="menuToggle" class="sidebar-overlay"></label>

  <div class="main-content">
    <header class="topbar">
      <label for="menuToggle" class="menu-icon">&#9776;</label>
      <h1>Laporan Saya</h1>
    </header>
    <main class="content-area">
      <div class="heading">
        <div>
          <p class="eyebrow">Riwayat</p>
          <h1>Laporan Saya</h1>
          <p class="muted">Semua pengaduan yang kamu kirim.</p>
        </div>
        <a class="btn" href="<?= e(url('user/buat_pengaduan')) ?>">+ Buat Pengaduan</a>
      </div>

      <section class="panel">
        <div class="list">

          <?php if (mysqli_num_rows($r) === 0): ?>
            <p class="muted">Belum ada pengaduan.</p>
          <?php endif; ?>

          <?php while ($a = mysqli_fetch_assoc($r)): ?>
            <div class="item-wrap">
              <a class="item has-action"
                href="<?= e(url('user/detail_laporan')) ?>?id=<?= (int)$a['id_aspirasi'] ?>">
                <div>
                  <b>#<?= (int)$a['id_aspirasi'] ?> &middot; <?= e($a['nama_kategori']) ?></b>
                  <span><?= e($a['lokasi']) ?></span>
                  <small><?= e(date('d-m-Y H:i', strtotime($a['tanggal']))) ?> &middot; <?= e($a['keterangan']) ?></small>
                </div>
                <span class="badge <?= strtolower($a['status']) ?>"><?= e($a['status']) ?></span>
              </a>
            </div>
          <?php endwhile;
          mysqli_stmt_close($st); ?>

        </div>
      </section>
    </main>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal-overlay" id="modalHapus">
    <div class="modal-box">
      <h3>Hapus Laporan?</h3>
      <p id="modalDesc">Laporan ini akan dihapus permanen.</p>
      <small id="modalNote"></small>
      <div class="modal-actions">
        <button class="btn-batal" onclick="tutupModal()">Batal</button>
        <form method="POST" action="<?= e($base) ?>/controller/c_hapus_laporan.php" style="display:inline">
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
</body>

</html>