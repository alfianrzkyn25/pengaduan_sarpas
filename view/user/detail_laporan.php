<?php
require_once __DIR__ . '/../../config/database.php';
require_role('siswa');
$user = current_user();
$id = (int)($_GET['id'] ?? 0);
$saved = isset($_GET['saved']);

$st = mysqli_prepare($conn, 'SELECT a.*,s.nama,s.kelas,k.nama_kategori FROM aspirasi a JOIN siswa s ON s.nis=a.nis LEFT JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.id_aspirasi=? AND a.nis=?');
mysqli_stmt_bind_param($st, 'is', $id, $user['nis']);
mysqli_stmt_execute($st);
$a = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
mysqli_stmt_close($st);

$hist = [];
if ($a) {
    $st = mysqli_prepare($conn, 'SELECT status_lama,status_baru,catatan,diubah_oleh,waktu_ubah FROM histori WHERE id_aspirasi=? ORDER BY waktu_ubah ASC,id_histori ASC');
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $rr = mysqli_stmt_get_result($st);
    while ($h = mysqli_fetch_assoc($rr)) $hist[] = $h;
    mysqli_stmt_close($st);
}

$fotoPengaduan = [];
$fotoFeedback = [];
if ($a) {
    $st = mysqli_prepare($conn, 'SELECT url_file,nama_file FROM lampiran WHERE id_aspirasi=? ORDER BY id_lampiran ASC');
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $lr = mysqli_stmt_get_result($st);
    while ($lf = mysqli_fetch_assoc($lr)) {
        if (strpos(basename($lf['url_file']), 'feedback-') === 0) {
            $fotoFeedback[] = $lf;
        } else {
            $fotoPengaduan[] = $lf;
        }
    }
    mysqli_stmt_close($st);
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detail Laporan</title>
    <link rel="stylesheet" href="<?= e($base) ?>/assets/admin.css">
</head>

<body>
    <input type="checkbox" id="menuToggle" class="menu-toggle">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Pengaduan Sarpras</div>
        <div class="sidebar-sub">Portal Siswa</div>
        <ul>
            <li><a href="<?= e(url('user/dashboard')) ?>" class="">Beranda</a></li>
            <li><a href="<?= e(url('user/buat_pengaduan')) ?>" class="">Buat Pengaduan</a></li>
            <li><a href="<?= e(url('user/laporan_saya')) ?>" class="active">Laporan Saya</a></li>
            <li><a href="<?= e(url('auth/logout')) ?>">Keluar</a></li>
        </ul>
        <div class="admin-user"><strong><?= e($user["nama"] ?? "Siswa") ?></strong>Siswa</div>
    </aside>
    <label for="menuToggle" class="sidebar-overlay"></label>
    <div class="main-content">
        <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
            <h1>Detail Laporan</h1>
        </header>
        <main class="content-area">
            <?php if ($saved): ?><div class="alert success">Pengaduan berhasil dikirim.</div><?php endif; ?>
            <?php if (!$a): ?><div class="alert error">Laporan tidak ditemukan.</div><a class="btn" href="<?= e(url('user/laporan_saya')) ?>">Kembali</a><?php else: ?>
                <div class="heading">
                    <div>
                        <p class="eyebrow">Laporan #<?= (int)$a['id_aspirasi'] ?></p>
                        <h1><?= e($a['nama_kategori']) ?></h1>
                        <p class="muted"><?= e($a['tanggal']) ?></p>
                    </div><span class="badge <?= strtolower($a['status']) ?>"><?= e($a['status']) ?></span>
                </div>
                <div class="detail">
                    <section class="panel">
                        <h2>Informasi Pengaduan</h2>
                        <p><b>Pelapor</b><?= e($a['nama']) ?> · <?= e($a['kelas']) ?></p>
                        <p><b>Lokasi</b><?= e($a['lokasi']) ?></p>
                        <p><b>Keterangan</b><?= e($a['keterangan']) ?></p>
                        <?php if ($fotoPengaduan): ?><div style="margin-top:10px"><b>Foto Pengaduan</b>
                                <div class="lampiran-grid"><?php foreach ($fotoPengaduan as $lf): ?><a href="<?= e($base) ?>/<?= e($lf['url_file']) ?>" target="_blank"><img src="<?= e($base) ?>/<?= e($lf['url_file']) ?>" alt="<?= e($lf['nama_file']) ?>"></a><?php endforeach; ?></div>
                            </div><?php endif; ?>
                    </section>
                    <section class="panel">
                        <h2>Perkembangan</h2>
                        <div class="timeline"><?php foreach ($hist as $h): ?><div class="timeline-item"><b><?= e($h['status_baru']) ?></b><small><?= e($h['waktu_ubah']) ?> · <?= e($h['diubah_oleh']) ?></small>
                                    <div><?= e($h['catatan'] ?: '-') ?></div>
                                </div><?php endforeach; ?></div>
                    </section>
                </div>
                <section class="panel">
                    <h2>Feedback dari Admin</h2>
                    <?php if ($a['feedback']): ?>
                        <div class="alert <?= strtolower($a['status']) === 'selesai' ? 'success' : 'info' ?>" style="margin-bottom:<?= $fotoFeedback ? '14px' : '0' ?>"><?= e($a['feedback']) ?></div>
                    <?php else: ?>
                        <p class="muted" style="margin:0 0 <?= $fotoFeedback ? '14px' : '0' ?>">Belum ada feedback dari admin.</p>
                    <?php endif; ?>
                    <?php if ($fotoFeedback): ?><div><b>Foto Bukti Perbaikan</b>
                            <div class="lampiran-grid"><?php foreach ($fotoFeedback as $lf): ?><a href="<?= e($base) ?>/<?= e($lf['url_file']) ?>" target="_blank"><img src="<?= e($base) ?>/<?= e($lf['url_file']) ?>" alt="<?= e($lf['nama_file']) ?>"></a><?php endforeach; ?></div>
                        </div><?php endif; ?>
                </section>
                <a class="btn secondary" href="<?= e(url('user/laporan_saya')) ?>">← Kembali</a>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>