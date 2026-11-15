<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Pembelian
            </h2>
            <flux:button variant="primary" href="{{ route('admin.pembelian.create') }}" wire:navigate icon="plus">Tambah Pembelian</flux:button>
        </div>
    </x-slot>

    <!-- Stats Carousel -->
    @if(isset($stats))
        <x-stat-carousel :cols="4" class="mb-6">
            <x-stat-card
                title="Pembelian Hari Ini"
                :value="'Rp ' . number_format($stats['totalHariIni'], 0, ',', '.')"
                icon="banknotes"
                color="emerald"
                description="Total belanja masuk hari ini"
            />
            <x-stat-card
                title="Transaksi Hari Ini"
                :value="number_format($stats['transaksiHariIni'])"
                icon="shopping-bag"
                color="indigo"
                description="Faktur pembelian tercatat hari ini"
            />
            <x-stat-card
                title="Pembelian Bulan Ini"
                :value="'Rp ' . number_format($stats['totalBulanIni'], 0, ',', '.')"
                icon="chart-bar"
                color="amber"
                description="Akumulasi belanja bulan berjalan"
            />
            <x-stat-card
                title="Total Seluruh Pembelian"
                :value="number_format($stats['totalSemua'])"
                icon="circle-stack"
                color="zinc"
                description="Semua faktur transaksi pembelian"
            />
        </x-stat-carousel>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode transaksi atau supplier..." icon="magnifying-glass" class="w-full sm:w-1/3" />
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
                            <flux:table.column>Supplier</flux:table.column>
                            <flux:table.column>Detail Barang</flux:table.column>
                            <flux:table.column>Total Transaksi</flux:table.column>
                            <flux:table.column>Petugas</flux:table.column>
                            <flux:table.column class="text-center w-16">Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($pembelians as $pembelian)
                                @php
                                    $itemCount = $pembelian->detail->count();
                                    $totalQty = $pembelian->detail->sum('jumlah');
                                @endphp
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $pembelian->kode }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $pembelian->created_at->format('d/m/Y H:i') }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ optional($pembelian->supplier)->nama ?? '-' }}</div>
                                        @if(optional($pembelian->supplier)->hp)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 font-mono">{{ $pembelian->supplier->hp }}</div>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="space-y-1.5 max-w-sm py-1">
                                            <div class="flex items-center gap-1.5">
                                                <flux:badge size="sm" color="zinc" class="font-medium text-[11px]">
                                                    {{ $itemCount }} item ({{ $totalQty }} unit)
                                                </flux:badge>
                                            </div>

                                            @if($itemCount > 0)
                                                <div class="space-y-1">
                                                    @foreach($pembelian->detail->take(2) as $d)
                                                        <div class="text-xs flex items-center justify-between gap-3 text-zinc-700 dark:text-zinc-300">
                                                            <span class="truncate font-medium text-zinc-900 dark:text-zinc-100" title="{{ optional($d->barang)->nama ?? '-' }}">
                                                                {{ optional($d->barang)->nama ?? '-' }}
                                                            </span>
                                                            <span class="shrink-0 text-zinc-500 dark:text-zinc-400 tabular-nums font-mono text-[11px]">
                                                                {{ $d->jumlah }} {{ optional($d->barang)->satuan ?? 'PCS' }} × Rp {{ number_format($d->harga, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    @endforeach

                                                    @if($itemCount > 2)
                                                        <div x-data="{ open: false }">
                                                            <button type="button" @click="open = !open" class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                                                <span x-text="open ? 'Sembunyikan' : '+ {{ $itemCount - 2 }} barang lainnya'"></span>
                                                            </button>
                                                            <div x-show="open" x-cloak class="mt-1 space-y-1 pt-1 border-t border-dashed border-zinc-200 dark:border-zinc-700">
                                                                @foreach($pembelian->detail->skip(2) as $d)
                                                                    <div class="text-xs flex items-center justify-between gap-3 text-zinc-700 dark:text-zinc-300">
                                                                        <span class="truncate font-medium text-zinc-900 dark:text-zinc-100" title="{{ optional($d->barang)->nama ?? '-' }}">
                                                                            {{ optional($d->barang)->nama ?? '-' }}
                                                                        </span>
                                                                        <span class="shrink-0 text-zinc-500 dark:text-zinc-400 tabular-nums font-mono text-[11px]">
                                                                            {{ $d->jumlah }} {{ optional($d->barang)->satuan ?? 'PCS' }} × Rp {{ number_format($d->harga, 0, ',', '.') }}
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-zinc-400 italic">Tidak ada item</span>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="font-bold text-base text-emerald-600 dark:text-emerald-400 tabular-nums">
                                            Rp {{ number_format($pembelian->total, 0, ',', '.') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                            {{ optional($pembelian->petugas)->authable->nama ?? optional($pembelian->petugas)->username ?? '-' }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-center">
                                        <flux:button size="sm" variant="danger" wire:click="delete('{{ $pembelian->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus transaksi ini beserta seluruh detailnya?" icon="trash" tooltip="Hapus Transaksi"></flux:button>
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
                    @forelse ($pembelians as $pembelian)
                        @php
                            $supplierNama = optional($pembelian->supplier)->nama ?? 'Supplier Umum';
                            $petugasName = optional($pembelian->petugas)->authable->nama ?? optional($pembelian->petugas)->username ?? null;
                            $subtitle = ($pembelian->kode ?? '-') . ' • ' . $pembelian->created_at->format('d/m/Y H:i') . ($petugasName ? ' • ' . $petugasName : '');
                            $itemCount = $pembelian->detail->count();
                            $totalQty = $pembelian->detail->sum('jumlah');
                        @endphp
                        <x-mobile-card
                            :title="$supplierNama"
                            :subtitle="$subtitle"
                            icon="truck"
                            avatar-color="amber"
                        >
                            <x-slot:badge>
                                <span class="text-base font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                    Rp {{ number_format($pembelian->total, 0, ',', '.') }}
                                </span>
                            </x-slot:badge>

                            <div class="space-y-2 pt-1 border-t border-zinc-100 dark:border-zinc-700/60">
                                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Rincian Barang:</span>
                                    <span class="font-semibold text-zinc-600 dark:text-zinc-400 text-[11px]">
                                        {{ $itemCount }} item ({{ $totalQty }} unit)
                                    </span>
                                </div>

                                @if($itemCount > 0)
                                    <div class="bg-zinc-50 dark:bg-zinc-900/60 rounded-xl p-2.5 space-y-1.5 border border-zinc-100 dark:border-zinc-800">
                                        @foreach($pembelian->detail->take(2) as $d)
                                            <div class="text-xs flex items-center justify-between gap-2">
                                                <div class="truncate text-zinc-900 dark:text-zinc-100 font-medium">
                                                    {{ optional($d->barang)->nama ?? '-' }}
                                                </div>
                                                <div class="shrink-0 text-zinc-500 dark:text-zinc-400 tabular-nums text-[11px] font-mono">
                                                    {{ $d->jumlah }} {{ optional($d->barang)->satuan ?? 'PCS' }} × Rp {{ number_format($d->harga, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        @endforeach

                                        @if($itemCount > 2)
                                            <div x-data="{ open: false }" class="pt-1">
                                                <button type="button" @click="open = !open" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                                    <span x-text="open ? 'Tutup rincian' : '+ Lihat {{ $itemCount - 2 }} barang lainnya'"></span>
                                                </button>
                                                <div x-show="open" x-cloak class="mt-1.5 space-y-1.5 pt-1.5 border-t border-dashed border-zinc-200 dark:border-zinc-700">
                                                    @foreach($pembelian->detail->skip(2) as $d)
                                                        <div class="text-xs flex items-center justify-between gap-2">
                                                            <div class="truncate text-zinc-900 dark:text-zinc-100 font-medium">
                                                                {{ optional($d->barang)->nama ?? '-' }}
                                                            </div>
                                                            <div class="shrink-0 text-zinc-500 dark:text-zinc-400 tabular-nums text-[11px] font-mono">
                                                                {{ $d->jumlah }} {{ optional($d->barang)->satuan ?? 'PCS' }} × Rp {{ number_format($d->harga, 0, ',', '.') }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-zinc-400 italic">Tidak ada item tercatat.</div>
                                @endif
                            </div>

                            <x-slot:action>
                                <div class="flex items-center justify-end w-full">
                                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $pembelian->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus transaksi ini beserta seluruh detailnya?" icon="trash">
                                        Hapus
                                    </flux:button>
                                </div>
                            </x-slot:action>
                        </x-mobile-card>
                    @empty
                        <div class="p-8 text-center bg-white dark:bg-zinc-800/80 rounded-2xl border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400">
                            Data pembelian tidak ditemukan.
                        </div>
                    @endforelse
                </x-slot:mobile>
            </x-responsive-view>

            <div class="mt-4">
                {{ $pembelians->links() }}
            </div>
        </div>
    </div>
</div>
