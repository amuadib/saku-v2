<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Transaksi (Buku Besar)
            </h2>
            <flux:button variant="primary" href="{{ route('admin.transaksi.create') }}" wire:navigate icon="plus">
                Transaksi Manual
            </flux:button>
        </div>
    </x-slot>

    <!-- Stats Carousel -->
    @if(isset($stats))
        <x-stat-carousel :cols="4" class="mb-6">
            <x-stat-card
                title="Masuk Hari Ini"
                :value="'Rp ' . number_format($stats['masukHariIni'], 0, ',', '.')"
                icon="arrow-trending-up"
                color="emerald"
                description="Arus kas masuk hari ini"
            />
            <x-stat-card
                title="Keluar Hari Ini"
                :value="'Rp ' . number_format($stats['keluarHariIni'], 0, ',', '.')"
                icon="arrow-trending-down"
                color="rose"
                description="Arus kas keluar hari ini"
            />
            <x-stat-card
                title="Transaksi Hari Ini"
                :value="number_format($stats['transaksiHariIni'])"
                icon="clock"
                color="indigo"
                description="Total mutasi tercatat hari ini"
            />
            <x-stat-card
                title="Total Seluruh Mutasi"
                :value="number_format($stats['totalSemua'])"
                icon="circle-stack"
                color="zinc"
                description="Seluruh riwayat buku besar"
            />
        </x-stat-carousel>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode atau keterangan..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <x-responsive-view>
                <x-slot:desktop>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Kode / Waktu</flux:table.column>
                            <flux:table.column>Jenis</flux:table.column>
                            <flux:table.column>Nominal</flux:table.column>
                            <flux:table.column>Keterangan</flux:table.column>
                            <flux:table.column>Petugas</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($transaksis as $transaksi)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="font-medium">{{ $transaksi->kode ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $transaksi->created_at->format('d/m/Y H:i') }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if (class_basename($transaksi->transable_type) === 'Tabungan')
                                            <flux:badge color="green">Tabungan</flux:badge>
                                        @elseif (class_basename($transaksi->transable_type) === 'Tagihan')
                                            <flux:badge color="red">Tagihan</flux:badge>
                                        @elseif (class_basename($transaksi->transable_type) === 'Penjualan')
                                            <flux:badge color="blue">Penjualan</flux:badge>
                                        @elseif (class_basename($transaksi->transable_type) === 'Kas')
                                            <flux:badge color="yellow">{{ $transaksi->transable->nama ?? 'Kas' }}</flux:badge>
                                        @else
                                            <flux:badge color="zinc">Manual</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="font-semibold {{ ($transaksi->kode[0] ?? '') == 'M' || $transaksi->jumlah > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ ($transaksi->kode[0] ?? '') == 'M' || $transaksi->jumlah > 0 ? '+' : '-' }}Rp {{ number_format(abs($transaksi->jumlah), 0, ',', '.') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        {{ $transaksi->keterangan }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ optional($transaksi->petugas)->authable->nama ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if(!str_starts_with($transaksi->keterangan, '[REVERSAL]') && !in_array($transaksi->kode, $reversedOriginalKodes))
                                            <flux:button size="sm" variant="danger" wire:click="reversal('{{ $transaksi->id }}')" wire:confirm="Apakah Anda yakin ingin melakukan reversal transaksi ini? Saldo akan dikembalikan." icon="arrow-uturn-left"></flux:button>
                                        @elseif(in_array($transaksi->kode, $reversedOriginalKodes))
                                            <flux:badge color="red" size="sm">Reversed</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="6" class="text-center">Data tidak ditemukan.</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </x-slot:desktop>

                <x-slot:mobile>
                    @forelse ($transaksis as $transaksi)
                        @php
                            $isMasuk = ($transaksi->kode[0] ?? '') == 'M' || $transaksi->jumlah > 0;
                            $petugasName = optional($transaksi->petugas)->authable->nama ?? null;
                            $subtitle = $transaksi->created_at->format('d/m/Y H:i') . ($petugasName ? ' • ' . $petugasName : '');
                            $isReversed = in_array($transaksi->kode, $reversedOriginalKodes);
                            $isReversal = str_starts_with($transaksi->keterangan, '[REVERSAL]');
                        @endphp
                        <x-mobile-card
                            :title="$transaksi->kode ?? '-'"
                            :subtitle="$subtitle"
                            :icon="$isMasuk ? 'arrow-trending-up' : 'arrow-trending-down'"
                            :avatar-color="$isMasuk ? 'emerald' : 'rose'"
                        >
                            <x-slot:badge>
                                @if (class_basename($transaksi->transable_type) === 'Tabungan')
                                    <flux:badge color="green" size="sm">Tabungan</flux:badge>
                                @elseif (class_basename($transaksi->transable_type) === 'Tagihan')
                                    <flux:badge color="red" size="sm">Tagihan</flux:badge>
                                @elseif (class_basename($transaksi->transable_type) === 'Penjualan')
                                    <flux:badge color="blue" size="sm">Penjualan</flux:badge>
                                @elseif (class_basename($transaksi->transable_type) === 'Kas')
                                    <flux:badge color="yellow" size="sm">{{ $transaksi->transable->nama ?? 'Kas' }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Manual</flux:badge>
                                @endif
                            </x-slot:badge>

                            <div class="space-y-2">
                                <div class="flex items-baseline justify-between pt-1">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Nominal</span>
                                    <span class="text-base font-bold {{ $isMasuk ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $isMasuk ? '+' : '-' }}Rp {{ number_format(abs($transaksi->jumlah), 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($transaksi->keterangan)
                                    <div class="text-xs text-zinc-700 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-900/60 p-2.5 rounded-xl border border-zinc-200/70 dark:border-zinc-700/60">
                                        {{ $transaksi->keterangan }}
                                    </div>
                                @endif
                            </div>

                            @if(!$isReversal && !$isReversed)
                                <x-slot:action>
                                    <flux:button size="sm" variant="danger" wire:click="reversal('{{ $transaksi->id }}')" wire:confirm="Apakah Anda yakin ingin melakukan reversal transaksi ini? Saldo akan dikembalikan." icon="arrow-uturn-left">
                                        Reversal
                                    </flux:button>
                                </x-slot:action>
                            @elseif($isReversed)
                                <x-slot:action>
                                    <flux:badge color="red" size="sm">Reversed</flux:badge>
                                </x-slot:action>
                            @endif
                        </x-mobile-card>
                    @empty
                        <div class="p-8 text-center bg-white dark:bg-zinc-800/80 rounded-2xl border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400">
                            Data transaksi tidak ditemukan.
                        </div>
                    @endforelse
                </x-slot:mobile>
            </x-responsive-view>

            <div class="mt-4">
                {{ $transaksis->links() }}
            </div>
        </div>
    </div>
</div>
