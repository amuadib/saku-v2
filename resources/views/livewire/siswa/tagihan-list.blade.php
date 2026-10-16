<div class="bg-gray-50 min-h-screen pb-24">
    <!-- Header -->
    <div class="px-6 pt-10 pb-4 bg-white shadow-xs border-b border-gray-100 sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 transition active:scale-95" title="Kembali">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">Tagihan</h2>
                <p class="text-[11px] text-gray-400">Daftar tanggungan pembayaran siswa</p>
            </div>
        </div>
    </div>

    <!-- Summary Card: Total Tagihan Belum Lunas -->
    <div class="px-6 pt-5">
        <div class="bg-gradient-to-br from-rose-600 via-rose-500 to-amber-600 rounded-3xl p-5 text-white shadow-xl shadow-rose-900/10 relative overflow-hidden">
            <!-- Card Background Decoration -->
            <div class="absolute -right-6 -top-6 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="absolute -left-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between mb-2">
                <div class="flex items-center gap-2 text-rose-100 text-xs font-semibold">
                    <div class="size-6 rounded-lg bg-white/20 backdrop-blur-md flex items-center justify-center">
                        <flux:icon name="banknotes" class="size-3.5 text-white" />
                    </div>
                    Total Tagihan Belum Lunas
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-md text-white border border-white/20">
                    {{ $countBelumLunas }} Tagihan
                </span>
            </div>

            <div class="relative z-10 text-[28px] font-black tracking-tight text-white mb-0.5">
                Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}
            </div>

            @if($totalTelahBayar > 0)
                <div class="relative z-10 pt-2.5 mt-2.5 border-t border-white/20 flex items-center justify-between text-xs text-rose-100">
                    <span>Total Tagihan: Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                    <span class="text-white font-semibold">Telah Dibayar: Rp {{ number_format($totalTelahBayar, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Tagihan List (Hanya Belum Lunas) -->
    <div class="px-6 pt-5 space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Daftar Tagihan Belum Lunas</h3>
            @if($countBelumLunas > 0)
                <span class="text-xs font-medium text-rose-600">{{ $countBelumLunas }} tertunda</span>
            @endif
        </div>

        @forelse ($tagihans as $tagihan)
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4 transition-all hover:shadow-md hover:border-rose-100">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="size-11 rounded-2xl flex items-center justify-center shrink-0 bg-rose-50 text-rose-600">
                            <flux:icon name="document-text" class="size-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-gray-800 truncate">{{ optional($tagihan->kas)->nama ?? 'Tagihan' }}</div>
                            <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1.5">
                                <span>{{ $tagihan->created_at->format('d M Y') }}</span>
                                <span>&bull;</span>
                                <span class="font-mono text-[10px] text-gray-400">{{ $tagihan->kode }}</span>
                            </div>
                            @if($tagihan->keterangan)
                                <div class="text-[12px] text-gray-500 mt-1 leading-tight truncate">{{ $tagihan->keterangan }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-extrabold text-sm text-gray-800">
                            Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}
                        </div>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold tracking-wider bg-rose-50 text-rose-700 border border-rose-200/80">
                            BELUM LUNAS
                        </span>
                    </div>
                </div>

                @if($tagihan->bayar > 0)
                    <div class="mt-3 border-t border-gray-50 pt-2.5 flex justify-between items-center text-xs">
                        <span class="text-gray-400 text-[11px]">Telah dibayar: <strong class="text-gray-700 font-medium">Rp {{ number_format($tagihan->bayar, 0, ',', '.') }}</strong></span>
                        <span class="font-bold text-rose-600 text-xs">Sisa Rp {{ number_format($tagihan->jumlah - $tagihan->bayar, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xs text-center my-4">
                <div class="size-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                    <flux:icon name="check-circle" class="size-8" variant="solid" />
                </div>
                <h4 class="font-bold text-gray-800 text-base">Semua Tagihan Lunas! 🎉</h4>
                <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto leading-relaxed">
                    Alhamdulillah, saat ini Anda tidak memiliki tagihan yang belum dibayar. Terima kasih telah menyelesaikan pembayaran tepat waktu.
                </p>
            </div>
        @endforelse
        
        @if($tagihans->hasPages())
            <div class="pt-4">
                {{ $tagihans->links() }}
            </div>
        @endif
    </div>
</div>
