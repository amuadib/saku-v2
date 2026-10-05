<?php

namespace App\Livewire\Admin;

use App\Models\Kas;
use App\Models\Tagihan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        if (auth()->user()->role_id == 5) {
            return $this->renderSiswa();
        }
        // 1. Saldo Kas
        $kasData = Kas::where('saldo', '>', 0)->get(['nama', 'saldo']);
        $kasLabels = $kasData->pluck('nama')->toArray();
        $kasValues = $kasData->pluck('saldo')->toArray();

        // 2. Tagihan
        $tagihanData = Tagihan::join('kas', 'tagihan.kas_id', '=', 'kas.id')
            ->selectRaw('kas.nama, SUM(tagihan.jumlah - tagihan.bayar) as sisa_tagihan')
            ->whereColumn('tagihan.jumlah', '>', 'tagihan.bayar')
            ->groupBy('kas.id', 'kas.nama')
            ->get();

        $tagihanLabels = $tagihanData->pluck('nama')->toArray();
        $tagihanValues = $tagihanData->pluck('sisa_tagihan')->toArray();

        // 3. Weekly Transactions
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $transactions = Transaksi::whereBetween('created_at', [$startDate, $endDate])->get();

        $dailyData = collect();
        $dates = [];

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays(6 - $i)->format('Y-m-d');
            $dates[] = $date;
            $dailyData->put($date, [
                'tanggal' => Carbon::parse($date)->format('d M Y'),
                'masuk' => 0,
                'keluar' => 0,
                'saldo' => 0,
            ]);
        }

        foreach ($transactions as $t) {
            $date = Carbon::parse($t->created_at)->format('Y-m-d');
            if (! $dailyData->has($date)) {
                continue;
            }

            $isMasuk = false;
            if (in_array($t->transable_type, ['App\\Models\\Tagihan', 'App\\Models\\Penjualan'])) {
                $isMasuk = true;
            } elseif (in_array($t->transable_type, ['App\\Models\\Pembelian', 'App\\Models\\Pengaduan'])) {
                $isMasuk = false;
            } else {
                $isMasuk = $t->jumlah > 0;
            }

            $amount = abs($t->jumlah);

            $day = $dailyData[$date];
            if ($isMasuk) {
                $day['masuk'] += $amount;
            } else {
                $day['keluar'] += $amount;
            }
            $day['saldo'] = $day['masuk'] - $day['keluar'];
            $dailyData[$date] = $day;
        }

        $chartDates = [];
        $chartMasuk = [];
        $chartKeluar = [];
        foreach ($dates as $d) {
            $chartDates[] = Carbon::parse($d)->format('d M');
            $chartMasuk[] = $dailyData[$d]['masuk'];
            $chartKeluar[] = $dailyData[$d]['keluar'];
        }

        return view('livewire.admin.dashboard', [
            'kasLabels' => $kasLabels,
            'kasValues' => $kasValues,
            'tagihanLabels' => $tagihanLabels,
            'tagihanValues' => $tagihanValues,
            'chartDates' => $chartDates,
            'chartMasuk' => $chartMasuk,
            'chartKeluar' => $chartKeluar,
            'rekapTable' => $dailyData->values()->toArray(),
        ])->layout('components.admin-layout', ['header' => 'Dashboard']);
    }

    private function renderSiswa()
    {
        $siswa = auth()->user()->authable;
        $saldo = $siswa->tabungan()->sum('saldo');

        return view('livewire.siswa.dashboard', [
            'saldo' => $saldo,
            'siswa' => $siswa,
        ])->layout('layouts.siswa', ['title' => 'Dashboard Siswa']);
    }
}
