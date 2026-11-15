<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Kas
            </h2>
            <flux:button variant="primary" href="{{ route('admin.kas.create') }}" wire:navigate icon="plus">Tambah Kas</flux:button>
        </div>
    </x-slot>

    <!-- Stats Carousel -->
    @if(isset($stats))
        <x-stat-carousel :cols="4" class="mb-6">
            <x-stat-card
                title="Total Saldo Kas"
                :value="'Rp ' . number_format($stats['totalSaldo'], 0, ',', '.')"
                icon="banknotes"
                color="emerald"
                description="Akumulasi saldo seluruh akun kas"
            />
            <x-stat-card
                title="Total Akun Kas"
                :value="number_format($stats['totalKas'])"
                icon="wallet"
                color="indigo"
                description="Jumlah buku kas terdaftar"
            />
            <x-stat-card
                title="Kas Tagihan"
                :value="number_format($stats['kasTagihan'])"
                icon="document-text"
                color="amber"
                description="Kas penerima pos tagihan"
            />
            <x-stat-card
                title="Kas Tabungan"
                :value="number_format($stats['kasTabungan'])"
                icon="arrow-down-tray"
                color="sky"
                description="Kas penampung simpanan siswa"
            />
        </x-stat-carousel>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex flex-col sm:flex-row gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama kas..." icon="magnifying-glass" class="w-full sm:w-1/3" />
                <flux:select wire:model.live="filter_lembaga" placeholder="Semua Lembaga" class="w-full sm:w-1/4">
                    <flux:select.option value="">Semua Lembaga</flux:select.option>
                    @foreach(config('custom.lembaga') as $id => $val)
                        <flux:select.option value="{{ $id }}">{{ $val['nama'] ?? $val }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            @if (session()->has('error'))
                <flux:callout variant="danger" class="mb-4">
                    {{ session('error') }}
                </flux:callout>
            @endif

            <x-responsive-view>
                <x-slot:desktop>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Nama Kas</flux:table.column>
                            <flux:table.column>Lembaga</flux:table.column>
                            <flux:table.column>Keterangan</flux:table.column>
                            <flux:table.column>Saldo</flux:table.column>
                            <flux:table.column>Ada Tagihan</flux:table.column>
                            <flux:table.column>Tabungan</flux:table.column>
                            <flux:table.column>Penjualan</flux:table.column>
                            <flux:table.column>Setoran</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($kas_items as $kas)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium text-zinc-900 dark:text-zinc-100">{{ $kas->nama }}</flux:table.cell>
                                    <flux:table.cell>{{ config('custom.lembaga.'.$kas->lembaga_id.'.nama') }}</flux:table.cell>
                                    <flux:table.cell>{{ Str::limit($kas->keterangan, 50) }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($kas->saldo, 0, ',', '.') }}
                                            </span>
                                            @if($kas->setoran && $kas->saldo > 0)
                                                <flux:button size="xs" wire:click="setorDana('{{ $kas->id }}')" wire:confirm="Apakah Anda yakin ingin menyetor dana ini? Saldo kas akan di-reset menjadi 0." icon="banknotes" color="green" tooltip="Setor Dana"></flux:button>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($kas->ada_tagihan)
                                            <flux:badge color="green">Ya</flux:badge>
                                        @else
                                            <flux:badge color="gray">Tidak</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($kas->tabungan)
                                            <flux:badge color="green">Ya</flux:badge>
                                        @else
                                            <flux:badge color="gray">Tidak</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($kas->penjualan)
                                            <flux:badge color="green">Ya</flux:badge>
                                        @else
                                            <flux:badge color="gray">Tidak</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($kas->setoran)
                                            <flux:badge color="green">Ya</flux:badge>
                                        @else
                                            <flux:badge color="gray">Tidak</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button size="sm" href="{{ route('admin.kas.edit', $kas->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                        <flux:button size="sm" variant="danger" wire:click="delete('{{ $kas->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus kas ini?" icon="trash"></flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="9" class="text-center">Data tidak ditemukan.</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </x-slot:desktop>

                <x-slot:mobile>
                    @forelse ($kas_items as $kas)
                        @php
                            $lembagaNama = config('custom.lembaga.'.$kas->lembaga_id.'.nama') ?? '-';
                        @endphp
                        <x-mobile-card
                            :title="$kas->nama"
                            :subtitle="$lembagaNama"
                            icon="wallet"
                            avatar-color="indigo"
                        >
                            <x-slot:badge>
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($kas->saldo, 0, ',', '.') }}
                                </span>
                            </x-slot:badge>

                            <div class="space-y-2 pt-1">
                                <!-- Fitur Pills -->
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($kas->ada_tagihan)
                                        <flux:badge color="green" size="sm">Tagihan</flux:badge>
                                    @endif
                                    @if($kas->tabungan)
                                        <flux:badge color="blue" size="sm">Tabungan</flux:badge>
                                    @endif
                                    @if($kas->penjualan)
                                        <flux:badge color="yellow" size="sm">Penjualan</flux:badge>
                                    @endif
                                    @if($kas->setoran)
                                        <flux:badge color="purple" size="sm">Setoran</flux:badge>
                                    @endif
                                </div>

                                @if($kas->keterangan)
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900/50 p-2.5 rounded-lg border border-zinc-100 dark:border-zinc-800">
                                        {{ $kas->keterangan }}
                                    </div>
                                @endif
                            </div>

                            <x-slot:action>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($kas->setoran && $kas->saldo > 0)
                                        <flux:button size="sm" wire:click="setorDana('{{ $kas->id }}')" wire:confirm="Apakah Anda yakin ingin menyetor dana ini? Saldo kas akan di-reset menjadi 0." icon="banknotes" color="green">
                                            Setor Dana
                                        </flux:button>
                                    @endif
                                    <flux:button size="sm" href="{{ route('admin.kas.edit', $kas->id) }}" wire:navigate icon="pencil" color="yellow">
                                        Edit
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $kas->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus kas ini?" icon="trash">
                                        Hapus
                                    </flux:button>
                                </div>
                            </x-slot:action>
                        </x-mobile-card>
                    @empty
                        <div class="p-8 text-center bg-white dark:bg-zinc-800/80 rounded-2xl border border-zinc-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400">
                            Data kas tidak ditemukan.
                        </div>
                    @endforelse
                </x-slot:mobile>
            </x-responsive-view>

            <div class="mt-4">
                {{ $kas_items->links() }}
            </div>
        </div>
    </div>
</div>
