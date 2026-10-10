<div class="bg-gray-50 min-h-screen pb-24">
    <!-- Header -->
    <div class="px-6 pt-10 pb-4 bg-white shadow-sm border-b border-gray-100 sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 transition">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <h2 class="font-bold text-xl text-gray-800">Saldo Tabungan</h2>
        </div>
    </div>

    <!-- Tabungan List -->
    <div class="px-6 pt-6 space-y-4">
        @php
            $totalSaldo = $tabungans->sum('saldo');
        @endphp

        <!-- Total Summary Card -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden mb-6">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <p class="text-emerald-100 text-sm font-medium mb-1">Total Saldo Keseluruhan</p>
                <h3 class="text-3xl font-extrabold tracking-tight">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h3>
            </div>
        </div>

        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider mb-2 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
            Rincian per Jenis Tabungan
        </h3>

        @forelse ($tabungans as $tabungan)
            <div class="bg-white rounded-2xl shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 p-5 transition hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <flux:icon name="wallet" class="size-6" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-base mb-0.5">{{ optional($tabungan->kas)->nama ?? 'Tabungan' }}</h4>
                        <div class="text-emerald-600 font-bold text-lg">
                            Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-50">
                    <a href="{{ route('siswa.tabungan.riwayat') }}" wire:navigate class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition bg-gray-50 hover:bg-emerald-50 py-2.5 rounded-xl">
                        <flux:icon name="clock" class="size-4" />
                        Lihat Riwayat
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center p-4 bg-white shadow-sm border border-gray-100 rounded-full mb-3 text-gray-400">
                    <flux:icon name="wallet" class="size-8" />
                </div>
                <div class="text-gray-500 text-sm font-medium">Belum ada data tabungan.</div>
            </div>
        @endforelse
    </div>
</div>
