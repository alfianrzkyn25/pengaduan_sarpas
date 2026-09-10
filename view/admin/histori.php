<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Histori Laporan</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>
    <input type="checkbox" id="menuToggle" class="menu-toggle">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Pengaduan Sarpras</div>
        <ul>
            <li><a href="../controller/c_dashboard.php" class="">Dashboard</a></li>
            <li><a href="../controller/c_aspirasi.php" class="">Data Aspirasi</a></li>
            <li><a href="../controller/c_tambah_aspirasi.php" class="">form aspirasi</a></li>
            <li><a href="../controller/c_histori.php" class="active">Histori Laporan</a></li>
            <li><a href="../controller/c_kategori.php" class="">Kategori Sarpras</a></li>
            <li><a href="../logout.php">Keluar</a></li>
        </ul>
        <div class="admin-user"><strong><?= e($user["nama"] ?? "Admin") ?></strong>Administrator</div>
    </aside>
    <label for="menuToggle" class="sidebar-overlay"></label>
    <div class="main-content">
        <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
            <h1>Histori Laporan</h1>
        </header>
        <main class="content-area">
            <div class="grid-3">
                <div class="card stat"><span>Menunggu</span><strong><?= $stats['menunggu'] ?></strong></div>
                <div class="card stat"><span>Proses</span><strong><?= $stats['proses'] ?></strong></div>
                <div class="card stat"><span>Selesai</span><strong><?= $stats['selesai'] ?></strong></div>
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
                        </tr>
                        <?php while ($h = mysqli_fetch_assoc($r)): ?>
                            <tr>
                                <td><?= $h['id_histori'] ?></td>
                                <td><a class="btn secondary" style="padding:4px 8px" href="../controller/c_detail_aspirasi.php?id=<?= $h['id_aspirasi'] ?>">#<?= $h['id_aspirasi'] ?></a></td>
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
</body>

</html>