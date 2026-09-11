<?php
require_once __DIR__ . '/../config/database.php';
require_role('siswa');
$user = current_user();

$id_lampiran  = (int)($_POST['id_lampiran']  ?? 0);
$id_aspirasi  = (int)($_POST['id_aspirasi']  ?? 0);

if ($id_lampiran > 0 && $id_aspirasi > 0) {
    // Pastikan lampiran ini memang milik aspirasi si siswa yang masih Menunggu
    $st = mysqli_prepare($conn,
        'SELECT l.url_file FROM lampiran l
         JOIN aspirasi a ON a.id_aspirasi=l.id_aspirasi
         WHERE l.id_lampiran=? AND l.id_aspirasi=? AND a.nis=? AND a.status="Menunggu"');
    mysqli_stmt_bind_param($st, 'iis', $id_lampiran, $id_aspirasi, $user['nis']);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);

    if ($row) {
        // Hapus file fisik
        $path = __DIR__ . '/../' . $row['url_file'];
        if (file_exists($path)) @unlink($path);

        // Hapus record
        $st = mysqli_prepare($conn, 'DELETE FROM lampiran WHERE id_lampiran=?');
        mysqli_stmt_bind_param($st, 'i', $id_lampiran);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
    }
}

redirect('../controller/c_user_edit.php?id=' . $id_aspirasi);
