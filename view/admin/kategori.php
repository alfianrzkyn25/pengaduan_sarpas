<?php
require_once __DIR__ . '/../../config/database.php';
require_role('admin');
$user = current_user();
$r = mysqli_query($conn, "SELECT k.id_kategori,k.nama_kategori,k.ket_kategori,COUNT(a.id_aspirasi) jumlah FROM kategori k LEFT JOIN aspirasi a ON a.id_kategori=k.id_kategori GROUP BY k.id_kategori,k.nama_kategori,k.ket_kategori ORDER BY k.nama_kategori ASC");
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kategori Sarpras</title>
    <link rel="stylesheet" href="../../assets/admin.css">
</head>

<body>
    <input type="checkbox" id="menuToggle" class="menu-toggle">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Pengaduan Sarpras</div>
        <ul>
            <li><a href="dashboard.php" class="">Dashboard</a></li>
            <li><a href="aspirasi.php" class="">Data Aspirasi</a></li>
            <li><a href="form_aspirasi.php" class="">form aspirasi</a></li>
            <li><a href="histori.php" class="">Histori Laporan</a></li>
            <li><a href="kategori.php" class="active">Kategori Sarpras</a></li>
            <li><a href="../../controller/c_siswa.php">Kelola Siswa</a></li>
            <li><a href="../../logout.php">Keluar</a></li>
        </ul>
        <div class="admin-user"><strong><?= e($user["nama"] ?? "Admin") ?></strong>Administrator</div>
    </aside>
    <label for="menuToggle" class="sidebar-overlay"></label>
    <div class="main-content">
        <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
            <h1>Kategori Sarpras</h1>
        </header>
        <main class="content-area">
            <section class="panel">
                <h2 style="margin-top:0">Daftar Kategori & Jumlah Aspirasi</h2>
                <div style="overflow:auto">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>Kategori</th>
                            <th>Jumlah Aspirasi</th>
                            <th>Aksi</th>
                        </tr>
                        <?php while ($k = mysqli_fetch_assoc($r)): ?>
                            <tr>
                                <td><?= $k['id_kategori'] ?></td>
                                <td><?= e($k['nama_kategori']) ?></td>
                                <td><?= $k['jumlah'] ?></td>
                                <td><a class="btn" href="aspirasi.php?q=<?= urlencode($k['nama_kategori']) ?>">Lihat Aspirasi</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>

</html>