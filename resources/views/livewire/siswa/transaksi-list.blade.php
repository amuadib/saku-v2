<div class="bg-gray-50 min-h-screen pb-24">
    <!-- Header -->
    <div class="px-6 pt-10 pb-4 bg-white shadow-sm border-b border-gray-100 sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 transition">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <h2 class="font-bold text-xl text-gray-800">Semua Transaksi Pembayaran</h2>
        </div>
        
        <!-- Category Pills -->
        <div class="flex gap-2 mt-5 overflow-x-auto pb-2 scrollbar-hide" style="scrollbar-width: none;">
            <button wire:click="setKategori('Semua')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === 'Semua' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Semua</button>
            <button wire:click="setKategori('Tabungan')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === 'Tabungan' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Tabungan</button>
            <button wire:click="setKategori('Tagihan')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === 'Tagihan' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Tagihan</button>
            <button wire:click="setKategori('Penjualan')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === 'Penjualan' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Koperasi</button>
        </div>
    </div>

    <!-- Transaksi List -->
    <div class="px-6 pt-6 space-y-3">
        @forelse ($transaksis as $transaksi)
            @php
                $jenis = class_basename($transaksi->transable_type);
                $isMasuk = str_starts_with($transaksi->kode, 'M');
                
                // Color & Icon mapping based on transaction type
                $icon = 'arrows-right-left';
                $colorClass = 'bg-gray-50 text-gray-600';
                $textClass = 'text-gray-800';
                
                if ($jenis === 'Tabungan') {
                    $icon = $isMasuk ? 'arrow-trending-up' : 'arrow-trending-down';
                    $colorClass = $isMasuk ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600';
                    $textClass = $isMasuk ? 'text-emerald-600' : 'text-red-600';
                } elseif ($jenis === 'Tagihan') {
                    $icon = 'document-text';
                    $colorClass = 'bg-blue-50 text-blue-600';
                    $textClass = 'text-blue-600';
                } elseif ($jenis === 'Penjualan') {
                    $icon = 'shopping-bag';
                    $colorClass = 'bg-orange-50 text-orange-600';
                    $textClass = 'text-orange-600';
                }
            @endphp
            <div class="bg-white rounded-2xl shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 p-4 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl {{ $colorClass }}">
                            <flux:icon name="{{ $icon }}" class="size-5" />
                        </div>
                        <div>
                            <div class="font-bold text-sm text-gray-800">
                                @if($jenis === 'Tabungan')
                                    {{ $isMasuk ? 'Setoran Tabungan' : 'Penarikan Tabungan' }}
                                @elseif($jenis === 'Tagihan')
                                    Pembayaran Tagihan
                                @elseif($jenis === 'Penjualan')
                                    Belanja Toko/Kantin
                                @else
                                    Transaksi
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-400 mt-0.5">{{ $transaksi->created_at->format('d M Y, H:i') }} • {{ $transaksi->kode }}</div>
                            @if($transaksi->keterangan)
                                <div class="text-[12px] text-gray-500 mt-1 leading-tight">{{ $transaksi->keterangan }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-sm {{ $textClass }}">
                            @if($jenis === 'Tabungan')
                                {{ $isMasuk ? '+' : '-' }}Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                            @else
                                Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center p-4 bg-white shadow-sm border border-gray-100 rounded-full mb-3 text-gray-400">
                    <flux:icon name="arrows-right-left" class="size-8" />
                </div>
                <div class="text-gray-500 text-sm font-medium">Belum ada data transaksi.</div>
            </div>
        @endforelse
        
        @if($transaksis->hasPages())
            <div class="pt-4">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>
</div>
