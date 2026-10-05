<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Kas
            </h2>
            <flux:button variant="primary" href="{{ route('admin.kas.create') }}" wire:navigate icon="plus">Tambah Kas</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex flex-col sm:flex-row gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama kas..." icon="magnifying-glass" class="w-full sm:w-1/3" />
                <flux:select wire:model.live="filter_lembaga" placeholder="Semua Lembaga" class="w-full sm:w-1/4">
                    <flux:select.option value="">Semua Lembaga</flux:select.option>
                    @foreach(config('custom.lembaga') as $id => $nama)
                        <flux:select.option value="{{ $id }}">{{ $nama }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nama Kas</flux:table.column>
                    <flux:table.column>Lembaga</flux:table.column>
                    <flux:table.column>Keterangan</flux:table.column>
                    <flux:table.column>Saldo</flux:table.column>
                    <flux:table.column>Ada Tagihan</flux:table.column>
                    <flux:table.column>Tabungan</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($kas_items as $kas)
                        <flux:table.row>
                            <flux:table.cell>{{ $kas->nama }}</flux:table.cell>
                            <flux:table.cell>{{ config('custom.lembaga.'.$kas->lembaga_id) }}</flux:table.cell>
                            <flux:table.cell>{{ Str::limit($kas->keterangan, 50) }}</flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($kas->saldo, 0, ',', '.') }}</flux:table.cell>
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
                                <flux:button size="sm" href="{{ route('admin.kas.edit', $kas->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $kas->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus kas ini?" icon="trash"></flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center">Data tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $kas_items->links() }}
            </div>
        </div>
    </div>
</div>
