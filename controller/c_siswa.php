<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$user = current_user();
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');

if (empty($_SESSION['siswa_csrf'])) {
    $_SESSION['siswa_csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['siswa_csrf'];

$error = '';
$success = '';
$editing = null;
$old = ['nis' => '', 'nama' => '', 'kelas' => ''];

function siswa_count_laporan(mysqli $conn, string $nis): int
{
    $st = mysqli_prepare($conn, 'SELECT COUNT(*) FROM aspirasi WHERE nis=?');
    if (!$st) return 0;
    mysqli_stmt_bind_param($st, 's', $nis);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $count);
    mysqli_stmt_fetch($st);
    mysqli_stmt_close($st);
    return (int)$count;
}

function admin_username_exists(mysqli $conn, string $nis): bool
{
    $st = mysqli_prepare($conn, 'SELECT id_admin FROM admin WHERE username=? LIMIT 1');
    if (!$st) return false;
    mysqli_stmt_bind_param($st, 's', $nis);
    mysqli_stmt_execute($st);
    mysqli_stmt_store_result($st);
    $exists = mysqli_stmt_num_rows($st) > 0;
    mysqli_stmt_close($st);
    return $exists;
}

function siswa_exists(mysqli $conn, string $nis): bool
{
    $st = mysqli_prepare($conn, 'SELECT nis FROM siswa WHERE nis=? LIMIT 1');
    if (!$st) return false;
    mysqli_stmt_bind_param($st, 's', $nis);
    mysqli_stmt_execute($st);
    mysqli_stmt_store_result($st);
    $exists = mysqli_stmt_num_rows($st) > 0;
    mysqli_stmt_close($st);
    return $exists;
}

function ambil_siswa(mysqli $conn, string $nis): ?array
{
    $st = mysqli_prepare($conn, 'SELECT nis,nama,kelas FROM siswa WHERE nis=? LIMIT 1');
    if (!$st) return null;
    mysqli_stmt_bind_param($st, 's', $nis);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $rowNis, $nama, $kelas);
    $found = mysqli_stmt_fetch($st);
    mysqli_stmt_close($st);

    if (!$found) return null;
    return ['nis' => $rowNis, 'nama' => $nama, 'kelas' => $kelas, 'old_nis' => $rowNis];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } elseif ($action === 'tambah') {
        $nis = trim($_POST['nis'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $password = $_POST['password'] ?? '';
        $old = compact('nis', 'nama', 'kelas');

        if ($nis === '' || $nama === '' || $kelas === '' || $password === '') {
            $error = 'Semua data wajib diisi untuk menambah siswa.';
        } elseif (!preg_match('/^[0-9]{4,10}$/', $nis)) {
            $error = 'NIS harus berupa angka 4-10 digit.';
        } elseif (mb_strlen($nama) < 3) {
            $error = 'Nama siswa minimal 3 karakter.';
        } elseif (mb_strlen($kelas) > 10) {
            $error = 'Kelas maksimal 10 karakter.';
        } elseif (strlen($password) < 4) {
            $error = 'Password minimal 4 karakter.';
        } elseif (siswa_exists($conn, $nis)) {
            $error = 'NIS tersebut sudah terdaftar.';
        } elseif (admin_username_exists($conn, $nis)) {
            $error = 'NIS tersebut tidak dapat digunakan karena sama dengan username admin.';
        } else {
            $st = mysqli_prepare($conn, 'INSERT INTO siswa (nis,nama,kelas,password) VALUES (?,?,?,?)');
            if (!$st) {
                $error = 'Gagal menyiapkan proses tambah siswa: ' . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($st, 'ssss', $nis, $nama, $kelas, $password);
                $ok = mysqli_stmt_execute($st);
                $dbError = mysqli_stmt_error($st);
                mysqli_stmt_close($st);

                if ($ok) {
                    redirect('c_siswa.php?msg=added');
                }
                $error = 'Gagal menambah siswa: ' . $dbError;
            }
        }
    } elseif ($action === 'edit') {
        // NIS dijadikan identitas tetap. Yang diubah hanya nama, kelas, dan password.
        $oldNis = trim($_POST['old_nis'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $password = $_POST['password'] ?? '';

        $editing = ['nis' => $oldNis, 'old_nis' => $oldNis, 'nama' => $nama, 'kelas' => $kelas];

        if ($oldNis === '' || $nama === '' || $kelas === '') {
            $error = 'Nama dan kelas wajib diisi.';
        } elseif (!preg_match('/^[0-9]{4,10}$/', $oldNis) || !siswa_exists($conn, $oldNis)) {
            $error = 'Data siswa yang diedit tidak ditemukan.';
        } elseif (mb_strlen($nama) < 3) {
            $error = 'Nama siswa minimal 3 karakter.';
        } elseif (mb_strlen($kelas) > 10) {
            $error = 'Kelas maksimal 10 karakter.';
        } elseif ($password !== '' && strlen($password) < 4) {
            $error = 'Password baru minimal 4 karakter.';
        } else {
            if ($password !== '') {
                $st = mysqli_prepare($conn, 'UPDATE siswa SET nama=?,kelas=?,password=? WHERE nis=? LIMIT 1');
                if ($st) {
                    mysqli_stmt_bind_param($st, 'ssss', $nama, $kelas, $password, $oldNis);
                }
            } else {
                $st = mysqli_prepare($conn, 'UPDATE siswa SET nama=?,kelas=? WHERE nis=? LIMIT 1');
                if ($st) {
                    mysqli_stmt_bind_param($st, 'sss', $nama, $kelas, $oldNis);
                }
            }

            if (!$st) {
                $error = 'Gagal menyiapkan proses edit siswa: ' . mysqli_error($conn);
            } else {
                $ok = mysqli_stmt_execute($st);
                $dbError = mysqli_stmt_error($st);
                $affected = mysqli_stmt_affected_rows($st);
                mysqli_stmt_close($st);

                // affected_rows = 0 tetap dianggap berhasil apabila data memang sama.
                if ($ok) {
                    redirect('c_siswa.php?msg=updated');
                }
                $error = 'Gagal memperbarui siswa: ' . $dbError;
            }
        }
    } elseif ($action === 'hapus') {
        $nis = trim($_POST['nis'] ?? '');

        if ($nis === '') {
            $error = 'NIS siswa tidak ditemukan.';
        } elseif (!siswa_exists($conn, $nis)) {
            $error = 'Data siswa sudah tidak ada.';
        } elseif (siswa_count_laporan($conn, $nis) > 0) {
            $error = 'Siswa tidak dapat dihapus karena sudah memiliki data pengaduan.';
        } else {
            $st = mysqli_prepare($conn, 'DELETE FROM siswa WHERE nis=? LIMIT 1');
            if (!$st) {
                $error = 'Gagal menyiapkan proses hapus siswa: ' . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($st, 's', $nis);
                $ok = mysqli_stmt_execute($st);
                $affected = mysqli_stmt_affected_rows($st);
                $dbError = mysqli_stmt_error($st);
                mysqli_stmt_close($st);

                if ($ok && $affected > 0) {
                    redirect('c_siswa.php?msg=deleted');
                }
                $error = 'Data siswa gagal dihapus' . ($dbError ? ': ' . $dbError : '.');
            }
        }
    }
}

if (!empty($_GET['msg'])) {
    $messages = [
        'added' => 'Siswa berhasil ditambahkan.',
        'updated' => 'Data siswa berhasil diperbarui.',
        'deleted' => 'Data siswa berhasil dihapus.'
    ];
    $success = $messages[$_GET['msg']] ?? '';
}

if ($editing === null && isset($_GET['edit']) && $_GET['edit'] !== '') {
    $editing = ambil_siswa($conn, trim($_GET['edit']));
    if (!$editing) {
        $error = 'Data siswa yang ingin diedit tidak ditemukan.';
    }
}

$q = trim($_GET['q'] ?? '');
$rows = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $st = mysqli_prepare($conn, "SELECT s.nis,s.nama,s.kelas,COUNT(a.id_aspirasi) AS jumlah_laporan
        FROM siswa s
        LEFT JOIN aspirasi a ON a.nis=s.nis
        WHERE s.nis LIKE ? OR s.nama LIKE ? OR s.kelas LIKE ?
        GROUP BY s.nis,s.nama,s.kelas
        ORDER BY s.nama ASC");
    mysqli_stmt_bind_param($st, 'sss', $like, $like, $like);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
} else {
    $res = mysqli_query($conn, "SELECT s.nis,s.nama,s.kelas,COUNT(a.id_aspirasi) AS jumlah_laporan
        FROM siswa s
        LEFT JOIN aspirasi a ON a.nis=s.nis
        GROUP BY s.nis,s.nama,s.kelas
        ORDER BY s.nama ASC");
}

while ($row = mysqli_fetch_assoc($res)) {
    $rows[] = $row;
}
if (isset($st) && $st instanceof mysqli_stmt) mysqli_stmt_close($st);

include __DIR__ . '/../view/admin/siswa.php';
