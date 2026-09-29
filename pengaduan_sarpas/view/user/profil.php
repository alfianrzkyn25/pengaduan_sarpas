<?php
require_once __DIR__ . '/../../config/database.php';
require_role('siswa');
$user = current_user();
if (!$user) redirect(url('auth/logout')); // akun siswa sudah tidak ada di database

// ---------------------------------------------------------------------------
// Helper foto profil
// ---------------------------------------------------------------------------
const FOTO_PROFIL_DIR_REL = 'assets/uploads/profil';
const FOTO_PROFIL_MAX = 5 * 1024 * 1024; // 5MB

// Hapus file foto profil lama (hanya yang berada di folder profil).
function hapus_file_foto_profil($foto)
{
    $foto = (string)$foto;
    if (strpos($foto, FOTO_PROFIL_DIR_REL . '/') !== 0) return;
    $path = dirname(__DIR__, 2) . '/' . FOTO_PROFIL_DIR_REL . '/' . basename($foto);
    if (is_file($path)) @unlink($path);
}

// Validasi + simpan foto profil. Return path relatif (mis. assets/uploads/profil/xxx.jpg)
// atau null kalau gagal (pesan ada di $error).
// Kalau ekstensi GD tersedia, foto di-crop kotak (tengah) & dikecilkan maks 480px
// supaya ringan dimuat di setiap halaman. Kalau tidak, file disimpan apa adanya.
function simpan_foto_profil(array $file, $nis, &$error)
{
    $jenis = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        $error = 'Ukuran foto terlalu besar. Maksimal 5MB.';
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        $error = 'Gagal mengunggah foto. Silakan coba lagi.';
        return null;
    }
    if ($file['size'] > FOTO_PROFIL_MAX) {
        $error = 'Ukuran foto maksimal 5MB.';
        return null;
    }
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !isset($jenis[$info[2]])) {
        $error = 'Format foto tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.';
        return null;
    }

    $dir = dirname(__DIR__, 2) . '/' . FOTO_PROFIL_DIR_REL;
    if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
        $error = 'Folder penyimpanan foto tidak bisa dibuat.';
        return null;
    }

    $nisAman = preg_replace('/[^A-Za-z0-9]/', '', (string)$nis);
    $nama = 'profil-' . $nisAman . '-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);

    // Coba kecilkan & crop kotak dengan GD (dilewati untuk gambar sangat besar agar tidak kehabisan memori).
    $piksel = (int)$info[0] * (int)$info[1];
    if ($piksel > 0 && $piksel <= 16000000 && function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
        $src = @imagecreatefromstring((string)file_get_contents($file['tmp_name']));
        if ($src) {
            // Perbaiki orientasi foto dari HP (EXIF).
            if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $exif = @exif_read_data($file['tmp_name']);
                $o = is_array($exif) ? (int)($exif['Orientation'] ?? 1) : 1;
                $sudut = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
                if ($sudut) {
                    $rot = imagerotate($src, $sudut, 0);
                    if ($rot) {
                        imagedestroy($src);
                        $src = $rot;
                    }
                }
            }
            $w = imagesx($src);
            $h = imagesy($src);
            $sisi = min($w, $h);
            $sx = intdiv($w - $sisi, 2);
            $sy = intdiv($h - $sisi, 2);
            $out = min(480, $sisi);
            $dst = imagecreatetruecolor($out, $out);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // latar putih utk PNG/WEBP transparan
            imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $out, $out, $sisi, $sisi);
            $ok = imagejpeg($dst, $dir . '/' . $nama . '.jpg', 88);
            imagedestroy($src);
            imagedestroy($dst);
            if ($ok) return FOTO_PROFIL_DIR_REL . '/' . $nama . '.jpg';
        }
    }

    // Cadangan: simpan file asli (ekstensi ditentukan dari isi file, bukan nama file).
    $namaFile = $nama . '.' . $jenis[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $namaFile)) {
        $error = 'Foto gagal disimpan di server.';
        return null;
    }
    return FOTO_PROFIL_DIR_REL . '/' . $namaFile;
}

