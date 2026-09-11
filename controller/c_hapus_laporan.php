<?php
require_once __DIR__ . '/../config/database.php';
require_role('siswa');
$user = current_user();

$id = (int)($_POST['id_aspirasi'] ?? 0);

if ($id > 0) {
    // Pastikan laporan milik siswa ini dan statusnya masih Menunggu
    $st = mysqli_prepare($conn, 'SELECT id_aspirasi, status FROM aspirasi WHERE id_aspirasi=? AND nis=?');
    mysqli_stmt_bind_param($st, 'is', $id, $user['nis']);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);

    if ($row && $row['status'] === 'Menunggu') {
        mysqli_begin_transaction($conn);

        // Hapus histori terkait
        $st = mysqli_prepare($conn, 'DELETE FROM histori WHERE id_aspirasi=?');
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);

        // Hapus lampiran terkait
        $st = mysqli_prepare($conn, 'DELETE FROM lampiran WHERE id_aspirasi=?');
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);

        // Hapus aspirasi
        $st = mysqli_prepare($conn, 'DELETE FROM aspirasi WHERE id_aspirasi=? AND nis=?');
        mysqli_stmt_bind_param($st, 'is', $id, $user['nis']);
        mysqli_stmt_execute($st);
        mysqli_stmt_close($st);

        mysqli_commit($conn);
        redirect('../controller/c_user_laporan.php?deleted=1');
    }
}

redirect('../controller/c_user_laporan.php?error=1');
