<?php
$models = [
    'Siswa.php',
    'Barang.php',
    'Tabungan.php',
    'Tagihan.php',
    'Kas.php',
    'Penjualan.php',
    'Pembelian.php',
    'Transaksi.php',
    'Pengaduan.php',
    'Supplier.php',
    'User.php',
    'Kelas.php',
    'Periode.php',
    'Anggota.php',
];

$dir = __DIR__ . '/app/Models/';

foreach ($models as $model) {
    $path = $dir . $model;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Skip if already has CatatAktivitas
        if (strpos($content, 'use App\Traits\CatatAktivitas;') !== false) {
            continue;
        }

        // Add import
        $content = preg_replace('/(namespace App\\\\Models;.*?)(use |class )/s', "$1use App\Traits\CatatAktivitas;\n$2", $content, 1);

        // Add use statement inside class
        $content = preg_replace('/(class [a-zA-Z0-9_]+ extends [a-zA-Z0-9_]+\s*\{)/', "$1\n    use CatatAktivitas;", $content, 1);

        file_put_contents($path, $content);
        echo "Updated $model\n";
    }
}
