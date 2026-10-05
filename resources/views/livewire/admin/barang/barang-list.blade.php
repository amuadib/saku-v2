<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Daftar Barang
            </h2>
            <flux:button variant="primary" href="{{ route('admin.barang.create') }}" wire:navigate>Tambah Barang</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode atau nama barang..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Nama</flux:table.column>
                    <flux:table.column>Jenis</flux:table.column>
                    <flux:table.column>Stok</flux:table.column>
                    <flux:table.column>Harga</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($barangs as $barang)
                        <flux:table.row>
                            <flux:table.cell>{{ $barang->kode }}</flux:table.cell>
                            <flux:table.cell>{{ $barang->nama }}</flux:table.cell>
                            <flux:table.cell>{{ $barang->jenis }}</flux:table.cell>
                            <flux:table.cell>{{ $barang->stok }} {{ $barang->satuan }}</flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($barang->harga, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" href="{{ route('admin.barang.edit', $barang->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $barang->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus barang ini?" icon="trash"></flux:button>
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
                {{ $barangs->links() }}
            </div>
        </div>
    </div>
</div>
