<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Daftar Periode
            </h2>
            <flux:button variant="primary" href="{{ route('admin.periode.create') }}" wire:navigate>Tambah Periode</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama periode..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nama Periode</flux:table.column>
                    <flux:table.column>Mulai</flux:table.column>
                    <flux:table.column>Selesai</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($periodes as $periode)
                        <flux:table.row>
                            <flux:table.cell>{{ $periode->nama }}</flux:table.cell>
                            <flux:table.cell>{{ \Carbon\Carbon::parse($periode->mulai)->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell>{{ \Carbon\Carbon::parse($periode->selesai)->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell>
                                @if($periode->aktif)
                                    <flux:badge color="green">Aktif</flux:badge>
                                @else
                                    <flux:badge color="gray">Nonaktif</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" href="{{ route('admin.periode.edit', $periode->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $periode->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus periode ini?" icon="trash"></flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center">Data tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $periodes->links() }}
            </div>
        </div>
    </div>
</div>
