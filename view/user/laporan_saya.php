<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Laporan Saya</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>
    <input type="checkbox" id="menuToggle" class="menu-toggle">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Pengaduan Sarpras</div>
        <div class="sidebar-sub">Portal Siswa</div>
        <ul>
            <li><a href="../controller/c_user_dashboard.php" class="">Beranda</a></li>
            <li><a href="../controller/c_user_tambah.php" class="">Buat Pengaduan</a></li>
            <li><a href="../controller/c_user_laporan.php" class="active">Laporan Saya</a></li>
            <li><a href="../logout.php">Keluar</a></li>
        </ul>
        <div class="admin-user"><strong><?= e($user["nama"] ?? "Siswa") ?></strong>Siswa</div>
    </aside>
    <label for="menuToggle" class="sidebar-overlay"></label>
    <div class="main-content">
        <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
            <h1>Laporan Saya</h1>
        </header>
        <main class="content-area">
            <div class="heading">
                <div>
                    <p class="eyebrow">Riwayat</p>
                    <h1>Laporan Saya</h1>
                    <p class="muted">Semua pengaduan yang kamu kirim.</p>
                </div><a class="btn" href="../controller/c_user_tambah.php">+ Buat Pengaduan</a>
            </div>
            <section class="panel">
                <div class="list">
                    <?php if (mysqli_num_rows($r) === 0): ?><p class="muted">Belum ada pengaduan.</p><?php endif; ?>
                    <?php while ($a = mysqli_fetch_assoc($r)): ?><a class="item" href="../controller/c_user_detail.php?id=<?= (int)$a['id_aspirasi'] ?>">
                            <div><b>#<?= (int)$a['id_aspirasi'] ?> · <?= e($a['nama_kategori']) ?></b><span><?= e($a['lokasi']) ?></span><small><?= e(date('d-m-Y H:i', strtotime($a['tanggal']))) ?> · <?= e($a['keterangan']) ?></small></div><span class="badge <?= strtolower($a['status']) ?>"><?= e($a['status']) ?></span>
                        </a><?php endwhile;
                        mysqli_stmt_close($st); ?>
                </div>
            </section>
        </main>
    </div>
</body>

</html>