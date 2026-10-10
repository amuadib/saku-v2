<div class="bg-gray-50 min-h-screen pb-24">
    <!-- Header -->
    <div class="px-6 pt-10 pb-4 bg-white shadow-sm border-b border-gray-100 sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 transition">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <h2 class="font-bold text-xl text-gray-800">Tagihan</h2>
        </div>
        
        <!-- Category Pills -->
        <div class="flex gap-2 mt-5 overflow-x-auto pb-2 scrollbar-hide" style="scrollbar-width: none;">
            <button wire:click="setStatus('Semua')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $status === 'Semua' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Semua</button>
            <button wire:click="setStatus('Belum Lunas')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $status === 'Belum Lunas' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Belum Lunas</button>
            <button wire:click="setStatus('Lunas')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $status === 'Lunas' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Lunas</button>
        </div>
    </div>

    <!-- Tagihan List -->
    <div class="px-6 pt-6 space-y-3">
        @forelse ($tagihans as $tagihan)
            @php
                $isLunas = $tagihan->isLunas();
            @endphp
            <div class="bg-white rounded-2xl shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 p-4 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl {{ $isLunas ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                            <flux:icon name="document-text" class="size-5" />
                        </div>
                        <div>
                            <div class="font-bold text-sm text-gray-800">{{ optional($tagihan->kas)->nama ?? 'Tagihan' }}</div>
                            <div class="text-[11px] text-gray-400 mt-0.5">{{ $tagihan->created_at->format('d M Y') }} • {{ $tagihan->kode }}</div>
                            @if($tagihan->keterangan)
                                <div class="text-[12px] text-gray-500 mt-1 leading-tight">{{ $tagihan->keterangan }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-sm text-gray-800">
                            Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] font-semibold mt-1 {{ $isLunas ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $isLunas ? 'LUNAS' : 'BELUM LUNAS' }}
                        </div>
                    </div>
                </div>
                @if(!$isLunas && $tagihan->bayar > 0)
                <div class="mt-3 border-t border-gray-50 pt-2 flex justify-between items-center text-xs">
                    <span class="text-gray-500">Telah dibayar:</span>
                    <span class="font-medium text-gray-800">Rp {{ number_format($tagihan->bayar, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center p-4 bg-white shadow-sm border border-gray-100 rounded-full mb-3 text-gray-400">
                    <flux:icon name="document-text" class="size-8" />
                </div>
                <div class="text-gray-500 text-sm font-medium">Belum ada data tagihan.</div>
            </div>
        @endforelse
        
        @if($tagihans->hasPages())
            <div class="pt-4">
                {{ $tagihans->links() }}
            </div>
        @endif
    </div>
</div>
