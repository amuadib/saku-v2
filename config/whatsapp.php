<?php

return [
    'WHATSAPP_NOTIFICATION' => env('WHATSAPP_NOTIFICATION', false),
    'WHATSAPP_TEST_NUMBER' => env('WHATSAPP_TEST_NUMBER', '6281234567890'),
    'WA_API_URL' => env('WA_API_URL', 'https://api.whatsapp.com'),
    'WA_API_TOKEN' => env('WA_API_TOKEN', 'your_api_token_here'),
    'WA_API_TOKEN_12' => env('WA_API_TOKEN_12', 'your_api_token_here'),
    'WA_API_TOKEN_34' => env('WA_API_TOKEN_34', 'your_api_token_here'),
    'WA_API_TOKEN_56' => env('WA_API_TOKEN_56', 'your_api_token_here'),
    'template' => [
        'awal' => '*Sistem Administrasi Keuangan (SAKU) SDI & SMPI Miftahul Ulum*

🗒️ Yth. Bapak/Ibu Wali siswa *{siswa.nama}*. '.PHP_EOL,
        'akhir' => '
Terima Kasih
        ',
        'akhir_bayar' => '
Terima kasih kami sampaikan.
Semoga Bapak/Ibu diberi rizki yang Lancar dan Barokah.
        ',
        'akhir_daftar' => '
Apabila terdapat *kesalahan* mohon konfirmasi ke Bagian TU {lembaga} ({kontak.nama}).
Selanjutnya tanda bukti pembayaran akan berupa Print Out (Kecuali Tahfid dan mobil).
Terima Kasih
        ',
        'awal_alumni' => '
Assalaamu\'alaikum Wr. Wb.
Semoga dalam lindungan Allah SWT serta diberikan kesehatan selalu🤲

🏫Berikut merupakan WA resmi sistem otomatis {lembaga} untuk para alumni.

Kami menginformasikan bahwasanya;

Ananda yang bernama *{siswa.nama}*

📋Memiliki *daftar pembayaran yang belum dilunasi (tanggungan pembayaran)* selama masih bersekolah di {lembaga}.
',
        'akhir_alumni' => '

🙏Mohon maaf apabila masih ada tanggungan maka *ijazah masih kami tangguhkan* (belum bisa kami berikan)

🖋️Apabila terdapat kesalahan dalam jumlah ataupun hal² lain bisa segera konfirmasi di kantor {lembaga}*

Atas perhatiannya kami sampaikan terima kasih dan mohon maaf.

Wassalaamu\'alaikum Wr. Wb
',
        'tagihan' => [
            'bayar' => '
Telah kami terima & *LUNAS* pembayaran atas tagihan *{tagihan.keterangan}* sejumlah *{tagihan.jumlah}*.',
            'bayar_banyak' => '
Telah kami terima & *LUNAS* pembayaran atas tagihan {tagihan.rincian} dengan total *{tagihan.total}*.',
            'daftar' => '🏫 Berikut informasi resmi terkait tanggungan ananda.'.PHP_EOL.'
{tagihan.rincian}Dengan total tagihan *{tagihan.total}*.',
            'tabungan' => PHP_EOL.'
🗳️ Ananda mempunyai tabungan sebanyak *{tabungan.total}*'.PHP_EOL,
            'daftar_alumni' => '
Berikut informasi tanggungan ananda.
{tagihan.rincian}Total tagihan *{tagihan.total}*.',
        ],
        'tabungan' => [
            'daftar' => '🏫 Berikut rincian Tabungan ananda.'.PHP_EOL.'
{tabungan.rincian}Dengan Saldo total *{tabungan.total}*.',
        ],
        'footer' => '
...
_Pesan ini dikirim otomatis oleh sistem, mohon tidak membalas pesan ke nomor ini_
        ',
    ],
];
