<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function cetakTagihan(Siswa $siswa)
    {
        $siswa->load([
            'kelas',
            'tagihan' => function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('bayar')->orWhereColumn('bayar', '<', 'jumlah');
                })->orderBy('created_at', 'desc')->with('kas');
            },
        ]);

        return view('print.tagihan', [
            'siswa' => $siswa,
            'kertas' => config('custom.kertas_printer.ukuran'),
            'orientasi' => config('custom.kertas_printer.orientasi'),
        ]);
    }

    public function cetakKwitansi(Siswa $siswa, Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));

        $siswa->load([
            'kelas',
            'tagihan' => function ($query) use ($ids) {
                $query->whereIn('id', $ids)->with('kas', 'transaksi');
            },
        ]);

        return view('print.kwitansi', [
            'siswa' => $siswa,
            'kertas' => config('custom.kertas_printer.ukuran'),
            'orientasi' => config('custom.kertas_printer.orientasi'),
        ]);
    }

    public function cetakTransaksi(Siswa $siswa, Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));

        $transaksis = Transaksi::whereIn('id', $ids)->get();

        return view('print.transaksi', [
            'siswa' => $siswa,
            'transaksis' => $transaksis,
            'kertas' => config('custom.kertas_printer.ukuran'),
            'orientasi' => config('custom.kertas_printer.orientasi'),
        ]);
    }
}
