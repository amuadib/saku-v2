<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Tagihan - {{ $siswa->nama }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Print Base Settings */
        @media print {
            @page {
                size: {{ in_array($kertas, ['58', '80']) ? $kertas.'mm auto' : $kertas.' '.$orientasi }};
                margin: {{ in_array($kertas, ['58', '80']) ? '0' : '10mm' }};
            }
            body {
                background-color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            /* Hide UI elements during print */
            .no-print {
                display: none !important;
            }
        }

        /* Thermal Printer Base Style */
        .thermal-print {
            width: {{ $kertas }}mm;
            margin: 0 auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.2;
            color: black;
        }

        /* Standard A4/F4 Base Style */
        .standard-print {
            max-width: {{ $orientasi === 'landscape' ? '297mm' : '210mm' }};
            margin: 0 auto;
            background-color: white;
            color: black;
            padding: 20px;
        }

        /* Helpers */
        .dashed-line {
            border-top: 1px dashed black;
            margin: 10px 0;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen text-black" onload="window.print()">

    <!-- UI Button for manual print (hidden on actual print) -->
    <div class="no-print p-4 flex justify-center bg-white shadow mb-8 fixed top-0 w-full z-50">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Sekarang
        </button>
    </div>

    <!-- PADDING FOR FIXED HEADER -->
    <div class="no-print h-20"></div>

    @if(in_array($kertas, ['58', '80']))
        <!-- ================= THERMAL LAYOUT ================= -->
        <div class="thermal-print p-2 bg-white shadow print:shadow-none print:p-0">
            <div class="text-center font-bold text-sm mb-1 uppercase">
                {{ config('custom.lembaga.'.$siswa->lembaga_id, 'Lembaga') }}
            </div>
            <div class="text-center text-xs mb-2">
                 {{ config('custom.kontak_lembaga.'.$siswa->lembaga_id.'.alamat', 'Alamat') }}
            </div>
            <div class="dashed-line"></div>
            <div class="text-center text-xs mb-2 font-bold underline">
                Tagihan Pembayaran
            </div>

            <div class="mb-2">
                <div class="flex justify-between">
                    <span>Waktu:</span>
                    <span>{{ date('d/m/Y H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Nama:</span>
                    <span class="text-right">{{ Str::limit($siswa->nama, 25) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Kelas:</span>
                    <span>{{ $siswa->kelas->nama ?? '-' }}</span>
                </div>
            </div>

            <div class="dashed-line"></div>
            <div class="font-bold mb-1">Rincian Belum Lunas:</div>
            
            @php $total_tagihan = 0; @endphp
            @forelse($siswa->tagihan as $t)
                @php 
                    $sisa = $t->jumlah - ($t->bayar ?? 0); 
                    $total_tagihan += $sisa;
                @endphp
                <div class="flex justify-between mb-1 items-start">
                    <div class="w-3/5 pr-1">
                        {{ $t->kas->nama ?? '-' }} <span class="text-[10px]">({{ $t->created_at->format('d/m/Y') }})</span>
                        @if($t->keterangan) <br><span class="text-[10px]">{{ $t->keterangan }} </span> @endif
                    </div>
                    <div class="w-2/5 text-right">{{ number_format($sisa, 0, ',', '.') }}</div>
                </div>
            @empty
                <div class="text-center py-2">Tidak ada tagihan</div>
            @endforelse

            <div class="dashed-line"></div>
            
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($total_tagihan, 0, ',', '.') }}</span>
            </div>

            <div class="dashed-line"></div>
            
            <div class="text-center text-[10px] mt-4 mb-2">
                Struk ini merupakan bukti tagihan yang harus dibayar.<br>
                Mohon bayar tagihan tepat waktu.<br>
                <b>Terima kasih.</b>
            </div>
            <div class="text-center text-[10px] text-gray-400">
                dicetak oleh {{ auth()->user()->name }}
            </div>
        </div>

    @else
        <!-- ================= STANDARD (A4/F4) LAYOUT ================= -->
        <div class="standard-print shadow-lg print:shadow-none bg-white">
            <!-- Header (Kop Surat) -->
            @php
                $kop_path = public_path('kop_lembaga_'.$siswa->lembaga_id.'.jpg');
                $kop_url = asset('kop_lembaga_'.$siswa->lembaga_id.'.jpg');
            @endphp
            
            
                @if(file_exists($kop_path))
                    <div class="mb-6">
                        <img src="{{ $kop_url }}" alt="Kop Lembaga" class="w-full h-auto max-h-32 object-contain">
                    </div>
                @else<div class="mb-6 border-b-4 border-black pb-4">
                    <div class="text-center">
                        <h1 class="text-2xl font-bold uppercase">{{ config('custom.lembaga.'.$siswa->lembaga_id, 'Miftahul Ulum') }}</h1>
                        <p class="text-sm">Sistem Administrasi Keuangan Terpadu</p>
                    </div>
            </div>
                @endif

            <div class="text-center mb-8">
                <h2 class="text-xl font-bold uppercase underline">Surat Pemberitahuan Tagihan</h2>
            </div>

            <!-- Student Info -->
            <div class="mb-6">
                <table class="text-sm w-full">
                    <tr>
                        <td class="py-1 w-32 font-semibold">Nama Siswa</td>
                        <td class="py-1 w-4">:</td>
                        <td class="py-1 uppercase font-bold">{{ $siswa->nama }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold">NIS/NISN</td>
                        <td class="py-1">:</td>
                        <td class="py-1">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold">Kelas</td>
                        <td class="py-1">:</td>
                        <td class="py-1">{{ $siswa->kelas->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold">Tanggal Cetak</td>
                        <td class="py-1">:</td>
                        <td class="py-1">{{ date('d F Y') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Table of Bills -->
            <table class="w-full text-sm border-collapse border border-gray-400 mb-8">
                <thead>
                    <tr class="bg-gray-100 text-center font-bold">
                        <th class="border border-gray-400 py-2 px-3 w-12">No</th>
                        <th class="border border-gray-400 py-2 px-3">Jenis Tagihan</th>
                        <th class="border border-gray-400 py-2 px-3">Keterangan</th>
                        <th class="border border-gray-400 py-2 px-3">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total_tagihan = 0; @endphp
                    @forelse($siswa->tagihan as $index => $t)
                        @php 
                            $sisa = $t->jumlah - ($t->bayar ?? 0); 
                            $total_tagihan += $sisa;
                        @endphp
                        <tr>
                            <td class="border border-gray-400 py-2 px-3 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-400 py-2 px-3 font-medium">{{ $t->kas->nama ?? '-' }}</td>
                            <td class="border border-gray-400 py-2 px-3">{{ $t->keterangan ?? '-' }}</td>
                            <td class="border border-gray-400 py-2 px-3 text-right tabular-nums">{{ number_format($sisa, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border border-gray-400 py-4 px-3 text-center italic">Tidak ada tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td colspan="3" class="border border-gray-400 py-3 px-3 text-right">TOTAL KEKURANGAN TAGIHAN:</td>
                        <td class="border border-gray-400 py-3 px-3 text-right text-lg">Rp {{ number_format($total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Signatures -->
            <div class="flex justify-end mt-12 text-sm">
                <div class="text-center w-64">
                    <p class="mb-20">Blitar, {{ date('d F Y') }}<br>Petugas Administrasi,</p>
                    <p class="font-bold underline">{{ auth()->user()->name ?? '.......................' }}</p>
                </div>
            </div>
            
            <div class="mt-12 pt-4 border-t border-gray-300 text-xs text-gray-500 italic text-center">
                * Dokumen ini dicetak secara otomatis oleh {{ config('custom.app.singkatan') }} pada {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    @endif

</body>
</html>
