<?php
// Halaman Kelola Siswa sekarang berada langsung di view/admin/siswa.php.
// File ini hanya menjaga kompatibilitas untuk tautan/bookmark lama.
require_once __DIR__ . '/../config/database.php';
$query = $_SERVER['QUERY_STRING'] ?? '';
redirect('../view/admin/siswa.php' . ($query !== '' ? '?' . $query : ''));
