<?php

$config = [
    'app' => [
        'nama' => 'Sistem Administrasi Keuangan',
        'singkatan' => 'SAKU',
        'keterangan' => 'Sistem Administrasi Keuangan (SAKU) SDI & SMPI Miftahul Ulum',
    ],
    'kertas_printer' => [
        'orientasi' => env('ORIENTASI_KERTAS_PRINTER', 'portrait'), // portrait,landscape
        'ukuran' => env('UKURAN_KERTAS_PRINTER', '58'), // A4,F4,80,58,
    ],
    'api_akademik' => [
        'url' => env('API_AKADEMIK_URL'),
        'token' => env('API_AKADEMIK_TOKEN'),
    ],
    'roles' => [
        1 => 'Admin',
        2 => 'Yayasan',
        3 => 'Kepala Sekolah',
        4 => 'Bendahara',
        5 => 'Siswa',
        6 => 'Orang Tua',
        99 => 'Default',
    ],
    'jam_kerja' => [
        'Senin - Sabtu: 07:00 - 13:30',
        'Jum\'at: 07:00 - 10:00',
        'Hari Ahad & Libur Nasional Tutup',
    ],
    'lembaga' => [
        1 => 'SDI Miftahul Ulum Klemunan',
        2 => 'SMPI Miftahul Ulum',
        3 => 'SMAI Miftahul Ulum',
        99 => 'Yayasan Bastomiyah Rahman',
    ],
    'tingkat' => [
        1 => [1, 2, 3, 4, 5, 6],
        2 => [7, 8, 9],
        99 => [],
    ],
    'kontak_lembaga' => [
        1 => [
            'singkatan' => 'SDI',
            'alamat' => 'Jl. Manggar Lingk. Jatikeplek',
            'kontak' => '',
            'telp' => '',
            'lat' => 0,
            'lon' => 0,
        ],
        2 => [
            'singkatan' => 'SMPI',
            'alamat' => 'Jl. Manggar Lingk. Jatikeplek',
            'kontak' => '',
            'telp' => '',
            'lat' => 0,
            'lon' => 0,
        ],
        99 => [
            'singkatan' => 'YPIB',
            'alamat' => 'Jl. Manggar Lingk. Jatikeplek',
            'kontak' => '',
            'telp' => '',
            'lat' => 0,
            'lon' => 0,
        ],
    ],
    'siswa' => [
        'status' => [
            1 => 'Aktif',
            2 => 'Mutasi',
            3 => 'Lulus',
            99 => 'Non Aktif',
        ],
        'label' => [
            1 => 'Yatim',
            2 => 'Piatu',
            11 => 'Ikut Tahfid',
            12 => 'Pondok',
            21 => 'Keluarga Pegawai Yayasan',
        ],
    ],
    'barang' => [
        'jenis' => [
            'SRG' => 'Seragam',
            'AKS' => 'Aksesoris',
            'LKS' => 'Lembar Kerja Siswa',
            'USM' => 'Buku Usmani',
            'BKU' => 'Buku lain',
            'LLN' => 'Lain-lain',
        ],
        'satuan' => [
            'PCS' => 'Pcs',
            'STL' => 'Setel',
            'PKT' => 'Paket',
            'BKS' => 'Bungkus',
        ],
    ],
    'pembayaran' => [
        'tun' => 'Tunai',
        'tag' => 'Tagihan',
        'tab' => 'Tabungan',
    ],

    'tabungan' => [
        'potongan' => [
            'lembaga' => [1, 2],
            'kas_admin_id' => '',
            'min_saldo_tidak_kena_admin' => 5000,
            'jumlah_per_tahun' => 1000,
            'tanggal' => '2024-01-01',
        ],
    ],
    'cetak_struk' => [
        'mode' => env('MODE_CETAK_STRUK'),
    ],
];
$local_config = [];
@include storage_path().'/app/local_config.php';

return array_merge($config, $local_config);
