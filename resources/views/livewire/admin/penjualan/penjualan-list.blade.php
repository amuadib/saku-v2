<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Penjualan
            </h2>
            <flux:button variant="primary" href="{{ route('admin.penjualan.create') }}" wire:navigate icon="plus">
                Transaksi Penjualan Baru
            </flux:button>
        </div>
    </x-slot>

    <!-- Stats Carousel -->
    @if(isset($stats))
        <x-stat-carousel :cols="4" class="mb-6">
            <x-stat-card
                title="Omset Hari Ini"
                :value="'Rp ' . number_format($stats['omsetHariIni'], 0, ',', '.')"
                icon="banknotes"
                color="emerald"
                description="Total penjualan hari ini"
            />
            <x-stat-card
                title="Transaksi Hari Ini"
                :value="number_format($stats['transaksiHariIni'])"
                icon="shopping-bag"
                color="indigo"
                description="Jumlah transaksi hari ini"
            />
            <x-stat-card
                title="Omset Bulan Ini"
                :value="'Rp ' . number_format($stats['omsetBulanIni'], 0, ',', '.')"
                icon="chart-bar"
                color="amber"
                description="Total omset bulan berjalan"
            />
            <x-stat-card
                title="Total Penjualan"
                :value="number_format($stats['totalSemua'])"
                icon="circle-stack"
                color="zinc"
                description="Seluruh transaksi penjualan"
            />
        </x-stat-carousel>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode transaksi, atau nama siswa..." icon="magnifying-glass" class="w-full sm:w-1/3" />
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
                            <flux:table.column>Kode / Tanggal</flux:table.column>
                            <flux:table.column>Siswa</flux:table.column>
                            <flux:table.column>Total Transaksi</flux:table.column>
                            <flux:table.column>Tipe Pembayaran</flux:table.column>
                            <flux:table.column>Petugas</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($penjualans as $penjualan)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="font-medium flex items-center gap-2">
                                            {{ $penjualan->kode ?? '-' }}
                                            @if(isset($penjualan->status) && $penjualan->status === 'batal')
                                                <flux:badge color="red" size="sm">Batal</flux:badge>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $penjualan->created_at->format('d/m/Y H:i') }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="font-medium">{{ optional($penjualan->siswa)->nama ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">NIS: {{ optional($penjualan->siswa)->nis ?? '-' }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if($penjualan->pembayaran == 'tun')
                                            <flux:badge color="green">Tunai</flux:badge>
                                        @elseif($penjualan->pembayaran == 'tab')
                                            <flux:badge color="blue">Tabungan</flux:badge>
                                        @elseif($penjualan->pembayaran == 'tag')
                                            <flux:badge color="red">Tagihan</flux:badge>
                                        @else
                                            <flux:badge color="gray">{{ $penjualan->pembayaran }}</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>{{ optional($penjualan->petugas)->authable->nama ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button size="sm" href="{{ route('admin.penjualan.edit', $penjualan->id) }}" wire:navigate icon="eye" tooltip="Detail Penjualan"></flux:button>
                                        <flux:button size="sm" variant="danger" wire:click="delete('{{ $penjualan->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus transaksi ini beserta detailnya?" icon="trash"></flux:button>
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
                    @forelse ($penjualans as $penjualan)
                        @php
                            $siswaNama = optional($penjualan->siswa)->nama ?? 'Non-Siswa / Umum';
                            $petugasName = optional($penjualan->petugas)->authable->nama ?? null;
                            $subtitle = ($penjualan->kode ?? '-') . ' • ' . $penjualan->created_at->format('d/m/Y H:i') . ($petugasName ? ' • ' . $petugasName : '');
                            
                            $color = match($penjualan->pembayaran) {
                                'tun' => 'emerald',
                                'tab' => 'sky',
                                'tag' => 'rose',
                                default => 'zinc',
                            };
                        @endphp
                        <x-mobile-card
                            :title="$siswaNama"
                            :subtitle="$subtitle"
                            :avatar-color="$color"
                        >
                            <x-slot:badge>
                                <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                    @if(isset($penjualan->status) && $penjualan->status === 'batal')
                                        <flux:badge color="red" size="sm">Batal</flux:badge>
                                    @endif

                                    @if($penjualan->pembayaran == 'tun')
                                        <flux:badge color="green" size="sm">Tunai</flux:badge>
                                    @elseif($penjualan->pembayaran == 'tab')
                                        <flux:badge color="blue" size="sm">Tabungan</flux:badge>
                                    @elseif($penjualan->pembayaran == 'tag')
                                        <flux:badge color="red" size="sm">Tagihan</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ $penjualan->pembayaran }}</flux:badge>
                                    @endif
                                </div>
                            </x-slot:badge>

                            <div class="space-y-1 pt-1">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Total Transaksi</span>
                                    <span class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($penjualan->total, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if(optional($penjualan->siswa)->nis)
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        NIS: {{ $penjualan->siswa->nis }}
                                    </div>
                                @endif
                            </div>

                            <x-slot:action>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" href="{{ route('admin.penjualan.edit', $penjualan->id) }}" wire:navigate icon="eye">
                                        Detail
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $penjualan->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus transaksi ini beserta detailnya?" icon="trash">
                                        Hapus
                                    </flux:button>
                                </div>
                            </x-slot:action>
                        </x-mobile-card>
                    @empty
                        <div class="p-8 text-center bg-white dark:bg-zinc-800/80 rounded-2xl border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400">
                            Data penjualan tidak ditemukan.
                        </div>
                    @endforelse
                </x-slot:mobile>
            </x-responsive-view>

            <div class="mt-4">
                {{ $penjualans->links() }}
            </div>
        </div>
    </div>
</div>