// ---------------------------------------------------------------------------
// Proses form
// ---------------------------------------------------------------------------
$error = '';
$form = ['nama' => $user['nama'], 'kelas' => $user['kelas']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'simpan';

    if ($aksi === 'hapus_foto') {
        if (!empty($user['foto'])) {
            $st = mysqli_prepare($conn, 'UPDATE siswa SET foto=NULL WHERE nis=?');
            mysqli_stmt_bind_param($st, 's', $user['nis']);
            mysqli_stmt_execute($st);
            mysqli_stmt_close($st);
            hapus_file_foto_profil($user['foto']);
        }
        redirect(url('user/profil') . '?fotodihapus=1');
    }

    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $form = ['nama' => $nama, 'kelas' => $kelas];
    $hasFile = !empty($_FILES['foto']['name']);

    if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
        $error = 'Ukuran foto terlalu besar. Maksimal 5MB.'; // melebihi post_max_size PHP
    } elseif ($nama === '' || $kelas === '') {
        $error = 'Nama dan kelas wajib diisi.';
    } elseif (mb_strlen($nama) < 3) {
        $error = 'Nama lengkap minimal 3 karakter.';
    } elseif (mb_strlen($nama) > 50) {
        $error = 'Nama lengkap maksimal 50 karakter.';
    } elseif (mb_strlen($kelas) > 10) {
        $error = 'Kelas maksimal 10 karakter.';
    } else {
        $fotoBaru = $hasFile ? simpan_foto_profil($_FILES['foto'], $user['nis'], $error) : null;

        if ($error === '') {
            try {
                if ($fotoBaru !== null) {
                    $st = mysqli_prepare($conn, 'UPDATE siswa SET nama=?,kelas=?,foto=? WHERE nis=?');
                    mysqli_stmt_bind_param($st, 'ssss', $nama, $kelas, $fotoBaru, $user['nis']);
                } else {
                    $st = mysqli_prepare($conn, 'UPDATE siswa SET nama=?,kelas=? WHERE nis=?');
                    mysqli_stmt_bind_param($st, 'sss', $nama, $kelas, $user['nis']);
                }
                $ok = mysqli_stmt_execute($st);
                mysqli_stmt_close($st);
            } catch (Throwable $ex) {
                $ok = false;
            }

            if ($ok) {
                if ($fotoBaru !== null) hapus_file_foto_profil($user['foto']); // buang foto lama
                $_SESSION['nama'] = $nama;
                redirect(url('user/profil') . '?saved=1');
            }
            if ($fotoBaru !== null) hapus_file_foto_profil($fotoBaru);
            $error = 'Profil gagal disimpan. Silakan coba lagi.';
        }
    }
}

$pesan = '';
if (isset($_GET['saved'])) $pesan = 'Profil berhasil diperbarui.';
if (isset($_GET['fotodihapus'])) $pesan = 'Foto profil berhasil dihapus.';
$punyaFoto = foto_profil_url($user['foto'] ?? '') !== '';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="<?= e($base) ?>/assets/admin.css">
    <link rel="stylesheet" href="<?= e($base) ?>/assets/profil.css">
</head>

