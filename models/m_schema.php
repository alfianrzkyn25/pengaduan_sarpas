<?php
/**
 * Helper schema compatibility untuk database versi lama/baru.
 * Beberapa database lama memakai kolom siswa.nama, sedangkan versi baru memakai siswa.nama_siswa.
 */
function table_columns(mysqli $conn, string $table): array {
    $columns = [];
    $tableSafe = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `{$tableSafe}`");
    if (!$result) return $columns;
    while ($row = mysqli_fetch_assoc($result)) $columns[] = $row['Field'];
    return $columns;
}

function first_existing_column(array $columns, array $candidates): ?string {
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $columns, true)) return $candidate;
    }
    return null;
}

function qident(string $name): string {
    return '`' . str_replace('`', '``', $name) . '`';
}

function siswa_schema(mysqli $conn): array {
    $columns = table_columns($conn, 'siswa');
    $nis = first_existing_column($columns, ['nis','NIS','id_siswa']);
    $name = first_existing_column($columns, ['nama_siswa','nama','nama_lengkap','name']);
    $class = first_existing_column($columns, ['kelas','class','rombel']);
    if (!$nis || !$name) {
        throw new RuntimeException('Struktur tabel siswa tidak sesuai. Dibutuhkan kolom NIS dan nama siswa (nama_siswa atau nama).');
    }
    return ['nis'=>$nis, 'name'=>$name, 'class'=>$class];
}
