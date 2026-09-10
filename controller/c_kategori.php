<?php
require_once __DIR__ . '/../config/database.php'; require_role('admin'); $user=current_user();
$r=mysqli_query($conn,"SELECT k.id_kategori,k.nama_kategori,k.ket_kategori,COUNT(a.id_aspirasi) jumlah FROM kategori k LEFT JOIN aspirasi a ON a.id_kategori=k.id_kategori GROUP BY k.id_kategori,k.nama_kategori,k.ket_kategori ORDER BY k.nama_kategori ASC");
include __DIR__.'/../view/admin/kategori.php';
