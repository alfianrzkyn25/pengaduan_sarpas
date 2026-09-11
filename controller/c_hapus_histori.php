<?php
require_once __DIR__ . '/../config/database.php';
require_role('admin');

$id = (int)($_POST['id_histori'] ?? 0);
$back = $_POST['back'] ?? '../controller/c_histori.php';

if ($id > 0) {
    $st = mysqli_prepare($conn, 'DELETE FROM histori WHERE id_histori=?');
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
}

redirect($back . '?deleted=1');
