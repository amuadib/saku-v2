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

    'lembaga' => [
        1 => [
            'nama' => 'SDI Miftahul Ulum Klemunan',
            'lat' => 0,
            'lon' => 0,
            'telp' => '089697110580',
            'alamat' => 'Jl. Manggar Lingk. Jatikeplek',
            'kontak' => 'Nada Rahman, S. Pd. I.',
            'singkatan' => 'SDI',
            'logo' => '/images/sdi.png',
            'kop' => '/images/kop-sdi.png',
            'tingkat' => [1, 2, 3, 4, 5, 6],
            'jam_kerja' => [
                'Senin - Sabtu: 07:00 - 13:30',
                'Jum\'at: 07:00 - 10:00',
                'Hari Ahad & Libur Nasional Tutup',
            ],
        ],
        2 => [
            'nama' => 'SMPI Miftahul Ulum',
            'lat' => 0,
            'lon' => 0,
            'telp' => '081335562970',
            'alamat' => 'Jl. Manggar Lingk. Jatikeplek',
            'kontak' => 'Lisna Yunita, S. H. I',
            'singkatan' => 'SMPI',
            'logo' => '/images/smpi.png',
            'kop' => '/images/kop-smpi.png',
            'tingkat' => [7, 8, 9],
            'jam_kerja' => [
                'Senin - Sabtu: 07:00 - 13:30',
                'Jum\'at: 07:00 - 10:00',
                'Hari Ahad & Libur Nasional Tutup',
            ],
        ],
        3 => [
            'nama' => 'SMAI Miftahul Ulum',
            'lat' => 0,
            'lon' => 0,
            'telp' => '08113789495',
            'alamat' => 'Jl. Manggar Lingk. Jatikeplek',
            'kontak' => 'Drs. Rinuji Sulaksono',
            'singkatan' => 'SMAI',
            'logo' => '/images/smai.png',
            'kop' => '/images/kop-smai.png',
            'tingkat' => [10, 11, 12],
            'jam_kerja' => [
                'Senin - Sabtu: 07:00 - 13:30',
                'Jum\'at: 07:00 - 10:00',
                'Hari Ahad & Libur Nasional Tutup',
            ],
        ],
        99 => [
            'nama' => 'Yayasan Bastomiyah Rahman',
            'lat' => 0,
            'lon' => 0,
            'telp' => '',
            'alamat' => 'Jl. Manggar Lingk. Jatikeplek',
            'kontak' => '',
            'singkatan' => 'YPIB',
            'logo' => '/images/ypib.png',
            'kop' => '/images/kop-ypib.png',
            'tingkat' => [],
            'jam_kerja' => [
                'Senin - Sabtu: 07:00 - 13:30',
                'Jum\'at: 07:00 - 10:00',
                'Hari Ahad & Libur Nasional Tutup',
            ],
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
