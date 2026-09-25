<?php
require_once __DIR__ . '/../../config/database.php';
require_role('admin');
$user = current_user();
$stats = ['menunggu' => 0, 'proses' => 0, 'selesai' => 0, 'ditolak' => 0];
$rs = mysqli_query($conn, "SELECT status_baru,COUNT(*) jumlah FROM histori GROUP BY status_baru");
while ($x = mysqli_fetch_assoc($rs)) {
  $key = strtolower($x['status_baru']);
  if (isset($stats[$key])) $stats[$key] = (int)$x['jumlah'];
}
$r = mysqli_query($conn, "SELECT h.*,s.nama,s.nis,COALESCE(ad.nama,h.diubah_oleh) diubah_oleh FROM histori h LEFT JOIN aspirasi a ON a.id_aspirasi=h.id_aspirasi LEFT JOIN siswa s ON s.nis=a.nis LEFT JOIN admin ad ON ad.id_admin=h.id_admin ORDER BY h.waktu_ubah DESC,h.id_histori DESC");
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Histori Laporan</title>
  <link rel="stylesheet" href="<?= e($base) ?>/assets/admin.css">
  <style>
    .btn-hapus {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 5px 12px;
      font-size: 12px;
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fecaca;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: background .15s ease, transform .15s ease;
    }

    .btn-hapus:hover {
      background: #fecaca;
      transform: translateY(-1px);
    }

    .alert-success {
      background: #f0fdf4;
      border-left: 4px solid #16a34a;
      color: #166534;
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 16px;
      font-size: 14px;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, .5);
      backdrop-filter: blur(2px);
      z-index: 999;
      align-items: center;
      justify-content: center;
    }

    .modal-overlay.active {
      display: flex;
    }

    .modal-box {
      background: #fff;
      border-radius: 18px;
      padding: 30px 32px;
      max-width: 380px;
      width: 90%;
      box-shadow: 0 24px 48px -12px rgba(15, 23, 42, .35);
      text-align: center;
      animation: modalIn .22s cubic-bezier(.4, 0, .2, 1);
    }

    @keyframes modalIn {
      from {
        opacity: 0;
        transform: translateY(10px) scale(.97)
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1)
      }
    }

    .modal-box h3 {
      margin: 0 0 8px;
      font-size: 18px;
      color: #111827;
      font-weight: 800;
    }

    .modal-box p {
      margin: 0 0 20px;
      font-size: 14px;
      color: #667085;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .modal-actions .btn-batal {
      padding: 9px 20px;
      border: 1.5px solid #e2e6ee;
      border-radius: 10px;
      background: #fff;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      color: #374151;
      transition: background .15s ease;
    }

    .modal-actions .btn-batal:hover {
      background: #f8fafc;
    }

    .modal-actions .btn-konfirm {
      padding: 9px 20px;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, #ef4444, #b91c1c);
      color: #fff;
      cursor: pointer;
      font-size: 14px;
      font-weight: 700;
      box-shadow: 0 8px 18px -8px rgba(220, 38, 38, .5);
      transition: transform .15s ease, filter .15s ease;
    }

    .modal-actions .btn-konfirm:hover {
      transform: translateY(-1px);
      filter: brightness(1.05);
    }
  </style>
</head>

<body>
  <input type="checkbox" id="menuToggle" class="menu-toggle">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">Pengaduan Sarpras</div>
    <ul>
      <li><a href="<?= e(url('admin/dashboard')) ?>" class="">Dashboard</a></li>
      <li><a href="<?= e(url('admin/aspirasi')) ?>" class="">Data Aspirasi</a></li>
      <li><a href="<?= e(url('admin/form_aspirasi')) ?>" class="">form aspirasi</a></li>
      <li><a href="<?= e(url('admin/histori')) ?>" class="active">Histori Laporan</a></li>
      <li><a href="<?= e(url('admin/kategori')) ?>" class="">Kategori Sarpras</a></li>
      <li><a href="<?= e(url('admin/siswa')) ?>">Kelola Siswa</a></li>
      <li><a href="<?= e(url('auth/logout')) ?>">Keluar</a></li>
    </ul>
    <div class="admin-user"><strong><?= e($user["nama"] ?? "Admin") ?></strong>Administrator</div>
  </aside>
  <label for="menuToggle" class="sidebar-overlay"></label>
  <div class="main-content">
    <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
      <h1>Histori Laporan</h1>
    </header>
    <main class="content-area">

      <?php if (isset($_GET['deleted'])): ?>
        <div class="alert-success">&#10003; Histori berhasil dihapus.</div>
      <?php endif; ?>

      <div class="grid">
        <div class="card stat"><span>Menunggu</span><strong><?= $stats['menunggu'] ?></strong></div>
        <div class="card stat"><span>Proses</span><strong><?= $stats['proses'] ?></strong></div>
        <div class="card stat"><span>Selesai</span><strong><?= $stats['selesai'] ?></strong></div>
        <div class="card stat"><span>Ditolak</span><strong><?= $stats['ditolak'] ?></strong></div>
      </div>
      <section class="panel">
        <h2 style="margin-top:0">Riwayat Perubahan Status</h2>
        <div style="overflow:auto">
          <table class="table">
            <tr>
              <th>ID</th>
              <th>Aspirasi</th>
              <th>Status Lama</th>
              <th>Status Baru</th>
              <th>Catatan</th>
              <th>Oleh</th>
              <th>Waktu</th>
              <th>Aksi</th>
            </tr>
            <?php while ($h = mysqli_fetch_assoc($r)): ?>
              <tr>
                <td><?= $h['id_histori'] ?></td>
                <td><a class="btn secondary" style="padding:4px 8px" href="<?= e(url('admin/detail_aspirasi')) ?>?id=<?= $h['id_aspirasi'] ?>">#<?= $h['id_aspirasi'] ?></a></td>
                <td><?= e($h['status_lama'] ?: '-') ?></td>
                <td><span class="badge <?= strtolower($h['status_baru']) ?>"><?= e($h['status_baru']) ?></span></td>
                <td><?= e($h['catatan'] ?: '-') ?></td>
                <td><?= e($h['diubah_oleh']) ?></td>
                <td><?= e(date('d-m-Y H:i', strtotime($h['waktu_ubah']))) ?></td>
              </tr>
            <?php endwhile; ?>
          </table>
        </div>
      </section>
    </main>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal-overlay" id="modalHapus">
    <div class="modal-box">
      <h3>Hapus Histori?</h3>
      <p>Entri histori ini akan dihapus permanen dan tidak dapat dikembalikan.</p>
      <div class="modal-actions">
        <button class="btn-batal" onclick="tutupModal()">Batal</button>
        <form method="POST" action="<?= e($base) ?>/controller/c_hapus_histori.php" style="display:inline">
          <input type="hidden" name="id_histori" id="inputIdHistori" value="">
          <input type="hidden" name="back" value="../view/admin/histori.php">
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
</body>

</html>