<body>
    <input type="checkbox" id="menuToggle" class="menu-toggle">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Pengaduan Sarpras</div>
        <div class="sidebar-sub">Portal Siswa</div>
        <ul>
            <li><a href="<?= e(url('user/dashboard')) ?>" class="">Beranda</a></li>
            <li><a href="<?= e(url('user/buat_pengaduan')) ?>" class="">Buat Pengaduan</a></li>
            <li><a href="<?= e(url('user/laporan_saya')) ?>" class="">Laporan Saya</a></li>
            <li><a href="<?= e(url('user/profil')) ?>" class="active">Profil Saya</a></li>
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
        <header class="topbar"><label for="menuToggle" class="menu-icon">&#9776;</label>
            <h1>Profil Saya</h1>
        </header>
        <main class="content-area">
            <div class="heading">
                <div>
                    <p class="eyebrow">Portal Siswa</p>
                    <h1>Profil Saya</h1>
                    <p class="muted">Kelola foto dan data dirimu. Foto dan nama ini tampil di setiap pengaduan yang kamu buat.</p>
                </div>
            </div>

            <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
            <?php if ($pesan): ?><div class="alert success"><?= e($pesan) ?></div><?php endif; ?>

            <section class="panel profile-hero">
                <?= avatar($user['foto'] ?? null, $user['nama'], 'xl') ?>
                <div>
                    <h2><?= e($user['nama']) ?></h2>
                    <div class="profile-meta">
                        <span class="profile-chip">NIS · <?= e($user['nis']) ?></span>
                        <span class="profile-chip soft">Kelas · <?= e($user['kelas']) ?></span>
                    </div>
                    <?php if ($punyaFoto): ?>
                        <form method="post" onsubmit="return confirm('Hapus foto profil?');">
                            <input type="hidden" name="aksi" value="hapus_foto">
                            <button type="submit" class="btn secondary small">Hapus Foto</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>

            <section class="panel">
                <h2 style="margin-top:0;margin-bottom:16px">Ubah Profil</h2>
                <form method="post" class="admin-form profile-form" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="simpan">

                    <div>
                        <label for="foto">Foto Profil</label>
                        <div class="photo-field" style="margin-top:7px">
                            <span id="fotoPreview"><?= avatar($user['foto'] ?? null, $form['nama'] ?: $user['nama'], 'lg') ?></span>
                            <div class="photo-input">
                                <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp">
                                <span class="form-help">Format JPG/PNG/GIF/WEBP, maksimal 5MB. Foto otomatis dipotong kotak agar pas jadi foto profil.</span>
                                <span class="photo-error" id="fotoError"></span>
                            </div>
                        </div>
                    </div>

                    <label>Nama Lengkap
                        <input name="nama" maxlength="50" required value="<?= e($form['nama']) ?>" placeholder="Nama lengkap">
                    </label>

                    <div class="form-grid-2">
                        <label>NIS
                            <input value="<?= e($user['nis']) ?>" readonly tabindex="-1">
                            <span class="form-help">NIS tidak dapat diubah.</span>
                        </label>
                        <label>Kelas
                            <input name="kelas" maxlength="10" required value="<?= e($form['kelas']) ?>" placeholder="Contoh: XII RPL 2">
                        </label>
                    </div>

                    <div class="form-actions">
                        <a class="btn secondary" href="<?= e(url('user/dashboard')) ?>">Batal</a>
                        <button class="btn" type="submit">Simpan Perubahan</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
        // Pratinjau foto sebelum disimpan.
        (function() {
            var input = document.getElementById('foto');
            var preview = document.getElementById('fotoPreview');
            var err = document.getElementById('fotoError');
            var maxBytes = 5 * 1024 * 1024;
            var semula = preview.innerHTML;
            input.addEventListener('change', function() {
                err.style.display = 'none';
                var f = input.files && input.files[0];
                if (!f) {
                    preview.innerHTML = semula;
                    return;
                }
                if (!/^image\/(jpeg|png|gif|webp)$/.test(f.type)) {
                    err.textContent = 'Format foto tidak didukung.';
                    err.style.display = 'block';
                    input.value = '';
                    preview.innerHTML = semula;
                    return;
                }
                if (f.size > maxBytes) {
                    err.textContent = 'Ukuran foto maksimal 5MB.';
                    err.style.display = 'block';
                    input.value = '';
                    preview.innerHTML = semula;
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(ev) {
                    var span = document.createElement('span');
                    span.className = 'avatar avatar-lg';
                    var img = document.createElement('img');
                    img.src = ev.target.result;
                    img.alt = 'Pratinjau foto profil';
                    span.appendChild(img);
                    preview.innerHTML = '';
                    preview.appendChild(span);
                };
                reader.readAsDataURL(f);
            });
        })();
    </script>
</body>

</html>
