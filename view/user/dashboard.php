<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>
    <input type="checkbox" id="menuToggle" class="menu-toggle">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Pengaduan Sarpras</div>
        <div class="sidebar-sub">Portal Siswa</div>
        <ul>
            <li><a href="../controller/c_user_dashboard.php" class="active">Beranda</a></li>
            <li><a href="../controller/c_user_tambah.php" class="">Buat Pengaduan</a></li>
            <li><a href="../controller/c_user_laporan.php" class="">Laporan Saya</a></li>
            <li><a href="../logout.php">Keluar</a></li>
        </ul>
        <div class="admin-user"><strong><?= e($user["nama"] ?? "Siswa") ?></strong>Siswa</div>
    </aside>
    <label for="menuToggle" class="sidebar-overlay"></label>
    <div class="main-content">
        <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
            <h1>Dashboard Siswa</h1>
        </header>
        <main class="content-area">
            <div class="heading">
                <div>
                    <p class="eyebrow">Portal Siswa</p>
                    <h1>Halo, <?= e($user['nama']) ?> 👋</h1>
                    <p class="muted">Kelola pengaduan sarana dan lihat perkembangan laporanmu.</p>
                </div><a class="btn" href="../controller/c_user_tambah.php">+ Buat Pengaduan</a>
            </div>
            <div class="grid">
                <div class="card stat"><span>Total Laporan</span><strong><?= $stats['total'] ?></strong></div>
                <div class="card stat"><span>Menunggu</span><strong><?= $stats['menunggu'] ?></strong></div>
                <div class="card stat"><span>Diproses</span><strong><?= $stats['proses'] ?></strong></div>
                <div class="card stat"><span>Selesai</span><strong><?= $stats['selesai'] ?></strong></div>
            </div>
            <section class="panel">
                <div class="panel-head">
                    <h2>Laporan Terbaru</h2><a href="../controller/c_user_laporan.php">Lihat semua</a>
                </div>
                <div class="list"><?php while ($a = mysqli_fetch_assoc($r)): ?><a class="item" href="../controller/c_user_detail.php?id=<?= (int)$a['id_aspirasi'] ?>">
                            <div><b>#<?= (int)$a['id_aspirasi'] ?> · <?= e($a['nama_kategori']) ?></b><span><?= e($a['lokasi']) ?></span><small><?= e($a['keterangan']) ?></small></div><span class="badge <?= strtolower($a['status']) ?>"><?= e($a['status']) ?></span>
                        </a><?php endwhile;
                                    mysqli_stmt_close($st); ?></div>
            </section>
        </main>
    </div>
</body>

</html>